<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * RF-3: Reservar cita. 
     * ELIMINADA la creación automática de pago aquí.
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
        ], [
            'scheduled_at.after' => 'La fecha de la cita debe ser posterior a la hora actual.',
            'scheduled_at.required' => 'La fecha y hora son obligatorias.',
            'description.required' => 'Debes proporcionar una descripción para el tatuaje.',
            'tattoo_artist_id.exists' => 'El tatuador seleccionado no es válido.'
        ]);

        // SOLO CREAMOS LA CITA. El pago se hará desde la pasarela de Stripe.
        $appointment = Appointment::create([
            'client_id' => $user->id,
            'tattoo_artist_id' => $request->tattoo_artist_id,
            'scheduled_at' => $request->scheduled_at,
            'description' => $request->description,
            'status' => 'pending', 
        ]);
        
        // BORRADO: Payment::create([...]) -> Esto causaba el duplicado sin stripe_id.

        return response()->json([
            'message' => 'Cita reservada. Procede al pago del depósito para que el tatuador pueda confirmarla.',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * index: Lista de citas. 
     * AÑADIDO: with('payments') para que el botón de pago desaparezca en Vue.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Cargamos siempre con la relación 'payments' para el control de la interfaz
        if ($user->role_id === 2) {
            $appointments = Appointment::where('tattoo_artist_id', $user->id)
                ->with(['client:id,name,email', 'payments']) // <-- IMPORTANTE
                ->orderBy('scheduled_at')
                ->get();
            $message = 'Agenda de citas cargada.';
        } else {
            $appointments = Appointment::where('client_id', $user->id)
                ->with(['tattooArtist:id,name', 'payments']) // <-- IMPORTANTE
                ->orderBy('scheduled_at')
                ->get();
            $message = 'Tus citas cargadas.';
        }

        return response()->json([
            'message' => $message,
            'appointments' => $appointments
        ]);
    }

    // ... Resto de funciones (confirmAppointment, update, cancelAppointment) se mantienen igual ...
    
    public function confirmAppointment(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        if ($user->role_id !== 2 || $appointment->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        if ($appointment->status === 'pending') {
            $appointment->status = 'approved';
            $appointment->save();
            return response()->json(['message' => 'Cita confirmada con éxito.', 'appointment' => $appointment]);
        }
        return response()->json(['message' => 'La cita ya fue confirmada o cancelada previamente.'], 400);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        if ($appointment->client_id !== $user->id && $appointment->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }
        if ($appointment->status === 'canceled') {
             return response()->json(['message' => 'No se puede modificar una cita cancelada.'], 400);
        }
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'description' => 'required|string|max:500',
        ], [
            'scheduled_at.after' => 'La fecha de la cita debe ser una fecha futura.',
            'scheduled_at.required' => 'La fecha es necesaria para reprogramar.',
            'description.required' => 'La descripción no puede estar vacía.'
        ]);
        $appointment->update($data);
        return response()->json(['message' => 'Cita actualizada con éxito.', 'appointment' => $appointment], 200);
    }

    public function cancelAppointment(Appointment $appointment)
    {
        $user = Auth::user();
        if ($appointment->client_id !== $user->id && $appointment->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }
        if ($appointment->status === 'canceled') {
            return response()->json(['message' => 'La cita ya está cancelada.'], 400);
        }
        $appointment->status = 'canceled';
        $appointment->save();
        return response()->json(['message' => 'Cita cancelada con éxito.'], 200);
    }

    public function getTattooArtists() {
        $artists = User::where('role_id', 2)->select('id', 'name')->get();
        return response()->json(['artists' => $artists]);
    }
}