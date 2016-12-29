<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Libraries\XMPPHP\XMPPHP_XMPP;
use App\Kuota;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->call(function () {
            $query = "hh.id";
            $xmpp['result'] = "";
            $conn = new XMPPHP_XMPP('fujabber.com', 5222, 'statinject', 'j4nzky94','home');
            $conn->useEncryption(false);
            $conn->connect();
            $payloads = $conn->processUntil('session_start');
            $conn->presence($status='Cheese!');
            $payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'));
            $conn->message('Ziecenter01@fujabber.com', $query);
            $payloads = $conn->processUntil('message');
            $payloads = $conn->processUntil('message');
            $xmpp['result'] .= $payloads[0][1]['body'];
            while (true) {
                if(preg_match("/(\d{1,3}\.\d{1,3}(..|..\[K\]|..\[G\]))$/i", $payloads[0][1]['body'])){
                    $conn->disconnect();
                    $xmpp['query'] = $query;
                    XMPPQuery::Create($xmpp);
                    preg_match_all("/(sd|id|idc|idp|xd|xdcx|xdcxp|xdp|xdx|tk|v|axd|blk)\w{1,5}/i", $xmpp['result'], $kode);
                    foreach ($kode[0] as $key => $kod) {
                        if(preg_match("/".$kod."(?=\=\d{1,3}\.\d{1,3}..(\[K\]|\[G\]))/i", $xmpp['result'])){
                            Kuota::where([['kode', $kod]])->update(['isAvailable'=>0]);
                        } else{
                            Kuota::where([['kode', $kod]])->update(['isAvailable'=>1]);
                        }
                    }
                    return;
                } else{
                    $payloads = $conn->processUntil('message');
                    $xmpp['result'] .= $payloads[0][1]['body'];
                }
            }
        })->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
