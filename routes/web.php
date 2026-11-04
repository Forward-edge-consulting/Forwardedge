<?php

use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminDashboard;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminEventCategoryController;
use App\Http\Controllers\Admin\AdminFormController;
use App\Http\Controllers\Admin\AdminResponseController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\SecretAdminSetupController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Secret Admin Setup Route
|--------------------------------------------------------------------------
| This route is protected by a secret key and custom URL path.
| Only accessible to those who know the secret path and key.
*/

Route::get(config('admin.secret_setup_path'), [SecretAdminSetupController::class, 'show'])
    ->name('admin.secret.show');
Route::post(config('admin.secret_setup_path'), [SecretAdminSetupController::class, 'update'])
    ->middleware('throttle:5,10')
    ->name('admin.secret.update');

/*
|--------------------------------------------------------------------------
| Public Static Pages
|--------------------------------------------------------------------------
*/

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/about', function () {
    return view('user.pages.about');
})->name('about');
Route::view('/consultancy', 'user.pages.consultancy')->name('consultancy');
Route::view('/terms', 'user.pages.terms')->name('terms');
Route::view('/privacy-policy', 'user.pages.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| Content Routes (Blog, Services, etc.)
|--------------------------------------------------------------------------
*/
// Blog routes
Route::prefix('blog')->name('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/{slug}', [BlogController::class, 'show'])->name('s.show');
});

Route::post('/blog/{slug}/comments', [\App\Http\Controllers\BlogCommentController::class, 'store'])->middleware('throttle:5,10')->name('blog.comment.store');
Route::post('/blog/{slug}/comments/{comment}/reply', [\App\Http\Controllers\BlogCommentController::class, 'reply'])->name('blog.comment.reply');

// Service routes  
Route::prefix('services')->name('services')->group(function () {
    Route::get('/', [ServiceController::class, 'ServiceList']);
    Route::get('/{slug}', [ServiceController::class, 'show'])->name('.show');
});

Route::view('/contact', 'user.pages.contact')->name('contact');
Route::get('/f/{slug}', [FormSubmissionController::class, 'show'])->name('forms.show');
Route::get('/r/{token}', \App\Http\Controllers\ResourceDownloadController::class)->middleware('throttle:30,1')->name('forms.resource');
Route::get('/booking/availability', \App\Http\Controllers\BookingAvailabilityController::class)->middleware('throttle:60,1')->name('booking.availability');
Route::get('/booking/calendar/{token}.ics', \App\Http\Controllers\BookingFeedController::class)->where('token', '[A-Za-z0-9]+')->middleware('throttle:30,1')->name('booking.feed');
Route::middleware(['signed', 'throttle:30,1'])->group(function () {
    Route::get('/booking/review/{booking}', [\App\Http\Controllers\BookingReviewController::class, 'show'])->name('booking.review');
    Route::post('/booking/review/{booking}', [\App\Http\Controllers\BookingReviewController::class, 'decide'])->name('booking.decide');
});
Route::post('/f/{slug}', [FormSubmissionController::class, 'store'])->middleware('throttle:10,1')->name('forms.submit');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,10')->name('newsletter.subscribe');
Route::get('/gallery', [GalleryController::class, 'getPhotos'])->name('gallery');

/*
|--------------------------------------------------------------------------
| Event Routes
|--------------------------------------------------------------------------
*/
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/upcoming', fn () => redirect()->route('events.index', ['when' => 'upcoming'], 301))->name('upcoming');
    Route::get('/featured', fn () => redirect()->route('events.index', [], 301))->name('featured');
    // Online registration was retired: old links go to the event page instead.
    Route::get('/{slug}/register', fn (string $slug) => redirect()->route('events.show', $slug, 301))->name('register.form');
    Route::get('/{slug}', [EventController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes - Secured with Custom Prefix
| Change "ctrl-panel-v2" to something unique and memorable for your team
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->prefix('ctrl-panel-v2')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('admin.login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'role:admin'])->prefix('ctrl-panel-v2')->group(function () {

    // Dashboard (Login Landing)
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin - Forms and Responses
    |--------------------------------------------------------------------------
    */
    Route::prefix('forms')->name('admin.forms.')->group(function () {
        Route::get('/', [AdminFormController::class, 'index'])->name('index');
        Route::get('/create', [AdminFormController::class, 'create'])->name('create');
        Route::post('/', [AdminFormController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [AdminFormController::class, 'edit'])->name('edit');
        Route::put('/{form}', [AdminFormController::class, 'update'])->name('update');
        Route::post('/{form}/duplicate', [AdminFormController::class, 'duplicate'])->name('duplicate');
        Route::delete('/{form}', [AdminFormController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bookings')->name('admin.bookings.')->group(function () {
        $c = \App\Http\Controllers\Admin\AdminBookingController::class;
        Route::get('/', [$c, 'index'])->name('index');
        Route::get('/settings', [$c, 'settings'])->name('settings');
        Route::put('/settings', [$c, 'updateSettings'])->name('settings.update');
        Route::post('/settings/feed', [$c, 'regenerateFeed'])->name('feed.regenerate');
        Route::get('/{booking}', [$c, 'show'])->name('show');
        Route::post('/{booking}/approve', [$c, 'approve'])->name('approve');
        Route::post('/{booking}/decline', [$c, 'decline'])->name('decline');
        Route::post('/{booking}/cancel', [$c, 'cancel'])->name('cancel');
        Route::put('/{booking}/notes', [$c, 'notes'])->name('notes');
    });

    Route::prefix('responses')->name('admin.responses.')->group(function () {
        Route::get('/', [AdminResponseController::class, 'index'])->name('index');
        Route::post('/export', [AdminResponseController::class, 'export'])->name('export');
        Route::post('/export/{batch}/undo', [AdminResponseController::class, 'undoExport'])->name('undo');
        Route::post('/forget', [AdminResponseController::class, 'forget'])->name('forget');
        Route::post('/{response}/resend-resource', [AdminResponseController::class, 'resendResource'])->name('resend');
        Route::get('/{response}', [AdminResponseController::class, 'show'])->name('show');
        Route::delete('/{response}', [AdminResponseController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin - Services Management
    |--------------------------------------------------------------------------
    */
    Route::get('/services/add', function () {
        return view('admin.pages.services.add_service');
    })->name('admin.services.add');

    Route::prefix('services')->name('admin.services.')->group(function () {
        Route::get('/', [AdminServiceController::class, 'index'])->name('index');
        Route::post('/', [AdminServiceController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminServiceController::class, 'show'])->name('show');
        Route::put('/{id}', [AdminServiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminServiceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/contents', [AdminServiceController::class, 'storeContent'])->name('contents.store');
        Route::put('/{serviceId}/contents/{contentId}', [AdminServiceController::class, 'updateContent'])->name('contents.update');
        Route::delete('/{serviceId}/contents/{contentId}', [AdminServiceController::class, 'destroyContent'])->name('contents.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin - Events Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->name('admin.events.')->group(function () {
        Route::get('/', [AdminEventController::class, 'index'])->name('list');
        Route::get('/create', [AdminEventController::class, 'create'])->name('create');
        Route::post('/', [AdminEventController::class, 'store'])->name('store');
        // The old registrations list moved into Responses.
        Route::get('/registrations', fn () => redirect()->route('admin.responses.index', ['form' => \App\Models\Form::where('slug', 'event-registrations')->value('id')]))->name('registrations');
        Route::get('/{event}/dashboard', [AdminEventController::class, 'dashboard'])->name('dashboard');
        Route::put('/{event}', [AdminEventController::class, 'update'])->name('update');
        Route::delete('/{event}', [AdminEventController::class, 'destroy'])->name('destroy');
        Route::post('/{event}/page', [AdminEventController::class, 'createPage'])->name('page.create');
        Route::post('/{event}/page/recap', [AdminEventController::class, 'addRecap'])->name('page.recap');
    });

    Route::prefix('event-categories')->name('admin.event-categories.')->group(function () {
        Route::get('/', [AdminEventCategoryController::class, 'index'])->name('index');
        Route::post('/', [AdminEventCategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [AdminEventCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [AdminEventCategoryController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin - Blogs Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('blogs')->name('admin.blogs.')->group(function () {
        Route::get('/', [AdminBlogController::class, 'index'])->name('index');
        Route::post('/', [AdminBlogController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminBlogController::class, 'show'])->name('show');
        Route::put('/{id}/content', [AdminBlogController::class, 'save'])->name('save');
        Route::post('/{id}/upload', [AdminBlogController::class, 'upload'])->name('upload');
        Route::post('/{id}/cover', [AdminBlogController::class, 'cover'])->name('cover');
        Route::delete('/{id}', [AdminBlogController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin - FAQs Management
    |--------------------------------------------------------------------------
    */
    Route::resource('faqs', FaqController::class)
        ->except(['create', 'edit', 'show'])
        ->names([
            'index' => 'admin.faqs.index',
            'store' => 'admin.faqs.store',
            'update' => 'admin.faqs.update',
            'destroy' => 'admin.faqs.destroy',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Admin - Gallery Management
    |--------------------------------------------------------------------------
    */
    Route::resource('gallery', GalleryController::class)
        ->except(['create', 'edit', 'show'])
        ->names([
            'index' => 'admin.gallery.index',
            'store' => 'admin.gallery.store',
            'update' => 'admin.gallery.update',
            'destroy' => 'admin.gallery.destroy',
        ]);
   


    Route::prefix('page-builder')->group(function () {
        Route::get('/',                 [PageBuilderController::class, 'adminPages'])->name('pb.pages');
        Route::get('/create',           [PageBuilderController::class, 'createPage'])->name('pb.pages.create');
        Route::post('/store',           [PageBuilderController::class, 'storePage'])->name('pb.pages.store');
        Route::get('/{page}/edit',      [PageBuilderController::class, 'editPage'])->name('pb.pages.edit');
        Route::put('/{page}',           [PageBuilderController::class, 'updatePage'])->name('pb.pages.update');
        Route::delete('/{page}',        [PageBuilderController::class, 'destroyPage'])->name('pb.pages.destroy');

        // Blocks (the one-page screen uses this index)
        Route::get('/{page}/blocks',    [PageBuilderController::class, 'adminBlocks'])->name('pb.blocks');
        Route::post('/{page}/blocks',   [PageBuilderController::class, 'storeBlock'])->name('pb.blocks.store');

        // Edit/save are still used by the one-page form when you click “Edit”
        Route::put('/block/{block}',    [PageBuilderController::class, 'updateBlock'])->name('pb.blocks.update');
        Route::delete('/block/{block}', [PageBuilderController::class, 'destroyBlock'])->name('pb.blocks.destroy');
        Route::post('/block/{block}/publish', [PageBuilderController::class, 'togglePublish'])->name('pb.blocks.publish');
        Route::post('/block/{block}/clone',   [PageBuilderController::class, 'cloneBlock'])->name('pb.blocks.clone');

        // Drag-sort autosave
        Route::post('/{page}/blocks/reorder', [PageBuilderController::class, 'reorderBlocks'])->name('pb.blocks.reorder');

        // Admin-only preview (shows draft blocks too)
        Route::get('/{page}/preview', [PageBuilderController::class, 'previewPage'])->name('pb.blocks.preview');
    });
});

/*
|--------------------------------------------------------------------------
| Dynamic Page Builder Routes
|--------------------------------------------------------------------------
*/
$pageReservedSlugs = [
    'about',
    'academy',
    'cart',
    'course-content',
    'email-access',
    'profile',
    'student',
    'verify',
    'wishlist',
    'ajax',
    'api',
    'blog',
    'checkout',
    'confirm-password',
    'contact',
    'consultancy',
    'course',
    'ctrl-panel-v2',
    'enroll',
    'events',
    'forgot-password',
    'gallery',
    'login',
    'logout',
    'my-courses',
    'newsletter',
    'password',
    'payment',
    'register',
    'reset-password',
    'scholarships',
    'services',
    'shop',
    'storage',
    'up',
    'user',
    'verify-email',
    'privacy-policy',
    'terms',
    'p',
];

$pageReservedPattern = implode('|', array_map(fn ($slug) => preg_quote($slug, '#'), $pageReservedSlugs));

Route::get('/{slug}', [PageBuilderController::class, 'show'])
    ->where('slug', '^(?!(' . $pageReservedPattern . ')$)[A-Za-z0-9\.-]+$')
    ->name('page.show');

Route::get('/p/{slug}', function (string $slug) {
    return redirect()->route('page.show', ['slug' => $slug], 301);
})->where('slug', '[A-Za-z0-9\.-]+')->name('page.legacy');
/*
|--------------------------------------------------------------------------
| Laravel Breeze Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
