<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all business data to start from a clean state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting business data...');

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'investor_transactions',
            'ipo_sales',
            'ipo_allocations',
            'ipo_account_placements',
            'investor_fundings',
            'ipos',
            'mitra_accounts',
            'investors',
            'watchlists',
            'cache'
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\DB::table($table)->truncate();
                $this->line("- Truncated: {$table}");
            }
        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('--- Data Reset Successful ---');
    }
}
