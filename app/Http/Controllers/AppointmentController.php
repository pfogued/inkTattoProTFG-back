<?php

namespace App\Http\Controllers;

use App\Models\Appointment; 
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

class AppointmentController extends Controller
{
    /**
     * RF-5: Valida y crea una nueva cita (Reserva).
     */
    public function store(Request $request)
    {
        $client_id = Auth::id(); 
        
        $request->validate([
            'tattoo_artist_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'duration_minutes' => 'integer|min:30', 
            'details' => 'required|string|min:20',
        ]);
        
        $date = $request->input('appointment_date');
        $time = $request->input('appointment_time');
        $artistId = $request->input('tattoo_artist_id');

        // RF-6: Verificar disponibilidad
        if (!$this->checkAvailability($artistId, $date, $time)) {
            throw ValidationException::withMessages([
                'schedule' => ['El tatuador no está disponible en la hora seleccionada. Conflicto de cita.'],
            ]);
        }
        
        $appointment = Appointment::create([
            'user_id' => $client_id,
            'tattoo_artist_id' => $artistId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'duration_minutes' => $request->input('duration_minutes', 60),
            'details' => $request->details,
            'status' => 'pending', // Siempre inicia como pendiente de pago
        ]);

        return response()->json([
            'message' => 'Cita creada como pendiente de pago.',
            'appointment_id' => $appointment->id,
        ], 201);
    }
    
    /**
     * RF-6: Verifica si hay citas solapadas para el tatuador en esa hora.
     */
    private function checkAvailability($artistId, $date, $time)
    {
        $overlappingAppointment = Appointment::where('tattoo_artist_id', $artistId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_time', $time) 
            ->exists();

        return !$overlappingAppointment;
    }

    /**
     * RF-8: Obtiene la agenda.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id === 2) {
            // Tatuador (Rol 2): Ver todas las citas asignadas a él.
            $appointments = Appointment::where('tattoo_artist_id', $user->id)
                ->with(['user', 'tattooArtist'])
                ->get();
        } else {
            // Cliente (Rol 1): Ver solo sus citas.
            $appointments = Appointment::where('user_id', $user->id)
                ->with(['user', 'tattooArtist'])
                ->get();
        }

        return response()->json($appointments);
    }

    /**
     * CU-12: Tatuador confirma una cita pendiente.
     */
    public function confirmAppointment(Appointment $appointment)
    {
        if (Auth::id() !== $appointment->tattoo_artist_id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ($appointment->status === 'pending') {
            $appointment->status = 'confirmed';
            $appointment->save();
            return response()->json(['message' => 'Cita confirmada con éxito.']);
        }

        return response()->json(['message' => 'La cita no está pendiente de confirmación.'], 400);
    }
}