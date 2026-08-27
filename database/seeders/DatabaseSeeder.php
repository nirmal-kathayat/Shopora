<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public $adminPassword = '$2y$10$JcmAHe5eUZ2rS0jU1GWr/.xhwCnh2RU13qwjTPcqfmtZXjZxcryPO';
    public function run(): void
    {
        // admin seeder
        \DB::connection('mysql')->table('admins')->insert([
            [
                'id' => '1',
                'username' => 'superadmin',
                'password' => $this->adminPassword,
                'email' => 'superadmin@gmail.com',
                'name' => 'Super Admin',
                'created_at' => date('Y-m-d H:i:s')
            ],
        ]);

        // permission seeder
        \DB::statement("
         INSERT INTO `permissions` (`id`, `name`, `access_uri`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
         (1, 'All System Control', '/*', NULL, NULL, '2022-07-04 21:22:16', '2022-07-04 21:22:16')   
      ");
        // role seeder
        \DB::statement("
       INSERT INTO `roles` (`id`, `name`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
       (1, 'Super Admin', NULL, NULL, '2022-07-04 21:23:23', '2022-07-04 21:23:23')
      ");


        \DB::statement("
       INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
       (1, 1, 1)
      ");


        \DB::statement("
       INSERT INTO `admin_roles` (`id`, `role_id`, `admin_id`) VALUES
       (1, 1, 1)
      ");

        // payment modes needed by sales / dashboard / invoice
        $this->call(PaymentModeSeeder::class);
    }
}
