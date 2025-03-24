<?php

namespace App\Api\v1\Controllers;

use App\Models\Offer;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;

class OfferController extends ApiController
{
    public function index()
    {
        return Offer::all();
    }

    public function getOffersStatistics()
    {
        $stats = Offer::countByStatusWithPercentages()->get();
        
        return response()->json([
            'statistics' => $stats,
            'total_offers' => Offer::count(),
        ]);
    }
    
    public function getClientOffersStatistics()
    {
        $stats = Offer::countByClientWithPercentages()->get();
        
        return response()->json([
            'statistics_by_client' => $stats,
            'total_offers' => Offer::count(),
        ]);
    }

    public function getUserOffersStatistics()
    {
        $stats = Offer::countByUserWithPercentages()->get();
        
        return response()->json([
            'statistics_by_user' => $stats,
            'total_offers' => Offer::count(),
        ]);
    }
}
