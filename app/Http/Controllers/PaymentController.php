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
        // Buscamos pagos donde la cita pertenezca a este tatuador
        $payments = Payment::whereHas('appointment', function ($query) use ($user) {
            $query->where('tattoo_artist_id', $user->id);
        })
        ->with(['appointment', 'client']) // Cargamos quién pagó y qué cita es
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

        // Usamos la clave del .env o la que proporciones
        Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51SjM6dFEByp3k6AXxY9v...')); // Pon aquí tu clave completa si no usas .env

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
     * Paso 2: RF-13 - Guarda el pago en la base de datos tras el éxito en Stripe.
     * Esta es la función que te faltaba y causaba el error 500.
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
            $payment = Payment::create([
                'client_id'      => Auth::id(),
                'appointment_id' => $request->appointment_id,
                'amount'         => $request->amount,
                'stripe_id'      => $request->stripe_id,
                'status'         => $request->status, // 'completed'
                'type'           => 'deposit',    // Identificador para tu historial
            ]);

             $appointment = Appointment::find($request->appointment_id);
             $appointment->update(['status' => 'approved']);

            return response()->json([
                'message' => 'Pago registrado correctamente en la base de datos.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al guardar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}