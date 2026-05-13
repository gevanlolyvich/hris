{{ Form::open(['url' => 'shift', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('shift_name',  __('Shift Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('shift_name', null, ['class' => 'form-control' ,'required' => 'required']) }}
            </div>
            {{-- <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('branch_id',  __('Branch'), ['class' => 'col-form-label']) }}
                {{ Form::select('branch_id', $branch, ['class' => 'form-control select2' ,'required' => 'required','id' => 'branch_id']) }}
            </div> --}}
            <div class="form-group col-md-12">
                {{ Form::label('branch_id', __('Select Branch'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                <div class="form-icon-user">
                    {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Branch')]) }}
                </div>
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
                                {{-- {{ Form::checkbox('status[]',null,['class'=>"form-check-input"]) }} --}}
                                <input type="checkbox" class="form-check-input"
                                    id="status" name="status1"
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
                                    {{-- @if ($status == 'on') checked @endif  --}}
                                    />

                                <label class="form-check-label f-w-600 pl-1"
                                    for="status"></label>
                            </div>
                        </td>
                        <td>
                            {{ Form::time('company_start_time[]', null, ['class' => 'form-control timepicker_format']) }}
                            @error('company_start_time')
                                <span class="invalid-company_start_time" role="alert">
                                    <small class="text-danger">{{ $message }}</small>
                                </span>
                            @enderror
                        </td>
                        <td>
                            {{ Form::time('company_end_time[]', null, ['class' => 'form-control timepicker_format']) }}
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
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
