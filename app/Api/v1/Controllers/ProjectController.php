<?php

namespace App\Api\v1\Controllers;

use App\Models\Project;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{
    public function index()
    {
        return Project::all();
    }
}
