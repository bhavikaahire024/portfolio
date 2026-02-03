<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vote_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->string('ip_address', 64);
            $table->foreignId('previous_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->timestamp('previous_voted_at');
            $table->timestamp('released_at');
            $table->foreignId('new_option_id')->nullable()->constrained('poll_options')->nullOnDelete();
            $table->timestamp('new_voted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_histories');
    }
};
