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
        Schema::create('incoming_dispositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_letter_id')->constrained('incoming_letters')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('target_division');
            $table->text('instruction')->nullable();
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_dispositions');
    }
};
