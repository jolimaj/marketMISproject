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
        Schema::create('fee_masterlist', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->double('amount')->default(0);
            $table->boolean('is_daily')->default(false); // per day
            $table->boolean('is_monthly')->default(false); // per day
            $table->boolean('is_styro')->default(false); // per day
            $table->boolean('is_per_kilo')->default(false); // per day
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_masterlist');
    }
};
