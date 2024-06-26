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

class AuthController extends Controller
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

    public function register(Request $request)
    {
        $validated = $this->validate($request, [
            'NAMA_OPERATOR' => 'required|max:255',
            'EMAIL' => 'required|EMAIL|max:255|unique:operator,EMAIL',
            'PASSWORD' => 'required|min:6',
            'ID_AKSES' => 'required|exists:hak_akses,ID_AKSES'
        ]);

        $hakAkses = HakAkses::find($validated['ID_AKSES']);

        if (!$hakAkses) {
            return response()->json(['error' => 'Invalid ID_AKSES provided.'], 400);
        }

        $operator = new Operator();
        $operator->NAMA_OPERATOR = $validated['NAMA_OPERATOR'];
        $operator->EMAIL = $validated['EMAIL'];
        $operator->PASSWORD = Hash::make($validated['PASSWORD']);
        $operator->ID_AKSES = $hakAkses->ID_AKSES;
        $operator->save();
        if ($operator) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully Registered',
                'data' => $operator
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed',
                'data' => null
            ], 400);
        }
    }

    public function login(Request $request)
    {
        // Validasi input
        $validated = $this->validate($request, [
            'EMAIL' => 'required|EMAIL|exists:operator,EMAIL',
            'PASSWORD' => 'required'
        ]);

        // Ambil operator berdasarkan EMAIL
        $operator = Operator::where('EMAIL', $validated['EMAIL'])->first();

        // Periksa kecocokan PASSWORD
        if (!$operator || !Hash::check($validated['PASSWORD'], $operator->PASSWORD)) {
            return response()->json([
                'success' => false,
                'message' => 'EMAIL or PASSWORD incorrect',
            ], 401);
        }
        //delete session ketika melakukan relog
        Session::where('ID_OPERATOR', $operator->ID_OPERATOR)->delete();

        // Set waktu kedaluwarsa token (misalnya, 6 jam)
        $expirationTimeInSeconds =  60 * 60; // 6 jam dalam detik
        // Hitung waktu kedaluwarsa dalam detik sejak epoch
        $expirationTime = time() + $expirationTimeInSeconds;
        // Konversi durasi waktu kedaluwarsa menjadi format jam
        $expiresInMinutes = $expirationTimeInSeconds / 60;

        // Buat payload JWT
        $payload = [
            'iat' => time(),
            'exp' => $expirationTime,
            'uid' => $operator->ID_OPERATOR
        ];

        try {
            // Encode payload menjadi token JWT
            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            $nama_operator = Operator::where('ID_OPERATOR', $operator->ID_OPERATOR)->value('NAMA_OPERATOR');
            $ID_OPERATOR = Operator::where('ID_OPERATOR', $operator->ID_OPERATOR)->value('ID_OPERATOR');
            $ID_AKSES = Operator::where('ID_OPERATOR', $operator->ID_OPERATOR)->value('ID_AKSES');
                // Save token and user ID_OPERATOR to Session table
                $session = new Session();
                $session->JWT_TOKEN = $token;
                $session->STATUS = 'Aktif';
                $session->EXPIRED_AT = Carbon::now()->setTimezone('GMT+7')->addMinutes(60);
                $session->ID_OPERATOR = $operator->ID_OPERATOR; // Assuming this is the field name for the operator's ID
                $session->save();

            // Tanggapan sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Login',
                'nama_operator' => $nama_operator,
                'ID_OPERATOR' => $ID_OPERATOR,
                'ID_AKSES' => $ID_AKSES,
                'access_token' => $token,
                'token_expired' => 'Kadaluwarsa dalam '.$expiresInMinutes . ' Menit',
            ], 200);
        } catch (\Exception $e) {
            // Tanggapan jika gagal menghasilkan token
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
            ], 500);
        }
    }


}
