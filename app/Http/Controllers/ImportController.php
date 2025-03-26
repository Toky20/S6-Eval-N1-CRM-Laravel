<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Import\ImportService;
use DB;

class ImportController extends Controller
{
    private $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showForm()
    {
        return view('import.form');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'projects' => 'required|file|mimes:csv,txt',
            'tasks' => 'required|file|mimes:csv,txt',
            'leads' => 'required|file|mimes:csv,txt'
        ]);

        // Traitement des fichiers
        $projects = $this->importService->processFile($request->file('projects'), 'project');
        $tasks = $this->importService->processFile($request->file('tasks'), 'task');
        $leads = $this->importService->processFile($request->file('leads'), 'lead');

        $errors = array_merge(
            $projects['errors'],
            $tasks['errors'],
            $leads['errors']
        );

        if (!empty($errors)) {
            return redirect()->back()
                           ->withErrors($errors)
                           ->withInput();
        }

        // Création des tables temporaires
        $this->importService->createTempTables();

        // Insertion des données
        $this->importService->insertData($projects['data'], 'temp_projects');
        $this->importService->insertData($tasks['data'], 'temp_tasks');
        $this->importService->insertData($leads['data'], 'temp_leads_invoices');

        $this->importService->importFromTempTables();

        /* return redirect()->back()
                       ->with('success', 'Les fichiers ont été importés avec succès !'); */
        // Ajout des statistiques et du message de succès
        return redirect()->back()
        ->with('success', [
            'message' => 'Les fichiers ont été importés avec succès !',
            'counts' => [
                'projects_count' => DB::table('temp_projects')->count(),
                'tasks_count' => DB::table('temp_tasks')->count(),
                'leads_count' => DB::table('temp_leads_invoices')->count()
            ]
        ]);               
    }
}