<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sent_at',
        'status',
        'due_at',
        'client_id',
        'source_id',
        'source_type',
        'status',
        'external_id'
    ];

    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function setAsWon()
    {
        $this->status = OfferStatus::won()->getStatus();
        $this->save();
    }

    public function setAsLost()
    {
        $this->status = OfferStatus::lost()->getStatus();
        $this->save();
    }

    /* Statistique par rapport au status */  
    public function scopeCountByStatusWithPercentages()
    {
        $totalOffers = self::count();
        
        return self::selectRaw("
            status,
            count(*) as total,
            round((count(*) / {$totalOffers}) * 100, 2) as percentage
        ")
        ->groupBy('status')
        ->orderBy('total', 'desc');
    }
    /* Statistique par rapport au status */

    
    public function scopeCountByClientWithPercentages()
    {
        $totalOffers = Offer::count();
        
        return Offer::selectRaw("
            contacts.name as client_name,
            COUNT(*) as total_offers,
            ROUND((COUNT(*) / {$totalOffers}) * 100, 2) as percentage_of_total
        ")
        ->join('clients', 'offers.client_id', '=', 'clients.id')
        ->join('contacts', 'clients.id', '=', 'contacts.client_id')
        ->groupBy('clients.id', 'contacts.name')
        ->orderBy('total_offers', 'desc');

        /* ,
            SUM(CASE WHEN offers.status = '" . OfferStatus::won()->getStatus() . "' THEN 1 ELSE 0 END) as won_offers,
            SUM(CASE WHEN offers.status = '" . OfferStatus::lost()->getStatus() . "' THEN 1 ELSE 0 END) as lost_offers */
    }

    public function scopeCountByUserWithPercentages()
    {
        $totalOffers = Offer::count();
        
        return Offer::selectRaw("
            users.name as user_name,
            COUNT(*) as total_offers,
            ROUND((COUNT(*) / {$totalOffers}) * 100, 2) as percentage_of_total
        ")
        ->join('leads', 'offers.source_id', '=', 'leads.id')
        ->join('users', 'leads.user_assigned_id', '=', 'users.id')
        ->groupBy('users.id', 'users.name')
        ->orderBy('total_offers', 'desc');
    }
}
