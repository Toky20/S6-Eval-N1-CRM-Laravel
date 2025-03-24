<?php

namespace App\Api\v1\Controllers;

use App\Models\User;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;
use Datatables;

class UserController extends ApiController
{
    public function index()
    {
        return User::all();
        //return Datatables::of(User::all());
    }
}
