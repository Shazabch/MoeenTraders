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
        Schema::create('batch_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id');
            $table->integer('sale_id');
            $table->integer('sort_order')->default(0);
            $table->string('delivery_status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_orders');
    }
};
