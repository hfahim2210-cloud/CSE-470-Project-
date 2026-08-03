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
    Schema::create('gigs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller/User ID
        $table->string('title');
        $table->string('category'); // e.g., Academics, Tech, Creative
        $table->text('description');
        $table->decimal('price', 8, 2);
        $table->integer('delivery_time'); // in days
        $table->boolean('is_archived')->default(false); // Archive feature
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};
