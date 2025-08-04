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
        Schema::create('hearing_sheets', function (Blueprint $table) {
            $table->id();
            $table->dateTime('interview_datetime');
            $table->string('investigation_type');
            $table->string('client_name');
            $table->string('staff_name');
            $table->text('purpose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearing_sheets');
    }
};
