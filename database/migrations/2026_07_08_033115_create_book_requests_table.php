<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('requester_name')->nullable();
            $table->text('requester_note')->nullable();
            $table->string('original_filename');
            $table->string('file_path');
            $table->enum('status', ['pending', 'claimed', 'processing', 'completed', 'rejected'])
                ->default('pending');
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
