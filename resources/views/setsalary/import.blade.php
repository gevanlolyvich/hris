{{ Form::open(['route' => ['setsalary.import'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-6">
            <label for="file" class="form-label">Download template salary (berisi daftar employee + panduan pengisian di sheet "Petunjuk")</label>
            <a href="{{ route('setsalary.export') }}"
                class="btn btn-sm btn-primary rounded">
                <i class="ti ti-download"></i> {{ __('Download') }}
            </a>
        </div>
        <div class="col-md-12 mb-2">
            <div class="alert alert-info">
                <i class="ti ti-info-circle"></i>
                Hanya kolom <b>Salary*</b> yang wajib diisi. Kolom allowance, commission, other payment, loan,
                bpjs, dan deduction bersifat opsional. Untuk menambah lebih dari satu data per komponen,
                tambahkan baris baru di bawah karyawan tersebut (kosongkan kolom Salary). Lihat sheet
                <b>"Petunjuk"</b> di file template untuk cara pengisian lengkap.
            </div>
        </div>
        <div class="choose-files mt-3">
            <label for="file">
                <div class=" bg-primary "> <i
                        class="ti ti-upload px-1"></i>{{ __('Select CSV File') }}
                </div>
                <input type="file" class="form-control file"
                    name="file" id="file"
                    data-filename="file">
            </label>
        </div>


        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Upload') }}" class="btn btn-primary">
        </div>


    </div>
</div>
{{ Form::close() }}
