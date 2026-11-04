<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class PageBlueprint
{
    /**
     * Allowed block types. Keep these aligned to your Blade partial filenames
     * under resources/views/user/pages/block/{type}.blade.php
     */
    public static function allowedTypes(): array
    {
        return [
            // Current sections
            'page_header',
            'rich_text',
            'stats',
            'form',
            'events_grid',
            'event_facts',
            'cta_band',
            'faq_list',
            'feature_grid',
            'steps',
            'quote_list',
            'contact_info',
            // Classic sections
            'hero',
            'hero2',
            'hero3',
            'hero4',
            'program_overview',
            'overview',
            'overview2',
            'form_dark',
            'form_light',
            'logo_slider',
            'about',
            'about2',
            'sections',
            'sections2',
            'marquees',
            'gallary',        // (spelled this way to match the provided blade)
            'testimonial',
            'faq',
            'closing_cta',
        ];
    }

    /** Sections built in the current design. Pages that use them scroll natively so jump links and keyboard focus work. */
    public static function modernTypes(): array
    {
        return ['page_header', 'rich_text', 'stats', 'form', 'events_grid', 'event_facts', 'cta_band', 'faq_list', 'feature_grid', 'steps', 'quote_list', 'contact_info'];
    }

    /** Types offered when adding a section. The two older form blocks keep rendering but are no longer offered. */
    public static function pickerTypes(): array
    {
        return array_values(array_diff(self::allowedTypes(), ['form_dark', 'form_light']));
    }

    public static function ensureValidType(string $type): void
    {
        if (! in_array($type, self::allowedTypes(), true)) {
            abort(422, "Unsupported block type: {$type}");
        }
    }

    /**
     * Validation rules for a block's `data` payload. These keys must match the JSON
     * your admin editor serializes.
     */

    public static function rulesFor(string $type): array
    {
        // Files (UploadedFile) – 5MB, common image types
        $fileImg = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return match ($type) {

            /* ================= PAGE HEADER ================= */
            'page_header' => [
                'kicker'         => ['nullable', 'string', 'max:80'],
                'title'          => ['required', 'string', 'max:180'],
                'subtitle'       => ['nullable', 'string', 'max:400'],
                'primary_text'   => ['nullable', 'string', 'max:60'],
                'primary_link'   => self::linkRule(),
                'secondary_text' => ['nullable', 'string', 'max:60'],
                'secondary_link' => self::linkRule(),
                'image'          => $fileImg,
                'image_alt'      => ['nullable', 'string', 'max:160'],
            ],

            /* ================= RICH TEXT ================= */
            'rich_text' => [
                'title' => ['nullable', 'string', 'max:180'],
                'body'  => ['required', 'string', 'max:30000'],
                'width' => ['nullable', Rule::in(['narrow', 'wide'])],
                'tone'  => ['nullable', Rule::in(['white', 'tint'])],
            ],

            /* ================= STATS ================= */
            'stats' => [
                'title'          => ['nullable', 'string', 'max:160'],
                'subtitle'       => ['nullable', 'string', 'max:320'],
                'tone'           => ['nullable', Rule::in(['white', 'tint', 'navy'])],
                'items'          => ['required', 'array', 'min:1', 'max:8'],
                'items.*.value'  => ['required', 'string', 'max:24'],
                'items.*.label'  => ['required', 'string', 'max:90'],
            ],

            /* ================= FORM (from Admin > Forms) ================= */
            'form' => [
                'form_id' => ['required', 'integer', 'exists:forms,id'],
                'title'   => ['nullable', 'string', 'max:160'],
                'intro'   => ['nullable', 'string', 'max:400'],
                'tone'    => ['nullable', Rule::in(['white', 'tint', 'navy'])],
            ],

            /* ================= EVENTS LIST ================= */
            'events_grid' => [
                'title'      => ['nullable', 'string', 'max:160'],
                'subtitle'   => ['nullable', 'string', 'max:320'],
                'show'       => ['required', Rule::in(['upcoming', 'past', 'all'])],
                'event_type' => ['nullable', 'string', 'max:40'],
                'category_id' => ['nullable', 'integer', 'exists:event_categories,id'],
                'limit'      => ['nullable', 'integer', 'min:1', 'max:12'],
                'link_text'  => ['nullable', 'string', 'max:60'],
                'link'       => self::linkRule(),
            ],

            /* ================= EVENT FACTS (date, place, type of the linked event) ================= */
            'event_facts' => [
                'title'       => ['nullable', 'string', 'max:120'],
                'note'        => ['nullable', 'string', 'max:300'],
                'button_text' => ['nullable', 'string', 'max:60'],
                'button_link' => self::linkRule(),
            ],

            /* ================= FAQ LIST ================= */
            'faq_list' => [
                'title'       => ['nullable', 'string', 'max:160'],
                'subtitle'    => ['nullable', 'string', 'max:320'],
                'tone'        => ['nullable', Rule::in(['white', 'tint'])],
                'items'       => ['required', 'array', 'min:1', 'max:50'],
                'items.*.q'   => ['required', 'string', 'max:200'],
                'items.*.a'   => ['required', 'string', 'max:1200'],
            ],

            /* ================= FEATURE GRID (cards: what you get, services, benefits) ================= */
            'feature_grid' => [
                'title'           => ['nullable', 'string', 'max:160'],
                'subtitle'        => ['nullable', 'string', 'max:320'],
                'tone'            => ['nullable', Rule::in(['white', 'tint', 'navy'])],
                'columns'         => ['nullable', Rule::in(['2', '3', '4'])],
                'items'           => ['required', 'array', 'min:1', 'max:12'],
                'items.*.title'   => ['required', 'string', 'max:100'],
                'items.*.text'    => ['nullable', 'string', 'max:400'],
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'    => self::linkRule(),
            ],

            /* ================= STEPS (numbered process) ================= */
            'steps' => [
                'title'           => ['nullable', 'string', 'max:160'],
                'subtitle'        => ['nullable', 'string', 'max:320'],
                'tone'            => ['nullable', Rule::in(['white', 'tint'])],
                'items'           => ['required', 'array', 'min:2', 'max:8'],
                'items.*.title'   => ['required', 'string', 'max:100'],
                'items.*.text'    => ['nullable', 'string', 'max:400'],
            ],

            /* ================= QUOTES (testimonials) ================= */
            'quote_list' => [
                'title'           => ['nullable', 'string', 'max:160'],
                'tone'            => ['nullable', Rule::in(['white', 'tint'])],
                'items'           => ['required', 'array', 'min:1', 'max:6'],
                'items.*.quote'   => ['required', 'string', 'max:600'],
                'items.*.name'    => ['required', 'string', 'max:80'],
                'items.*.role'    => ['nullable', 'string', 'max:120'],
            ],

            /* ================= CONTACT INFO ================= */
            'contact_info' => [
                'title'           => ['nullable', 'string', 'max:160'],
                'subtitle'        => ['nullable', 'string', 'max:320'],
                'tone'            => ['nullable', Rule::in(['white', 'tint'])],
                'email'           => ['nullable', 'email', 'max:150'],
                'phone'           => ['nullable', 'string', 'max:40'],
                'address'         => ['nullable', 'string', 'max:300'],
                'hours'           => ['nullable', 'string', 'max:200'],
            ],

            /* ================= CTA BAND ================= */
            'cta_band' => [
                'title'          => ['required', 'string', 'max:200'],
                'subtitle'       => ['nullable', 'string', 'max:320'],
                'primary_text'   => ['nullable', 'string', 'max:60'],
                'primary_link'   => self::linkRule(),
                'secondary_text' => ['nullable', 'string', 'max:60'],
                'secondary_link' => self::linkRule(),
            ],

            /* ================= HERO (legacy) ================= */
            'hero' => [
                'title'        => ['required', 'string', 'max:180'],
                'slug'         => ['required', 'string', 'max:180'],
                'subtitle'     => ['nullable', 'string', 'max:400'],
                'sub_text'     => ['nullable', 'string', 'max:400'],
                'banner_image' => $fileImg, // file upload (not URL)
                'link_text'    => ['nullable', 'string', 'max:60'],
                'link'         => self::linkRule(),
                'link_text_secondary'    => ['nullable', 'string', 'max:60'],
                'link_secondary'         => self::linkRule(),
            ],

            /* ================= OVERVIEW (alias used in your UI) ================= */
            'overview' => [
                'kicker'            => ['nullable', 'string', 'max:80'],
                'title'             => ['required', 'string', 'max:160'],
                'description'       => ['nullable', 'string', 'max:600'],
                'link_text'         => ['nullable', 'string', 'max:60'],
                'link'              => self::linkRule(),
                'items'             => ['nullable', 'array', 'max:6'],
                'items.*.icon'      => ['nullable', 'string', 'max:100'],
                'items.*.icon_bi'   => ['nullable', 'string', 'max:100'],
                'items.*.subtitle'  => ['required_with:items', 'string', 'max:160'],
                'items.*.text'      => ['nullable', 'string', 'max:400'],
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
            ],
            'program_overview' => [
                'kicker'            => ['nullable', 'string', 'max:80'],
                'title'             => ['required', 'string', 'max:160'],
                'description'       => ['nullable', 'string', 'max:600'],
                'link_text'         => ['nullable', 'string', 'max:60'],
                'link'              => self::linkRule(),
                'items'             => ['nullable', 'array', 'max:6'],
                'items.*.icon'      => ['nullable', 'string', 'max:100'],
                'items.*.icon_bi'   => ['nullable', 'string', 'max:100'],
                'items.*.subtitle'  => ['required_with:items', 'string', 'max:160'],
                'items.*.text'      => ['nullable', 'string', 'max:400'],
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
            ],

            /* ================= HOW IT WORKS ================= */
            'how_it_works' => [
                'layout'            => ['nullable', Rule::in(['slider', 'stack'])],
                'title'             => ['required', 'string', 'max:160'],
                'subtitle'          => ['nullable', 'string', 'max:320'],
                'items'             => ['required', 'array', 'min:2', 'max:12'],
                'items.*.title'     => ['required', 'string', 'max:120'],
                'items.*.subtitle'  => ['nullable', 'string', 'max:160'],
                'items.*.text'      => ['nullable', 'string', 'max:600'],
                'items.*.list'      => ['nullable', 'array', 'max:10'],
                'items.*.list.*'    => ['required_with:items.*.list', 'string', 'max:140'],
                'items.*.image'     => $fileImg, // file upload
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
            ],

            'about' => [
                'kicker'            => ['nullable', 'string', 'max:60'],
                'title'             => ['required', 'string', 'max:160'],
                'subtitle'          => ['nullable', 'string', 'max:320'],
                'text'              => ['nullable', 'string', 'max:1500'],

                // Top-level list - optional, items are strings
                'list'              => ['nullable', 'array', 'max:12'],
                'list.*'            => ['string', 'max:140'], // Removed 'required_with'

                // Cards - optional
                'cards'             => ['nullable', 'array', 'max:6'],
                'cards.*.title'     => ['required_with:cards', 'string', 'max:120'],
                'cards.*.text'      => ['nullable', 'string', 'max:300'],
                'cards.*.image'     => $fileImg,

                'banner_left'       => $fileImg,

                // Tiles
                'tiles'             => ['nullable', 'array', 'max:6'],
                'tiles.*.type'      => ['nullable', Rule::in(['counter', 'image', 'customers'])],
                'tiles.*.label'     => ['nullable', 'string', 'max:120'],
                'tiles.*.value'     => ['nullable', 'string', 'max:100'],
                'tiles.*.suffix'    => ['nullable', 'string', 'max:10'],
                'tiles.*.note'      => ['nullable', 'string', 'max:140'],
                'tiles.*.bg'        => $fileImg,
                'tiles.*.text'      => ['nullable', 'string', 'max:300'],
                'tiles.*.link_text' => ['nullable', 'string', 'max:60'],
                'tiles.*.link'      => self::linkRule(),

                'cta.link_text'     => ['nullable', 'string', 'max:60'],
                'cta.link'          => self::linkRule(),

                'text_color'        => ['nullable', 'string', 'max:30'],
            ],

            'about2' => [
                'kicker'       => ['nullable', 'string', 'max:80'],
                'title'        => ['required', 'string', 'max:180'],
                'text'         => ['nullable', 'string', 'max:1500'],
                'link_text'    => ['nullable', 'string', 'max:60'],
                'link'         => self::linkRule(),
                'about_image'  => $fileImg,

                'columns'      => ['nullable', 'array', 'max:2'],
                'columns.*.head'        => ['nullable', 'string', 'max:120'],
                'columns.*.subhead'     => ['nullable', 'string', 'max:140'],
                'columns.*.description' => ['nullable', 'string', 'max:600'],
                'columns.*.list'        => ['nullable', 'array', 'max:20'],
                'columns.*.list.*'      => ['string', 'max:140'], // Removed 'required_with'

                'text_color'            => ['nullable', 'string', 'max:30'],
            ],

            'sections' => [
                'kicker'        => ['nullable', 'string', 'max:60'],
                'title'         => ['required', 'string', 'max:160'],
                'subtitle'      => ['nullable', 'string', 'max:320'],
                'text'          => ['nullable', 'string', 'max:1500'],

                'list'          => ['nullable', 'array', 'max:12'],
                'list.*'        => ['string', 'max:140'],

                'items'             => ['nullable', 'array', 'max:12'],
                'items.*.icon'      => ['nullable', 'string', 'max:100'],
                'items.*.title'     => ['required_with:items', 'string', 'max:140'],
                'items.*.subtitle'  => ['nullable', 'string', 'max:200'],
                'items.*.text'      => ['nullable', 'string', 'max:600'],
                'items.*.image'     => $fileImg,
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
                'items.*.list'      => ['nullable', 'array', 'max:20'],
                'items.*.list.*'    => ['string', 'max:140'], // Removed 'required_with'
            ],

            'sections2' => [
                'kicker'        => ['nullable', 'string', 'max:80'],
                'title'         => ['required', 'string', 'max:180'],
                'desc'          => ['nullable', 'string', 'max:600'],
                'link_text'     => ['nullable', 'string', 'max:60'],
                'link'          => self::linkRule(),

                'items'             => ['required', 'array', 'min:1', 'max:12'],
                'items.*.icon'      => ['nullable', 'string', 'max:100'],
                'items.*.title'     => ['required', 'string', 'max:140'],
                'items.*.subtitle'  => ['nullable', 'string', 'max:200'],
                'items.*.description' => ['nullable', 'string', 'max:600'],
                'items.*.image'     => $fileImg,
                'items.*.list'      => ['nullable', 'array', 'max:20'],
                'items.*.list.*'    => ['string', 'max:140'],
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
            ],

            'overview2' => [
                'kicker'            => ['nullable', 'string', 'max:80'],
                'title'             => ['required', 'string', 'max:160'],
                'subtitle'          => ['nullable', 'string', 'max:200'],
                'desc'              => ['nullable', 'string', 'max:600'],

                'list'              => ['nullable', 'array', 'max:20'],
                'list.*'            => ['string', 'max:140'],

                'link_text'         => ['nullable', 'string', 'max:60'],
                'link'              => self::linkRule(),

                'items'             => ['required', 'array', 'min:1', 'max:24'],
                'items.*.title'     => ['required', 'string', 'max:140'],
                'items.*.description' => ['nullable', 'string', 'max:400'],
                'items.*.list'      => ['nullable', 'array', 'max:20'],
                'items.*.list.*'    => ['string', 'max:140'],
                'items.*.image'     => $fileImg,
                'items.*.link'      => self::linkRule(),
            ],

            'form_dark', 'form_light' => self::formBlockRules(),




            /* ================= GALLERY (spelled “gallary” in code) ================= */
            'gallary' => [
                'kicker'            => ['nullable', 'string', 'max:80'],
                'section_title'     => ['required', 'string', 'max:180'],
                'link_text'         => ['nullable', 'string', 'max:60'],
                'link'              => self::linkRule(),
                'items'             => ['required', 'array', 'min:1', 'max:16'],
                'items.*.image'     => $fileImg,
                'items.*.title'     => ['required', 'string', 'max:120'],
                'items.*.link_text' => ['nullable', 'string', 'max:60'],
                'items.*.link'      => self::linkRule(),
            ],

            /* ================= TESTIMONIAL ================= */
            'testimonial' => [
                'title'               => ['nullable', 'string', 'max:160'],
                'subtitle'            => ['nullable', 'string', 'max:320'],
                'items'               => ['required', 'array', 'min:1', 'max:20'],
                'items.*.name'        => ['required', 'string', 'max:100'],
                'items.*.designation' => ['nullable', 'string', 'max:100'],
                'items.*.photo'       => $fileImg,
                'items.*.text'        => ['required', 'string', 'max:700'],
                'items.*.rating_fill' => ['nullable', 'integer', 'min:0', 'max:100'], // percent
            ],

       

            /* ================= FAQ ================= */
            'faq' => [
                'title'        => ['nullable', 'string', 'max:160'],
                'subtitle'     => ['nullable', 'string', 'max:320'],
                'items'        => ['required', 'array', 'min:1', 'max:50'],
                'items.*.q'    => ['required', 'string', 'max:200'],
                'items.*.a'    => ['required', 'string', 'max:1200'],
            ],

            /* ================= CLOSING CTA (accept new + legacy keys) ================= */
            'closing_cta' => [
                'title'           => ['required', 'string', 'max:200'],
                'subtitle'        => ['nullable', 'string', 'max:320'],
                'ctas'            => ['nullable', 'array', 'max:3'],
                // New schema
                'ctas.*.link_text' => ['nullable', 'string', 'max:60'],
                'ctas.*.link'     => self::linkRule(),
                // Legacy compatibility
                'ctas.*.text'     => ['nullable', 'string', 'max:60'],
                'ctas.*.href'     => self::linkRule(),
            ],

            /* ================= MARQUEES ================= */
            'marquees' => [
                'slides'          => ['nullable', 'array', 'max:30'],
                'slides.*.title'  => ['required_with:slides', 'string', 'max:120'],
                'slides.*.image'  => $fileImg,
            ],

            /* ================= NEW: HERO 2 (year comes externally) ================= */
            'hero2' => [
                'title'       => ['required', 'string', 'max:180'],
                'link_text'   => ['nullable', 'string', 'max:60'],
                'link'        => self::linkRule(),
                'desc'        => ['nullable', 'string', 'max:400'],
                // support either key; Blade prefers hero_image, also allow banner_image
                'hero_image'  => $fileImg,
                'banner_image' => $fileImg,
            ],

            /* ================= NEW: HERO 3 (trusted banner with segments) ================= */
            'hero3' => [
                'title'              => ['required_without:title_segments', 'string', 'max:180'],
                'description'        => ['nullable', 'string', 'max:500'],
                'icon_bi'            => ['nullable', 'string', 'max:80'],
                'title_segments'     => ['nullable', 'array', 'min:1', 'max:6'],
                'title_segments.*'   => ['string', 'max:60'],
                'banner_image'       => $fileImg,
                'image'              => $fileImg,         // alias
                'hero_image'         => $fileImg,         // alias
                'verified_icon'      => $fileImg,
            ],

            'hero4' => [
                'kicker'                => ['nullable', 'string', 'max:80'],
                'title'                 => ['required', 'string', 'max:180'],
                'subtitle'              => ['nullable', 'string', 'max:200'],
                'description'           => ['nullable', 'string', 'max:600'],
                'primary_button_text'   => ['nullable', 'string', 'max:60'],
                'primary_button_link'   => self::linkRule(),
                'secondary_button_text' => ['nullable', 'string', 'max:60'],
                'secondary_button_link' => self::linkRule(),
                'hero_image'            => $fileImg,
            ],

            /* ================= NEW: LOGO SLIDER ================= */
            'logo_slider' => [
                'kicker'        => ['nullable', 'string', 'max:80'],
                'title'         => ['required', 'string', 'max:180'],
                'link_text'     => ['nullable', 'string', 'max:60'],
                'link'          => self::linkRule(),

                // accept either "logos" or "brands"
                'logos'         => ['nullable', 'array', 'max:40'],
                'brands'        => ['nullable', 'array', 'max:40'],

                'logos.*.image' => $fileImg,
                'logos.*.alt'   => ['nullable', 'string', 'max:120'],
                'logos.*.href'  => self::linkRule(),

                'brands.*.image' => $fileImg,
                'brands.*.alt'  => ['nullable', 'string', 'max:120'],
                'brands.*.href' => self::linkRule(),
            ],

            default => ['_' => ['prohibited']],
        };
    }


    public static function prefixedRulesFor(string $type): array
    {
        $rules = self::rulesFor($type);
        $prefixed = [];

        foreach ($rules as $key => $value) {
            $prefixed["data.{$key}"] = $value;
        }

        return $prefixed;
    }

    private static function linkRule(): array
    {
        return [
            'nullable',
            'string',
            'max:2048',
            function ($attribute, $value, $fail) {
                if (blank($value)) {
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    return;
                }

                // an internal path, or a jump to a section on the same page (#interest)
                if (is_string($value) && (str_starts_with($value, '/') || preg_match('/^#[A-Za-z][\w\-:.]*$/', $value))) {
                    return;
                }

                $fail('The ' . $attribute . ' must be a valid URL, an internal path starting with /, or an in-page link like #interest.');
            },
        ];
    }

    private static function formBlockRules(): array
    {
        return [
            'title'       => ['nullable', 'string', 'max:160'],
            'subtitle'    => ['nullable', 'string', 'max:320'],
            'action'      => self::linkRule(),
            'method'      => ['nullable', Rule::in(['POST', 'GET'])],
            'button_text' => ['nullable', 'string', 'max:40'],
            'tags'        => ['nullable', 'string', 'max:255'],
            'email_mode'  => ['nullable', Rule::in(['newsletter', 'thank_you', 'custom', 'none'])],
            'email_subject' => ['nullable', 'string', 'max:160'],
            'email_body'    => ['nullable', 'string', 'max:5000'],

            'fields'                 => ['required', 'array', 'min:1', 'max:10'],
            'fields.*.label'         => ['required', 'string', 'max:120'],
            'fields.*.name'          => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_\\-]+$/'],
            'fields.*.type'          => ['required', Rule::in(['text', 'email', 'tel', 'textarea'])],
            'fields.*.placeholder'   => ['nullable', 'string', 'max:200'],
            'fields.*.required'      => ['nullable', 'boolean'],
            'fields.*.width'         => ['nullable', Rule::in(['full', 'half'])],
        ];
    }
}
