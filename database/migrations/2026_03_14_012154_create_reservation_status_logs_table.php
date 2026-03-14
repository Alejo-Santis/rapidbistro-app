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
        Schema::create('reservation_status_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('previous_status', [
                'pending',
                'confirmed',
                'seated',
                'completed',
                'cancelled',
                'no_show',
            ])->nullable();
            $table->enum('new_status', [
                'pending',
                'confirmed',
                'seated',
                'completed',
                'cancelled',
                'no_show',
            ]);
            $table->string('reason')->nullable()->comment('Reason for status change, e.g. cancellation reason');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_status_logs');
    }
};
