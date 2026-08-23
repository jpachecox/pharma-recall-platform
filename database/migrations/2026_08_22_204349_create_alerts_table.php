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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number', 50);
            $table->string('channel', 20)->default('email');
            $table->enum('status', ['sent', 'failed', 'queued', 'pending'])->default('queued');
            $table->text('message_body')->nullable();
            $table->index(['lot_number', 'sent_at'], 'idx_alerts_lot_sent');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
