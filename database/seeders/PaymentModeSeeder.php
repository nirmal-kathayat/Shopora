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
        // payment mode seeder - updateOrInsert so re-seeding an existing
        // database adds what is missing instead of duplicating the list.
        $modes = ['Cash', 'Fonepay', 'eSewa', 'Bank', 'Khalti', 'Cash on Delivery'];

        foreach ($modes as $title) {
            DB::table('payment_modes')->updateOrInsert(
                ['payment_title' => $title],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
