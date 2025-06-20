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
        $projects = Project::paginate($perPage);
        return view('ceo.project_list', compact('projects'));
    }
}

