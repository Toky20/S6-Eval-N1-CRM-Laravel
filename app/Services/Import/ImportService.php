<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;
use App\Imports\{
    ProjectCsvDTO,
    TaskCsvDTO,
    LeadInvoiceCsvDTO
};
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Invoice;

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
        DB::statement("DROP TABLE  IF EXISTS temp_projects;");
        DB::statement("DROP TABLE  IF EXISTS temp_tasks;");
        DB::statement("DROP TABLE  IF EXISTS temp_leads_invoices;");
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
    public function importFromTempTables()
    {
        // À implémenter selon les besoins
        // Exemple de requête possible :
        // DB::insert("INSERT INTO $targetTable ($columns) SELECT $columnsMapping FROM $tempTable");

        //DB::statement('ALTER TABLE comments AUTO_INCREMENT = 1');
        
        DB::statement("insert into clients ( company_name,user_id,industry_id)
        select distinct 
        tp.client_name ,
        1,1
        from temp_projects tp 
        left join clients 
        c on c.company_name = tp.client_name where c.company_name is null;");

        DB::statement("insert into contacts (name,email,client_id,is_primary)
        select distinct cl.company_name, 
        CONCAT(LOWER(cl.company_name), '@', LOWER(cl.company_name), '.com'),
        cl.id,1 from clients cl 
        left join contacts co on cl.id=co.client_id where co.client_id is null;");

        DB::statement("insert into projects (title,description,status_id,user_assigned_id,user_created_id,
        client_id,deadline,created_at)
        select distinct tp.project_title,
        tp.project_title,11,1,1,cl.id,DATE_ADD(CURDATE(), INTERVAL 3 DAY),UTC_TIMESTAMP()
        from temp_projects tp 
        left join projects p on p.title=tp.project_title 
        join clients cl on cl.company_name=tp.client_name
        where p.title is null;");

        DB::statement("insert into tasks (title,description,status_id,
        user_assigned_id,user_created_id,client_id,project_id,deadline,created_at)
        select distinct tt.task_title,tt.task_title,11,1,1,
        p.client_id,p.id, DATE_ADD(CURDATE(), INTERVAL 3 DAY),UTC_TIMESTAMP()
        from temp_tasks tt 
        left join tasks t on t.title=tt.task_title 
        join projects p on p.title=tt.project_title 
        where t.title is null;");

        DB::statement("insert into clients ( company_name,user_id,industry_id)
        select distinct 
        tp.client_name ,
        1,1
        from temp_leads_invoices tp 
        left join clients 
        c on c.company_name = tp.client_name where c.company_name is null;");

        DB::statement("insert into contacts (name,email,client_id,is_primary)
        select distinct cl.company_name, 
        CONCAT(LOWER(cl.company_name), '@', LOWER(cl.company_name), '.com'),
        cl.id,1 from clients cl 
        left join contacts co on cl.id=co.client_id where co.client_id is null;");

        DB::statement("insert into leads(title,description,status_id,user_assigned_id,
        client_id,user_created_id,deadline)
        select distinct tli.lead_title,tli.lead_title,
        7,1,cl.id,1,DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        from temp_leads_invoices tli 
        left join leads l on l.title=tli.lead_title 
        join clients cl on cl.company_name=tli.client_name
        where l.title is null;");

        DB::statement("insert into offers(source_type,source_id,client_id,status,created_at,updated_at)
        select distinct CONCAT('App\Models\Lead') ,
        l.id,c.id,'in-progress',CURDATE(),CURDATE() 
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        where tli.type='offers' 
        and o.source_id is null and o.client_id is null ;");

        DB::statement("insert into products (name,number,default_type,archived,price,created_at,updated_at)
        select distinct tli.produit,1,'pieces',0,0,CURDATE(),CURDATE() 
        from temp_leads_invoices tli 
        left join products p on p.name=tli.produit 
        where p.name is null;");

        DB::statement("insert into invoice_lines(title,comment,price ,offer_id ,type ,quantity ,created_at,updated_at,product_id)
        select distinct tli.produit,tli.produit,tli.prix,o.id,'pieces',tli.quantite,CURDATE(),CURDATE(),p.id
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        left join products p on p.name=tli.produit
        left join invoice_lines il on il.offer_id = o.id
        and il.title=tli.produit and il.quantity =tli.quantite
        where tli.type='offers' 
        and il.offer_id is null and il.title is null 
        and il.quantity is null;");

        DB::statement("insert into offers(source_type,source_id,client_id,status,created_at,updated_at)
        select distinct CONCAT('App\Models\Lead') ,
        l.id,c.id,'won',CURDATE(),CURDATE() 
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        where tli.type='invoice' 
        and o.source_id is null and o.client_id is null ;");

        DB::statement("insert into invoices(source_type,source_id,client_id,status,created_at,updated_at,offer_id)
        select distinct CONCAT('App\Models\Lead') ,
        l.id,c.id,'draft',CURDATE(),CURDATE() ,
        o.id
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        left join invoices inv on inv.offer_id = o.id
        where tli.type='invoice' 
        and inv.id is null ;");

        DB::statement("insert into invoice_lines(title,comment,price ,offer_id ,type ,quantity ,created_at,updated_at,product_id)
        select distinct tli.produit,tli.produit,tli.prix,o.id,'pieces',tli.quantite,CURDATE(),CURDATE(),p.id
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        left join products p on p.name=tli.produit
        left join invoice_lines il on il.offer_id = o.id
        and il.title=tli.produit and il.quantity =tli.quantite
        where tli.type='invoice' 
        and il.offer_id is null ;");

        DB::statement("insert into invoice_lines(title,comment,price ,invoice_id  ,type ,quantity ,created_at,updated_at,product_id)
        select distinct tli.produit,tli.produit,tli.prix,inv.id,'pieces',tli.quantite,CURDATE(),CURDATE(),p.id
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        left join products p on p.name=tli.produit
        left join invoice_lines il on il.offer_id = o.id
        and il.title=tli.produit and il.quantity =tli.quantite
        left join invoices inv on inv.offer_id = o.id
        where tli.type='invoice' 
        and il.invoice_id is null ;");

        Offer::query()->update(['source_type' => Lead::class]);
        Invoice::query()->update(['source_type' => Lead::class]);

    }
}
/* select *
        from temp_leads_invoices tli 
        join leads l on tli.lead_title=l.title 
        left join offers o on o.source_id=l.id 
        join clients c on c.company_name=tli.client_name 
        left join products p on p.name=tli.produit
        left join invoice_lines il on il.offer_id = o.id
        join invoices inv on inv.offer_id = o.id

        and il.title=tli.produit and il.quantity =tli.quantite
        where tli.type='invoice' 
        and il.offer_id is null and il.title is null 
        and il.quantity is null; */