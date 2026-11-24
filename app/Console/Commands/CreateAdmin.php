<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Create a staff (admin) account. This is the only way to make the first one:
 * there is no public sign-up and the seeders don't create admins.
 */
class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
        {--name= : The admin\'s name}
        {--email= : The admin\'s email address}';

    protected $description = 'Create a staff (admin) account; asks for the password';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: $this->ask('Name'),
            'email' => $this->option('email') ?: $this->ask('Email address'),
            // Never an option: it would end up in the shell history.
            'password' => $this->secret('Password (at least 8 characters)'),
        ];
        $data['password_confirmation'] = $this->secret('Repeat the password');

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ADMIN,
        ]);

        $this->info("Admin {$user->email} created. Log in at ".route('login'));

        return self::SUCCESS;
    }
}
