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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('categories_ids')->constrained('categories')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade'); // zmiana typu kolumny na foreignUuid
            // $table->foreignId('post_detail_id')->constrained('post_details')->onDelete('cascade');
            $table->string('name');
            $table->enum('status', ['published', 'draft']);
            $table->string('hero-image');
            $table->string('link')->unique();
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
