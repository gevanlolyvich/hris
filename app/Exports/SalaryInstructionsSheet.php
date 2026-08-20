<?php

namespace App\Exports;

use App\Models\AllowanceOption;
use App\Models\BpjsOption;
use App\Models\DeductionOption;
use App\Models\LoanOption;
use App\Models\PayslipType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalaryInstructionsSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        $rows = [];
        $rows[] = ['PANDUAN PENGISIAN TEMPLATE GAJI'];
        $rows[] = [];
        $rows[] = ['A. ATURAN DASAR'];
        $rows[] = ['   1. Hanya kolom Salary* yang WAJIB diisi. Kolom lain boleh dikosongkan jika tidak dipakai.'];
        $rows[] = ['   2. Baris pertama setiap karyawan berisi Employee Id (jangan diubah) dan Salary (wajib).'];
        $rows[] = ['   3. Ingin menambah data lain (allowance, commission, loan, dll.) lebih dari satu untuk karyawan yang sama?'];
        $rows[] = ['      Tambahkan baris baru di bawah karyawan tersebut, kosongkan kolom Salary, lalu isi minimal 1 kelompok kolom yang ingin ditambahkan.'];
        $rows[] = ['      Contoh: karyawan yang punya 2 allowance = baris ke-1 (Salary + allowance 1) dan baris ke-2 (Salary kosong + allowance 2).'];
        $rows[] = [];
        $rows[] = ['B. CARA MENGISI KOLOM'];
        $rows[] = [];

        $rows[] = ['   1) EMPLOYEE ID & NAME'];
        $rows[] = ['      Jangan diubah. Hanya sebagai tanda/cocokkan karyawan.'];
        $rows[] = [];

        $rows[] = ['   2) SALARY* & SALARY TYPE'];
        $rows[] = ['      - Salary*: gaji pokok dalam angka, tanpa titik atau koma. Contoh: 5000000'];
        $rows[] = ['      - Salary Type: pilih salah satu berikut (opsional). Jika dikosongkan, tipe gaji tidak diubah.'];
        foreach (PayslipType::orderBy('name', 'asc')->pluck('name') as $salaryType) {
            $rows[] = ['          - ' . $salaryType];
        }
        $rows[] = [];

        $rows[] = ['   3) ALLOWANCE (Tunjangan)'];
        $rows[] = ['      - Allowance Option  : nama pilihan tunjangan. Contoh: ' . $this->optionNames(AllowanceOption::class)];
        $rows[] = ['      - Allowance Title    : nama tunjangan (bebas). Jika kosong, dipakai nama Allowance Option.'];
        $rows[] = ['      - Allowance Recurring: 0 = hanya 1 bulan (wajib isi Period), 1 = berulang tiap bulan, 2 = prorata kehadiran.'];
        $rows[] = ['      - Allowance Period   : isi format YYYY-MM bila Recurring = 0. Contoh: 2026-08'];
        $rows[] = ['      - Allowance Amount   : nominal tunjangan (angka).'];
        $rows[] = [];

        $rows[] = ['   4) COMMISSION (Komisi)'];
        $rows[] = ['      - Title    : nama komisi (bebas). Jika kosong, diisi "Commission".'];
        $rows[] = ['      - Type     : fixed = nominal tetap, atau percentage = persen dari gaji. Contoh: percentage'];
        $rows[] = ['      - Recurring: 0 = hanya 1 bulan (wajib isi Period), 1 = berulang tiap bulan.'];
        $rows[] = ['      - Period   : format YYYY-MM bila Recurring = 0.'];
        $rows[] = ['      - Amount   : nominal komisi (bila Type = fixed) atau persen (bila Type = percentage).'];
        $rows[] = [];

        $rows[] = ['   5) OTHER PAYMENT (Pembayaran Lainnya)'];
        $rows[] = ['      Caranya sama dengan Commission: Title, Type (fixed/percentage), Recurring (0/1), Period, Amount.'];
        $rows[] = [];

        $rows[] = ['   6) LOAN (Pinjaman)'];
        $rows[] = ['      - Title          : nama pinjaman (bebas). Jika kosong, diisi "Loan".'];
        $rows[] = ['      - Loan Option    : nama pilihan pinjaman. Contoh: ' . $this->optionNames(LoanOption::class)];
        $rows[] = ['      - Recurring      : 0 = hanya 1 bulan (wajib isi Period), 1 = berulang dalam periode tertentu.'];
        $rows[] = ['      - Period         : format YYYY-MM bila Recurring = 0. Contoh: 2026-08'];
        $rows[] = ['      - Period Start   : format YYYY-MM bila Recurring = 1. Contoh: 2026-08'];
        $rows[] = ['      - Period End     : format YYYY-MM bila Recurring = 1. Contoh: 2026-12'];
        $rows[] = ['      - Type           : fixed = nominal tetap, atau percentage = persen dari gaji.'];
        $rows[] = ['      - Loan Amount    : nominal pinjaman (bila Type = fixed) atau persen (bila Type = percentage).'];
        $rows[] = ['      - Loan Reason    : alasan pinjaman (bebas).'];
        $rows[] = [];

        $rows[] = ['   7) BPJS'];
        $rows[] = ['      - BPJS Option    : nama pilihan BPJS. Contoh: ' . $this->optionNames(BpjsOption::class)];
        $rows[] = ['      - BPJS Recurring : biasanya 1 = berulang tiap bulan.'];
        $rows[] = ['      - BPJS Type      : percentage (persen dari gaji).'];
        $rows[] = ['      - BPJS Amount (%) : persentase BPJS. Contoh: 4 untuk 4%.'];
        $rows[] = [];

        $rows[] = ['   8) DEDUCTION (Potongan Lain)'];
        $rows[] = ['      - Deduction Option : nama pilihan potongan. Contoh: ' . $this->optionNames(DeductionOption::class)];
        $rows[] = ['      - Title            : nama potongan (bebas). Jika kosong, dipakai nama Deduction Option.'];
        $rows[] = ['      - Type             : fixed = nominal tetap, atau percentage = persen dari gaji.'];
        $rows[] = ['      - Recurring        : 0 = hanya 1 bulan (wajib isi Period), 1 = berulang tiap bulan.'];
        $rows[] = ['      - Period           : format YYYY-MM bila Recurring = 0.'];
        $rows[] = ['      - Amount           : nominal potongan (bila Type = fixed) atau persen (bila Type = percentage).'];
        $rows[] = [];

        $rows[] = ['C. CARA KERJA SAAT FILE DI-UPLOAD'];
        $rows[] = ['   1. Salary selalu diperbarui untuk setiap karyawan yang barisnya valid.'];
        $rows[] = ['   2. Untuk allowance/commission/other payment/loan/bpjs/deduction:'];
        $rows[] = ['      - Jika data yang sama SUDAH ada (karyawan + berulang/periode + judul + pilihan sama), nilainya akan DIPERBARUI.'];
        $rows[] = ['      - Jika BELUM ada, akan DIBUAT data baru.'];
        $rows[] = ['      - Jika kolom dikosongkan, data lama TIDAK diubah maupun dihapus.'];
        $rows[] = ['   3. Baris yang Salary-nya kosong/tidak valid akan dilewati dan dihitung sebagai data gagal.'];
        $rows[] = [];

        return $rows;
    }

    private function optionNames($modelClass): string
    {
        $names = $modelClass::orderBy('name', 'asc')->pluck('name')->take(3);

        return $names->isNotEmpty() ? implode(', ', $names->toArray()) : 'ikuti nama pilihan yang sudah ada di sistem';
    }

    public function title(): string
    {
        return 'Petunjuk';
    }
}
