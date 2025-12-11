<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * RF-13: Muestra el historial de pagos del usuario logueado.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role_id === 1) {
            // Cliente: solo ve sus pagos
            $payments = Payment::where('client_id', $user->id)
                ->with('appointment') // Cargamos información de la cita
                ->latest()
                ->get();
        } else {
            // Tatuador: ve todos los pagos que le hicieron por sus citas
            // Filtramos pagos asociados a citas donde el tatuador es el receptor
            $payments = Payment::whereHas('appointment', function ($query) use ($user) {
                $query->where('tattoo_artist_id', $user->id);
            })
            ->with('appointment', 'client:id,name') // Carga info de la cita y del cliente
            ->latest()
            ->get();
        }

        return response()->json(['payments' => $payments]);
    }
}