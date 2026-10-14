<?php

namespace App\Services;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentTransactionService
{
    public function record(
        Course $course,
        User $user,
        string $type,
        int $quantity,
        ?string $referenceNumber = null,
        ?string $notes = null,
    ): EnrollmentTransaction {
        return DB::transaction(function () use ($course, $user, $type, $quantity, $referenceNumber, $notes): EnrollmentTransaction {
            $lockedCourse = Course::query()
                ->lockForUpdate()
                ->findOrFail($course->id);

            return $lockedCourse->enrollmentTransactions()->create([
                'user_id' => $user->id,
                'type' => $type,
                'quantity' => $quantity,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ]);
        });
    }

    public function enroll(Course $course, User $user): EnrollmentTransaction
    {
        return DB::transaction(function () use ($course, $user): EnrollmentTransaction {
            $lockedCourse = Course::query()
                ->lockForUpdate()
                ->findOrFail($course->id);

            if ($lockedCourse->enrollments()->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'course_id' => 'You are already enrolled in this course.',
                ]);
            }

            $transaction = $lockedCourse->enrollmentTransactions()->create([
                'user_id' => $user->id,
                'type' => EnrollmentTransaction::TYPE_ENROLL,
                'quantity' => -1,
            ]);

            $lockedCourse->enrollments()->create([
                'user_id' => $user->id,
            ]);

            return $transaction;
        });
    }

    public function cancel(Course $course, User $user): EnrollmentTransaction
    {
        return DB::transaction(function () use ($course, $user): EnrollmentTransaction {
            $lockedCourse = Course::query()
                ->lockForUpdate()
                ->findOrFail($course->id);
            $enrollment = $lockedCourse->enrollments()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $enrollment) {
                throw ValidationException::withMessages([
                    'course_id' => 'You are not currently enrolled in this course.',
                ]);
            }

            $transaction = $lockedCourse->enrollmentTransactions()->create([
                'user_id' => $user->id,
                'type' => EnrollmentTransaction::TYPE_CANCEL,
                'quantity' => 1,
            ]);

            $enrollment->delete();

            return $transaction;
        });
    }
}
