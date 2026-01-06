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
        Schema::create('letter_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoing_letter_id')->nullable()->constrained('outgoing_letters')->onDelete('cascade');
            $table->foreignId('incoming_letter_id')->nullable()->constrained('incoming_letters')->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('users');
            $table->string('action'); // approved, rejected, etc
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_histories');
    }
};
