<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Demo: accept any credentials and mark session as admin for demo purposes.
        session(['is_admin' => true, 'admin_name' => 'Administrador Demo']);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['is_admin', 'admin_name']);
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        // Datos falsos de ejemplo
        $users = [
            ['id' => 1, 'name' => 'María Pérez', 'email' => 'maria@example.com', 'role' => 'Usuario'],
            ['id' => 2, 'name' => 'José Gómez', 'email' => 'jose@example.com', 'role' => 'Moderador'],
            ['id' => 3, 'name' => 'Ana Ruiz', 'email' => 'ana@example.com', 'role' => 'Usuario'],
        ];

        $ads = [
            ['id' => 101, 'title' => 'Vendo bici de montaña', 'user' => 'María Pérez', 'status' => 'Publicado'],
            ['id' => 102, 'title' => 'Se alquila departamento', 'user' => 'José Gómez', 'status' => 'Pendiente'],
            ['id' => 103, 'title' => 'Curso de guitarra', 'user' => 'Ana Ruiz', 'status' => 'Publicado'],
        ];

        $transactions = [
            ['id' => 'T-9001', 'user' => 'María Pérez', 'amount' => 'S/ 120.00', 'status' => 'Completada'],
            ['id' => 'T-9002', 'user' => 'José Gómez', 'amount' => 'S/ 45.50', 'status' => 'Reembolsada'],
            ['id' => 'T-9003', 'user' => 'Ana Ruiz', 'amount' => 'S/ 300.00', 'status' => 'Pendiente'],
        ];

        return view('admin.dashboard', compact('users', 'ads', 'transactions'));
    }
}
