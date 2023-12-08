{{ Form::model($shift, ['route' => ['shift.update', $shift->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('shift_name',  __('Shift Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('shift_name', $shift_type->name, ['class' => 'form-control' ,'required' => 'required']) }}
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
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>{{ __('Monday') }}</td>
                        <td>
                            <div class="form-check form-switch rtl-hide">
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status1"
                                    @if ($shift->shiftTimes[0]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[0]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[0]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status2"
                                    @if ($shift->shiftTimes[1]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[1]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[1]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status3"
                                    @if ($shift->shiftTimes[2]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[2]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[2]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status4"                                    
                                    @if ($shift->shiftTimes[3]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[3]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[3]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status5"
                                    @if ($shift->shiftTimes[4]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[4]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[4]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status6"
                                    @if ($shift->shiftTimes[5]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[5]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[5]->end_time, ['class' => 'form-control timepicker_format']) }}
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
                                    id="status" name="status7"
                                    @if ($shift->shiftTimes[6]->is_working == true) checked @endif 
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', $shift->shiftTimes[6]->start_time, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', $shift->shiftTimes[6]->end_time, ['class' => 'form-control timepicker_format']) }}
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
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
