<?php

namespace App\Imports;

use InvalidArgumentException;

class ProjectInvoiceDTO
{
    public $client;
    public $project;
    public $lead;
    public $product;
    public $price;
    public $quantity;
    

    public function __construct(
        string $client,
        string $project,
        string $lead,
        string $product,
        float $price,
        int $quantity
    ) {
        //$this->validate($price, $quantity);

        $this->client = $client;
        $this->lead = $lead;
        $this->project = $project;
        $this->product = $product;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    private function validate(float $price, int $quantity): void
    {

        if($price < 0) {
            throw new \InvalidArgumentException("Le prix ne peut pas être négatif");
        }

        if($quantity < 0) {
            throw new \InvalidArgumentException("La quantité ne peut pas être négative");
        }
    }

    public function toArray(): array
    {
        return [
            'client' => $this->client,
            'lead' => $this->lead,
            'project' => $this->project,
            'product' => $this->product,
            'price' => $this->price,
            'quantity' => $this->quantity
        ];
    }

    public static function getHeaders(): array
    {
        return ['client_name', 'lead_title', 'project', 'produit', 'prix', 'quantite'];
    }
}