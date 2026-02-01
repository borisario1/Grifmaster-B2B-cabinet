<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Resource;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    /**
     * Display the files page.
     */
    public function index(Request $request)
    {
        // Блок 1: Общие документы (pinned)
        $pinnedResources = Resource::active()
            ->pinned()
            ->with('brand')
            ->get();
        
        // Блок 2: Бренды
        $brands = Brand::active()
            ->ordered()
            ->withCount(['resources' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();
        
        // Блок 3: Все документы, сгруппированные по типам
        $resourcesByType = Resource::active()
            ->with('brand')
            ->get()
            ->groupBy('type');
        
        return view('files.files', compact('pinnedResources', 'brands', 'resourcesByType'));
    }
}
