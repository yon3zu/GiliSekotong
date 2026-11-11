<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Foto & video keindahan gili untuk beranda. */
class MediaController extends Controller
{
    public const MAX_IMAGE_KB = 8192;

    public const MAX_VIDEO_KB = 35840;

    public function index()
    {
        return view('admin.media.index', [
            'hero' => MediaItem::query()->where('placement', 'hero')->orderBy('sort')->orderBy('id')->get(),
            'gallery' => MediaItem::query()->where('placement', 'gallery')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.media.form', ['item' => new MediaItem([
            'placement' => $request->query('posisi') === 'hero' ? 'hero' : 'gallery',
            'is_active' => true,
            'sort' => (MediaItem::max('sort') ?? 0) + 1,
        ])]);
    }

    public function store(Request $request, MediaUploader $uploader)
    {
        $data = $this->validated($request, true);
        $file = $request->file('file');
        $isVideo = str_starts_with((string) $file->getMimeType(), 'video/');

        try {
            $data['type'] = $isVideo ? 'video' : 'image';
            $data['path'] = $isVideo ? $uploader->storeVideo($file) : $uploader->storeImage($file);
            if ($isVideo) {
                $data['poster_path'] = $request->hasFile('poster')
                    ? $uploader->storeImage($request->file('poster'), 'media', 1600)
                    : $uploader->posterFromVideo($data['path']);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        MediaItem::create($data);

        return redirect()->route('admin.media.index')->with('success', $isVideo && empty($data['poster_path'])
            ? 'Video ditambahkan. Unggah gambar sampul agar tampil cepat di HP.'
            : 'Media ditambahkan ke beranda.');
    }

    public function edit(MediaItem $medium)
    {
        return view('admin.media.form', ['item' => $medium]);
    }

    public function update(Request $request, MediaItem $medium, MediaUploader $uploader)
    {
        $data = $this->validated($request, false);

        try {
            if ($request->hasFile('poster') && $medium->isVideo()) {
                $this->deleteFile($medium->poster_path);
                $data['poster_path'] = $uploader->storeImage($request->file('poster'), 'media', 1600);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['poster' => $e->getMessage()]);
        }

        $medium->update($data);

        return redirect()->route('admin.media.index')->with('success', 'Media diperbarui.');
    }

    public function destroy(MediaItem $medium)
    {
        $this->deleteFile($medium->path);
        $this->deleteFile($medium->poster_path);
        $medium->delete();

        return back()->with('success', 'Media dihapus.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'file' => [$creating ? 'required' : 'prohibited', 'file', 'mimetypes:image/jpeg,image/png,video/mp4,video/webm,video/quicktime', 'max:'.self::MAX_VIDEO_KB],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:'.self::MAX_IMAGE_KB],
            'title' => ['required', 'string', 'max:120'],
            'location' => ['nullable', Rule::in(MediaItem::LOCATIONS)],
            'placement' => ['required', Rule::in(array_keys(MediaItem::PLACEMENTS))],
            'sort' => ['required', 'integer', 'min:0', 'max:999'],
        ], [
            'file.mimetypes' => 'Format yang diterima: foto JPG/PNG atau video MP4/WebM/MOV.',
            'file.max' => 'Ukuran file maksimal 35 MB. Perkecil video terlebih dahulu (lihat tips di samping).',
            'title.required' => 'Isi keterangan singkat, misalnya "Pasir putih Gili Kedis". Teks ini juga dibaca Google.',
        ]);

        if ($creating && $request->file('file')?->getMimeType() && str_starts_with($request->file('file')->getMimeType(), 'image/')
            && $request->file('file')->getSize() > self::MAX_IMAGE_KB * 1024) {
            throw \Illuminate\Validation\ValidationException::withMessages(['file' => 'Ukuran foto maksimal 8 MB.']);
        }

        unset($data['file'], $data['poster']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
