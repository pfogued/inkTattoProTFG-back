<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DesignController extends Controller
{
    /**
     * RF-9: Mostrar diseños filtrados por privacidad.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Si no hay usuario autenticado, solo mostramos diseños públicos.
        if (!$user) {
             $designs = Design::where('is_private', false)
                ->with('tattooArtist:id,name,email')
                ->latest()
                ->get();
            return response()->json(['designs' => $designs]);
        }

        $designs = Design::query()
            ->with('tattooArtist:id,name,email')
            ->where(function ($query) use ($user) {
                // Condición 1: Mostrar todos los diseños públicos
                $query->where('is_private', false);
                
                // Condición 2: El Tatuador ve todos sus diseños (públicos y privados)
                if ($user->role_id === 2) { 
                    $query->orWhere('tattoo_artist_id', $user->id);
                }

                // Condición 3: El Cliente ve los diseños privados dirigidos a él
                if ($user->role_id === 1) { 
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('is_private', true)
                          ->where('client_id', $user->id);
                    });
                }
            })
            ->latest()
            ->get();

        return response()->json(['designs' => $designs]);
    }

    /**
     * RF-8: Permite a un Tatuador (role_id=2) subir un nuevo diseño a su portafolio.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'image_url' => 'required|url|max:2048', 
            'description' => 'nullable|string',
            'style' => ['required', Rule::in(['traditional', 'watercolor', 'blackwork', 'geometric', 'other'])],
            'is_private' => 'sometimes|boolean',
            'client_id' => 'nullable|exists:users,id',
        ]);

        $design = Design::create([
            'tattoo_artist_id' => $user->id,
            'client_id' => $request->client_id, 
            'is_private' => $request->is_private ?? false,
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
     * Eliminar un diseño (Solo el Tatuador que lo subió puede hacerlo).
     */
    public function destroy(Design $design)
    {
        $user = Auth::user();

        if (!$user || $user->role_id !== 2) {
            return response()->json(['message' => 'Acceso denegado.'], 403);
        }

        if ($design->tattoo_artist_id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para eliminar este diseño.'], 403);
        }

        $design->delete();
        return response()->json(['message' => 'Diseño eliminado con éxito.'], 200);
    }

    /**
     * RF-10: Permite al Cliente guardar su anotación/comentario.
     */
    public function updateAnnotation(Request $request, Design $design)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado. Inicie sesión.'], 401);
        }
        
        // El tatuador puede ver la anotación, pero solo el cliente puede editarla.
        if ($user->role_id === 1 && $design->client_id !== $user->id) {
            return response()->json(['message' => 'Solo el cliente asociado puede anotar este diseño.'], 403);
        }
        
        $request->validate([
            'client_annotation' => 'nullable|string|max:1000',
        ]);

        $design->client_annotation = $request->client_annotation;
        $design->save();

        return response()->json([
            'message' => 'Anotación guardada con éxito.',
            'design' => $design // Devolvemos el diseño actualizado
        ], 200);
    }
}