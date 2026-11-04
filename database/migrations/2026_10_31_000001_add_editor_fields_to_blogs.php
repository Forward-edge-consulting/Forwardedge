<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('category');
            // Live when is_published is true and published_at is empty (older posts) or has passed.
            $table->timestamp('published_at')->nullable()->after('is_published');
            $table->unsignedBigInteger('signup_form_id')->nullable()->index()->after('published_at');
            $table->string('signup_position', 20)->default('end')->after('signup_form_id'); // none | end | after_paragraph
            $table->unsignedTinyInteger('signup_after')->nullable()->after('signup_position');
            $table->string('signup_heading', 160)->nullable()->after('signup_after');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'published_at', 'signup_form_id', 'signup_position', 'signup_after', 'signup_heading']);
        });
    }
};
