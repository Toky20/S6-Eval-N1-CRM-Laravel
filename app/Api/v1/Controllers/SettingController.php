<?php

namespace App\Api\v1\Controllers;

use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends ApiController
{
    /**
     * Récupère le taux de remise actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDiscountRate()
    {
        try {
            $setting = Setting::firstOrCreate([], [
                'tauxremise' => 0
            ]);
            
            return response()->json([
                'tauxremise' => $setting->tauxremise,
                'message' => 'Taux de remise récupéré avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du taux de remise',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le taux de remise
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDiscountRate(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'tauxremise' => 'required|numeric|between:0,100'
            ]);
            
            // Récupérer ou créer le setting
            $setting = Setting::firstOrCreate([]);
            
            // Mettre à jour le taux de remise
            $setting->update([
                'tauxremise' => $request->input('tauxremise')
            ]);
            
            return response()->json([
                'tauxremise' => $setting->tauxremise,
                'message' => 'Taux de remise mis à jour avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du taux de remise',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}