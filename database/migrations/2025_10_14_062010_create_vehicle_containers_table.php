<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('vehicle_containers', function (Blueprint $table) {
            $table->id();
            $table->integer('assignment_id')->nullable();
            $table->string('container_name')->nullable(); // Front, Back, Left, Right, etc.
            $table->integer('position')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_containers');
    }
};
