<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Setting;
use App\Services\MediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Isi halaman publik Tim Pengembang (hanya super admin). */
class DeveloperController extends Controller
{
    private const PHOTO_SIDE = 800;

    private const PHOTO_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:8192'];

    public function index()
    {
        return view('admin.developers.index', [
            'lecturers' => Developer::query()->where('kind', 'dosen')->orderBy('sort')->orderBy('id')->get(),
            'students' => Developer::query()->where('kind', 'mahasiswa')->orderBy('sort')->orderBy('id')->get(),
            'banner' => Setting::get('dev_banner'),
            'leadPhoto' => Setting::get('dev_lead_photo'),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.developers.form', ['developer' => new Developer([
            'kind' => $request->query('jenis') === 'dosen' ? 'dosen' : 'mahasiswa',
            'is_active' => true,
            'sort' => (Developer::max('sort') ?? 0) + 1,
        ])]);
    }

    public function store(Request $request, MediaUploader $uploader)
    {
        $data = $this->validated($request);

        try {
            if ($request->hasFile('photo')) {
                $data['photo'] = $uploader->storeImage($request->file('photo'), 'developers', self::PHOTO_SIDE);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['photo' => $e->getMessage()]);
        }

        Developer::create($data);

        return redirect()->route('admin.developers.index')->with('success', 'Anggota tim ditambahkan.');
    }

    public function edit(Developer $developer)
    {
        return view('admin.developers.form', compact('developer'));
    }

    public function update(Request $request, Developer $developer, MediaUploader $uploader)
    {
        $data = $this->validated($request);

        try {
            if ($request->hasFile('photo')) {
                $this->deleteFile($developer->photo);
                $data['photo'] = $uploader->storeImage($request->file('photo'), 'developers', self::PHOTO_SIDE);
            } elseif ($request->boolean('remove_photo')) {
                $this->deleteFile($developer->photo);
                $data['photo'] = null;
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['photo' => $e->getMessage()]);
        }

        $developer->update($data);

        return redirect()->route('admin.developers.index')->with('success', 'Anggota tim diperbarui.');
    }

    public function destroy(Developer $developer)
    {
        $this->deleteFile($developer->photo);
        $developer->delete();

        return back()->with('success', 'Anggota tim dihapus.');
    }

    /** Foto banner halaman dan foto web developer utama. */
    public function appearance(Request $request, MediaUploader $uploader)
    {
        $request->validate(['banner' => self::PHOTO_RULES, 'lead_photo' => self::PHOTO_RULES]);

        try {
            $this->replaceSetting($request, $uploader, 'banner', 'dev_banner', MediaUploader::MAX_IMAGE_SIDE);
            $this->replaceSetting($request, $uploader, 'lead_photo', 'dev_lead_photo', self::PHOTO_SIDE);
        } catch (RuntimeException $e) {
            return back()->withErrors(['banner' => $e->getMessage()]);
        }

        return back()->with('success', 'Foto disimpan.');
    }

    private function replaceSetting(Request $request, MediaUploader $uploader, string $field, string $key, int $side): void
    {
        if ($request->hasFile($field)) {
            $this->deleteFile(Setting::get($key));
            Setting::put([$key => $uploader->storeImage($request->file($field), 'developers', $side)]);
        } elseif ($request->boolean("remove_{$field}")) {
            $this->deleteFile(Setting::get($key));
            Setting::put([$key => '']);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:60'],
            'kind' => ['required', Rule::in(array_keys(Developer::KINDS))],
            'sort' => ['required', 'integer', 'min:0', 'max:999'],
            'photo' => self::PHOTO_RULES,
        ]);

        unset($data['photo']);
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
