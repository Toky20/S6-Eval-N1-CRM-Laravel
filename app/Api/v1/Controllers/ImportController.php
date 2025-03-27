<?php

namespace App\Api\v1\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Import\ImportService;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Services\Import\ApiImportService;
use Exception;


class ImportController extends Controller
{
    public function index (Request $request, ApiImportService $service)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $result = $service->processCSV($request->file('csv_file')->getRealPath());

            $service->insertData($result['data'], 'temp_project_invoices');

            $service->importFromTempTables();

            /* $errors = array_merge(
                $projects['errors'],
                $tasks['errors'],
                $leads['errors']
            );
     if (!empty($errors)) {
                return redirect()->back()
                               ->withErrors($errors)
                               ->withInput();
            } */
            
            /* return response()->json([
                'message' => 'Import partiellement réussi',
                'success_count' => count($result['data']),
                'error_count' => count($result['errors']),
                'errors' => $result['errors'],
                'sample_data' => array_slice($result['data'], 0, 5)
            ]); */

            return response()->json([
                'success' => [
                    'imported' => count($result['data']),
                    'temporary_table' => 'temp_project_invoices'
                ],
                'errors' => $result['errors']
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Échec critique de l\'import',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}