<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el panel de control principal.
     * Ruta libre de middleware auth (por compatibilidad con el flujo nativo).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return view('dashboard', [
            'user' => $user,
        ]);
    }
}
