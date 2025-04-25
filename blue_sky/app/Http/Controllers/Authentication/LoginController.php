<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(Request $request)
    {
        try {

            //verifica se existe um user com o email informado
            $user = User::where(DB::raw('BINARY `email`'), $request->email)
                ->first();

            //se user não for encontrado ou senha errada
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'E-mail ou senha inválidos.'
                ]);
            }

            $token = $user;

            //apaga o ultimo token ativo
            $token->tokens()->delete();

            $token = null;

            //cria o token
            $token = $user->createToken('UserToken')->plainTextToken;


            //pega apenas o hash
            $tokenFormatted = explode('|', $token)[1];

            if ($tokenFormatted) {

                return response()->json([
                    'success' => true,
                    'message' => 'Login realizado com sucesso.',
                    'data' => $tokenFormatted,
                ]);
            }
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}