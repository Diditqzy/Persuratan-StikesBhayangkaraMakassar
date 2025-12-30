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
        Schema::create('outgoing_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Pemohon
            $table->foreignId('type_id')->constrained('letter_types');
            $table->foreignId('signer_id')->constrained('signers');
            
            // Data Surat
            $table->string('letter_number')->nullable()->unique();
            $table->string('recipient');
            $table->string('subject');
            $table->date('letter_date');
            $table->string('attachment_text')->nullable();
            $table->json('content_data')->nullable();
            $table->string('status')->default('draft');
            
            // Log Admin
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Log Pimpinan & Final
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->string('final_file_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_letters');
    }
};
