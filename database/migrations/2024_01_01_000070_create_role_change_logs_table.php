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
        Schema::create('role_change_logs', function (Blueprint $table) {
            $table->id();
            // The user whose role/status was changed
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // The admin who made the change (nullable for system actions)
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('old_role', 50)->nullable();
            $table->string('new_role', 50)->nullable();
            $table->boolean('old_status')->nullable();
            $table->boolean('new_status')->nullable();
            // e.g. 'role_change', 'status_change'
            $table->string('action', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();

            $table->index('user_id');
            $table->index('changed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_change_logs');
    }
};
