<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryMapping;
use Illuminate\Http\Request;

class CategoryMappingController extends Controller
{
    public function index()
    {
        $categories = CategoryMapping::orderBy('internal_name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'internal_name' => 'required|string|max:255|unique:category_mappings,internal_name',
            'ozon_category_ids' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Преобразуем строку с ID (например "123,456,789") в массив целых чисел
        $ids = array_map('intval', array_filter(array_map('trim', explode(',', $request->ozon_category_ids))));

        if (empty($ids)) {
            return back()->withErrors(['ozon_category_ids' => 'Введите хотя бы один числовой ID категории.'])->withInput();
        }

        $category = CategoryMapping::create([
            'internal_name' => $validated['internal_name'],
            'ozon_category_ids' => $ids,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Категория «{$category->internal_name}» добавлена.");
    }

    public function edit(CategoryMapping $category)
    {
        $idsString = implode(', ', $category->ozon_category_ids);
        return view('admin.categories.edit', compact('category', 'idsString'));
    }

    public function update(Request $request, CategoryMapping $category)
    {
        $validated = $request->validate([
            'internal_name' => 'required|string|max:255|unique:category_mappings,internal_name,' . $category->id,
            'ozon_category_ids' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $ids = array_map('intval', array_filter(array_map('trim', explode(',', $request->ozon_category_ids))));

        if (empty($ids)) {
            return back()->withErrors(['ozon_category_ids' => 'Введите хотя бы один числовой ID категории.'])->withInput();
        }

        $category->update([
            'internal_name' => $validated['internal_name'],
            'ozon_category_ids' => $ids,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Категория «{$category->internal_name}» обновлена.");
    }

    public function destroy(CategoryMapping $category)
    {
        $name = $category->internal_name;
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', "Категория «{$name}» удалена.");
    }
}
