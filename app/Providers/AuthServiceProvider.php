<?php

namespace App\Providers;

use App\Models\Operator;
use App\Models\Session;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Providers\stdClass;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $auth = $request->header('Authorization');

            // Log the Authorization header
            error_log('Authorization Header: ' . print_r($auth, true));

            if (empty($auth)) {
                error_log('Authorization header is empty');
                return null;
            }

            $authParts = explode(' ', $auth);
            if (count($authParts) != 2 || $authParts[0] != 'Bearer') {
                error_log('Authorization header is not in the expected format');
                return null;
            }

            $token = $authParts[1];
            if (empty($token)) {
                error_log('Token is empty');
                return null;
            }

            // Log the JWT_SECRET value to ensure it's being read correctly
            $jwtSecret = env('JWT_SECRET');
            error_log('JWT_SECRET: ' . $jwtSecret);

            if (empty($jwtSecret)) {
                error_log('JWT_SECRET is empty');
                return null;
            }

            try {
                // Use the correct decoding method
                $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
                error_log('Decoded JWT: ' . print_r($decoded, true));

                // Check if token is expired
                if ($decoded->exp < time()) {
                    error_log('Token is expired');
                    return null;
                }

                // Cek apakah token ada di tabel Session
                $session = Session::where('jwt_token', $token)->first();
                if (!$session) {
                    error_log('Token tidak ditemukan di tabel Session');
                    return null;
                }

                // Ambil Operator berdasarkan ID dari payload JWT
                $operator = Operator::find($decoded->uid);

                if ($operator) {
                    error_log('Operator found: ' . $operator->ID_OPERATOR);
                } else {
                    error_log('Operator not found');
                }
                return $operator;

            } catch (\Throwable $th) {
                // Log the error
                error_log('JWT decoding error: ' . $th->getMessage());
                return null;
            }
        });
    }
}
