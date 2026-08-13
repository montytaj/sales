<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleanDatabaseForClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-for-client {--force : Force execution without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean test and demo data from database, preserving users, permissions, roles, settings, and level 1 accounts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('WARNING: This will erase all test/demo data. Do you want to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $sqlPath = database_path('clean_database.sql');
        if (!File::exists($sqlPath)) {
            $this->error("SQL file not found at: {$sqlPath}");
            return 1;
        }

        $sql = File::get($sqlPath);

        $this->info('Executing cleanup SQL script...');
        DB::unprepared($sql);

        // Clear application cache
        $this->call('cache:clear');

        $this->info('Database cleaned successfully!');
        return 0;
    }
}
