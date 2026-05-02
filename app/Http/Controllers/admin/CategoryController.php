<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GoogleService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
     public function index()
    {
        // $categories = Category::all(); // MySQL (main source)
        $categories = Category::orderBy('order')->get(); // MySQL (main source)
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order'          => 'nullable|integer',
            'name_en' => 'required|string|max:255',
            'name_km' => 'nullable|string|max:255',
            'name_ch' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($validated['name_en'], '-');

        DB::beginTransaction();

        try {
            // 1️⃣ Store in MySQL
            $category = Category::create([
                'name_en' => $validated['name_en'],
                'name_km' => $validated['name_km'],
                'name_ch' => $validated['name_ch'],
                'slug'    => $slug,
                'order'   => $validated['order'],
            ]);

            DB::commit();

            return redirect()->route('category.index')
                ->with('success', 'Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'We could not create the category. Please try again.');
        }
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
                'order'          => 'nullable|integer',
            'name_en' => 'required|string|max:255',
            'name_km' => 'nullable|string|max:255',
            'name_ch' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($validated['name_en'], '-');

        DB::beginTransaction();

        try {
            // 1️⃣ Update MySQL
            $category = Category::findOrFail($id);
            $category->update([
                'name_en' => $validated['name_en'],
                'name_km' => $validated['name_km'],
                'name_ch' => $validated['name_ch'],
                'slug'    => $slug,
                'order'   => $validated['order'],
            ]);

            DB::commit();

            return redirect()->route('category.index')
                ->with('success', 'Category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'We could not update the category. Please try again.');
        }
    }

      public function reorder(Request $request)
    {
        $newOrder = $request->newOrder;

        foreach ($newOrder as $item) {
            DB::table('categories')->where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function delete(string $id)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Delete from MySQL
            Category::where('id', $id)->delete();

            DB::commit();

            return redirect()->route('category.index')
                ->with('success', 'Category deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('category.index')
                ->with('error', 'We could not delete the category. Please try again.');
        }
    }
}
