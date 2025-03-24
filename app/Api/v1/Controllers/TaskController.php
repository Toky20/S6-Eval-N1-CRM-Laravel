<?php

namespace App\Api\v1\Controllers;

use App\Models\Task;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;

class TaskController extends ApiController
{
    public function index()
    {
        return Task::all();
    }
}
