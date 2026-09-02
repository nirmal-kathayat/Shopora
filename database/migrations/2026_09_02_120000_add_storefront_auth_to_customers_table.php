<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give the customer record the columns it needs to double as a storefront
     * login. A row created by the admin from the POS screen simply leaves
     * email and password null, which is what marks it as "not registered".
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email', 191)->nullable()->unique()->after('name');
            $table->string('password', 191)->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->rememberToken()->after('pan_number');

            // The phone doubles as a login identifier, so it has to resolve to
            // exactly one customer.
            $table->unique('ph_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_ph_number_unique');
            $table->dropUnique('customers_email_unique');
            $table->dropColumn(['email', 'password', 'email_verified_at', 'remember_token']);
        });
    }
};
