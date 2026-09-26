<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            $table->enum('status', [
                'draft',
                'pending_review',
                'published',
                'revision',
                'rejected',
                'archived',
            ])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes for frequently queried columns
            $table->index('status');
            $table->index('published_at');
            $table->index('views');
            // Composite index for public feed queries (status + published_at)
            $table->index(['status', 'published_at']);
        });

        // Full-text index on title and content for search functionality (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE articles ADD FULLTEXT INDEX articles_fulltext (title, content)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
