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
        Schema::create('incoming_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('input_by_user_id')->constrained('users');
            $table->string('agenda_number')->unique();
            $table->string('sender');
            $table->string('reference_number');
            $table->string('subject');
            $table->date('letter_date');
            $table->date('received_date');
            $table->string('priority')->default('Biasa');
            $table->string('file_path');
            $table->string('status')->default('waiting_disposition');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_letters');
    }
};
