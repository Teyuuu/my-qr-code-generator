<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@company.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'department' => null,
            'email_verified_at' => now(),
        ]);

        // Create IT Staff
        User::create([
            'name' => 'John Doe',
            'email' => 'staff1@company.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'department' => 'IT Department',
            'email_verified_at' => now(),
        ]);

        // Create HR Staff
        User::create([
            'name' => 'Jane Smith',
            'email' => 'staff2@company.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'department' => 'HR Department',
            'email_verified_at' => now(),
        ]);

        // Create Finance Staff
        User::create([
            'name' => 'Mike Johnson',
            'email' => 'staff3@company.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'department' => 'Finance Department',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('Admin: admin@company.com / admin123');
        $this->command->info('IT Staff: staff1@company.com / staff123');
        $this->command->info('HR Staff: staff2@company.com / staff123');
        $this->command->info('Finance Staff: staff3@company.com / staff123');
    }
}
