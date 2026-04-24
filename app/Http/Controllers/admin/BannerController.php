<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'name'  => 'nullable|string',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,svg,mp4,webm,mov|max:512000',
        ]);

        $banner->name = $request->name;

        if ($request->hasFile('media')) {

            // delete old file
            if (!empty($banner->media['path'])) {
                $this->deleteFile(public_path($banner->media['path']));
            }

            // upload new file
            $banner->media = $this->uploadMedia($request->file('media'));
        }

        $banner->save();

        return redirect()->route('banner.index')
            ->with('success', 'Banner updated successfully!');
    }

    private function uploadMedia($file)
    {
        $mime = $file->getMimeType();
        $type = str_starts_with($mime, 'video/') ? 'video' : 'image';

        $folder = $type === 'video' ? 'uploads/videos' : 'uploads/images';

        // create folder if not exists
        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0777, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path($folder), $filename);

        return [
            'type' => $type,
            'path' => $folder . '/' . $filename,
            'mime' => $mime,
        ];
    }

    private function deleteFile($fullPath)
    {
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}