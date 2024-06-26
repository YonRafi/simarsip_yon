<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use App\Models\Operator;
use App\Models\Session;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OperatorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api',['except'=>['login', 'register']]);
    }

    public function showOperator()
    {
        $operators = Operator::with('session')
            ->select('ID_OPERATOR', 'NAMA_OPERATOR', 'EMAIL','ID_AKSES')
            ->get();

        $operatorData = $operators->map(function ($operator) {
            $status = $operator->session->isEmpty() ? 'Nonaktif' : $operator->session->first()->STATUS;

            return [
                'ID_OPERATOR' => $operator->ID_OPERATOR,
                'NAMA_OPERATOR' => $operator->NAMA_OPERATOR,
                'EMAIL' => $operator->EMAIL,
                'ID_AKSES' => $operator->ID_AKSES,
                'STATUS' => $status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data' => $operatorData,
        ], 200);
    }

    public function changeAkses(Request $request, $ID_OPERATOR)
    {
        $validated = $this->validate($request, [
            'ID_AKSES' => 'required|exists:hak_akses,ID_AKSES'
        ]);

        $operator = Operator::find($ID_OPERATOR);
        if (!$operator) {
            return response()->json(['error' => 'Operator tidak ditemukan'], 404);
        }

        $hakAkses = HakAkses::find($validated['ID_AKSES']);
        if (!$hakAkses) {
            return response()->json(['error' => 'Invalid ID_AKSES provided.'], 400);
        }

        $operator->ID_AKSES = $hakAkses->ID_AKSES;
        $operator->save();

        if ($operator) {
            return response()->json([
                'success' => true,
                'message' => 'Hak akses berhasil diperbarui',
                'data' => $operator
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui hak akses',
                'data' => null
            ], 400);
        }
    }


    public function deleteOperator(Request $request, $ID_OPERATOR)
    {
        try {
            $operator = Operator::findOrFail($ID_OPERATOR);

            $operator->delete();

            return response()->json(['message' => 'Operator berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus operator'], 500);
        }
    }

}
