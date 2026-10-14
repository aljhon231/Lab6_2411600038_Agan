<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Services\EnrollmentTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                match ($request->string('status')->toString()) {
                    'almost-full' => $query->almostFull(),
                    'full' => $query->full(),
                    'available' => $query->available(),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('courses.create', ['course' => new Course]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->courseRules());
        $validated['available_slots'] = $validated['maximum_slots'];

        $course = Course::create($validated);

        return redirect()->route('courses.show', $course)->with('success', 'Course created.');
    }

    public function show(Course $course): View
    {
        $course->loadCount('enrollments');

        return view('courses.show', [
            'course' => $course,
            'canDelete' => ! $course->enrollmentTransactions()->exists()
                && ! $course->enrollments()->exists(),
            'isEnrolled' => $course->enrollments()->where('user_id', auth()->id())->exists(),
            'recentTransactions' => $course->enrollmentTransactions()
                ->with('user')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function edit(Course $course): View
    {
        return view('courses.edit', compact('course'));
    }

    public function update(
        Request $request,
        Course $course,
        EnrollmentTransactionService $service,
    ): RedirectResponse {
        $validated = $request->validate($this->courseRules($course));

        DB::transaction(function () use ($course, $validated, $request, $service): void {
            $lockedCourse = Course::query()->lockForUpdate()->findOrFail($course->id);
            $occupiedSlots = $lockedCourse->maximum_slots - $lockedCourse->available_slots;

            if ($validated['maximum_slots'] < $occupiedSlots) {
                throw ValidationException::withMessages([
                    'maximum_slots' => 'Maximum slots cannot be less than the number of seats already occupied.',
                ]);
            }

            $previousMaximum = $lockedCourse->maximum_slots;
            $lockedCourse->update($validated);

            $slotChange = $validated['maximum_slots'] - $previousMaximum;
            if ($slotChange !== 0) {
                $service->record(
                    $lockedCourse,
                    $request->user(),
                    EnrollmentTransaction::TYPE_ADJUSTMENT,
                    $slotChange,
                    null,
                    "Maximum course capacity changed from {$previousMaximum} to {$validated['maximum_slots']}.",
                );
            }
        });

        return redirect()->route('courses.show', $course)->with('success', 'Course details updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->enrollmentTransactions()->exists() || $course->enrollments()->exists()) {
            return back()->with('error', 'This course has enrollment history and cannot be deleted.');
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted.');
    }

    public function adjust(Request $request, Course $course, EnrollmentTransactionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'not_in:0', 'between:-'.$course->maximum_slots.','.$course->maximum_slots],
            'reference_number' => ['nullable', 'string', 'max:100', 'unique:enrollment_transactions,reference_number'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $service->record(
            $course,
            $request->user(),
            EnrollmentTransaction::TYPE_ADJUSTMENT,
            (int) $validated['quantity'],
            $validated['reference_number'] ?? null,
            $validated['notes'],
        );

        return redirect()->route('courses.show', $course)->with('success', 'Available slots adjusted and recorded.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function courseRules(?Course $course = null): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'unique:courses,code'.($course ? ','.$course->id : '')],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:100'],
            'instructor' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'maximum_slots' => ['required', 'integer', 'min:1', 'max:10000'],
            'minimum_slots' => ['required', 'integer', 'min:0', 'lte:maximum_slots'],
        ];
    }
}
