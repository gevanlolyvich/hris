@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Meeting') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Meeting') }}</li>
@endsection

@section('action-button')
    @can('Create Meeting New')
        <a href="#" data-url="{{ route('meeting-new.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Meeting') }}" data-size="xl" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Time') }}</th>
                                <th>{{ __('Deadline') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td>{{ $meeting->title }}</td>
                                    <td>{{ $meeting->meeting_type }}</td>
                                    <td>{{ $meeting->meeting_date->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i') }}</td>
                                    <td>{{ $meeting->deadline->format('d M Y H:i') }}</td>
                                    <td>
                                        @if ($meeting->isLocked())
                                            <span class="badge bg-danger">{{ __('Di Tutup') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Open') }}</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                                                    data-url="{{ route('meeting-new.show', $meeting->id) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                    data-title="{{ __('View Meeting') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                            @if (!$meeting->isLocked() && $meeting->created_by == Auth::id())
                                                @can('Edit Meeting New')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                            data-url="{{ route('meeting-new.edit', $meeting->id) }}"
                                                            data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip"
                                                            data-title="{{ __('Edit Meeting') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            @endif
                                            @if (!$meeting->isDeleteLocked() && (\Auth::user()->type == 'company' || $meeting->created_by == Auth::id()))
                                                @can('Delete Meeting New')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['meeting-new.destroy', $meeting->id]]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" data-original-title="Delete">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    window.meetingNewDocPreview = function(input) {
        var previewWrap = document.getElementById('docPreview');
        var previewList = document.getElementById('docPreviewList');
        var nameWrap = document.getElementById('docFileNames');
        if (!previewWrap || !previewList || !nameWrap) return;

        previewList.innerHTML = '';
        nameWrap.innerHTML = '';

        var files = input.files || [];
        var hasImage = false;

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var nameEl = document.createElement('span');
            nameEl.className = 'badge bg-light text-dark me-1 mb-1';
            nameEl.textContent = file.name;
            nameWrap.appendChild(nameEl);

            if (file.type && file.type.startsWith('image/')) {
                hasImage = true;
                var img = document.createElement('img');
                img.src = window.URL.createObjectURL(file);
                img.className = 'me-2 mb-2';
                img.width = 100;
                previewList.appendChild(img);
            }
        }

        if (hasImage) {
            previewWrap.classList.remove('d-none');
        } else {
            previewWrap.classList.add('d-none');
        }
    };

    $(document).ready(function() {

        // Checkbox toggle: max 1 per department
        $(document).on('change', '.employee-check', function() {
            var dept = $(this).attr('data-dept');
            if (this.checked) {
                $('.employee-check').not(this).filter('[data-dept="' + dept + '"]').prop('checked', false);
            }
        });

        // Branch change → AJAX filter employees
        $(document).on('change', '#branch_id', function() {
            var branchId = $(this).val();
            $.ajax({
                url: '{{ route("meeting-new.getemployee") }}',
                type: 'POST',
                data: {
                    branch_id: branchId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    var html = '';
                    var grouped = {};
                    data.forEach(function(emp) {
                        var deptId = emp.department_id;
                        if (!grouped[deptId]) {
                            grouped[deptId] = { name: emp.department_name || 'Unknown', employees: [] };
                        }
                        grouped[deptId].employees.push(emp);
                    });

                    Object.keys(grouped).sort().forEach(function(deptId) {
                        var dept = grouped[deptId];
                        html += '<div class="col-lg-4 col-md-4 col-sm-6 mb-3">';
                        html += '<div class="card"><div class="card-header py-2"><strong>' + dept.name + '</strong></div>';
                        html += '<div class="card-body py-2">';
                        dept.employees.forEach(function(emp) {
                            html += '<div class="form-check">';
                            html += '<input class="form-check-input employee-check" type="checkbox" name="emp_check[]" value="' + emp.id + '" data-dept="' + emp.department_id + '" id="emp_' + emp.id + '">';
                            html += '<label class="form-check-label" for="emp_' + emp.id + '">' + emp.name + '</label>';
                            html += '</div>';
                        });
                        html += '</div></div></div>';
                    });

                    $('#employeeChecklist').html(html);
                }
            });
        });

        // Doc delete toggle (edit modal)
        $(document).on('click', '.delete-doc-btn', function() {
            var btn = $(this);
            var wrap = btn.closest('.doc-item');
            var doc = btn.data('doc');
            var input = $('#commonModal form').find('input[name="delete_documents[]"][value="' + doc + '"]');
            if (input.length) {
                input.remove();
                btn.removeClass('btn-danger').addClass('btn-outline-danger');
                wrap.removeClass('text-decoration-line-through opacity-75');
            } else {
                wrap.addClass('text-decoration-line-through opacity-75');
                btn.removeClass('btn-outline-danger').addClass('btn-danger');
                wrap.append('<input type="hidden" name="delete_documents[]" value="' + doc + '">');
            }
        });

        // Form submit → collect checked employees
        $(document).on('submit', '#commonModal form', function(e) {
            var $form = $(this);
            var selected = [];
            $form.find('input.employee-check:checked').each(function() {
                selected.push($(this).val());
            });
            if (selected.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 employee!');
                return false;
            }
            $form.find('input[name="employee_id[]"]').remove();
            selected.forEach(function(val) {
                $form.append('<input type="hidden" name="employee_id[]" value="' + val + '">');
            });
        });
    });
</script>
@endpush
