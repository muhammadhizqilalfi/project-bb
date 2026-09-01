<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * Signature command : name, nip, password
     */
    protected $signature = 'make:user {name} {nip} {password}';

    /**
     * Desc
     */
    protected $description = 'Membuat akun pengguna baru langsung dari argumen CLI';

    public function handle()
    {
        $name = $this->argument('name');
        $nip = $this->argument('nip');
        $password = $this->argument('password');

        if (User::where('nip', $nip)->exists()) {
            $this->error("Gagal: User dengan NIP '{$nip}' sudah ada di database!");
            return 1;
        }

        User::create([
            'name' => $name,
            'nip' => $nip,
            'password' => Hash::make($password),
        ]);

        $this->info("Berhasil! Akun '{$name}' (NIP: {$nip}) telah dibuat.");
        return 0;
    }
}