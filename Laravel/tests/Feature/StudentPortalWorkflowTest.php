<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Models\User;
use App\Notifications\FewSlotsNotification;
use App\Services\EnrollmentTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentPortalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_and_student_pages_require_authentication(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/courses')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Sign in to your account');

        $this->post('/register', [
            'name' => 'Sam Student',
            'email' => 'sam@example.com',
            'password' => 'SecurePass!2026',
            'password_confirmation' => 'SecurePass!2026',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sam@example.com', 'is_admin' => false]);
        $this->get('/dashboard')->assertOk()->assertSee('Welcome back, Sam.');
    }

    public function test_student_can_enroll_and_cancel_with_history_and_slot_updates(): void
    {
        $student = User::factory()->create();
        $course = $this->makeCourse();

        $this->actingAs($student)->post(route('enrollments.store'), [
            'course_id' => $course->id,
        ])->assertRedirect();

        $this->assertSame(29, $course->fresh()->available_slots);
        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseHas('enrollment_transactions', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'type' => 'enroll',
            'quantity' => -1,
        ]);

        $this->actingAs($student)->post(route('enrollments.store'), [
            'course_id' => $course->id,
        ])->assertSessionHasErrors('course_id');
        $this->assertDatabaseCount('enrollment_transactions', 1);

        $this->post(route('courses.cancel', $course))->assertRedirect(route('courses.show', $course));
        $this->assertSame(30, $course->fresh()->available_slots);
        $this->assertDatabaseMissing('enrollments', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseCount('enrollment_transactions', 2);
        $this->get(route('enrollments.index'))->assertOk()->assertSee('Cancel');
    }

    public function test_full_course_rejects_enrollment_and_keeps_the_database_unchanged(): void
    {
        $student = User::factory()->create();
        $course = $this->makeCourse(['available_slots' => 0]);

        $this->actingAs($student)->post(route('enrollments.store'), [
            'course_id' => $course->id,
        ])->assertSessionHasErrors('course_id');

        $this->assertSame(0, $course->fresh()->available_slots);
        $this->assertDatabaseCount('enrollment_transactions', 0);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_course_management_and_slot_adjustments_are_admin_only(): void
    {
        $student = User::factory()->create();
        $course = $this->makeCourse(['available_slots' => 20]);

        $this->actingAs($student)->get(route('courses.create'))->assertForbidden();
        $this->delete(route('courses.destroy', $course))->assertForbidden();
        $this->post(route('courses.adjust', $course), [
            'quantity' => 1,
            'notes' => 'Unauthorized attempt',
        ])->assertForbidden();

        $admin = $this->makeAdmin();
        $this->actingAs($admin)->post(route('courses.adjust', $course), [
            'quantity' => 2,
            'reference_number' => 'REG-2026-101',
            'notes' => 'Registrar approved two extra seats.',
        ])->assertRedirect(route('courses.show', $course));

        $this->assertSame(22, $course->fresh()->available_slots);
        $this->assertDatabaseHas('enrollment_transactions', [
            'type' => 'adjustment',
            'quantity' => 2,
            'reference_number' => 'REG-2026-101',
            'notes' => 'Registrar approved two extra seats.',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_create_and_delete_a_course_without_history(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('courses.store'), [
            'code' => 'DB301',
            'name' => 'Database Systems',
            'category' => 'Database',
            'instructor' => 'Faculty of Computing',
            'description' => 'Relational database design.',
            'maximum_slots' => 24,
            'minimum_slots' => 4,
        ])->assertRedirect();

        $course = Course::query()->where('code', 'DB301')->firstOrFail();
        $this->assertSame(24, $course->available_slots);
        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Delete course');

        $this->delete(route('courses.destroy', $course))
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_course_with_enrollment_history_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $student = User::factory()->create();
        $course = $this->makeCourse();

        app(EnrollmentTransactionService::class)->record(
            $course,
            $student,
            EnrollmentTransaction::TYPE_ENROLL,
            -1,
        );

        $this->actingAs($admin)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertDontSee('Delete course');

        $this->delete(route('courses.destroy', $course))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('enrollment_transactions', ['course_id' => $course->id]);
    }

    public function test_course_capacity_edits_are_recorded_as_slot_adjustments(): void
    {
        $admin = $this->makeAdmin();
        $course = $this->makeCourse(['available_slots' => 20]);

        $this->actingAs($admin)->put(route('courses.update', $course), [
            'code' => $course->code,
            'name' => $course->name,
            'category' => $course->category,
            'instructor' => 'Registrar Office',
            'description' => '',
            'maximum_slots' => 32,
            'minimum_slots' => 5,
        ])->assertRedirect(route('courses.show', $course));

        $this->assertSame(22, $course->fresh()->available_slots);
        $this->assertSame(32, $course->fresh()->maximum_slots);
        $this->assertDatabaseHas('enrollment_transactions', [
            'course_id' => $course->id,
            'user_id' => $admin->id,
            'type' => 'adjustment',
            'quantity' => 2,
        ]);

        $this->put(route('courses.update', $course), [
            'code' => $course->code,
            'name' => $course->name,
            'category' => $course->category,
            'maximum_slots' => 9,
            'minimum_slots' => 5,
        ])->assertSessionHasErrors('maximum_slots');
    }

    public function test_adjustment_that_exceeds_capacity_rolls_back_the_transaction(): void
    {
        $admin = $this->makeAdmin();
        $course = $this->makeCourse();

        $this->actingAs($admin)->post(route('courses.adjust', $course), [
            'quantity' => 1,
            'notes' => 'Should exceed max.',
        ])->assertSessionHasErrors('course_id');

        $this->assertSame(30, $course->fresh()->available_slots);
        $this->assertDatabaseCount('enrollment_transactions', 0);
    }

    public function test_few_slots_alert_is_sent_to_admins_when_course_crosses_threshold(): void
    {
        Notification::fake();
        $admin = $this->makeAdmin();
        $student = User::factory()->create();
        $course = $this->makeCourse(['available_slots' => 6]);

        app(EnrollmentTransactionService::class)->record(
            $course,
            $student,
            EnrollmentTransaction::TYPE_ADJUSTMENT,
            -1,
            null,
            'Reduce remaining seats for threshold test.',
        );

        Notification::assertSentTo($admin, FewSlotsNotification::class);
        $this->assertSame(5, $course->fresh()->available_slots);
    }

    public function test_dashboard_api_returns_statistics_and_course_data(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(2)->create();
        $course = $this->makeCourse(['available_slots' => 5]);

        $this->actingAs($admin)
            ->getJson(route('api.dashboard.stats'))
            ->assertOk()
            ->assertJsonPath('total_students', 2)
            ->assertJsonPath('total_courses', 1)
            ->assertJsonPath('almost_full_courses', 1);

        $this->getJson(route('api.courses.almost-full'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $course->id);

        $this->getJson(route('api.enrollments.recent'))
            ->assertOk()
            ->assertJsonPath('data', []);

        $this->get(route('dashboard'))->assertOk()->assertSee('Welcome back');
        $this->get(route('courses.index'))->assertOk()->assertSee($course->name);
        $this->get(route('courses.show', $course))->assertOk()->assertSee('Available seats');
        $this->get(route('reports.index'))->assertOk()->assertSee('Course reports');
    }

    public function test_student_sees_only_their_own_transaction_details(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->makeCourse();
        $transaction = app(EnrollmentTransactionService::class)->record(
            $course,
            $otherStudent,
            EnrollmentTransaction::TYPE_ENROLL,
            -1,
        );

        $this->actingAs($student)
            ->get(route('enrollments.show', $transaction))
            ->assertForbidden();

        $this->get(route('enrollments.index'))
            ->assertOk()
            ->assertDontSee($otherStudent->name);
    }

    /**
     * @param  array<string, int|string>  $overrides
     */
    private function makeCourse(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'code' => 'WEB'.fake()->unique()->numerify('###'),
            'name' => 'Web Systems and Technologies',
            'category' => 'Web Development',
            'maximum_slots' => 30,
            'available_slots' => 30,
            'minimum_slots' => 5,
        ], $overrides));
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }
}
