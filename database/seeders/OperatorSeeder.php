<?php

namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'NAMA_OPERATOR' => 'Rafi',
                'EMAIL' => 'operator1@example.com',
                'PASSWORD' => Hash::make('password123'),
                'ID_AKSES' => 3,
            ],
            [
                'NAMA_OPERATOR' => 'Idris',
                'EMAIL' => 'operator2@example.com',
                'PASSWORD' => Hash::make('password456'),
                'ID_AKSES' => 3,
            ],
            [
                'NAMA_OPERATOR' => 'Dhika',
                'EMAIL' => 'operator3@example.com',
                'PASSWORD' => Hash::make('password123'),
                'ID_AKSES' => 2,
            ],
            [
                'NAMA_OPERATOR' => 'Badruz',
                'EMAIL' => 'operator4@example.com',
                'PASSWORD' => Hash::make('password123'),
                'ID_AKSES' => 1,
            ],
        ];

        // Memasukkan data operator ke dalam database
        foreach ($operators as $operator) {
            Operator::create($operator);
        }
    }
}
