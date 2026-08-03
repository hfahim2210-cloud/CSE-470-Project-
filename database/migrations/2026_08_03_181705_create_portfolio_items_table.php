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
    Schema::create('portfolio_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('gig_id')->constrained()->onDelete('cascade'); // Links to gig
        $table->string('file_path'); // Path where image/doc is saved
        $table->string('caption')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
    }
};
