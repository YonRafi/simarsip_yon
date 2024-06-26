<?php

namespace Database\Seeders;


use App\Models\JenisDokumen;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisDokumens = [
            ['ID_DOKUMEN' => 1, 'NAMA_DOKUMEN' => 'Akta Kelahiran'],
            ['ID_DOKUMEN' => 2, 'NAMA_DOKUMEN' => 'Akta Perkawinan'],
            ['ID_DOKUMEN' => 3, 'NAMA_DOKUMEN' => 'Akta Perceraian'],
            ['ID_DOKUMEN' => 4, 'NAMA_DOKUMEN' => 'Akta Pengangkatan Anak'],
            ['ID_DOKUMEN' => 5, 'NAMA_DOKUMEN' => 'Akta Pengakuan Anak'],
            ['ID_DOKUMEN' => 6, 'NAMA_DOKUMEN' => 'Akta Pengesahan Anak'],
            ['ID_DOKUMEN' => 7, 'NAMA_DOKUMEN' => 'Akta Kematian'],
            ['ID_DOKUMEN' => 8, 'NAMA_DOKUMEN' => 'Kartu Tanda Penduduk'],
            ['ID_DOKUMEN' => 9, 'NAMA_DOKUMEN' => 'Kartu Keluarga'],
            ['ID_DOKUMEN' => 10, 'NAMA_DOKUMEN' => 'Surat Pindah'],
            ['ID_DOKUMEN' => 11, 'NAMA_DOKUMEN' => 'SKTT'],
            ['ID_DOKUMEN' => 12, 'NAMA_DOKUMEN' => 'SKOT'],

        ];

        // Memasukkan data jenis dokumen ke dalam database
        foreach ($jenisDokumens as $jenisDokumen) {
            JenisDokumen::create($jenisDokumen);
        }
    }
}
