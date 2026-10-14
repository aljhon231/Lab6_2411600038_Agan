<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use App\Services\EnrollmentTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = EnrollmentTransaction::query()
            ->with(['course', 'user'])
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('enrollments.index', compact('transactions'));
    }

    public function create(): View
    {
        return view('enrollments.create', [
            'courses' => Course::available()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, EnrollmentTransactionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);
        $course = Course::findOrFail($validated['course_id']);

        $transaction = $service->enroll($course, $request->user());

        return redirect()
            ->route('enrollments.show', $transaction)
            ->with('success', 'You are enrolled. One available slot has been reserved.');
    }

    public function show(EnrollmentTransaction $enrollmentTransaction): View
    {
        abort_unless(
            auth()->user()->isAdmin() || $enrollmentTransaction->user_id === auth()->id(),
            403,
        );

        return view('enrollments.show', [
            'transaction' => $enrollmentTransaction->load(['course', 'user']),
            'isEnrolled' => $enrollmentTransaction->course
                ->enrollments()
                ->where('user_id', $enrollmentTransaction->user_id)
                ->exists(),
        ]);
    }

    public function cancel(
        Course $course,
        Request $request,
        EnrollmentTransactionService $service,
    ): RedirectResponse {
        $service->cancel($course, $request->user());

        return redirect()->route('courses.show', $course)->with('success', 'Enrollment cancelled and one slot restored.');
    }

    public function destroy(EnrollmentTransaction $enrollmentTransaction): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return back()->with(
            'error',
            'Enrollment transactions are kept as an audit history. Record a cancellation or adjustment instead of deleting one.',
        );
    }
}
