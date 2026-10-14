<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Models\User;
use App\Services\EnrollmentTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CourseEnrollmentFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_reports_its_slot_availability(): void
    {
        $course = Course::create([
            'code' => 'WEB101',
            'name' => 'Web Systems and Technologies',
            'maximum_slots' => 30,
            'available_slots' => 5,
            'minimum_slots' => 5,
        ]);

        $this->assertTrue($course->hasAvailableSlots());
        $this->assertTrue($course->isAlmostFull());
        $this->assertFalse($course->isFull());
        $this->assertSame('Almost Full', $course->getEnrollmentStatus());
        $this->assertSame('Almost Full', $course->enrollment_status);
        $this->assertTrue(Course::almostFull()->whereKey($course)->exists());

        $course->available_slots = 0;
        $course->save();

        $this->assertFalse($course->hasAvailableSlots());
        $this->assertFalse($course->isAlmostFull());
        $this->assertTrue($course->isFull());
        $this->assertSame('Full', $course->getEnrollmentStatus());
        $this->assertTrue(Course::full()->whereKey($course)->exists());
        $this->assertFalse(Course::available()->whereKey($course)->exists());
    }

    public function test_enrollment_transaction_is_linked_to_its_course_and_user(): void
    {
        $course = Course::create([
            'code' => 'WEB101',
            'name' => 'Web Systems and Technologies',
            'maximum_slots' => 30,
            'available_slots' => 30,
        ]);
        $user = User::factory()->create();
        $transaction = app(EnrollmentTransactionService::class)->record(
            $course,
            $user,
            EnrollmentTransaction::TYPE_ENROLL,
            -1,
        );

        $this->assertTrue($transaction->course->is($course));
        $this->assertTrue($transaction->user->is($user));
        $this->assertTrue($course->enrollmentTransactions->contains($transaction));
        $this->assertTrue($user->enrollmentTransactions->contains($transaction));
        $this->assertSame(29, $course->fresh()->available_slots);
    }

    public function test_cancellation_restores_a_slot_and_adjustments_obey_course_limits(): void
    {
        $course = Course::create([
            'code' => 'WEB101',
            'name' => 'Web Systems and Technologies',
            'maximum_slots' => 30,
            'available_slots' => 29,
        ]);
        $user = User::factory()->create();
        $service = app(EnrollmentTransactionService::class);

        $service->record($course, $user, EnrollmentTransaction::TYPE_CANCEL, 1);

        $this->assertSame(30, $course->fresh()->available_slots);

        try {
            $service->record($course, $user, EnrollmentTransaction::TYPE_ADJUSTMENT, 1);
            $this->fail('An adjustment must not exceed the course maximum.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('course_id', $exception->errors());
        }

        $this->assertSame(30, $course->fresh()->available_slots);
        $this->assertDatabaseCount('enrollment_transactions', 1);
    }

    public function test_full_course_rejects_enrollment_without_saving_a_transaction(): void
    {
        $course = Course::create([
            'code' => 'WEB101',
            'name' => 'Web Systems and Technologies',
            'maximum_slots' => 30,
            'available_slots' => 0,
        ]);
        $user = User::factory()->create();

        try {
            app(EnrollmentTransactionService::class)->record(
                $course,
                $user,
                EnrollmentTransaction::TYPE_ENROLL,
                -1,
            );
            $this->fail('A full course must reject enrollment.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('course_id', $exception->errors());
        }

        $this->assertSame(0, $course->fresh()->available_slots);
        $this->assertDatabaseCount('enrollment_transactions', 0);
    }
}
