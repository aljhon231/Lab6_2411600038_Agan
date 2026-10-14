<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'almostFullCourses' => Course::almostFull()->orderBy('available_slots')->limit(5)->get(),
            'recentTransactions' => EnrollmentTransaction::with(['course', 'user'])->latest()->limit(8)->get(),
            'notifications' => auth()->user()->unreadNotifications()->latest()->limit(5)->get(),
            'unreadNotificationCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_students' => User::query()->where('is_admin', false)->count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'almost_full_courses' => Course::almostFull()->count(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function almostFull(): JsonResponse
    {
        return response()->json([
            'data' => Course::almostFull()
                ->orderBy('available_slots')
                ->get(['id', 'code', 'name', 'available_slots', 'minimum_slots']),
        ]);
    }

    public function recentEnrollments(): JsonResponse
    {
        return response()->json([
            'data' => EnrollmentTransaction::with(['course:id,code,name', 'user:id,name'])
                ->latest()
                ->limit(10)
                ->get(['id', 'course_id', 'user_id', 'type', 'quantity', 'created_at']),
        ]);
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
