<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'category', 'instructor', 'description', 'maximum_slots', 'available_slots', 'minimum_slots'])]
class Course extends Model
{
    /**
     * @return HasMany<EnrollmentTransaction, $this>
     */
    public function enrollmentTransactions(): HasMany
    {
        return $this->hasMany(EnrollmentTransaction::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function hasAvailableSlots(): bool
    {
        return $this->available_slots > 0;
    }

    public function isAlmostFull(): bool
    {
        return $this->hasAvailableSlots()
            && $this->available_slots <= $this->minimum_slots;
    }

    public function isFull(): bool
    {
        return ! $this->hasAvailableSlots();
    }

    public function getEnrollmentStatusAttribute(): string
    {
        return $this->getEnrollmentStatus();
    }

    public function getEnrollmentStatus(): string
    {
        if ($this->isFull()) {
            return 'Full';
        }

        return $this->isAlmostFull() ? 'Almost Full' : 'Available';
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeAlmostFull(Builder $query): Builder
    {
        return $query->where('available_slots', '>', 0)
            ->whereColumn('available_slots', '<=', 'minimum_slots');
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeFull(Builder $query): Builder
    {
        return $query->where('available_slots', '<=', 0);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_slots', '>', 0);
    }
}
