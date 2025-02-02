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
        Schema::create('edges', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('shear_id')->nullable();
            $table->unsignedBigInteger('npp')->nullable();
            $table->unsignedBigInteger('i1')->nullable();
            $table->unsignedBigInteger('i2')->nullable();
            $table->float('k')->nullable();
            $table->float('h')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edges');
    }
};
