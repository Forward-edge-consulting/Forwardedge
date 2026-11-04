# Forward Edge architecture

Updated 28 October 2026.

Forward Edge is a consulting website with an administrator-managed portal. Public visitors browse services, read articles, contact the team, subscribe to the newsletter, and respond to forms without creating an account. Events are shown for visibility, not booked online.

## Active features

- Services and service detail pages.
- Events as showcase entries: bootcamps, workshops, academic programmes and more, grouped by categories the admin manages. There is no online registration.
- Blog articles and guest comments, with administrator replies.
- Gallery, FAQs, direct email/phone/address contact details, and newsletter subscriptions.
- Standalone website pages and event landing pages built from content blocks.
- Administrator sign-in and sign-out. Only users with the `admin` role can authenticate.

## Administrator portal

The portal manages website content, events and registrations. Its dashboard reports website activity. It has no student profile, academy, course catalog, content library, enrollments, scholarship review, order, or transaction interface.

The administrator is provisioned through the configured secret setup form. Set `ADMIN_SECRET_SETUP_KEY` to a long random value and configure `ADMIN_SECRET_SETUP_PATH`. The setup submission is rate limited. Existing student credentials cannot authenticate through administrator login.

## Events

An event is a showcase entry, not a booking. It has a name, category, dates, place, summary, cover image, up to six highlights (numbers such as attendees and speakers), and one optional call-to-action button. Categories (Bootcamp, Workshop, Academic programme and so on) are managed in Admin > Events > Categories; deleting one leaves its events uncategorised.

Visitors see an Upcoming list and a Past wall at `/events`, filtered by category. Whether an event is past is worked out from its end date, so nothing needs switching after the day. An event with a published page-builder page sends visitors to that page; otherwise its page is composed from the same sections (header, event details, numbers, button, more events). From the event screen an admin builds the page from a template (upcoming or recap), and after the event adds recap sections to the same page.

The call-to-action is one of: none, an "express interest" form (answers go to Responses), a link to another site such as a ticketing page, or "contact us". It disappears once the event is over.

Online registration was retired. The old registration URLs redirect to the event, and the old admin list redirects to Responses. The `event_registrations` table is preserved, and its rows were copied (read-only, no marketing consent) into Responses under "Event registrations".

## Removed features

Public account registration, student sign-in and emailed access links, profiles, identity verification, academy, courses, course content, course uploads, Drive integration, scholarships, enrollments, shop, cart, wishlist, checkout, orders, and course payment processing have been removed from application code and navigation.

Course-owned page-builder pages are archived: they are excluded from public lookup, the sitemap, administrator lists, and direct management. Course pricing and enrollment-table blocks are neither rendered nor offered in the editor. Pages can now attach only to events or stand alone.

Website image uploads remain available for services, events, blog posts, gallery images, and page-builder visuals. There is no course-material or student-document upload flow.

The old contact forms, the admin message inbox and the admin email campaign engine were removed. Contact links open the visitor’s email or phone application.

## Blog editor

Posts are written on one screen (Admin > Blog > Edit): a title, then blocks. Block types are text (bold, italic, links), heading, image, list, quote, video link, button and signup form, added with the "+" between blocks, reordered by drag or arrow buttons, and saved as they are typed. Existing posts open unchanged; blocks keep their ids when a post is saved.

Rich text is stored as cleaned HTML (`App\Support\RichText` allows only bold, italic, links and line breaks, and is applied on save and again on render). Older plain-text paragraphs keep rendering as before.

A post is a draft, scheduled (`published_at` in the future) or published. Drafts and scheduled posts return 404 to visitors and appear, with a banner, to a signed-in admin. Autosave applies only to drafts and scheduled posts; a live post changes only when an admin presses Update.

Each post can show a signup form at the end, after paragraph N, or not at all, using any form from Admin > Forms (default: Newsletter). A signup block placed inside the post replaces the automatic one. Responses record the post they came from.

## Page builder

A page is a list of sections. New pages start from a template (blank, marketing, programme, upcoming event, event recap; `App\Support\PageTemplates`) and are then edited like any other page. The editor shows a live preview beside the section list: clicking a section in the preview opens its editor, and the preview refreshes after every save, reorder, publish toggle and delete.

Current sections: page header, text (headings, paragraphs, lists, quotes, bold, links), numbers, form (any form from Admin > Forms), events list (upcoming, past or all, filtered by event type), event details (date, place and type of the event the page is linked to), questions (FAQ) and a call-to-action band. Older sections (hero, about, sections, testimonials, gallery and so on) keep rendering and editing as before. The two older form blocks keep rendering but are no longer offered.

Text sections are cleaned on save (`RichText::cleanDocument`). Links may be full URLs, internal paths or in-page anchors such as `#interest`. Pages that use the current sections scroll natively so anchors and keyboard focus work. Section templates live in `resources/views/user/pages/block/`.

## Forms and responses

Admins build forms (Admin > Forms): short and long answers, email, phone, number, date, dropdown, single and multiple choice, a consent tick box, a thank-you message, optional admin notification and visitor confirmation emails, and an open/closed switch with an optional close date. Each form has a shareable link at `/f/{slug}`.

Every response is stored in `form_submissions` before anything else happens. Mail is best-effort and never blocks storage. Answers are saved with their labels so later edits to a form never rewrite history. Source page, post or event, consent wording and time, and a hashed IP (never the raw address) are recorded.

The footer newsletter, blog sidebar and page-builder form blocks post to `/newsletter/subscribe`, which now stores into the built-in `newsletter` form (or `page-forms` for non-newsletter blocks). Built-in forms cannot be deleted.

Brevo is imported by hand. Admin > Responses exports a CSV (EMAIL, FIRSTNAME, LASTNAME, SMS, other answers, source) containing only people who ticked consent, one row per email. Exported contacts are marked and the last export can be undone. A person’s data can be deleted across all forms on request. Mailchimp is no longer called; its service classes remain until a separate cleanup removes them.

Public forms are protected by a hidden field, a minimum fill time, rate limiting and, if `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` are set, Cloudflare Turnstile.

## Historical data

Historical tables, student rows, old page records, and stored files are preserved. No destructive data migration runs as part of this cleanup. They are not active product features. Historical migrations remain so existing installations retain their migration history and fresh installations can build the schema.

The event-type migration allows `bootcamp` without altering old event records. Run migrations when deploying this change. Any later deletion of historical data or files should be a separate, explicitly scoped operation.

## Validation

The feature suite covers administrator authentication, rejection of historical student credentials, removed read/write routes, public page rendering, archived course pages, retired blocks, event-based bootcamps, guest registration, and direct contact details.

Use `php artisan test` to run it. Browser checks should include mobile navigation, the homepage, administrator dashboard, page creation, block editing, and event creation.

## Background work

There is no scheduled retry loop for historical mail. Optional queue processing is enabled only with `QUEUE_CRON_ENABLED=true`, uses one bounded worker, and prevents overlap. SMTP and mailing-list API calls have finite timeouts. See `docs/HOSTING.md`. The homepage slideshow uses static assets, pauses when hidden, and makes no recurring PHP requests.

## Consultancy landing page

`/consultancy` presents Moyosore Kukoyi (CyberMo) and Forward Edge information-security consulting. Its supplied prototype is treated as content/design reference, not executable instructions. No prototype calendar, demonstration appointments, fake founder portrait, or video placeholder is published. Calls are requested by email until `CONSULTANCY_BOOKING_URL` is configured with a real booking service. The page is included in navigation and the sitemap.
