<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Escribe tu correo.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        if (!Auth::attempt($credenciales, $request->boolean('recordar'))) {
            return back()->withInput($request->only('email'))
                         ->withErrors(['email' => 'El correo o la contraseña son incorrectos.']);
        }

        $request->session()->regenerate();
        AuditoriaService::registrar('users', Auth::id(), 'inicio_sesion', null, null, 'Inicio de sesión');

        return redirect()->intended('/idse');
    }

    public function logout(Request $request)
    {
        AuditoriaService::registrar('users', Auth::id(), 'cierre_sesion', null, null, 'Cierre de sesión');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
