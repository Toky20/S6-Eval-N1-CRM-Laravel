<?php

namespace App\Api\v1\Controllers;

use App\Models\Payment;
use App\Api\v1\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Services\Invoice\InvoiceCalculator;
use App\Services\Invoice\GenerateInvoiceStatus;

class PaymentController extends ApiController
{
    /**
     * Retourne la somme totale par source de paiement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalByPaymentSource()
    {
        $totals = Payment::selectRaw("
            payment_source,
            COUNT(*) as total_payments,
            SUM(amount) as total_amount,
            ROUND((SUM(amount) / (SELECT SUM(amount) FROM payments)) * 100, 2) as percentage_of_total
        ")
        ->groupBy('payment_source')
        ->orderBy('total_amount', 'desc')
        ->get();

        return response()->json([
            'totals_by_source' => $totals,
            'total_amount' => Payment::sum('amount'),
        ]);
    }

    /**
     * Retourne tous les paiements pour une source donnée
     *
     * @param string $paymentSource
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentsBySource($paymentSource)
    {
        $payments = Payment::where('payment_source', $paymentSource)
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'payments' => $payments,
            'total' => $payments->sum('amount'),
        ]);
    }

    /**
     * Retourne un paiement spécifique
     *
     * @param string $externalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($externalId)
    {
        $payment = Payment::where('external_id', $externalId)
            //->with('invoice')
            ->firstOrFail();

        /* return response()->json([
            'payment' => $payment,
            'invoice' => $payment->invoice,
        ]); */
        return response()->json($payment
        );
    }

    /**
     * Met à jour un paiement existant
     *
     * @param Request $request
     * @param string $externalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $externalId)
    {
        if ($request->input('amount') < 0) {
            return response()->json([
                'error' => 'Le montant ne peut pas etre négatif'
            ], 422);
        }

        /*  */
        $payment = Payment::where('external_id', $externalId)
        ->with('invoice')
        ->firstOrFail();

        // Calcul du montant total après mise à jour
        $newAmount = $request->input('amount') * 100;
        $currentAmount = $payment->amount;
        $otherPayments = Payment::where('invoice_id', $payment->invoice_id)
            ->where('id', '!=', $payment->id)
            ->sum('amount');  
        $newTotalPayments = $otherPayments - $currentAmount + $newAmount;
        $newTotalPayments = $newTotalPayments / 100;

        // Vérification du montant total
        $invoiceCalculator = new InvoiceCalculator($payment->invoice);
        $amountDue = $invoiceCalculator->getAmountDue()->getBigDecimalAmount();

        if ($newTotalPayments > $amountDue) {
            return response()->json([
                'error' => 'Le montant total des paiements dépasse le montant dû',
                'amount_due' => $amountDue,
                'new_total_payments' => $newTotalPayments,
            ], 422);
        }

        /*  */

        $payment->update([
            'amount' => $newAmount,
        ]);

        app(GenerateInvoiceStatus::class, ['invoice' => $payment->invoice])->createStatus();

        return response()->json([
            'payment' => $payment->fresh(),
            'message' => 'Paiement mis à jour avec succès',
        ]);
    }


    /**
     * Retourne la somme totale des paiements par client
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalPaymentsByClient()
    {
        $totals = Payment::selectRaw("
            clients.id,
            clients.company_name,
            COALESCE(SUM(payments.amount), 0) as total_amount,
            COUNT(payments.id) as number_of_payments,
            ROUND((COALESCE(SUM(payments.amount), 0) / (SELECT SUM(amount) FROM payments)) * 100, 2) as percentage_of_total
        ")
        ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
        ->join('clients', 'invoices.client_id', '=', 'clients.id')
        ->groupBy('clients.id', 'clients.company_name')
        ->orderBy('total_amount', 'desc')
        ->get();

        return response()->json([
            'totals_by_client' => $totals,
            'total_amount' => Payment::sum('amount'),
        ]);
    }

    /**
     * Retourne les détails des paiements pour un client
     *
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentsByClient($clientId)
    {
        $payments = Payment::whereHas('invoice', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })
        ->with('invoice')
        ->orderBy('payment_date', 'desc')
        ->get();

        return response()->json([
            'payments' => $payments,
            'total_amount' => $payments->sum('amount'),
            'client_total' => Payment::whereHas('invoice', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->sum('amount'),
        ]);
    }

    /**
     * Retourne la somme totale des paiements par jour
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalPaymentsByDay()
    {
        $totals = Payment::selectRaw("
            DATE(payment_date) as payment_date,
            COALESCE(SUM(amount), 0) as total_amount,
            COUNT(*) as number_of_payments,
            ROUND((COALESCE(SUM(amount), 0) / (SELECT SUM(amount) FROM payments)) * 100, 2) as percentage_of_total
        ")
        ->groupBy('payment_date')
        ->orderBy('payment_date', 'desc')
        ->get();

        return response()->json([
            'totals_by_day' => $totals,
            'total_amount' => Payment::sum('amount'),
        ]);
    }

    /**
     * Retourne les détails des paiements pour une date spécifique
     *
     * @param string $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentsByDay($date)
    {
        $payments = Payment::whereDate('payment_date', $date)
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'payments' => $payments,
            'total_amount' => $payments->sum('amount'),
            'daily_total' => Payment::whereDate('payment_date', $date)->sum('amount'),
        ]);
    }
}







