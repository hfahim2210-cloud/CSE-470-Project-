<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->integer('max_weekly_orders')->default(5)->after('price');
            $table->boolean('is_accepting_orders')->default(true)->after('max_weekly_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->dropColumn(['max_weekly_orders', 'is_accepting_orders']);
        });
    }
};