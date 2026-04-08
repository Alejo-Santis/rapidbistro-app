<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->date('anniversary')->nullable();
            $table->text('allergies')->nullable();
            $table->text('preferences')->nullable();
            $table->text('staff_notes')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->timestamps();

            $table->unique(['restaurant_id', 'email']);
        });

        Schema::table('waitlists', function (Blueprint $table) {
            $table->enum('source', ['online', 'walk_in'])->default('online')->after('uuid');
            $table->timestamp('arrived_at')->nullable()->after('notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropColumn(['source', 'arrived_at']);
        });
        Schema::dropIfExists('guests');
    }
};
