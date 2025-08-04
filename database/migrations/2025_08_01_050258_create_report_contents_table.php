<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id'); // ← 外部キー制約なし
            $table->text('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_contents');
    }
};