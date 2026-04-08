<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->enum('type', ['event', 'blocked', 'limited'])->default('event');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity_override')->nullable()->comment('Override max reservations for this date');
            $table->boolean('booking_allowed')->default(true);
            $table->string('color', 7)->default('#f59e0b')->comment('Hex color for calendar display');
            $table->timestamps();

            $table->unique(['restaurant_id', 'date', 'name']);
            $table->index(['restaurant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_dates');
    }
};
