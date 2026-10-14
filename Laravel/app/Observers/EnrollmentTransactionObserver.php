<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Models\User;
use App\Notifications\FewSlotsNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EnrollmentTransactionObserver
{
    public function created(EnrollmentTransaction $enrollmentTransaction): void
    {
        $course = Course::query()
            ->lockForUpdate()
            ->findOrFail($enrollmentTransaction->course_id);
        $quantity = (int) $enrollmentTransaction->quantity;

        if ($quantity === 0) {
            throw ValidationException::withMessages([
                'quantity' => 'The slot change must not be zero.',
            ]);
        }

        if (
            ($enrollmentTransaction->type === EnrollmentTransaction::TYPE_ENROLL && $quantity >= 0)
            || ($enrollmentTransaction->type === EnrollmentTransaction::TYPE_CANCEL && $quantity <= 0)
            || ! in_array($enrollmentTransaction->type, [
                EnrollmentTransaction::TYPE_ENROLL,
                EnrollmentTransaction::TYPE_CANCEL,
                EnrollmentTransaction::TYPE_ADJUSTMENT,
            ], true)
        ) {
            throw ValidationException::withMessages([
                'type' => 'The transaction type and slot change do not match.',
            ]);
        }

        $updatedSlots = $course->available_slots + $quantity;

        if ($updatedSlots < 0) {
            throw ValidationException::withMessages([
                'course_id' => 'There are not enough available slots for this enrollment.',
            ]);
        }

        if ($updatedSlots > $course->maximum_slots) {
            throw ValidationException::withMessages([
                'course_id' => 'The slot change cannot exceed the course maximum.',
            ]);
        }

        $previousSlots = $course->available_slots;
        $course->available_slots = $updatedSlots;
        $course->save();

        if (
            $previousSlots > $course->minimum_slots
            && $updatedSlots <= $course->minimum_slots
        ) {
            Notification::send(
                User::query()->where('is_admin', true)->get(),
                new FewSlotsNotification($course),
            );
        }
    }
}
