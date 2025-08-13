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
        Schema::create('ip_publiks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_waf');
            $table->string('ip_publik');
            $table->timestamps();

            $table->foreign('id_waf')->references('id')->on('wafs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_publiks');
    }
}; 