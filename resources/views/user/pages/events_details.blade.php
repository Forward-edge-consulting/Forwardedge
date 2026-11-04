@extends('user.master_page')
@section('body_attributes', 'data-native-scroll')
@section('title', $event->title . ' | Forward Edge Consulting')
@php
    $past = $event->is_past;
    $image = $event->banner_image ?: $event->thumbnail;
    $cta = !$past ? $event->cta_type : 'none';
    $ctaText = $event->cta_label ?: ['interest' => 'Express interest', 'link' => 'Find out more', 'contact' => 'Contact us'][$cta] ?? null;
    $ctaLink = match ($cta) { 'interest' => '#interest', 'link' => $event->cta_url, 'contact' => route('contact'), default => null };
    $highlights = collect($event->highlights ?? [])->filter(fn ($h) => filled($h['value'] ?? null) && filled($h['label'] ?? null))->values()->all();

    // An event with no page of its own is composed from the same sections the page builder uses.
    $sections = [
        ['page_header', array_filter([
            'kicker' => $event->category_label . ($past ? ', recap' : ''),
            'title' => $event->title,
            'subtitle' => $event->short_description,
            'primary_text' => $ctaLink ? $ctaText : null,
            'primary_link' => $ctaLink,
            'image' => $image,
            'image_alt' => $event->title,
        ])],
        ['event_facts', []],
    ];
    if ($highlights) {
        $sections[] = ['stats', ['title' => $past ? 'The day in numbers' : 'At a glance', 'tone' => 'tint', 'items' => $highlights]];
    }
    if ($cta === 'interest') {
        $formId = $event->cta_form_id ?: \App\Support\PageTemplates::interestForm()->id;
        $sections[] = ['form', ['form_id' => $formId, 'title' => $ctaText, 'intro' => 'Leave your details and we will be in touch.', 'anchor' => 'interest']];
    }
    $sections[] = ['events_grid', ['title' => 'More events', 'show' => 'upcoming', 'limit' => 3, 'link_text' => 'All events', 'link' => route('events.index')]];
@endphp

@section('main')
    @foreach ($sections as [$type, $data])
        @include('user.pages.block.' . $type, ['block' => (object) ['id' => 0, 'data' => $data, 'page' => (object) ['pageable' => $event]]])
    @endforeach
@endsection
