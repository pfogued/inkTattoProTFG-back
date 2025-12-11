<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Design; // Asegúrate de que esta importación esté presente si la usaste
use App\Models\User; // <-- IMPORTACIÓN CRÍTICA
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
        
        // Restricción: Solo Tatuadores (role_id=2) pueden obtener esta lista
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
     * RF-3: Permite a un Cliente (role_id=1) reservar una cita con un Tatuador.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. Verificar si el usuario es un Cliente
        if ($user->role_id !== 1) {
            return response()->json(['message' => 'Solo los Clientes pueden reservar citas.'], 403);
        }

        // 2. Validación
        $request->validate([
            'tattoo_artist_id' => [
                'required', 
                'exists:users,id',
                // Asegurar que el ID proporcionado sea realmente un Tatuador (role_id=2)
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('role_id', 2);
                }),
            ],
            'scheduled_at' => 'required|date|after:now',
            'description' => 'required|string|max:500',
        ]);

        // 3. SIMULACIÓN DE PROCESO DE PAGO DE DEPÓSITO (50€)
        $paymentSuccess = true; 
        
        if (!$paymentSuccess) {
             return response()->json(['message' => 'Error al procesar el depósito de 50€.'], 400);
        }

        // 4. Creación de la cita
        $appointment = Appointment::create([
            'client_id' => $user->id,
            'tattoo_artist_id' => $request->tattoo_artist_id,
            'scheduled_at' => $request->scheduled_at,
            'description' => $request->description,
            'status' => 'pending', 
        ]);

        // 5. Devolver confirmación
        return response()->json([
            'message' => 'Cita reservada con éxito. Pendiente de confirmación del Tatuador.',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * RF-5: Muestra la agenda del Tatuador o las citas del Cliente.
     */
    public function index(Request $request)
    {
        // ... (Tu función index aquí) ...
        $user = Auth::user();
        
        if ($user->role_id === 2) {
            // Tatuador: Ver todas las citas dirigidas a él.
            $appointments = Appointment::where('tattoo_artist_id', $user->id)
                ->with('client:id,name,email') // Mostrar info del cliente
                ->orderBy('scheduled_at')
                ->get();

            $message = 'Agenda de citas cargada.';

        } else {
            // Cliente: Ver solo sus propias citas.
            $appointments = Appointment::where('client_id', $user->id)
                ->with('tattooArtist:id,name') // Mostrar info del tatuador
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
        // ... (Tu función confirmAppointment aquí) ...
        $user = Auth::user();
        
        // 1. Restricción: Solo Tatuadores pueden confirmar.
        if ($user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado. Solo Tatuadores pueden confirmar citas.'], 403);
        }

        // 2. Restricción: Solo el Tatuador asignado puede confirmar.
        if ($appointment->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para confirmar esta cita.'], 403);
        }

        // 3. Confirmación: Si el estado es 'pending', lo cambiamos a 'approved'.
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