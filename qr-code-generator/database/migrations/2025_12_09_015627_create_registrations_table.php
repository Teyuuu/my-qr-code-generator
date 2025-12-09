<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('short_code', 10);

            $table->string('firstname', 50);
            $table->string('middlename', 50)->nullable();
            $table->string('lastname', 50);
            $table->string('lgu_company', 100);
            $table->string('position', 50);
            $table->string('contact', 50);
            $table->text('purpose');

            // Best practice: use timestamps() instead of manual created_at
            $table->timestamps(); // created_at + updated_at (auto-handled)

            // Foreign key
            $table->foreign('short_code')
                  ->references('short_code')
                  ->on('short_urls')
                  ->onDelete('cascade');

            // Index for fast lookups
            $table->index('short_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
