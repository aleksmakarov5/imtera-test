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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->Date('Date');
            $table->float('Summ');
            $table->boolean('Type');
            $table->string('NazPay');
            $table->integer('budget_item_id')->nullable();
            $table->string('Kontragent');
            $table->string('Sch');
            $table->integer('deal_id')->nullable();
            $table->integer('status_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};