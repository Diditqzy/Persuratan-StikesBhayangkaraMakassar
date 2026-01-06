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
        Schema::create('letter_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoing_letter_id')->constrained('outgoing_letters')->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_attachments');
    }
};
