<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB; // Pour les requêtes SQL directement si nécessaire

class DataReset extends Model
{
    // Spécifie que ce modèle ne correspond à aucune table spécifique pour l'instant
    protected $table = null;

    public static function resetDatabase()
    {
        // Exemple de réinitialisation des données, par suppression ou réinitialisation des tables
        DB::beginTransaction();
        try {
            // Réinitialiser les tables en fonction des besoins
            DB::table('comments')->delete();
            DB::table('mails')->delete();
            DB::table('absences')->delete();
            DB::table('payments')->delete();
            DB::table('invoice_lines')->delete();
            DB::table('offers')->delete();
            DB::table('invoices')->delete();
            DB::table('contacts')->delete();
            DB::table('clients')->delete();
            DB::table('projects')->delete();
            DB::table('leads')->delete();
            DB::table('tasks')->delete();
            DB::table('appointments')->delete();
            DB::table('products')->delete();

            DB::statement('ALTER TABLE comments AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE mails AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE absences AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE payments AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE invoice_lines AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE offers AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE invoices AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE contacts AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE projects AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE leads AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE tasks AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE appointments AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE clients AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');

            // Si tout est ok, on valide la transaction
            DB::commit();
        } catch (\Exception $e) {
            // En cas d'erreur, on annule la transaction
            DB::rollBack();
            throw $e;
        }
    }
}
