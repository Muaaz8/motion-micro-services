<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['tournament', 'match', 'league', 'friendly', 'other'])->default('other');
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedBigInteger('created_by')->comment('User ID from auth-service');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
