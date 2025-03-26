<?php

namespace App\Imports;

use InvalidArgumentException;

class LeadInvoiceCsvDTO 
{
    public $client_name;
    public $lead_title;
    public $type;
    public $produit;
    public $prix;
    public $quantite;

    public function __construct(
        string $client_name,
        string $lead_title,
        string $type,
        string $produit,
        float $prix,
        int $quantite
    ) {
        // Validation
        /* if (!in_array($type, ['offer', 'invoice'])) {
            throw new InvalidArgumentException("Type invalide (doit être 'offer' ou 'invoice')");
        } */

        if ($quantite < 0 && $prix < 0) {
            throw new InvalidArgumentException("La quantité et le prix ne peuvent pas être négative");
        }
        
        if ($prix < 0) {
            throw new InvalidArgumentException("Le prix ne peut pas être négatif");
        }
        
        if ($quantite < 0) {
            throw new InvalidArgumentException("La quantité ne peut pas être négative");
        }

        // Assignation
        $this->client_name = $client_name;
        $this->lead_title = $lead_title;
        $this->type = $type;
        $this->produit = $produit;
        $this->prix = $prix;
        $this->quantite = $quantite;
    }

    public function toArray(): array
    {
        return [
            'client_name' => $this->client_name,
            'lead_title' => $this->lead_title,
            'type' => $this->type,
            'produit' => $this->produit,
            'prix' => $this->prix,
            'quantite' => $this->quantite
        ];
    }
}