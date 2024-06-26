<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelurahan;

class KelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelurahans = [
            ['ID_KELURAHAN' => 1, 'NAMA_KELURAHAN' => 'Bareng', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 2, 'NAMA_KELURAHAN' => 'GadingKasri', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 3, 'NAMA_KELURAHAN' => 'Kasin', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 4, 'NAMA_KELURAHAN' => 'Kauman', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 5, 'NAMA_KELURAHAN' => 'Kidul Dalem', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 6, 'NAMA_KELURAHAN' => 'Klojen', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 7, 'NAMA_KELURAHAN' => 'Oro Oro Dowo', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 8, 'NAMA_KELURAHAN' => 'Penanggungan', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 9, 'NAMA_KELURAHAN' => 'Rampal Celaket', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 10, 'NAMA_KELURAHAN' => 'Samaan', 'ID_KECAMATAN' => 1],
            ['ID_KELURAHAN' => 11, 'NAMA_KELURAHAN' => 'Sukoharjo', 'ID_KECAMATAN' => 1],

            ['ID_KELURAHAN' => 12, 'NAMA_KELURAHAN' => 'Arjowinangun', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 13, 'NAMA_KELURAHAN' => 'Bumiayu', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 14, 'NAMA_KELURAHAN' => 'Buring', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 15, 'NAMA_KELURAHAN' => 'CemoroKandang', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 16, 'NAMA_KELURAHAN' => 'KedungKandang', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 17, 'NAMA_KELURAHAN' => 'Kotalama', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 18, 'NAMA_KELURAHAN' => 'Lesanpuro', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 19, 'NAMA_KELURAHAN' => 'Madyopuro', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 20, 'NAMA_KELURAHAN' => 'Mergosono', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 21, 'NAMA_KELURAHAN' => 'Sawojajar', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 22, 'NAMA_KELURAHAN' => 'Tlogowaru', 'ID_KECAMATAN' => 2],
            ['ID_KELURAHAN' => 23, 'NAMA_KELURAHAN' => 'Wonokoyo', 'ID_KECAMATAN' => 2],

            ['ID_KELURAHAN' => 24, 'NAMA_KELURAHAN' => 'Arjosari', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 25, 'NAMA_KELURAHAN' => 'Balearjosari', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 26, 'NAMA_KELURAHAN' => 'Blimbing', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 27, 'NAMA_KELURAHAN' => 'Bunulrejo', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 28, 'NAMA_KELURAHAN' => 'Jodipan', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 29, 'NAMA_KELURAHAN' => 'Kesatrian', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 30, 'NAMA_KELURAHAN' => 'Pandanwangi', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 31, 'NAMA_KELURAHAN' => 'Polehan', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 32, 'NAMA_KELURAHAN' => 'Polowijen', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 33, 'NAMA_KELURAHAN' => 'Purwantoro', 'ID_KECAMATAN' => 3],
            ['ID_KELURAHAN' => 34, 'NAMA_KELURAHAN' => 'Purwodadi', 'ID_KECAMATAN' => 3],

            ['ID_KELURAHAN' => 35, 'NAMA_KELURAHAN' => 'Dinoyo', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 36, 'NAMA_KELURAHAN' => 'Jatimulyo', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 37, 'NAMA_KELURAHAN' => 'KetawangGede', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 38, 'NAMA_KELURAHAN' => 'Lowokwaru', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 39, 'NAMA_KELURAHAN' => 'Merjosari', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 40, 'NAMA_KELURAHAN' => 'Mojolangu', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 41, 'NAMA_KELURAHAN' => 'Sumbersari', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 42, 'NAMA_KELURAHAN' => 'Tasikmadu', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 43, 'NAMA_KELURAHAN' => 'Tunggulwulung', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 44, 'NAMA_KELURAHAN' => 'Tlogomas', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 45, 'NAMA_KELURAHAN' => 'Tulusrejo', 'ID_KECAMATAN' => 4],
            ['ID_KELURAHAN' => 46, 'NAMA_KELURAHAN' => 'Tujungsekar', 'ID_KECAMATAN' => 4],

            ['ID_KELURAHAN' => 47, 'NAMA_KELURAHAN' => 'Bakalan Krajan', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 48, 'NAMA_KELURAHAN' => 'Bandulan', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 49, 'NAMA_KELURAHAN' => 'BandungRejosari', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 50, 'NAMA_KELURAHAN' => 'Ciptomulyo', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 51, 'NAMA_KELURAHAN' => 'Gadang', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 52, 'NAMA_KELURAHAN' => 'Karang Besuki', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 53, 'NAMA_KELURAHAN' => 'Kebonsari', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 54, 'NAMA_KELURAHAN' => 'Mulyorejo', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 55, 'NAMA_KELURAHAN' => 'Pisang Candi', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 56, 'NAMA_KELURAHAN' => 'Sukun', 'ID_KECAMATAN' => 5],
            ['ID_KELURAHAN' => 57, 'NAMA_KELURAHAN' => 'Tanjungrejo', 'ID_KECAMATAN' => 5],

            ['ID_KELURAHAN' => 58, 'NAMA_KELURAHAN' => 'Lain-lain', 'ID_KECAMATAN' => 6]

        ];

        // Memasukkan data kelurahan ke dalam database
        foreach ($kelurahans as $kelurahan) {
            Kelurahan::create($kelurahan);
        }
    }
}
