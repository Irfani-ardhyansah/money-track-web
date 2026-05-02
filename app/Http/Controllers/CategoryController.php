<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $categories = Category::when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories', 'type'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $q    = $request->input('q', '');
        $type = $request->input('type');

        $categories = Category::where('name', 'like', "%{$q}%")
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('type')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(
            $categories->map(fn ($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
            ])
        );
    }
}
