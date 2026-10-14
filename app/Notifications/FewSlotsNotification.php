<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FewSlotsNotification extends Notification
{
    use Queueable;

    public function __construct(public Course $course) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->line($this->message());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'available_slots' => $this->course->available_slots,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return "{$this->course->name} has only {$this->course->available_slots} available slots remaining.";
    }
}
