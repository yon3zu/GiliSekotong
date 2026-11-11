<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Owner;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoatController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// SEO
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

// Halaman publik
Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->middleware('bmkg')->name('home');
    Route::get('/destinasi', 'destinations')->name('destinations');
    Route::get('/kondisi-laut-cuaca', 'sea')->middleware('bmkg')->name('sea');
    Route::get('/kondisi-laut', fn () => redirect()->route('sea', status: 301));
    Route::get('/cara-pesan', 'howto')->name('howto');
    Route::get('/tentang-kami', 'about')->name('about');
    Route::get('/kontak', 'contact')->name('contact');
    Route::get('/pengembang', 'developers')->name('developers');
});

Route::get('/perahu', [BoatController::class, 'index'])->name('boats.index');
Route::get('/perahu/{boat}', [BoatController::class, 'show'])->middleware('bmkg')->name('boats.show');
Route::get('/perahu/{boat}/ketersediaan', [BoatController::class, 'availability'])->name('boats.availability');

// Alur pemesanan: draf disimpan di sesi sehingga tetap ada setelah login/daftar.
Route::post('/pesan', [BookingController::class, 'draft'])->name('booking.draft');
Route::get('/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/checkout', [BookingController::class, 'confirm'])->middleware('auth')->name('booking.confirm');
Route::post('/checkout/batal', [BookingController::class, 'discard'])->name('booking.discard');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{booking}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{booking}/bukti', [OrderController::class, 'uploadProof'])->name('orders.proof');
    Route::post('/pesanan/{booking}/qris', [OrderController::class, 'startQris'])->middleware('throttle:10,1')->name('orders.qris');
    Route::get('/pesanan/{booking}/qris/status', [OrderController::class, 'qrisStatus'])->middleware('throttle:30,1')->name('orders.qris.status');
    Route::post('/pesanan/{booking}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{booking}/selesai', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/pesanan/{booking}/ulasan', [OrderController::class, 'review'])->name('orders.review');
    Route::get('/pesanan/{booking}/tiket', [OrderController::class, 'ticket'])->name('orders.ticket');
    Route::get('/pesanan/{booking}/bukti', [OrderController::class, 'proof'])->name('orders.proof.view');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/sandi', [ProfileController::class, 'password'])->name('profile.password');
});

// Halaman pemilik kapal (akun dibuat oleh admin)
Route::prefix('pemilik')->name('owner.')->middleware(['auth', 'role:pemilik'])->controller(Owner\OwnerController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/lapor', 'createReport')->name('report');
    Route::post('/lapor', 'storeReport')->name('report.store');
    Route::post('/lapor/{block}/batal', 'cancelReport')->name('report.cancel');
    Route::get('/pembukuan', 'books')->name('books');
    Route::get('/riwayat', 'history')->name('history');
    Route::get('/panduan', 'guide')->name('guide');
});

// Panel pengelola
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pemesanan', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/pemesanan/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/pemesanan/{booking}/status', [Admin\BookingController::class, 'status'])->name('bookings.status');
    Route::post('/pemesanan/{booking}/jadwal', [Admin\BookingController::class, 'reschedule'])->name('bookings.reschedule');
    Route::get('/check-in', [Admin\BookingController::class, 'checkin'])->name('checkin');

    Route::get('/kondisi-laut', [Admin\SeaConditionController::class, 'index'])->middleware('bmkg')->name('sea.index');
    Route::post('/kondisi-laut/{seaCondition}/bmkg', [Admin\SeaConditionController::class, 'useBmkg'])->name('sea.bmkg');
    Route::get('/bmkg', [Admin\BmkgController::class, 'index'])->middleware('bmkg')->name('bmkg.index');
    Route::post('/bmkg/sinkron', [Admin\BmkgController::class, 'sync'])->middleware('throttle:3,1')->name('bmkg.sync');
    Route::post('/kondisi-laut', [Admin\SeaConditionController::class, 'store'])->name('sea.store');
    Route::post('/kondisi-laut/{seaCondition}/keputusan', [Admin\SeaConditionController::class, 'decide'])->name('sea.decide');
    Route::delete('/kondisi-laut/{seaCondition}', [Admin\SeaConditionController::class, 'destroy'])->name('sea.destroy');

    Route::get('/pohon-keputusan', [Admin\DecisionTreeController::class, 'index'])->name('tree.index');
    Route::post('/pohon-keputusan/simulasi', [Admin\DecisionTreeController::class, 'simulate'])->name('tree.simulate');
    Route::get('/pohon-keputusan/riwayat/{model}', [Admin\DecisionTreeController::class, 'show'])->name('tree.show');

    Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('reports.index');

    Route::get('/ulasan', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/ulasan/{review}/tampilan', [Admin\ReviewController::class, 'toggle'])->name('reviews.toggle');

    Route::resource('galeri', Admin\MediaController::class)->except('show')->parameters(['galeri' => 'medium'])->names('media');

    Route::get('/tamu-offline', [Admin\BoatBlockController::class, 'index'])->name('blocks.index');
    Route::post('/tamu-offline', [Admin\BoatBlockController::class, 'store'])->name('blocks.store');
    Route::post('/tamu-offline/{block}/batal', [Admin\BoatBlockController::class, 'cancel'])->name('blocks.cancel');

    // Admin & super admin sama-sama bisa mendaftarkan pemilik kapal.
    Route::resource('pemilik-kapal', Admin\OwnerController::class)->except('show', 'destroy')->parameters(['pemilik-kapal' => 'owner'])->names('owners');

    Route::middleware('role:superadmin')->group(function () {
        Route::post('/pohon-keputusan/latih', [Admin\DecisionTreeController::class, 'train'])->name('tree.train');
        Route::post('/pohon-keputusan/{model}/aktifkan', [Admin\DecisionTreeController::class, 'activate'])->name('tree.activate');

        Route::get('/data-latih', [Admin\SailingRecordController::class, 'index'])->name('records.index');
        Route::post('/data-latih', [Admin\SailingRecordController::class, 'store'])->name('records.store');
        Route::delete('/data-latih/{record}', [Admin\SailingRecordController::class, 'destroy'])->name('records.destroy');
        Route::post('/data-latih/impor', [Admin\SailingRecordController::class, 'import'])->name('records.import');
        Route::get('/data-latih/ekspor', [Admin\SailingRecordController::class, 'export'])->name('records.export');

        Route::resource('perahu', Admin\BoatController::class)->except('show')->parameters(['perahu' => 'boat'])->names('boats');
        Route::resource('paket-makan', Admin\MealPackageController::class)->except('show')->parameters(['paket-makan' => 'meal'])->names('meals');
        Route::resource('sewa-alat', Admin\RentalItemController::class)->except('show')->parameters(['sewa-alat' => 'rental'])->names('rentals');
        Route::resource('pengguna', Admin\UserController::class)->except('show')->parameters(['pengguna' => 'user'])->names('users');

        Route::post('/pengembang/tampilan', [Admin\DeveloperController::class, 'appearance'])->name('developers.appearance');
        Route::resource('pengembang', Admin\DeveloperController::class)->except('show')->parameters(['pengembang' => 'developer'])->names('developers');

        Route::get('/seo', [Admin\SeoController::class, 'edit'])->name('seo.edit');
        Route::put('/seo', [Admin\SeoController::class, 'update'])->name('seo.update');
        Route::get('/pembayaran', [Admin\PaymentController::class, 'edit'])->name('payment.edit');
        Route::put('/pembayaran', [Admin\PaymentController::class, 'update'])->name('payment.update');
        Route::post('/pembayaran/uji', [Admin\PaymentController::class, 'test'])->middleware('throttle:5,1')->name('payment.test');

        Route::get('/email', [Admin\EmailController::class, 'edit'])->name('email.edit');
        Route::put('/email', [Admin\EmailController::class, 'update'])->name('email.update');
        Route::post('/email/uji', [Admin\EmailController::class, 'test'])->middleware('throttle:5,1')->name('email.test');

        Route::get('/pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
