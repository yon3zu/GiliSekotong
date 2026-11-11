<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Menyimpan foto/video galeri beranda.
 * Foto diperkecil dan disimpan ulang sebagai JPEG agar halaman tetap ringan; proses ini juga
 * membuang metadata EXIF (termasuk lokasi GPS kamera).
 */
class MediaUploader
{
    public const MAX_IMAGE_SIDE = 2000;

    public const JPEG_QUALITY = 82;

    public function storeImage(UploadedFile $file, string $dir = 'media', int $maxSide = self::MAX_IMAGE_SIDE): string
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
            throw new RuntimeException('Gambar tidak dapat dibaca. Gunakan JPG atau PNG.');
        }

        $image = $this->fixOrientation($image, $file->getRealPath());
        $image = $this->resize($image, $maxSide);

        // Latar putih untuk PNG transparan sebelum disimpan sebagai JPEG.
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imageinterlace($canvas, true); // JPEG progresif: tampil bertahap di jaringan lambat

        $path = $dir.'/'.Str::random(32).'.jpg';
        ob_start();
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        Storage::disk('public')->put($path, ob_get_clean());

        imagedestroy($image);
        imagedestroy($canvas);

        return $path;
    }

    public function storeVideo(UploadedFile $file, string $dir = 'media'): string
    {
        return $file->storeAs($dir, Str::random(32).'.'.strtolower($file->guessExtension() ?: 'mp4'), 'public');
    }

    /** Buat sampul dari detik pertama video bila ffmpeg tersedia di server; selain itu kembalikan null. */
    public function posterFromVideo(string $videoPath, string $dir = 'media'): ?string
    {
        $ffmpeg = config('services.ffmpeg', 'ffmpeg');
        $target = $dir.'/'.Str::random(32).'.jpg';

        try {
            $result = Process::timeout(30)->run([
                $ffmpeg, '-y', '-ss', '1', '-i', Storage::disk('public')->path($videoPath),
                '-frames:v', '1', '-vf', 'scale=min(1600\,iw):-2', '-q:v', '4', Storage::disk('public')->path($target),
            ]);
        } catch (Throwable $e) {
            Log::info('ffmpeg tidak tersedia, sampul video dilewati: '.$e->getMessage());

            return null;
        }

        return $result->successful() && Storage::disk('public')->exists($target) ? $target : null;
    }

    private function resize(\GdImage $image, int $maxSide): \GdImage
    {
        [$w, $h] = [imagesx($image), imagesy($image)];
        $scale = min(1, $maxSide / max($w, $h));
        if ($scale >= 1) {
            return $image;
        }

        // imagecopyresampled, bukan imagescale(IMG_BICUBIC): mode bicubic tidak didukung libgd sistem (PHP di Ubuntu).
        [$nw, $nh] = [(int) round($w * $scale), (int) round($h * $scale)];
        $resized = imagecreatetruecolor($nw, $nh);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($image);

        return $resized;
    }

    /** Foto dari HP sering tersimpan miring dengan petunjuk rotasi di EXIF. */
    private function fixOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

        return $angle ? imagerotate($image, $angle, 0) : $image;
    }
}
