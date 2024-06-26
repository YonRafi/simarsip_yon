<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\HistoryPelayanan;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\InfoArsipPengangkatan;
use App\Models\InfoArsipKelahiran;
use App\Models\InfoArsipKematian;
use App\Models\InfoArsipKtp;
use App\Models\InfoArsipPengakuan;
use App\Models\InfoArsipPengesahan;
use App\Models\InfoArsipPerkawinan;
use App\Models\InfoArsipSkot;
use App\Models\InfoArsipSktt;
use App\Models\InfoArsipKk;
use App\Models\InfoArsipPerceraian;
use App\Models\InfoArsipSuratPindah;
use App\Models\JenisDokumen;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function index ()
    {
        try {
            // Menghitung total data dalam tabel arsip
            $totalArsip = Arsip::count();

            // Menghitung total data dalam tabel permission untuk hari ini
            $totalRequestToday = Permission::whereDate('created_at', Carbon::today())->count();

            // Menghitung total user login
            $totalLoginToady = Session::whereDate('created_at', Carbon::today())->count();

            // Menghitung total data dalam tabel permission
            $totalRequest = Permission::count();
            // Menghitung total data setiap tabel info arsip
            $totalPengangkatan = InfoArsipPengangkatan::count();
            $totalPerceraian = InfoArsipPerceraian::count();
            $totalPengesahan = InfoArsipPengesahan::count();
            $totalKematian = InfoArsipKematian::count();
            $totalKelahiran = InfoArsipKelahiran::count();
            $totalPengakuan = InfoArsipPengakuan::count();
            $totalPerkawinan = InfoArsipPerkawinan::count();
            $totalKk = InfoArsipKk::count();
            $totalSkot = InfoArsipSkot::count();
            $totalSktt = InfoArsipSktt::count();
            $totalKtp = InfoArsipKtp::count();
            $totalSuratPindah = InfoArsipSuratPindah::count();

            // Jika berhasil menghitung, kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menampilkan Dashboard',
                'total_data_arsip' => $totalArsip,
                'total_user_online' => $totalLoginToady,
                'total_request_today' => $totalRequestToday,
                'total_all_request' => $totalRequest,
                'arsip_capil' => [
                    'arsip_percecraian'=>$totalPerceraian,
                    'arsip_pengesahan'=>$totalPengesahan,
                    'arsip_kematian'=>$totalKematian,
                    'arsip_kelahiran'=>$totalKelahiran,
                    'arsip_pengakuan'=>$totalPengakuan,
                    'arsip_perkawinan'=>$totalPerkawinan,
                    'arsip_pengangkatan'=>$totalPengangkatan
                ],
                'arsip_dafduk' => [
                    'arsip_suratpindah'=>$totalSuratPindah,
                    'arsip_kk'=>$totalKk,
                    'arsip_skot'=>$totalSkot,
                    'arsip_sktt'=>$totalSktt,
                    'arsip_ktp'=>$totalKtp
                ]
            ], 200);
        } catch (\Exception $e) {
            // Jika ada kesalahan, kembalikan response gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menampilkan Data',
                'data' => '',
            ], 500);
        }
    }

    public function showUser()
    {
        // Memeriksa apakah pengguna memiliki token JWT yang valid
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => []
            ], 401);
        }

        $user = Auth::user();
        $id_operator = $user->ID_OPERATOR;
        $nama_operator = $user->NAMA_OPERATOR;
        $email = $user->EMAIL;
        $id_akses = $user->ID_AKSES;

        $sessionStatus = Session::where('ID_OPERATOR', $id_operator)->pluck('STATUS')->first();

        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data' => [
                'ID_OPERATOR' => $id_operator,
                'NAMA_OPERATOR' => $nama_operator,
                'EMAIL' => $email,
                'ID_AKSES' => $id_akses,
                'SESSION_STATUS' => $sessionStatus // Menambahkan status sesuai dengan session
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $session = Session::where('JWT_TOKEN', $token)->first();
        if (!$session) {
            return response()->json(['message' => 'Token not found'], 404);
        }
        // Mendapatkan objek Operator terkait dengan Session
        $operator = $session->operator()->first();
        $nama_operator = $operator->NAMA_OPERATOR;

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Logout',
            'nama_operator' => $nama_operator,
        ], 200);
    }

}

