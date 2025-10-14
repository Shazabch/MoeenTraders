<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_tracking', function (Blueprint $table) {
            $table->id();
            $table->integer('assignment_id')->nullable();
            $table->integer('batch_order_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_tracking');
    }
};
