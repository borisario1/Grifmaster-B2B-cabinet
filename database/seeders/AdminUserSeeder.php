<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@admin.com';
        
        $user = User::withTrashed()->where('email', $email)->first();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->forceFill([
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('password'),
            ])->save();
        } else {
            User::create([
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        $this->command->info("Admin user {$email} created/updated successfully.");
        $this->command->info("Password: password");
    }
}
