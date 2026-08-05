<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('hire_request_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('hire_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hire_request_id');
        });
    }
};
