<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'membuat akun admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Nama user');
        $username = $this->ask('Username');
        $password = $this->secret('Password');

        if (User::where('Username', $username)->exists()) {
            $this->error('Username sudah terdaftar!');
            return;
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'role' => 'admin'
        ]);

        $this->info('User berhasil dibuat 🎉');

    }
}
