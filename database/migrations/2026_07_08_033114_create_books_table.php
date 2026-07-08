<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // ID of this book on the local (producer) instance — lets local
            // find and update the same hosted record on re-publish instead
            // of creating a duplicate every time.
            $table->unsignedBigInteger('source_local_id')->unique();
            $table->string('title');
            $table->string('author')->nullable();
            $table->unsignedInteger('total_pages')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
