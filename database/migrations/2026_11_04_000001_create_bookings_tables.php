<?php

use App\Models\Form;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('form_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 190)->nullable();
            $table->string('email', 190)->nullable()->index();
            $table->json('details')->nullable();            // the visitor's answers, frozen like a response
            $table->dateTime('starts_at')->index();          // stored in UTC
            $table->dateTime('ends_at');
            $table->string('status', 12)->default('pending'); // pending | approved | declined | cancelled
            // Holds the start time while the slot is in use, so two requests can never take the same slot.
            $table->string('active_slot', 20)->nullable()->unique();
            $table->string('meeting_link', 500)->nullable();
            $table->text('note_to_visitor')->nullable();
            $table->text('decline_reason')->nullable();
            $table->string('decided_via', 12)->nullable();    // email | admin
            $table->timestamp('decided_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('uid', 60)->unique();              // calendar event id
            $table->timestamps();
        });

        // The discovery-call form now picks a real slot instead of a free date and time.
        $form = Form::where('slug', 'security-discovery-call')->where('is_system', false)->first();
        if ($form) {
            $fields = collect($form->fields)->reject(fn ($f) => in_array($f['key'], ['pref_date', 'pref_time'], true))->values()->all();
            array_unshift($fields, ['key' => 'slot', 'label' => 'Choose a date and time', 'type' => 'slot', 'required' => true]);
            $form->fields = $fields;
            $form->settings = array_merge($form->settings ?? [], ['notify_email' => '', 'auto_reply' => false]);
            $form->save();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_settings');
    }
};
