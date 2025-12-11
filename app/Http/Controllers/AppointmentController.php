<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment; // <-- NUEVA IMPORTACIÓN: Modelo de Pago
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * Obtiene la lista de todos los Tatuadores (role_id = 2) disponibles.
     */
    public function getTattooArtists()
    {
        $artists = User::where('role_id', 2)
                        ->select('id', 'name')
                        ->get();

        return response()->json(['artists' => $artists]);
    }

    /**
     * Obtiene la lista única de Clientes que han reservado citas con el Tatuador (para Design Modal).
     */
    public function getAssociatedClients()
    {
        $user = Auth::user();
        
        if (!$user || $user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        $clientIds = Appointment::where('tattoo_artist_id', $user->id)
                                ->distinct('client_id')
                                ->pluck('client_id');
        
        $clients = User::whereIn('id', $clientIds)
                       ->select('id', 'name')
                       ->get();

        return response()->json(['clients' => $clients]);
    }

    /**
     * RF-3: Permite a un Cliente (role_id=1) reservar una cita con un Tatuador y pagar el depósito.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role_id !== 1) {
            return response()->json(['message' => 'Solo los Clientes pueden reservar citas.'], 403);
        }

        $request->validate([
            'tattoo_artist_id' => [
                'required', 
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('role_id', 2);
                }),
            ],
            'scheduled_at' => 'required|date|after:now',
            'description' => 'required|string|max:500',
        ]);

        // 1. SIMULACIÓN DE PROCESO DE PAGO DE DEPÓSITO (50€)
        $paymentSuccess = true; // SIMULACIÓN: Asumimos que Stripe/PayPal devolvió éxito
        $depositAmount = 50.00;
        
        if (!$paymentSuccess) {
             return response()->json(['message' => 'Error al procesar el depósito de 50€.'], 400);
        }

        // 2. Creación de la cita
        $appointment = Appointment::create([
            'client_id' => $user->id,
            'tattoo_artist_id' => $request->tattoo_artist_id,
            'scheduled_at' => $request->scheduled_at,
            'description' => $request->description,
            'status' => 'pending', 
        ]);
        
        // 3. REGISTRO DE LA TRANSACCIÓN (RF-13)
        Payment::create([
            'client_id' => $user->id,
            'appointment_id' => $appointment->id,
            'amount' => $depositAmount,
            'type' => 'deposit',
            'status' => 'completed',
        ]);

        // 4. Devolver confirmación
        return response()->json([
            'message' => 'Cita reservada y depósito pagado con éxito. Pendiente de confirmación del Tatuador.',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * RF-5: Muestra la agenda del Tatuador o las citas del Cliente.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role_id === 2) {
            // Tatuador: Ver todas las citas dirigidas a él.
            $appointments = Appointment::where('tattoo_artist_id', $user->id)
                ->with('client:id,name,email') 
                ->orderBy('scheduled_at')
                ->get();

            $message = 'Agenda de citas cargada.';

        } else {
            // Cliente: Ver solo sus propias citas.
            $appointments = Appointment::where('client_id', $user->id)
                ->with('tattooArtist:id,name') 
                ->orderBy('scheduled_at')
                ->get();
            
            $message = 'Tus citas cargadas.';
        }

        return response()->json([
            'message' => $message,
            'appointments' => $appointments
        ]);
    }
    
    /**
     * RF-6: Permite al Tatuador confirmar una cita pendiente.
     */
    public function confirmAppointment(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        
        if ($user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        if ($appointment->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para confirmar esta cita.'], 403);
        }

        if ($appointment->status === 'pending') {
            $appointment->status = 'approved';
            $appointment->save();

            return response()->json([
                'message' => 'Cita confirmada con éxito.',
                'appointment' => $appointment
            ]);
        }
        
        return response()->json(['message' => 'La cita ya fue confirmada o cancelada previamente.'], 400);
    }
}