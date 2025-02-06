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
        Schema::create('shears', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('M')->nullable();
            $table->unsignedBigInteger('K')->nullable();
            $table->float('Nsw')->nullable();
            $table->float('Msw')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shears');
    }
};
