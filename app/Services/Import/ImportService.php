<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;
use App\Imports\{
    ProjectCsvDTO,
    TaskCsvDTO,
    LeadInvoiceCsvDTO
};

class ImportService
{
    public function processFile($file, string $type): array
    {
        $errors = [];
        $data = [];
        $lineNumber = 1;

        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            try {
                switch ($type) {
                    case 'project':
                        $dto = new ProjectCsvDTO($row[0], $row[1]);
                        break;
                    case 'task':
                        $dto = new TaskCsvDTO($row[0], $row[1]);
                        break;
                    case 'lead':
                        $dto = new LeadInvoiceCsvDTO(
                            $row[0], 
                            $row[1], 
                            $row[2], 
                            $row[3], 
                            (float)$row[4], 
                            (int)$row[5]
                        );
                        break;
                }
                
                $data[] = $dto; // On stocke l'objet DTO directement
            } catch (\Exception $e) {
                $errors[] = "Fichier {$type} - Ligne {$lineNumber} : {$e->getMessage()}";
            }
        }

        fclose($handle);

        return ['data' => $data, 'errors' => $errors];
    }

    public function createTempTables()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS temp_projects (
                project_title VARCHAR(255) NOT NULL,
                client_name VARCHAR(255) NOT NULL
            )
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS temp_tasks (
                project_title VARCHAR(255) NOT NULL,
                task_title VARCHAR(255) NOT NULL
            )
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS temp_leads_invoices (
                client_name VARCHAR(255) NOT NULL,
                lead_title VARCHAR(255) NOT NULL,
                type VARCHAR(10) NOT NULL CHECK (type IN ('offer', 'invoice')),
                produit VARCHAR(255) NOT NULL,
                prix DECIMAL(10,2) NOT NULL CHECK (prix >= 0),
                quantite INT NOT NULL CHECK (quantite >= 0)
            )
        ");
    }

    public function insertData(array $data, string $table)
    {
        if (empty($data)) return;

        $chunks = array_chunk($data, 500);
        
        foreach ($chunks as $chunk) {
            $values = [];
            $params = [];
            $columns = [];
            
            // Initialiser les colonnes à partir du premier élément
            if (!empty($chunk)) {
                $firstItem = $chunk[0]->toArray();
                $columns = array_keys($firstItem);
            }
            
            foreach ($chunk as $item) {
                $itemArray = $item->toArray();
                $values[] = '(' . implode(', ', array_fill(0, count($itemArray), '?')) . ')';
                $params = array_merge($params, array_values($itemArray));
            }
            
            $columnsList = implode(', ', $columns);
            
            DB::insert("
                INSERT INTO {$table} ({$columnsList})
                VALUES " . implode(', ', $values),
                $params
            );
        }
    }
    


    public function getStats(): string
    {
        return sprintf(
            "Projets: %d, Tâches: %d, Transactions: %d",
            DB::table('temp_projects')->count(),
            DB::table('temp_tasks')->count(),
            DB::table('temp_leads_invoices')->count()
        );
    }

    /**
     * Méthode à implémenter pour les imports personnalisés
     * Exemple d'utilisation :
     * $this->importFromTempTables('users', 'temp_projects', 'project_title AS name, client_name AS company');
     */
    public function importFromTempTables(string $targetTable, string $tempTable, string $columnsMapping): void
    {
        // À implémenter selon les besoins
        // Exemple de requête possible :
        // DB::insert("INSERT INTO $targetTable ($columns) SELECT $columnsMapping FROM $tempTable");
    }
}



    

    