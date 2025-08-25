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
        Schema::create('stalls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('stall_category_id');
            $table->string('area_of_sqr_meter')->nullable();
            $table->json('size')->nullable();
            $table->text('location')->nullable();
            $table->integer('status')->default(2);//1- Occupied, 2-Vacant, 3- Reserved 4-Under Maintenance'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stalls');
    }
};
