<?php

namespace App\Api\v1\Controllers;

use App\Models\Client;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;

class ClientController extends ApiController
{
    public function index()
    {
        return Client::all();
    }
}
