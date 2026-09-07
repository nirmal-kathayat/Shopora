<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The gateway's own id for a payment attempt, where it mints one.
     *
     * payment_uuid is ours and is what a callback carries back; this is the
     * other direction - Stripe's Checkout Session id, which is the only handle
     * we have for asking Stripe about a payment when no callback ever arrives.
     * Nullable because eSewa has no equivalent: there, our uuid is the handle.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_session', 191)->nullable()->after('payment_uuid');
            $table->index('payment_session');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['payment_session']);
            $table->dropColumn('payment_session');
        });
    }
};
