<?php

namespace App\Http\Middleware;

use App\Services\Bmkg\BmkgImporter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Support\defer;

/**
 * Cadangan bila scheduler tidak berjalan (mis. XAMPP lokal tanpa cron):
 * setelah respons dikirim, perbarui data BMKG hanya jika jeda minimumnya sudah lewat.
 */
class RefreshBmkg
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config("bmkg.auto_refresh")) {
            defer(fn () => app(BmkgImporter::class)->refresh(), "bmkg-refresh");
        }

        return $next($request);
    }
}
