<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController {

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);

        $userData = $request->all();
        $userData['password'] = Hash::make($userData['password']);
        $user = User::create($userData);

        return $this->successResponse('Usuário registrado com sucesso', $user);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse('Login realizado com sucesso', [
                'token' => $token,
                'user' => $user,
            ]);
        }

        return $this->errorResponse('Credenciais inválidas', []);
    }

    public function logout(Request $request) {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado', []);
        }

        $user->tokens()->delete();

        return $this->successResponse('Logout realizado com sucesso');
    }
}
