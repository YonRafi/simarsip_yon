<?php

namespace Database\Seeders;

use App\Models\HakAkses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HakAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hakAkses = [
            ['ID_AKSES' => 1, 'NAMA_AKSES' => 'Loket'],
            ['ID_AKSES' => 2, 'NAMA_AKSES' => 'Arsiparis'],
            ['ID_AKSES' => 3, 'NAMA_AKSES' => 'Operator'],
        ];

        foreach ($hakAkses as $akses) {
            HakAkses::create($akses);
        }
    }
}
