<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kecamatan;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kecamatans = [
            ['ID_KECAMATAN' => 1, 'NAMA_KECAMATAN' => 'Klojen'],
            ['ID_KECAMATAN' => 2, 'NAMA_KECAMATAN' => 'KedungKandang'],
            ['ID_KECAMATAN' => 3, 'NAMA_KECAMATAN' => 'Blimbing'],
            ['ID_KECAMATAN' => 4, 'NAMA_KECAMATAN' => 'Lowokwaru'],
            ['ID_KECAMATAN' => 5, 'NAMA_KECAMATAN' => 'Sukun'],
            ['ID_KECAMATAN' => 6, 'NAMA_KECAMATAN' => 'Lain-lain'],
        ];

        // Memasukkan data kecamatan ke dalam database
        foreach ($kecamatans as $kecamatan) {
            Kecamatan::create($kecamatan);
        }
    }
}
