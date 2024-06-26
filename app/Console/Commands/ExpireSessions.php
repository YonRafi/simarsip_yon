<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireSessions extends Command
{
    protected $signature = 'expire:sessions';
    protected $description = 'Expire inactive sessions';

    public function handle()
    {
        $sessions = Session::where('EXPIRED_AT', '<', Carbon::now()->setTimezone('GMT+7'))->get();

        foreach ($sessions as $session) {
            if ($session->STATUS == 'Aktif' && $session->EXPIRED_AT < Carbon::now()->setTimezone('GMT+7')) {
                DB::table('session')
                    ->where('ID_SESSION', $session->ID_SESSION)
                    ->update(['STATUS' => 'Nonaktif']);
            }
        }
    }
}
