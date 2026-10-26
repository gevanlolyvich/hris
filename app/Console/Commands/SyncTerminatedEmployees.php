<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTerminatedEmployees extends Command
{
    protected $signature = 'terminate:sync-inactive {--dry-run : Tampilkan data tanpa melakukan perubahan}';
    protected $description = 'Sync employee dan user yang sudah memiliki termination tapi masih is_active = 1';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $employees = Employee::whereHas('terminations')
            ->where('is_active', 1)
            ->with('user')
            ->get();
        
        if ($employees->isEmpty()) {
            $this->info('Tidak ada employee terminated yang masih aktif.');
            return 0;
        }
        
        $this->info("Ditemukan {$employees->count()} employee yang perlu di-sync:");
        $this->table(
            ['Employee ID', 'Nama', 'User ID', 'User Active'],
            $employees->map(fn($e) => [
                $e->id,
                $e->name,
                $e->user_id,
                $e->user?->is_active ?? 'N/A'
            ])
        );
        
        if ($dryRun) {
            $this->warn('Dry-run mode: Tidak ada perubahan yang dilakukan.');
            return 0;
        }
        
        if (!$this->confirm('Lanjutkan menonaktifkan employee dan user ini?')) {
            $this->info('Dibatalkan.');
            return 0;
        }
        
        DB::transaction(function () use ($employees) {
            foreach ($employees as $employee) {
                $employee->is_active = 0;
                if (!$employee->terminated_by) {
                    $employee->terminated_by = $employee->created_by ?? 1;
                }
                $employee->save();
                
                if ($employee->user_id) {
                    $user = User::find($employee->user_id);
                    if ($user && $user->is_active == 1) {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
                $this->info("Synced: {$employee->name} (ID: {$employee->id})");
            }
        });
        
        $this->info('Sync selesai.');
        return 0;
    }
}
