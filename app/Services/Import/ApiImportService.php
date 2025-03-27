<?php
namespace App\Services\Import;

use App\Imports\ProjectInvoiceDTO;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\DTO\Import\ImportableInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Invoice;

class ApiImportService
{
    public function processCSV(string $filePath): array
    {
        $this->createTempTable();
        
        $rows = array_map('str_getcsv', file($filePath));
        $headers = array_shift($rows);
        $headers = array_map('trim', $headers);

        $errors = [];
        $dtos = [];

        //$this->validateHeaders($headers);

        foreach ($rows as $index => $row) {
            $row = array_map('trim', $row);
            
            try {
                //$this->validateRow($row, $index);
                
                $dto = new ProjectInvoiceDTO(
                    $row[0],  
                    $row[1],  
                    $row[2],  
                    $row[3],  
                    (int)$row[4],  
                    (int)$row[5] 
                );

                //dd($dto);

                // Insertion dans la table temporaire
                $dtos[] = $dto;

                /*DB::insert("
                    INSERT INTO temp_project_invoices 
                        (client, project, lead,product, price, quantity) 
                    VALUES 
                        (?, ?, ?, ?, ?,?)
                ", [
                    $dto->client,
                    $dto->project,
                    $dto->lead,
                    $dto->product,
                    $dto->price,
                    $dto->quantity
                ]); */

            } catch (Exception $e) {
                $errors[] = "Ligne " . ($index + 2) . " : " . $e->getMessage();
            }
        }

        return ['data' => $dtos, 'errors' => $errors];
    }

    private function createTempTable()
    {
        DB::statement("DROP TABLE IF EXISTS temp_project_invoices;");
        DB::statement("
            CREATE TABLE IF NOT EXISTS temp_project_invoices (
                client VARCHAR(255) NOT NULL,
                project VARCHAR(255) NOT NULL,
                lead VARCHAR(255) NOT NULL,
                product VARCHAR(255) NOT NULL,
                price INT NOT NULL ,
                quantity INT )
        ");
    }

    private function validateHeaders(array $headers)
    {
        $expected = ['client', 'lead', 'produit', 'prix', 'quantite'];
        if ($headers !== $expected) {
            throw new Exception("En-têtes CSV invalides. Attendu : " . implode(', ', $expected));
        }
    }

    private function validateRow(array $row, int $index)
    {
        if (count($row) !== 5) {
            throw new Exception("Nombre de colonnes incorrect");
        }

        if (!is_numeric($row[3])) {
            throw new Exception("Prix invalide");
        }

        if (!ctype_digit($row[4])) {
            throw new Exception("Quantité invalide");
        }
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

    /**
     * Méthode à implémenter pour les imports personnalisés
     * Exemple d'utilisation :
     * $this->importFromTempTables('users', 'temp_projects', 'project_title AS name, client_name AS company');
     */
    public function importFromTempTables()
    {
        // À implémenter selon les besoins
        // Exemple de requête possible :
        // DB::insert("INSERT INTO $targetTable ($columns) SELECT $columnsMapping FROM $tempTable");

        //DB::statement('ALTER TABLE comments AUTO_INCREMENT = 1');
        
        DB::statement("insert into clients ( company_name,user_id,industry_id)
        select distinct 
        tp.client ,
        1,1
        from temp_project_invoices tp 
        left join clients 
        c on c.company_name = tp.client where c.company_name is null;");

        DB::statement("insert into contacts (name,email,client_id,is_primary)
        select distinct cl.company_name, 
        CONCAT(LOWER(cl.company_name), '@', LOWER(cl.company_name), '.com'),
        cl.id,1 from clients cl 
        left join contacts co on cl.id=co.client_id where co.client_id is null;");

        DB::statement("insert into projects (title,description,status_id,user_assigned_id,user_created_id,
        client_id,deadline,created_at)
        select distinct tp.project,
        tp.project,11,1,1,cl.id,DATE_ADD(CURDATE(), INTERVAL 3 DAY),UTC_TIMESTAMP()
        from temp_project_invoices tp 
        left join projects p on p.title=tp.project 
        join clients cl on cl.company_name=tp.client
        where p.title is null;");

        DB::statement("insert into leads(title,description,status_id,user_assigned_id,
        client_id,user_created_id,deadline)
        select distinct tli.lead,tli.lead,
        7,1,cl.id,1,DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        from temp_project_invoices tli 
        left join leads l on l.title=tli.lead 
        join clients cl on cl.company_name=tli.client
        where l.title is null;");

        DB::statement("insert into products (name,number,default_type,archived,price,created_at,updated_at)
        select distinct tli.product,1,'pieces',0,1,CURDATE(),CURDATE() 
        from temp_project_invoices tli 
        left join products p on p.name=tli.product 
        where p.name is null;");

        DB::statement("insert into offers(source_type,source_id,client_id,status,created_at,updated_at)
        select distinct CONCAT('App\Models\Lead') ,
        l.id,c.id,'won',CURDATE(),CURDATE() 
        from temp_project_invoices tli 
        join leads l on tli.lead=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client 
        where 
        o.source_id is null and o.client_id is null ;");

        DB::statement("insert into invoices(source_type,source_id,client_id,status,created_at,updated_at,offer_id)
        select distinct CONCAT('App\Models\Lead') ,
        l.id,c.id,'draft',CURDATE(),CURDATE() ,
        o.id
        from temp_project_invoices tli 
        join leads l on tli.lead=l.title 
        join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client 
        left join invoices inv on inv.offer_id = o.id
        where inv.id is null ;");

        DB::statement("insert into invoice_lines(title,comment,price ,offer_id ,type ,quantity ,created_at,updated_at,product_id)
        select distinct tli.product,tli.product,tli.price,o.id,'pieces',tli.quantity,CURDATE(),CURDATE(),p.id
        from temp_project_invoices tli 
        join leads l on tli.lead=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client 
        left join products p on p.name=tli.product
        left join invoice_lines il on il.offer_id = o.id
        and il.title=tli.product and il.quantity =tli.quantity
        where il.offer_id is null ;");

        DB::statement("insert into invoice_lines(title,comment,price ,invoice_id  ,type ,quantity ,created_at,updated_at,product_id)
        select distinct tli.product,tli.product,tli.price,inv.id,'pieces',tli.quantity,CURDATE(),CURDATE(),p.id
        from temp_project_invoices tli 
        join leads l on tli.lead=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client 
        left join products p on p.name=tli.product
        left join invoice_lines il on il.offer_id = o.id
        and il.title=tli.product and il.quantity =tli.quantity
        left join invoices inv on inv.offer_id = o.id
        where il.invoice_id is null ;");

        Offer::query()->update(['source_type' => Lead::class]);
        Invoice::query()->update(['source_type' => Lead::class]);

    }
}








