<?php

namespace App\Support;

use App\Models\Form;
use App\Models\Page;

/**
 * Starting points for a new page. Each one is a list of ordinary blocks, so after creation
 * they are edited, reordered and deleted like any other. Pages start as drafts.
 */
final class PageTemplates
{
    public static function all(): array
    {
        return [
            'blank' => [
                'name' => 'Blank page',
                'description' => 'Start empty and add sections yourself.',
                'for' => 'Anything',
            ],
            'service' => [
                'name' => 'Service page',
                'description' => 'What you offer, what is included, how it works, what clients say, questions answered, and a booking call to action.',
                'for' => 'Consultancy, security assessments, training',
            ],
            'about' => [
                'name' => 'About us',
                'description' => 'Your story, the numbers behind it, what you stand for, and a way to get in touch.',
                'for' => 'About, team and company pages',
            ],
            'contact' => [
                'name' => 'Contact page',
                'description' => 'Email, phone and address up front, a message form, and quick answers to what people usually ask.',
                'for' => 'Contact and support pages',
            ],
            'resource' => [
                'name' => 'Resource download',
                'description' => 'A focused page for a guide or checklist: what is inside, why it helps, and a form that sends the download.',
                'for' => 'Lead magnets, guides, checklists',
            ],
            'thank_you' => [
                'name' => 'Thank you page',
                'description' => 'Confirms what happened, explains what comes next, and points to events and your main offer.',
                'for' => 'After a form, signup or booking',
            ],
            'marketing' => [
                'name' => 'Marketing page',
                'description' => 'Headline, key numbers, the case for it, common questions, a signup form and a closing call to action.',
                'for' => 'Services, offers, campaigns',
            ],
            'programme' => [
                'name' => 'Programme page',
                'description' => 'What the programme covers, how it runs, questions answered, and a way to express interest.',
                'for' => 'Bootcamps, academic and training programmes',
            ],
            'event_upcoming' => [
                'name' => 'Upcoming event',
                'description' => 'Date and place up top, what to expect, an interest form, and other events coming up.',
                'for' => 'Link it to an event so the date and place fill in',
            ],
            'event_recap' => [
                'name' => 'Event recap',
                'description' => 'The numbers from the day, what happened, and the next events to look forward to.',
                'for' => 'After the event, to show it off',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** @param array $fill real values (title, subtitle, highlights) that replace the template's placeholders */
    public static function seed(Page $page, string $key, array $fill = []): void
    {
        $order = 0;
        foreach (self::blocks($key) as [$type, $data]) {
            $data = self::fillFrom($type, $data, $fill);
            $page->blocks()->create([
                'type' => $type,
                'variant' => null,
                'data' => $data,
                'order' => $order += 10,
                'is_published' => true,
            ]);
        }
    }

    /** Add a few sections to the end of an existing page. */
    public static function append(Page $page, string $key): void
    {
        $order = (int) $page->blocks()->max('order');
        foreach (self::blocks($key) as [$type, $data]) {
            $page->blocks()->create(['type' => $type, 'variant' => null, 'data' => $data, 'order' => $order += 10, 'is_published' => true]);
        }
    }

    private static function fillFrom(string $type, array $data, array $fill): array
    {
        if ($type === 'page_header') {
            if (!empty($fill['title'])) {
                $data['title'] = $fill['title'];
            }
            if (!empty($fill['subtitle'])) {
                $data['subtitle'] = $fill['subtitle'];
            }
        }
        if ($type === 'stats' && !empty($fill['highlights'])) {
            $data['items'] = array_values($fill['highlights']);
        }

        return $data;
    }

    /** A general-purpose "express interest" form, created the first time a template needs it. */
    public static function interestForm(): Form
    {
        return Form::firstOrCreate(['slug' => 'interest'], [
            'name' => 'Express interest',
            'description' => 'Tell us a little about you and we will be in touch.',
            'fields' => [
                ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false, 'maps_to' => 'SMS'],
                ['key' => 'message', 'label' => 'Anything we should know?', 'type' => 'textarea', 'required' => false],
            ],
            'settings' => array_merge(Form::defaultSettings(), [
                'submit_label' => 'Express interest',
                'success_message' => 'Thank you. We will be in touch soon.',
                'consent_required' => true,
            ]),
            'status' => 'open',
        ]);
    }

    /** A general enquiry form, created the first time the contact template needs it. */
    public static function contactForm(): Form
    {
        return Form::firstOrCreate(['slug' => 'contact'], [
            'name' => 'Contact us',
            'description' => 'Send us a message and we will reply within two working days.',
            'fields' => [
                ['key' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'maps_to' => 'FULLNAME'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['key' => 'topic', 'label' => 'What is this about?', 'type' => 'select', 'required' => false, 'options' => ['A service', 'A programme', 'An event', 'Something else']],
                ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ],
            'settings' => array_merge(Form::defaultSettings(), [
                'submit_label' => 'Send message',
                'success_message' => 'Thank you. We will reply within two working days.',
                'consent_required' => true,
            ]),
            'status' => 'open',
        ]);
    }

    private static function blocks(string $key): array
    {
        $news = Form::system('newsletter')->id;

        return match ($key) {
            'service' => [
                ['page_header', [
                    'kicker' => 'Service',
                    'title' => 'Name of the service',
                    'subtitle' => 'Who it is for and the result they get, in one or two sentences.',
                    'primary_text' => 'Book a free discovery call',
                    'primary_link' => '/consultancy',
                    'secondary_text' => 'See how it works',
                    'secondary_link' => '#how-it-works',
                ]],
                ['feature_grid', [
                    'title' => 'What is included',
                    'subtitle' => 'The parts of the work, so nobody has to guess.',
                    'tone' => 'tint',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'First deliverable', 'text' => 'Say what it is and why it matters to the client.'],
                        ['title' => 'Second deliverable', 'text' => 'Keep each card to two short sentences.'],
                        ['title' => 'Ongoing support', 'text' => 'Describe what happens after the main work is done.'],
                    ],
                ]],
                ['steps', [
                    'title' => 'How it works',
                    'items' => [
                        ['title' => 'A free first conversation', 'text' => 'Thirty minutes to understand your situation. No obligation.'],
                        ['title' => 'A clear plan', 'text' => 'We agree scope, timeline and cost in writing before anything starts.'],
                        ['title' => 'The work', 'text' => 'Describe how you deliver and how often you report back.'],
                        ['title' => 'Handover and follow-up', 'text' => 'What the client keeps, and how you check it is working.'],
                    ],
                ]],
                ['quote_list', [
                    'title' => 'What clients say',
                    'tone' => 'tint',
                    'items' => [
                        ['quote' => 'Replace this with a real comment from a client, in their own words.', 'name' => 'Client name', 'role' => 'Role, Organisation'],
                    ],
                ]],
                ['faq_list', [
                    'title' => 'Questions',
                    'items' => [
                        ['q' => 'How long does it take?', 'a' => 'Give a typical range and what affects it.'],
                        ['q' => 'What does it cost?', 'a' => 'Say how pricing works, or how to get a quote.'],
                        ['q' => 'Do you work with small teams?', 'a' => 'Say who you work with and who you do not.'],
                    ],
                ]],
                ['cta_band', ['title' => 'Ready to talk it through?', 'subtitle' => 'A free 30 minute call. You leave with a clear next step either way.', 'primary_text' => 'Book a free discovery call', 'primary_link' => '/consultancy']],
            ],
            'about' => [
                ['page_header', [
                    'kicker' => 'About us',
                    'title' => 'Who we are and why we exist',
                    'subtitle' => 'One sentence on what you do and who you do it for.',
                ]],
                ['rich_text', [
                    'title' => 'Our story',
                    'width' => 'narrow',
                    'body' => '<p>How it started, the problem you saw, and what you built in response. Write it the way you would tell it to a new client.</p><p>Add a second paragraph on where you are now and where you are heading.</p>',
                ]],
                ['stats', [
                    'tone' => 'tint',
                    'items' => [
                        ['value' => '10+', 'label' => 'Years of experience'],
                        ['value' => '200', 'label' => 'People trained'],
                        ['value' => '40', 'label' => 'Organisations helped'],
                    ],
                ]],
                ['feature_grid', [
                    'title' => 'What we stand for',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'Plain language', 'text' => 'Security advice you can act on, without the jargon.'],
                        ['title' => 'Practical first', 'text' => 'We start with what reduces risk fastest.'],
                        ['title' => 'Honest advice', 'text' => 'If you do not need something, we will say so.'],
                    ],
                ]],
                ['cta_band', ['title' => 'Want to work with us?', 'primary_text' => 'Book a free discovery call', 'primary_link' => '/consultancy', 'secondary_text' => 'Get in touch', 'secondary_link' => '/contact']],
            ],
            'contact' => [
                ['page_header', [
                    'kicker' => 'Contact',
                    'title' => 'Get in touch',
                    'subtitle' => 'Tell us what you need and we will reply within two working days.',
                ]],
                ['contact_info', [
                    'email' => 'hello@forwardedge.com',
                    'phone' => '',
                    'address' => '',
                    'hours' => 'Monday to Friday, 9am to 5pm',
                ]],
                ['form', ['form_id' => self::contactForm()->id, 'title' => 'Send us a message', 'intro' => 'The more detail you give, the better we can help.', 'tone' => 'tint']],
                ['faq_list', [
                    'title' => 'Before you write',
                    'items' => [
                        ['q' => 'How quickly will you reply?', 'a' => 'Within two working days.'],
                        ['q' => 'Can we talk on a call instead?', 'a' => 'Yes. Book a free 30 minute discovery call from the consultancy page.'],
                    ],
                ]],
            ],
            'resource' => [
                ['page_header', [
                    'kicker' => 'Free guide',
                    'title' => 'Name of the guide or checklist',
                    'subtitle' => 'What it helps the reader do, in one sentence. Mention the format and length.',
                    'primary_text' => 'Get the download',
                    'primary_link' => '#download',
                ]],
                ['feature_grid', [
                    'title' => 'What is inside',
                    'tone' => 'tint',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'First thing they learn', 'text' => 'One short sentence.'],
                        ['title' => 'A checklist to use', 'text' => 'One short sentence.'],
                        ['title' => 'Next steps', 'text' => 'One short sentence.'],
                    ],
                ]],
                ['rich_text', [
                    'title' => 'Who it is for',
                    'width' => 'narrow',
                    'body' => '<p>Describe the reader and the situation where this guide helps most.</p>',
                ]],
                ['form', ['form_id' => self::interestForm()->id, 'title' => 'Get your copy', 'intro' => 'Enter your details and we will send the download straight away.']],
                ['quote_list', ['title' => 'From people who used it', 'items' => [['quote' => 'Replace with a real comment about the guide.', 'name' => 'Name', 'role' => 'Role, Organisation']]]],
            ],
            'thank_you' => [
                ['page_header', [
                    'kicker' => 'All done',
                    'title' => 'Thank you',
                    'subtitle' => 'We have your details. Here is what happens next.',
                ]],
                ['steps', [
                    'title' => 'What happens next',
                    'items' => [
                        ['title' => 'Check your inbox', 'text' => 'You will get a confirmation within a few minutes. Check spam if you do not see it.'],
                        ['title' => 'We review your request', 'text' => 'Someone from the team reads every message personally.'],
                        ['title' => 'We reply', 'text' => 'Expect to hear from us within two working days.'],
                    ],
                ]],
                ['events_grid', ['title' => 'While you wait', 'show' => 'upcoming', 'limit' => 3, 'link_text' => 'All events', 'link' => '/events']],
                ['cta_band', ['title' => 'Read our latest thinking', 'primary_text' => 'Visit the blog', 'primary_link' => '/blog']],
            ],
            'marketing' => [
                ['page_header', [
                    'kicker' => 'Information security',
                    'title' => 'Practical security for growing businesses',
                    'subtitle' => 'Say in one or two sentences what this page offers and who it is for.',
                    'primary_text' => 'Book a free discovery call',
                    'primary_link' => '/consultancy',
                ]],
                ['stats', [
                    'title' => 'In numbers',
                    'tone' => 'tint',
                    'items' => [
                        ['value' => '30 min', 'label' => 'First conversation, free'],
                        ['value' => '3', 'label' => 'Clear steps to a plan'],
                        ['value' => '0', 'label' => 'Obligation to continue'],
                    ],
                ]],
                ['rich_text', [
                    'title' => 'Why it matters',
                    'width' => 'narrow',
                    'body' => '<p>Explain the problem your reader has, in their words. Keep it short and concrete.</p><ul><li>One benefit they will notice first</li><li>A second thing that changes for them</li><li>What they do not need to worry about</li></ul>',
                ]],
                ['feature_grid', [
                    'title' => 'What you get',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'First benefit', 'text' => 'One short sentence.'],
                        ['title' => 'Second benefit', 'text' => 'One short sentence.'],
                        ['title' => 'Third benefit', 'text' => 'One short sentence.'],
                    ],
                ]],
                ['faq_list', [
                    'title' => 'Questions',
                    'items' => [
                        ['q' => 'Who is this for?', 'a' => 'Describe the people this page is written for.'],
                        ['q' => 'What does it cost?', 'a' => 'Say how pricing works, or how to get a quote.'],
                        ['q' => 'What happens next?', 'a' => 'Spell out the first step after someone gets in touch.'],
                    ],
                ]],
                ['form', ['form_id' => $news, 'title' => 'Get updates by email', 'intro' => 'New posts and programme dates, a few times a month.', 'tone' => 'tint']],
                ['cta_band', ['title' => 'Ready to talk it through?', 'primary_text' => 'Book a free discovery call', 'primary_link' => '/consultancy']],
            ],
            'programme' => [
                ['page_header', [
                    'kicker' => 'Programme',
                    'title' => 'Name of the programme',
                    'subtitle' => 'Who it is for, what they will be able to do afterwards, and how long it takes.',
                    'primary_text' => 'Express interest',
                    'primary_link' => '#interest',
                ]],
                ['stats', [
                    'tone' => 'tint',
                    'items' => [
                        ['value' => '4 weeks', 'label' => 'Duration'],
                        ['value' => 'Hands-on', 'label' => 'Format'],
                        ['value' => 'Lagos', 'label' => 'Location'],
                    ],
                ]],
                ['rich_text', [
                    'title' => 'What you will learn',
                    'width' => 'narrow',
                    'body' => '<p>Give a short overview of the programme.</p><h3>Topics covered</h3><ul><li>First topic</li><li>Second topic</li><li>Third topic</li></ul><h3>Who should join</h3><p>Describe the ideal participant.</p>',
                ]],
                ['steps', [
                    'title' => 'How it runs',
                    'tone' => 'tint',
                    'items' => [
                        ['title' => 'Apply', 'text' => 'Express interest below. We confirm your place by email.'],
                        ['title' => 'Learn', 'text' => 'Describe the weekly rhythm: sessions, practice and support.'],
                        ['title' => 'Show what you can do', 'text' => 'Say how the programme ends: a project, an assessment or a certificate.'],
                    ],
                ]],
                ['faq_list', [
                    'title' => 'Before you apply',
                    'items' => [
                        ['q' => 'Do I need prior experience?', 'a' => 'Say what background is expected, if any.'],
                        ['q' => 'When does the next cohort start?', 'a' => 'Give the date, or say how dates are announced.'],
                    ],
                ]],
                ['form', ['form_id' => self::interestForm()->id, 'title' => 'Express interest', 'intro' => 'Leave your details and we will tell you about the next intake.']],
            ],
            'event_upcoming' => [
                ['page_header', [
                    'kicker' => 'Upcoming event',
                    'title' => 'Name of the event',
                    'subtitle' => 'One sentence on what the event is and who should come.',
                ]],
                ['event_facts', ['title' => 'At a glance', 'button_text' => 'Express interest', 'button_link' => '#interest']],
                ['rich_text', [
                    'title' => 'What to expect',
                    'width' => 'narrow',
                    'body' => '<p>Describe the programme for the day, who is speaking and what people will take away.</p>',
                ]],
                ['steps', [
                    'title' => 'On the day',
                    'tone' => 'tint',
                    'items' => [
                        ['title' => 'Arrival and welcome', 'text' => 'Give the start time and what happens first.'],
                        ['title' => 'Main sessions', 'text' => 'Name the speakers or topics.'],
                        ['title' => 'Networking and close', 'text' => 'Say when it ends and what people take home.'],
                    ],
                ]],
                ['form', ['form_id' => self::interestForm()->id, 'title' => 'Express interest', 'intro' => 'Tell us you are interested and we will send details.']],
                ['events_grid', ['title' => 'More events', 'show' => 'upcoming', 'limit' => 3, 'link_text' => 'All events', 'link' => '/events']],
            ],
            'event_recap' => [
                ['page_header', [
                    'kicker' => 'Event recap',
                    'title' => 'Name of the event',
                    'subtitle' => 'One sentence on what happened and why it mattered.',
                ]],
                ['stats', [
                    'title' => 'The day in numbers',
                    'tone' => 'tint',
                    'items' => [
                        ['value' => '120', 'label' => 'Attendees'],
                        ['value' => '8', 'label' => 'Speakers'],
                        ['value' => '5', 'label' => 'Sessions'],
                    ],
                ]],
                ['rich_text', [
                    'title' => 'What happened',
                    'width' => 'narrow',
                    'body' => '<p>Tell the story of the event: the highlights, the quotes people remember and what comes next.</p>',
                ]],
                ['events_grid', ['title' => 'Coming up next', 'show' => 'upcoming', 'limit' => 3, 'link_text' => 'All events', 'link' => '/events']],
                ['cta_band', ['title' => 'Want to be there next time?', 'primary_text' => 'Get in touch', 'primary_link' => '/contact']],
            ],
            // Added to an existing event page once the event is over.
            'recap_sections' => [
                ['stats', [
                    'title' => 'The day in numbers',
                    'tone' => 'tint',
                    'items' => [
                        ['value' => '120', 'label' => 'Attendees'],
                        ['value' => '8', 'label' => 'Speakers'],
                        ['value' => '5', 'label' => 'Sessions'],
                    ],
                ]],
                ['rich_text', [
                    'title' => 'What happened',
                    'width' => 'narrow',
                    'body' => '<p>Tell the story of the event: the highlights, the quotes people remember and what comes next.</p>',
                ]],
            ],
            default => [],
        };
    }
}
