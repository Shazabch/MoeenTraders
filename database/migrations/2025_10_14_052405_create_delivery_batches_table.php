<?php

// database/migrations/xxxx_create_delivery_batches_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->integer('created_by')->nullable();
            $table->integer('area_id')->nullable();
            $table->date('delivery_date');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_batches');
    }
}






