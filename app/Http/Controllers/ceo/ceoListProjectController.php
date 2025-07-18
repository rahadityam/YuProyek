<?php
namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ceoListProjectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        
        $query = Project::query()->with('owner:id,name');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        $projects = $query->paginate($perPage);

        if ($request->wantsJson()) {
            return response()->json($projects);
        }

        return view('ceo.project_list', compact('projects'));
    }
}