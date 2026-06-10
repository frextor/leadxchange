<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminCreate extends Command
{
    protected $signature = 'admin:create
                            {--email= : Email address}
                            {--password= : Password}
                            {--first-name= : First name}
                            {--last-name= : Last name}
                            {--role=super_admin : Role (admin or super_admin)}';

    protected $description = 'Create an admin or super_admin user for local development';

    public function handle(): int
    {
        $email     = $this->option('email')      ?? $this->ask('Email');
        $password  = $this->option('password')   ?? $this->secret('Password');
        $firstName = $this->option('first-name') ?? $this->ask('First name', 'Super');
        $lastName  = $this->option('last-name')  ?? $this->ask('Last name',  'Admin');
        $role      = $this->option('role');

        if (!in_array($role, ['admin', 'super_admin'])) {
            $this->error("Invalid role \"{$role}\". Use admin or super_admin.");
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            // Update role if user already exists
            User::where('email', $email)->update(['role' => $role]);
            $this->info("User {$email} already exists — role updated to {$role}.");
            return self::SUCCESS;
        }

        $user = User::create([
            'first_name'           => $firstName,
            'last_name'            => $lastName,
            'email'                => $email,
            'password'             => Hash::make($password),
            'role'                 => $role,
            'onboarding_completed' => true,
            'points_balance'       => 0,
            'badge_level'          => 'bronze',
        ]);

        $this->info("✓ {$role} created successfully.");
        $this->table(['Field', 'Value'], [
            ['Name',  "{$user->first_name} {$user->last_name}"],
            ['Email', $user->email],
            ['Role',  $user->role],
            ['ID',    $user->id],
        ]);

        return self::SUCCESS;
    }
}
