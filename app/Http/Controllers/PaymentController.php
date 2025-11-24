<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Barryvdh\DomPDF\Facade\Pdf; // PDF Facade
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // 1. Create Intent
    public function createPaymentIntent(Request $request)
    {
        try {
            $secret = env('STRIPE_SECRET');
            if(!$secret) return response()->json(['error' => 'Stripe Secret Missing'], 500);

            Stripe::setApiKey($secret);

            $submission = Submission::findOrFail($request->submission_id);

            // Minimum amount check for Stripe (approx ₹50)
            if($submission->form->price < 50) {
                 return response()->json(['error' => 'Amount too low for online payment (Min ₹50)'], 400);
            }

            $intent = PaymentIntent::create([
                'amount' => $submission->form->price * 100, // paise
                'currency' => 'inr',
                'metadata' => ['submission_id' => $submission->id],
                'description' => 'Exam Fee: ' . $submission->form->title,
                'shipping' => [
                    'name' => 'Student',
                    'address' => ['line1'=>'India','postal_code'=>'000000','city'=>'India','state'=>'DL','country'=>'IN'],
                ],
            ]);

            return response()->json(['clientSecret' => $intent->client_secret]);

        } catch (\Exception $e) {
            Log::error("Stripe Intent Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Confirm & Generate PDF
    public function confirmPayment(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $payment_id = $request->payment_id;
            $submission_id = $request->submission_id;

            $submission = Submission::findOrFail($submission_id);

            if($submission->status === 'paid') {
                return response()->json(['message' => 'Already Paid']);
            }

            $submission->update(['status' => 'paid']);

            $payment = Payment::create([
                'submission_id' => $submission_id,
                'user_id' => auth()->id(),
                'amount' => $submission->form->price,
                'payment_id' => $payment_id,
                'status' => 'success'
            ]);

            // PDF Logic
            if (!file_exists(storage_path('app/receipts'))) {
                mkdir(storage_path('app/receipts'), 0777, true);
            }

            $pdf = Pdf::loadView('receipt', [
                'payment' => $payment,
                'submission' => $submission
            ]);

            $path = 'receipts/' . $payment->id . '.pdf';
            $pdf->save(storage_path('app/' . $path));

            $payment->update(['pdf_path' => $path]);

            return response()->json(['message' => 'Payment successful']);

        } catch (\Exception $e) {
            Log::error("Confirm Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Download Receipt (YEH FUNCTION MISSING THA)
    public function downloadReceipt($id)
    {
        $payment = Payment::findOrFail($id);
        
        $filePath = storage_path('app/' . $payment->pdf_path);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Receipt file not found'], 404);
        }

        return response()->download($filePath);
    }

    public function markFailed(Request $request)
    {
        $submission = Submission::findOrFail($request->submission_id);
        // Agar status already paid nahi hai, tabhi fail mark karo
        if($submission->status !== 'paid') {
            $submission->update(['status' => 'payment_failed']);
        }
        return response()->json(['message' => 'Marked as failed']);
    }
}