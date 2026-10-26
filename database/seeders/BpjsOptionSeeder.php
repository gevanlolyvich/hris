<?php

namespace Database\Seeders;

use App\Models\BpjsOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class BpjsOptionSeeder extends Seeder
{
    /**
     * Default BPJS components an employee can opt into.
     *
     * Only the component name lives here. The percentage is entered per employee
     * in the UI and stored on the bpjs row, because rates differ per company
     * and per risk class.
     */
    protected $options = [
        'BPJS Kesehatan',
        'JHT (Jaminan Hari Tua)',
        'JP (Jaminan Pensiun)',
        'JKK (Jaminan Kecelakaan Kerja)',
        'JKM (Jaminan Kematian)',
        'SH (Sosial Helper)',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyIds = User::where('type', 'company')->pluck('id');

        if ($companyIds->isEmpty()) {
            $this->command?->warn('BpjsOptionSeeder: tidak ada user bertipe company, dilewati.');

            return;
        }

        $existing = BpjsOption::query()
            ->whereIn('created_by', $companyIds)
            ->get(['created_by', 'name'])
            ->map(fn ($option) => $option->created_by . '|' . $option->name)
            ->all();

        $now = date('Y-m-d H:i:s');

        $rows = [];

        foreach ($companyIds as $companyId) {
            foreach ($this->options as $name) {
                if (in_array($companyId . '|' . $name, $existing, true)) {
                    continue;
                }

                $rows[] = [
                    'name' => $name,
                    'created_by' => $companyId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($rows)) {
            BpjsOption::insert($rows);
        }

        $this->command?->info('BpjsOptionSeeder: ' . count($rows) . ' opsi ditambahkan.');
    }
}
