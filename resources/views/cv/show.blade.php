@extends('layouts.admin')

@section('page-title')
    {{ __('CV') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('profile') }}">{{ __('Profile') }}</a></li>
    <li class="breadcrumb-item">{{ __('CV') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" id="download-pdf" data-bs-toggle="tooltip"
            title="{{ __('Download PDF') }}">
            <i class="ti ti-download"></i> {{ __('Download PDF') }}
        </a>
    </div>
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: 'CV_{{ preg_replace('/\s+/', '', $employee->name) }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body" id="printableArea" style="font-family: 'Segoe UI', Arial, sans-serif; color: #333;">
                <div class="row">
                    <div class="col-12">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 120px; vertical-align: middle;">
                                    <img src="{{ !empty($employee->user->avatar) ? $profile . $employee->user->avatar : $profile . '/avatar.png' }}"
                                        alt="{{ $employee->name }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #4f46e5;">
                                </td>
                                <td style="vertical-align: middle; padding-left: 20px;">
                                    <h2 style="margin: 0; color: #1f2937; font-size: 26px;">{{ $employee->name }}</h2>
                                    <p style="margin: 4px 0 0; color: #4f46e5; font-size: 16px; font-weight: 600;">
                                        {{ !empty($employee->designation) ? $employee->designation->name : '' }}</p>
                                    <p style="margin: 6px 0 0; color: #6b7280; font-size: 13px;">
                                        @if (!empty($employee->branch)) {{ $employee->branch->name }} @endif
                                        @if (!empty($employee->department)) &nbsp;|&nbsp; {{ $employee->department->name }} @endif
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr style="border-top: 3px solid #4f46e5; margin: 15px 0;">

                <div class="row" style="margin-bottom: 10px;">
                    <div class="col-md-4" style="font-size: 13px; color: #374151;">
                        <strong>{{ __('Email') }}:</strong> {{ $employee->email ?? '-' }}<br>
                        <strong>{{ __('Phone') }}:</strong> {{ $employee->phone ?? '-' }}<br>
                        @if (!empty($employee->dob))
                            <strong>{{ __('Date of Birth') }}:</strong> {{ \Auth::user()->dateFormat($employee->dob) }}<br>
                        @endif
                    </div>
                    <div class="col-md-4" style="font-size: 13px; color: #374151;">
                        <strong>{{ __('Gender') }}:</strong> {{ __($employee->gender ?? '-') }}<br>
                        <strong>{{ __('Nationality') }}:</strong> {{ __($employee->nationality ?? '-') }}<br>
                        <strong>{{ __('Marital Status') }}:</strong> {{ __($employee->marital_status ?? '-') }}<br>
                    </div>
                    <div class="col-md-4" style="font-size: 13px; color: #374151;">
                        @if (!empty($employee->address))
                            <strong>{{ __('Address') }}:</strong> {{ $employee->address }}<br>
                        @endif
                        @if (!empty($employee->domicile_address))
                            <strong>{{ __('Domicile Address') }}:</strong> {{ $employee->domicile_address }}<br>
                        @endif
                    </div>
                </div>

                @if (!empty($employee->cv?->summary))
                    <div style="margin-bottom: 15px;">
                        <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Professional Summary') }}</h4>
                        <p style="margin: 0; font-size: 13px; text-align: justify;">{{ $employee->cv->summary }}</p>
                    </div>
                @endif

                @if ($employee->cvExperiences->isNotEmpty())
                    <div style="margin-bottom: 15px;">
                        <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Work Experience') }}</h4>
                        @foreach ($employee->cvExperiences as $exp)
                            <div style="margin-bottom: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                    <strong style="font-size: 14px;">{{ $exp->position }}</strong>
                                    <span style="font-size: 12px; color: #6b7280;">
                                        @if (!empty($exp->start_date)) {{ \Auth::user()->dateFormat($exp->start_date) }} @endif
                                        @if (!empty($exp->start_date) || !empty($exp->end_date)) - @endif
                                        @if (!empty($exp->end_date)) {{ \Auth::user()->dateFormat($exp->end_date) }} @else {{ __('Present') }} @endif
                                    </span>
                                </div>
                                <div style="font-size: 13px; color: #4f46e5; font-weight: 500;">{{ $exp->company }}</div>
                                @if (!empty($exp->description))
                                    <p style="margin: 4px 0 0; font-size: 12px; text-align: justify;">{{ $exp->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($employee->cvEducations->isNotEmpty())
                    <div style="margin-bottom: 15px;">
                        <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Education') }}</h4>
                        @foreach ($employee->cvEducations as $edu)
                            <div style="margin-bottom: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                    <strong style="font-size: 14px;">{{ $edu->institution }}</strong>
                                    <span style="font-size: 12px; color: #6b7280;">
                                        @if (!empty($edu->start_year)) {{ $edu->start_year }} @endif
                                        @if (!empty($edu->start_year) || !empty($edu->end_year)) - @endif
                                        @if (!empty($edu->end_year)) {{ $edu->end_year }} @endif
                                    </span>
                                </div>
                                <div style="font-size: 13px;">
                                    @if (!empty($edu->degree)) {{ $edu->degree }} @endif
                                    @if (!empty($edu->field_of_study)) &nbsp;-&nbsp; {{ $edu->field_of_study }} @endif
                                    @if (!empty($edu->gpa)) &nbsp;(GPA: {{ $edu->gpa }}) @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="row">
                    @if ($employee->cvSkills->isNotEmpty())
                        <div class="col-md-6" style="margin-bottom: 15px;">
                            <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Skills') }}</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 13px;">
                                @foreach ($employee->cvSkills as $skill)
                                    <li>{{ $skill->skill }} @if (!empty($skill->proficiency)) <span style="color: #6b7280;">({{ __($skill->proficiency) }})</span> @endif</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($employee->cvLanguages->isNotEmpty())
                        <div class="col-md-6" style="margin-bottom: 15px;">
                            <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Languages') }}</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 13px;">
                                @foreach ($employee->cvLanguages as $lang)
                                    <li>{{ $lang->language }} @if (!empty($lang->proficiency)) <span style="color: #6b7280;">({{ __($lang->proficiency) }})</span> @endif</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @if ($employee->certificates->isNotEmpty())
                    <div style="margin-bottom: 15px;">
                        <h4 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 8px;">{{ __('Certificates') }}</h4>
                        <ul style="margin: 0; padding-left: 18px; font-size: 13px;">
                            @foreach ($employee->certificates as $certificate)
                                <li>
                                    <strong>{{ $certificate->name }}</strong>
                                    @if (!empty($certificate->issuer)) &nbsp;-&nbsp; {{ $certificate->issuer }} @endif
                                    @if (!empty($certificate->issue_date)) &nbsp;({{ \Auth::user()->dateFormat($certificate->issue_date) }}) @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection