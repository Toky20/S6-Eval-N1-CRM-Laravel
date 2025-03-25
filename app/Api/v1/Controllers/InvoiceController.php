<?php

namespace App\Api\v1\Controllers;

use App\Models\Invoice;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\InvoiceLine;
use App\Services\Invoice\InvoiceCalculator;
use App\Models\Setting;

class InvoiceController extends ApiController
{
    /* public function index()
    {
        return Invoice::all();
    } */

    /* public function index()
    {
        $invoices = Invoice::with('invoiceLines')
            ->withCount('invoiceLines')
            ->with('getTotalPriceAttribute')
            ->get();    
            
        return response()->json([
            'invoices' => $invoices,
            'total' => $invoices->count()
        ]);
    } */
//

    public function index()
    {
        $invoices = Invoice::with('invoiceLines')
            ->withCount('invoiceLines')
            ->get();
        
        return response()->json([
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'external_id' => $invoice->external_id,
                    'status' => $invoice->status,
                    'invoice_number' => $invoice->invoice_number,
                    'total_price' => $invoice->total_price,
                    'tauxremise' => $invoice->tauxremise,
                    'invoice_lines_count' => $invoice->invoice_lines_count,
                    'invoice_lines' => $invoice->invoice_lines,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at
                ];
            }),
            'total' => $invoices->count()
        ]);
    }

    public function getProductQty()
    {
        $quantities = Product::GetTotalQuantitiesByProductWithPercentages()
            ->get();
            
        return response()->json([
            'quantities_by_product' => $quantities,
            'total_quantity' => InvoiceLine::sum('quantity'),
        ]);
    }

    public function getInvoiceStatusDistribution()
    {
        $stats = Invoice::GetStatusDistribution()->get();
        
        return response()->json([
            'status_distribution' => $stats,
            'total_invoices' => Invoice::count(),
        ]);
    }

    /**
     * Applique une remise à une facture
     *
     * @param Request $request
     * @param string $externalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyDiscount(Request $request, $externalId)
    {
        $invoice = Invoice::where('external_id', $externalId)
            ->firstOrFail();

        $invoice->update([
            'tauxremise' => Setting::first()->tauxremise,
            'discount_applied_at' => now(),
        ]);

        return response()->json([
            'invoice' => $invoice->fresh(),
            'message' => 'Remise appliquée avec succès',
        ]);
    }

    /**
     * Annule la remise d'une facture
     *
     * @param string $externalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDiscount($externalId)
    {
        $invoice = Invoice::where('external_id', $externalId)
            ->firstOrFail();

        $invoice->update([
            'tauxremise' => null,
            'discount_applied_at' => null,
        ]);

        return response()->json([
            'invoice' => $invoice->fresh(),
            'message' => 'Remise annulée avec succès',
        ]);
    }

    /**
     * Synchronise toutes les factures ayant un taux de remise non null
     * avec la valeur du taux de remise dans les paramètres système.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncDiscountedInvoices()
    {
        try {
            // Récupérer le taux de remise des paramètres système
            $discountRate = Setting::first()->tauxremise;
            
            // Récupérer toutes les factures avec tauxremise non null
            $invoices = Invoice::whereNotNull('tauxremise')
                ->get();
            
            // Mettre à jour chaque facture
            $invoices->each(function ($invoice) use ($discountRate) {
                $invoice->update([
                    'tauxremise' => $discountRate,
                    'discount_applied_at' => now(),
                ]);
            });
            
            return response()->json([
                'updated_count' => $invoices->count(),
                'message' => 'Factures mises à jour avec succès',
                'invoices' => $invoices->fresh(),
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la synchronisation des factures',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
