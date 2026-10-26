<?php

namespace App\Http\Controllers;

use App\Models\SalaryChangeRequest;
use Illuminate\Http\Request;

class SalaryChangeRequestController extends Controller
{
    private function guard()
    {
        if (! SalaryChangeRequest::isReviewer(auth()->user())) {
            abort(403, __('Permission denied.'));
        }
    }

    /**
     * Display pending salary changes and the already reviewed ones.
     */
    public function index()
    {
        $this->guard();

        $pending = SalaryChangeRequest::pending()
            ->with(['employee', 'requester'])
            ->orderBy('created_at', 'desc')
            ->get();

        $reviewed = SalaryChangeRequest::reviewed()
            ->with(['employee', 'requester', 'reviewer'])
            ->orderBy('reviewed_at', 'desc')
            ->limit(50)
            ->get();

        return view('salarychangerequest.index', compact('pending', 'reviewed'));
    }

    /**
     * Apply the requested salary. Only employees holding a non-zero salary go
     * through this flow; an empty salary is written straight to the employee.
     */
    public function approve(Request $request, $id)
    {
        $this->guard();

        $salaryChangeRequest = SalaryChangeRequest::findOrFail($id);

        if ($salaryChangeRequest->status !== SalaryChangeRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', __('This request has already been reviewed.'));
        }

        $salaryChangeRequest->status = SalaryChangeRequest::STATUS_APPROVED;
        $salaryChangeRequest->reviewed_by = auth()->id();
        $salaryChangeRequest->reviewed_at = now();
        $salaryChangeRequest->note = $request->input('note');
        $salaryChangeRequest->save();

        $employee = $salaryChangeRequest->employee;
        $employee->update(['salary' => $salaryChangeRequest->new_salary]);

        return redirect()->back()->with('success', __('Salary Change Approved.'));
    }

    /**
     * Reject the request, leaving the employee salary untouched.
     */
    public function reject(Request $request, $id)
    {
        $this->guard();

        $salaryChangeRequest = SalaryChangeRequest::findOrFail($id);

        if ($salaryChangeRequest->status !== SalaryChangeRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', __('This request has already been reviewed.'));
        }

        $salaryChangeRequest->status = SalaryChangeRequest::STATUS_REJECTED;
        $salaryChangeRequest->reviewed_by = auth()->id();
        $salaryChangeRequest->reviewed_at = now();
        $salaryChangeRequest->note = $request->input('note');
        $salaryChangeRequest->save();

        return redirect()->back()->with('success', __('Salary Change Rejected.'));
    }
}
