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
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->index('subject'); 
            $table->index('letter_number');
            $table->index('letter_date');
        });

        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->index('sender');
            $table->index('subject');
            $table->index('reference_number');
            $table->index('agenda_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropIndex(['subject', 'letter_number', 'letter_date']);
        });
        
        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->dropIndex(['sender', 'subject', 'reference_number', 'agenda_number']);
        });
    }
};
