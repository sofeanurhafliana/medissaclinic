<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Doctor;

class SyncDoctors extends Command
{
    protected $signature = 'sync:doctors';
    protected $description = 'Sync users with role=doctor into the doctors table';

    public function handle()
    {
        $users = User::where('role', 'doctor')->get();
        $count = 0;

        foreach ($users as $user) {
            $doctors= Doctor::firstOrCreate(
                ['user_id' => $user->id],
                ['branch_id' => 1] // Default branch, or set dynamically
            );

            $count++;
        }

        $this->info("Synced $count doctors from user table.");
    }
}
