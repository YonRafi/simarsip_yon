<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\KecamatanSeeder;
use Database\Seeders\KelurahanSeeder;
use Database\Seeders\HakAksesSeeder;
use Database\Seeders\OperatorSeeder;
use Database\Seeders\JenisDokumenSeeder;
use Database\Seeders\ArsipSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(KecamatanSeeder::class);
        $this->call(KelurahanSeeder::class);
        $this->call(HakAksesSeeder::class);
        $this->call(OperatorSeeder::class);
        $this->call(JenisDokumenSeeder::class);
        $this->call(ArsipSeeder::class);
    }
}
