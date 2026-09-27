@php
    $departments = $departments->sortBy('name');
@endphp

@foreach($departments as $dept)
    @php
        $deptEmployees = $employees->where('department_id', $dept->id);
    @endphp
    @if($deptEmployees->count() > 0)
        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
            <div class="card">
                <div class="card-header py-2">
                    <strong>{{ $dept->name }}</strong>
                    @if($dept->branch)
                        <br><small class="text-muted">{{ $dept->branch->name }}</small>
                    @endif
                </div>
                <div class="card-body py-2">
                    @foreach($deptEmployees as $emp)
                        <div class="form-check">
                            <input class="form-check-input employee-check" type="checkbox"
                                name="emp_check[]" value="{{ $emp->id }}"
                                data-dept="{{ $emp->department_id }}"
                                id="emp_{{ $emp->id }}"
                                {{ in_array($emp->id, $selectedEmployeeIds ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="emp_{{ $emp->id }}">
                                {{ $emp->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endforeach
