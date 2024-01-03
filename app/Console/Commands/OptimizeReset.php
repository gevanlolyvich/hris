<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Optimize Cache (Clear and Cache)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('optimize:clear');
        $this->call('optimize');
        $this->call('event:cache');
        $this->call('view:cache');
        $this->call('config:clear');
        return Command::SUCCESS;
    }
}
