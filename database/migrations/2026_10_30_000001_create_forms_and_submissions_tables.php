<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields');
            $table->json('settings')->nullable();
            $table->string('status', 10)->default('open'); // open | closed
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('email', 190)->nullable()->index();
            $table->string('name', 190)->nullable();
            $table->json('data');
            $table->string('source_type', 30)->nullable();
            $table->string('source_label', 190)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->boolean('consent_given')->default(false);
            $table->string('consent_text', 500)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('exported_at')->nullable()->index();
            $table->string('export_batch', 36)->nullable()->index();
            $table->timestamps();

            $table->index(['form_id', 'created_at']);
        });

        // System forms always exist so the footer, blog widget and legacy page-builder
        // forms have somewhere to store leads, even before an admin builds anything.
        $now = now();
        DB::table('forms')->insert([
            [
                'name' => 'Newsletter',
                'slug' => 'newsletter',
                'description' => 'Newsletter signups from the footer, blog and page forms.',
                'fields' => json_encode([
                    ['key' => 'first_name', 'label' => 'First name', 'type' => 'text', 'required' => false, 'placeholder' => '', 'help' => '', 'options' => [], 'maps_to' => 'FIRSTNAME'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => '', 'help' => '', 'options' => [], 'maps_to' => ''],
                ]),
                'settings' => json_encode([
                    'submit_label' => 'Subscribe',
                    'success_message' => 'Thanks for subscribing.',
                    'consent_required' => true,
                    'consent_text' => 'I agree to receive emails from Forward Edge and accept the Privacy Policy.',
                ]),
                'status' => 'open',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Page forms',
                'slug' => 'page-forms',
                'description' => 'Responses from forms placed in page-builder sections before the form builder existed.',
                'fields' => json_encode([]),
                'settings' => json_encode(['submit_label' => 'Send', 'success_message' => 'Thanks, we will be in touch.', 'consent_required' => false]),
                'status' => 'open',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('forms');
    }
};
