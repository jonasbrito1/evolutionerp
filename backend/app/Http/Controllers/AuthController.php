<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login - Simples e Funcional
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email não encontrado',
                    'error' => true,
                ], 401);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Senha incorreta',
                    'error' => true,
                ], 401);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'message' => 'Usuário inativo',
                    'error' => true,
                ], 401);
            }

            // Gerar token usando Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Atualizar último login
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'error' => false,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : 'admin',
                    'company_id' => $user->company_id,
                    'avatar' => $user->avatar,
                    'status' => $user->status,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'error' => true,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao fazer login: ' . $e->getMessage(),
                'error' => true,
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            // Revogar token do Sanctum
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout realizado com sucesso',
                'error' => false,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao fazer logout',
                'error' => true,
            ], 500);
        }
    }

    /**
     * Get dados do usuário autenticado
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Token inválido ou expirado',
                    'error' => true,
                ], 401);
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : 'admin',
                'company_id' => $user->company_id,
                'avatar' => $user->avatar,
                'status' => $user->status,
                'department' => $user->department,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Me error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao buscar dados',
                'error' => true,
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            // Revogar token antigo
            $request->user()->currentAccessToken()->delete();

            // Criar novo token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Token atualizado com sucesso',
                'error' => false,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar token',
                'error' => true,
            ], 500);
        }
    }
}
