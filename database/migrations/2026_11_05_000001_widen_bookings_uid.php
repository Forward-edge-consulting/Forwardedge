<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A UUID plus "@forwardedgeconsulting.com" is 62 characters, which did not fit the original 60.
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('uid', 120)->change();
        });
    }

    public function down(): void
    {
        // Left wide on purpose: shrinking could truncate existing calendar ids.
    }
};
