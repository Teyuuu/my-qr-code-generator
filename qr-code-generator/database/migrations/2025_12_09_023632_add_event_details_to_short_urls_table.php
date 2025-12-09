<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->string('event_title')->after('short_code');
            $table->string('venue')->nullable()->after('event_title');
            $table->date('event_date')->nullable()->after('venue');
            $table->time('event_time')->nullable()->after('event_date');
            $table->string('department')->nullable()->after('event_time');
            $table->text('description')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->dropColumn(['event_title', 'venue', 'event_date', 'event_time', 'department', 'description']);
        });
    }
};
