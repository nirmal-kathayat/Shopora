<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // payment mode seeder 
        DB::table('payment_modes')->insert([
            ['payment_title' => 'Cash'],
            ['payment_title' => 'Fonepay'],
            ['payment_title' => 'eSewa'],
            ['payment_title' => 'Bank'],
            ['payment_title' => 'Khalti'],
        ]);
    }
}
