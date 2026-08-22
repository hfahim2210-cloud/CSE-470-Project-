<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hire_requests', function (Blueprint $table): void {
            $table->text('decline_reason')->nullable()->after('accepted_at');
            $table->timestamp('declined_at')->nullable()->after('decline_reason');
        });
    }

    public function down(): void
    {
        Schema::table('hire_requests', function (Blueprint $table): void {
            $table->dropColumn(['decline_reason', 'declined_at']);
        });
    }
};
