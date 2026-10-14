<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EnrollmentTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->withCount('enrollments')
            ->orderBy('name')
            ->get();

        $transactions = EnrollmentTransaction::query()
            ->with(['course', 'user'])
            ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', $request->integer('course_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reports.index', compact('courses', 'transactions'));
    }
}
