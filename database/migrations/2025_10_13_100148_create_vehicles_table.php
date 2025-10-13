<?php

// database/migrations/xxxx_xx_xx_create_vehicles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(); // e.g., "Van 101", "Truck A"
            $table->string('license_plate', 50)->unique()->nullable();
            $table->string('container_type', 50)->nullable(); // e.g., "Cooler Box", "Standard Cargo"
            $table->unsignedBigInteger('driver_id')->nullable(); // Foreign key to a User/Staff table
            $table->string('driver_name')->nullable(); // Foreign key to a User/Staff table
            $table->string('driver_contact')->nullable(); // Foreign key to a User/Staff table
            $table->double('max_capacity_kg', 8, 2)->nullable();
            $table->string('status')->default('available')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};