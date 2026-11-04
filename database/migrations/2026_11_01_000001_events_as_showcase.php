<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->index()->after('type');
            $table->json('highlights')->nullable()->after('short_description');
            $table->string('cta_type', 12)->default('none')->after('highlights'); // none | interest | link | contact
            $table->string('cta_label', 60)->nullable()->after('cta_type');
            $table->string('cta_url', 500)->nullable()->after('cta_label');
            $table->unsignedBigInteger('cta_form_id')->nullable()->after('cta_url');
        });

        // Starting categories. They are managed in Admin > Events > Categories.
        $now = now();
        $seed = [
            ['Bootcamp', 'bootcamp'], ['Workshop', 'workshop'], ['Training', 'training'], ['Seminar', 'seminar'],
            ['Webinar', 'webinar'], ['Conference', 'conference'], ['Academic programme', 'academic-programme'], ['Community', 'community'],
        ];
        foreach ($seed as $i => [$name, $slug]) {
            DB::table('event_categories')->insert(['name' => $name, 'slug' => $slug, 'sort_order' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Existing events keep their meaning: the old fixed type becomes a category.
        foreach (DB::table('event_categories')->get(['id', 'slug']) as $category) {
            DB::table('events')->where('type', $category->slug)->update(['category_id' => $category->id]);
        }

        // "Completed" events are past events, which now show on the Past wall. Past is worked out from the dates.
        DB::table('events')->where('status', 'completed')->update(['status' => 'published']);

        $this->copyRegistrationsIntoResponses($now);
    }

    /** A read-only copy, so every enquiry lives in Responses. The original table is left untouched. */
    private function copyRegistrationsIntoResponses($now): void
    {
        if (!Schema::hasTable('event_registrations') || !Schema::hasTable('form_submissions')
            || DB::table('forms')->where('slug', 'event-registrations')->exists()) {
            return;
        }

        $formId = DB::table('forms')->insertGetId([
            'name' => 'Event registrations (before online registration was retired)',
            'slug' => 'event-registrations',
            'description' => 'Copied from the old event registration list. Not shown to visitors.',
            'fields' => json_encode([]),
            'settings' => json_encode(['submit_label' => 'Send', 'success_message' => '', 'consent_required' => false]),
            'status' => 'closed',
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $titles = DB::table('events')->pluck('title', 'id');

        DB::table('event_registrations')->orderBy('id')->chunk(200, function ($rows) use ($formId, $titles) {
            $insert = [];
            foreach ($rows as $r) {
                $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? ''));
                $data = [];
                $add = function ($key, $label, $type, $maps, $value) use (&$data) {
                    if (filled($value)) {
                        $data[] = ['key' => $key, 'label' => $label, 'type' => $type, 'maps_to' => $maps, 'value' => (string) $value];
                    }
                };
                $add('name', 'Full name', 'text', 'FULLNAME', $name);
                $add('email', 'Email', 'email', '', $r->email);
                $add('phone', 'Phone', 'tel', 'SMS', $r->phone);
                $add('company', 'Company', 'text', '', $r->company);
                $add('job_title', 'Job title', 'text', '', $r->job_title);
                $add('requirements', 'Special requirements', 'textarea', '', $r->special_requirements);
                $add('event', 'Event', 'text', '', $titles[$r->event_id] ?? null);

                $when = $r->registered_at ?? $r->created_at ?? now();
                $insert[] = [
                    'form_id' => $formId,
                    'email' => filled($r->email) ? strtolower($r->email) : null,
                    'name' => $name !== '' ? $name : null,
                    'data' => json_encode($data),
                    'source_type' => 'event',
                    'source_label' => 'Event: ' . ($titles[$r->event_id] ?? 'Unknown event'),
                    'source_url' => null,
                    'consent_given' => false,   // registering for an event is not consent to marketing email
                    'created_at' => $when,
                    'updated_at' => $when,
                ];
            }
            DB::table('form_submissions')->insert($insert);
        });
    }

    public function down(): void
    {
        DB::table('forms')->where('slug', 'event-registrations')->delete();

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'highlights', 'cta_type', 'cta_label', 'cta_url', 'cta_form_id']);
        });
        Schema::dropIfExists('event_categories');
    }
};
