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
        Schema::create('volantes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('permit_id');
            $table->integer('stall_id');
            $table->string('business_name');
            $table->double('quantity')->default(0);
            $table->text('location')->nullable();
            $table->integer('duration'); // 1- Day, 2-Week, 3- Event
            $table->integer('status')->default(2);//1- Occupied, 2-Vacant, 3- Reserved'
            $table->date('started_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volantes');
    }
};
