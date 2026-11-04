<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('download_token', 40)->nullable()->unique()->after('export_batch');
            $table->timestamp('resource_sent_at')->nullable()->after('download_token');
            $table->string('resource_error', 255)->nullable()->after('resource_sent_at');
            $table->timestamp('resource_downloaded_at')->nullable()->after('resource_error');
            $table->unsignedSmallInteger('download_count')->default(0)->after('resource_downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['download_token', 'resource_sent_at', 'resource_error', 'resource_downloaded_at', 'download_count']);
        });
    }
};
