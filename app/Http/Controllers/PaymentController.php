<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    /**
     * RF-13: Muestra el historial de pagos del usuario logueado.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role_id === 1) { // CLIENTE
            $payments = Payment::where('client_id', $user->id)
                ->with(['appointment.tattooArtist']) 
                ->latest()
                ->get();
        } else { // TATUADOR (role_id === 2)
            $payments = Payment::whereHas('appointment', function ($query) use ($user) {
                $query->where('tattoo_artist_id', $user->id);
            })
            ->with(['appointment', 'client']) 
            ->latest()
            ->get();
        }

        return response()->json(['payments' => $payments]);
    }

    /**
     * Paso 1: Crea un Payment Intent en Stripe.
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'appointment_id' => 'required|exists:appointments,id'
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET')); 

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Céntimos
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'appointment_id' => $request->appointment_id,
                    'client_id' => Auth::id()
                ]
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Paso 2: Guarda el pago evitando duplicados y mantiene la cita pendiente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount'         => 'required|numeric',
            'stripe_id'      => 'required|string',
            'status'         => 'required|string',
        ]);

        try {
            // 1. COMPROBACIÓN DE DUPLICADOS: Si el stripe_id ya existe, no creamos otro.
            $existingPayment = Payment::where('stripe_id', $request->stripe_id)->first();
            
            if ($existingPayment) {
                return response()->json([
                    'message' => 'El pago ya estaba registrado.',
                    'payment' => $existingPayment
                ], 200);
            }

            // 2. CREACIÓN DEL PAGO
            $payment = Payment::create([
                'client_id'      => Auth::id(),
                'appointment_id' => $request->appointment_id,
                'amount'         => $request->amount,
                'stripe_id'      => $request->stripe_id,
                'status'         => $request->status, 
                'type'           => 'deposit',
            ]);

            // 3. ACTUALIZACIÓN DE LA CITA: Se queda en 'pending' para que el artista confirme.
            $appointment = Appointment::find($request->appointment_id);
            $appointment->update(['status' => 'pending']);

            return response()->json([
                'message' => 'Pago registrado correctamente. Cita pendiente de validación.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}