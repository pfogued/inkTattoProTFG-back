<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignController extends Controller
{
    /**
     * RF-8: Permite a un Tatuador (role_id=2) subir un nuevo diseño a su portafolio.
     */
    public function store(Request $request)
    {
        // 1. Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado. Inicie sesión.'], 401);
        }

        $user = Auth::user();

        // 2. RF-8: Restringir a Tatuadores (role_id = 2)
        if ($user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado. Solo Tatuadores pueden subir diseños.'], 403);
        }

        // 3. Validación de campos (RF-14)
        $request->validate([
            'title' => 'required|string|max:255',
            // En una aplicación real, aquí se manejaría la subida de archivos, pero usaremos URL simple.
            'image_url' => 'required|url|max:2048', 
            'description' => 'nullable|string',
            'style' => 'required|in:traditional,watercolor,blackwork,geometric,other',
        ]);

        // 4. Creación del diseño
        $design = Design::create([
            'tattoo_artist_id' => $user->id,
            'title' => $request->title,
            'image_url' => $request->image_url,
            'description' => $request->description,
            'style' => $request->style,
        ]);

        return response()->json([
            'message' => 'Diseño subido con éxito.',
            'design' => $design
        ], 201);
    }

    /**
     * RF-9: Mostrar todos los diseños del portafolio (para el Tatuador o para todos los clientes).
     */
    public function index()
    {
        // Si no se requiere filtrar por usuario, se muestran todos los diseños públicos.
        $designs = Design::with('tattooArtist:id,name')->latest()->get();

        return response()->json([
            'designs' => $designs
        ]);
    }
    
    // NOTA: Los métodos show, update y destroy pueden implementarse más adelante si es necesario.
}