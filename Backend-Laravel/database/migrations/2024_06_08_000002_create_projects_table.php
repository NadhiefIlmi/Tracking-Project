<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_app');
            $table->string('project_name');
            $table->string('base_project');
            $table->string('fungsi');
            $table->string('technology');
            $table->string('framework');
            $table->string('version');
            $table->timestamps();

            $table->foreign('id_app')->references('id')->on('apps')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
}; 