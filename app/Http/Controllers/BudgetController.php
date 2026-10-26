<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /**
     * Only company and HR accounts may use this module.
     * The permission check keeps the door closed; the type check makes the
     * "company and HR only" rule explicit even if a role is granted by mistake.
     */
    private function canAccess($permission)
    {
        return \Auth::user()->can($permission)
            && in_array(\Auth::user()->type, ['company', 'hr'], true);
    }

    /**
     * Branch ids the current user may see. An empty collection means every branch.
     */
    private function allowedBranchIds()
    {
        $branch    = Branch::find(\Auth::user()->branch_id);
        $branchIds = collect();

        if ($branch) {
            $branchIds->push($branch->id);
        }

        $children = $branch?->childBranchFlatten();
        if ($children?->isNotEmpty()) {
            foreach ($children as $child) {
                $branchIds->push($child->id);
            }
        }

        return $branchIds;
    }

    private function applyBranchScope($query)
    {
        $branchIds = $this->allowedBranchIds();

        return $branchIds->isNotEmpty() ? $query->whereIn('branch_id', $branchIds) : $query;
    }

    /**
     * Rules shared by store and update.
     */
    private function rules(Request $request)
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'jenis'       => 'required|string|max:100',
            'nominal'     => 'required|numeric|min:0',
            'tanggal'     => 'required|date',
            'file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ];
    }

    /**
     * Store an uploaded document and return the relative path, or null when
     * no file was sent. The previous path is kept when $keepCurrent is true.
     */
    private function storeDocument(Request $request, $keepCurrent = null)
    {
        if (!$request->hasFile('file')) {
            return $keepCurrent;
        }

        $file = $request->file('file');
        $name = time() . '_' . date('Y-m-d') . '_' . preg_replace('/\s+/', '', $request->jenis) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('uploads/budget', $name, 'public');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if ($this->canAccess('Manage Budget')) {
            $budgets = $this->applyBranchScope(
                Budget::with(['employee', 'branch', 'department'])
            )->orderBy('tanggal', 'desc')->get();

            return view('budget.index', compact('budgets'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if ($this->canAccess('Create Budget')) {
            $branchIds = $this->allowedBranchIds();
            $employees = $branchIds->isNotEmpty()
                ? Employee::where('is_active', 1)->whereIn('branch_id', $branchIds)->orderBy('name', 'ASC')->get()->pluck('name', 'id')
                : Employee::where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('budget.create', compact('employees'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($this->canAccess('Create Budget')) {
            $validator = \Validator::make($request->all(), $this->rules($request));
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $employee = Employee::find($request->employee_id);

            // Branch and department are always taken from the employee so they
            // can never disagree with the employee record.
            $budget              = new Budget();
            $budget->employee_id = $employee->id;
            $budget->branch_id   = $employee->branch_id;
            $budget->department_id = $employee->department_id;
            $budget->jenis       = $request->jenis;
            $budget->nominal     = $request->nominal;
            $budget->tanggal     = $request->tanggal;
            $budget->file        = $this->storeDocument($request);
            $budget->created_by  = \Auth::user()->id;
            $budget->save();

            return redirect()->route('budget.index')->with('success', __('Budget Successfully Created'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        if ($this->canAccess('Manage Budget')) {
            $branchIds = $this->allowedBranchIds();
            if ($branchIds->isNotEmpty() && !$branchIds->contains($budget->branch_id)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $budget->load(['employee', 'branch', 'department', 'creator']);

            return view('budget.show', compact('budget'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        if ($this->canAccess('Edit Budget')) {
            $branchIds = $this->allowedBranchIds();
            if ($branchIds->isNotEmpty() && !$branchIds->contains($budget->branch_id)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $branchIds = $this->allowedBranchIds();
            $employees = $branchIds->isNotEmpty()
                ? Employee::where('is_active', 1)->whereIn('branch_id', $branchIds)->orderBy('name', 'ASC')->get()->pluck('name', 'id')
                : Employee::where('is_active', 1)->orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('budget.edit', compact('budget', 'employees'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        if ($this->canAccess('Edit Budget')) {
            $branchIds = $this->allowedBranchIds();
            if ($branchIds->isNotEmpty() && !$branchIds->contains($budget->branch_id)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $validator = \Validator::make($request->all(), $this->rules($request));
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $employee = Employee::find($request->employee_id);

            $budget->employee_id   = $employee->id;
            $budget->branch_id     = $employee->branch_id;
            $budget->department_id = $employee->department_id;
            $budget->jenis         = $request->jenis;
            $budget->nominal       = $request->nominal;
            $budget->tanggal       = $request->tanggal;
            // Keep the existing document when no new file is uploaded.
            $budget->file          = $this->storeDocument($request, $budget->file);
            $budget->save();

            return redirect()->route('budget.index')->with('success', __('Budget Successfully Updated'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        if ($this->canAccess('Delete Budget')) {
            $branchIds = $this->allowedBranchIds();
            if ($branchIds->isNotEmpty() && !$branchIds->contains($budget->branch_id)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $budget->delete();

            return redirect()->route('budget.index')->with('success', __('Budget Successfully Deleted'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
