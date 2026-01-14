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
        Schema::table('letter_types', function (Blueprint $table) {
            $table->json('form_config')->nullable()->after('code'); 
        });

        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->json('additional_data')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_types', function (Blueprint $table) {
            $table->dropColumn('form_config');
        });
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropColumn('additional_data');
        });
    }
};
