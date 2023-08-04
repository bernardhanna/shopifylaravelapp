<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-24 11:37:47
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 13:18:10
 */
namespace App\Http\Controllers;

use App\Services\GarbageCollectionService;
use Illuminate\Http\Request;

class GarbageCollectionController extends Controller
{
    private $garbageCollectionService;

    public function __construct(GarbageCollectionService $garbageCollectionService)
    {
        $this->garbageCollectionService = $garbageCollectionService;
    }

    public function garbageCollection()
    {
        $this->garbageCollectionService->performGarbageCollection();

        return redirect()->back()->with('success', 'Garbage collection completed successfully.');
    }
}