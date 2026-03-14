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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('number', 10)->comment('Example: "T-01", "B-12", etc.');
            $table->unsignedSmallInteger('capacity')->comment('Number of seats at the table');
            $table->unsignedSmallInteger('min_capacity')->default(1);
            $table->enum('status', [
                'available',
                'reserved',
                'occupied',
                'maintenance',
                'unavailable',
            ])->default('available');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['zone_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
