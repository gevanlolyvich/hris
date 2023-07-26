{{ Form::open(['url' => 'shift', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('shift_name',  __('Shift Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('shift_name', null, ['class' => 'form-control' ,'required' => 'required']) }}
            </div>
        @endif
        <div class="table-responsive">
            <table class="table" id="pc-dt-simple">
                <thead>
                    <tr>
                        <th>{{ __('Days') }}</th>
                        <th>{{ __('Work') }}</th>
                        <th>{{ __('Start Time') }}</th>
                        <th>{{ __('End Time') }}</th>
                        {{-- <th>{{ __('Description') }}</th> --}}
                        {{-- @if (Gate::check('Edit Warning') || Gate::check('Delete Warning'))
                            <th width="200px">{{ __('Action') }}</th>
                        @endif --}}
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>{{ __('Monday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Tuesday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Wednesday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Thursday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Friday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Saturday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>{{ __('Sunday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_end_time')
                                <span class="invalid-company_end_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                    </tr>


                </tbody>
            </table>
        </div>
        {{-- <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('warning_to', __('Warning To'), ['class' => 'col-form-label']) }}
            {{ Form::select('warning_to', $employees, null, ['class' => 'form-control select2' ,'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('subject', __('Subject'), ['class' => 'col-form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control' ,'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('warning_date', __('Warning Date'), ['class' => 'col-form-label']) }}
            {{ Form::text('warning_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off' ,'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description') ,'rows' => '3' ,'required' => 'required']) }}
        </div> --}}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
