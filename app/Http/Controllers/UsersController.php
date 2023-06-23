<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    private $users;
    public function __construct(\App\Models\User $users)
    {
        $this->users = $users;
        $this->middleware('auth:sanctum', ['except' => ['LogIn']]);
    }

    public function LogIn(Request $request)
    {
        $validator = validator($request->only('email', 'password'), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails())
            return response()->json([
                'message' => 'Hubo un error de validación. ' . $validator->errors()->first(),
            ], 422);

        try {

            $user = $this->users->where('email', $request->email)->first();

            if (!$user || !\Hash::check($request->password, $user->password))
                return response()->json([
                    'message' => 'Credenciales incorrectas.'
                ], 401);

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hubo un error al iniciar sesión. ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function LogOut()
    {
        try {
            $user = auth()->user();

            if (!$user)
                return response()->json([
                    'message' => 'No se encontró el usuario.'
                ], 404);

            $user->tokens()->delete();

            return response()->json([
                'message' => 'Sesión cerrada correctamente.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hubo un error al cerrar sesión. ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}