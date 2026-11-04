<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('type')->default('conference')->change();
        });
    }

    public function down(): void
    {
        // Keep the string column so existing bootcamp events remain valid.
    }
};
