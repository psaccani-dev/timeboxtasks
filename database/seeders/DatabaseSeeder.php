<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cria 5 usuários, cada um com 3 tasks e 2 timeboxes
        User::factory()
            ->count(5)
            ->hasTasks(3)       // graças ao hasMany no Model User
            ->hasTimeBoxes(2)   // idem
            ->create();
    }
}
