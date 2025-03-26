<?php

namespace App\Models;

use App\Repositories\Money\Money;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceLine;

class Product extends Model
{
    protected $appends = ['divided_price'];
    protected $hidden=['id'];
    
    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function getMoneyPriceAttribute()
    {
        $money = new Money($this->price);
        return $money;
    }

    public function getDividedPriceAttribute()
    {
        return $this->price / 100;
    }

    public function scopeGetTotalQuantitiesByProductWithPercentages()
    {
        //$totalQuantity = InvoiceLine::sum('quantity');
        $totalQuantity = InvoiceLine::whereNotNull('invoice_id')->sum('quantity');
    
        return InvoiceLine::selectRaw("
            products.id,
            products.name,
            COALESCE(SUM(invoice_lines.quantity), 0) as total_quantity,
            ROUND((COALESCE(SUM(invoice_lines.quantity), 0) / {$totalQuantity}) * 100, 2) as percentage_of_total
        ")
        ->leftJoin('products', 'invoice_lines.product_id', '=', 'products.id')
        ->whereNotNull('invoice_lines.invoice_id')
        ->groupBy('products.id', 'products.name')
        ->orderBy('total_quantity', 'desc');
    }
    
}
