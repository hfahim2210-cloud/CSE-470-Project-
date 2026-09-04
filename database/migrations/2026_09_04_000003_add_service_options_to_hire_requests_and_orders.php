<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hire_requests', function (Blueprint $table): void {
            $table->json('selected_tier')->nullable()->after('proposed_deadline');
            $table->json('selected_addons')->nullable()->after('selected_tier');
            $table->decimal('quoted_price', 10, 2)->nullable()->after('selected_addons');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->json('selected_tier')->nullable()->after('agreed_price');
            $table->json('selected_addons')->nullable()->after('selected_tier');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['selected_tier', 'selected_addons']);
        });

        Schema::table('hire_requests', function (Blueprint $table): void {
            $table->dropColumn(['selected_tier', 'selected_addons', 'quoted_price']);
        });
    }
};
