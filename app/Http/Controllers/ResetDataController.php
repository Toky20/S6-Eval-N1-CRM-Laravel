<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataReset; // Utilisation du modèle pour gérer la logique de réinitialisation

class ResetDataController extends Controller
{
    // Affiche une vue, ou simplement une réponse pour le cas de réinitialisation
    public function index()
    {
        // Vous pouvez afficher une vue ici si nécessaire pour confirmer ou prévisualiser la réinitialisation
        return view('resetdata.index');
    }

    // Méthode pour réinitialiser les données
    public function reset()
    {
        try {
            // Appel à la logique du modèle pour réinitialiser les données
            DataReset::resetDatabase();

            Session()->flash('flash_message', __('Données réinitialisées avec succès!'));

            // Retourner un message de succès
            return redirect()->route('resetdata.index');
            
        } catch (\Exception $e) {
            session()->flash('flash_message_warning', __('Erreur lors de la réinitialisation des données: ') . $e->getMessage());
            // Gérer l'erreur si la réinitialisation échoue
            return redirect()->route('resetdata.index');
        }
    }
}
