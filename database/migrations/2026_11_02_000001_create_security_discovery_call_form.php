<?php

use App\Models\Form;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** The consultancy page embeds this form. It stays editable in Admin > Forms. */
    public function up(): void
    {
        if (Form::where('slug', 'security-discovery-call')->exists()) {
            return;
        }

        Form::create([
            'name' => 'Security discovery call',
            'slug' => 'security-discovery-call',
            'description' => 'Select a preferred date and time, then tell us about your organisation.',
            'status' => 'open',
            'is_system' => false,
            'fields' => [
                ['key' => 'pref_date', 'label' => 'Preferred date', 'type' => 'date', 'required' => true],
                ['key' => 'pref_time', 'label' => 'Preferred time', 'type' => 'select', 'required' => true,
                    'options' => ['10:00 AM', '11:30 AM', '2:00 PM', '4:00 PM']],
                ['key' => 'name', 'label' => 'Your name', 'type' => 'text', 'required' => true, 'placeholder' => 'Full name', 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Work email', 'type' => 'email', 'required' => true, 'placeholder' => 'name@company.com'],
                ['key' => 'company', 'label' => 'Organisation', 'type' => 'text', 'required' => true, 'placeholder' => 'Business or organisation name'],
                ['key' => 'size', 'label' => 'Organisation size', 'type' => 'select', 'required' => true,
                    'options' => ['1–10 employees', '11–50 employees', '51–200 employees', '201+ employees']],
                ['key' => 'concern', 'label' => 'Main concern (optional)', 'type' => 'textarea', 'required' => false,
                    'placeholder' => "Briefly describe what you'd like to discuss. Please don't share passwords or sensitive information."],
            ],
            'settings' => array_merge(Form::defaultSettings(), [
                'submit_label' => 'Request my free call',
                'success_message' => 'Thank you. We have your request and will reply within one business day to confirm a time.',
                'consent_required' => true,
                'consent_text' => 'I agree to be contacted by Forward Edge about this request and accept the Privacy Policy.',
                'notify_email' => config('contact.email'),
                'auto_reply' => true,
                'auto_reply_subject' => 'We received your discovery call request',
                'auto_reply_body' => 'Thank you for reaching out. We will reply within one business day to confirm a time for your free 30-minute security discovery call.',
            ]),
        ]);
    }

    public function down(): void
    {
        Form::where('slug', 'security-discovery-call')->where('is_system', false)->delete();
    }
};
