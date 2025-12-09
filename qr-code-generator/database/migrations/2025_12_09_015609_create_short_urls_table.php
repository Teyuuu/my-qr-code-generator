<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('short_code', 10)->unique()->index();
            $table->text('destination_url')->nullable();           // NULL = internal form
            $table->timestamp('expires_at')->nullable();

            // This single line fixes ALL timestamp issues
            $table->timestamps(); // created_at + updated_at with correct defaults

            $table->enum('status', ['active', 'expired'])->default('active');

            // Optional: who created this QR code
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Composite index for performance
            $table->index(['short_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
