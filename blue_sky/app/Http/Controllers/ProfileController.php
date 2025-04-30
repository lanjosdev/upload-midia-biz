<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $info;

    public function __construct(User $info)
    {
        $this->info = $info;
    }
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            $infoUserRequest = User::with([
                'region' => function ($query) {
                    $query->whereNull('deleted_at');
                },
            ])
                ->where('id', $user->id)
                ->first();

            $info = [
                'id' => $infoUserRequest->id,
                'name' => $infoUserRequest->name,
                'email' => $infoUserRequest->email,
                'uf' => $infoUserRequest->region->state_uf,
            ];

            return response()->json([
                'success' => true,
                'message' => 'sucesso',
                'data' => $info,
            ]);
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