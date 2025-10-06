<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['up', 'down']);
            $table->text('message')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['router_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_status_logs');
    }
};
