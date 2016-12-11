<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserTryOut;
use App\Mathstat;
use App\Engstat;
use App\Http\Controllers\Controller;
use DB;

class UserController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function index(Request $request)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        if($email==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('login');
        };

        //AMBIL SEMUA DATA USER
        $user = User::where('email', $email)->get();

        //AMBIL SEMUA CATATAN SOAL SESUAI TO DAN SUBJECT
        $maxBagianMath = UserTryOut::where([['userId', $user[0]['userId']], ['tryOutId', 3], ['status', 1]])
                                            ->where('bagian', DB::table('usertryout')->where([['userId', $user[0]['userId']], ['tryOutId', 3]])->max('bagian'))->get();
        $maxBagianEng = UserTryOut::where([['userId', $user[0]['userId']], ['tryOutId', 4], ['status', 1]])
                                            ->where('bagian', DB::table('usertryout')->where([['userId', $user[0]['userId']], ['tryOutId', 4]])->max('bagian'))->get();

        //PENGAMBILAN DATA MTK & BINGG UTK GRAFIK
        $math = null;
        $eng = null;
        $mathPoin = null;
        $engPoin = null;
        if($maxBagianMath!="[]"){ //JIKA SDH ADA TO
            $math = Mathstat::where([['userId', $user[0]['userId']], ['tryOutId', 3], ['bagian','<=',$maxBagianMath[0]['bagian']]])->get();
            for ($i=0; $i < $maxBagianMath[0]['bagian']; $i++) {
                $mathPoin[$i]['true']=$math->where('result', 1)->where('bagian', $i+1)->count('result');
                $mathPoin[$i]['false']=$math->where('result', 0)->where('bagian', $i+1)->count('result');
                $mathPoin[$i]['ngarang']=$math->where('result', 3)->where('bagian', $i+1)->count('result');
                $mathPoin[$i]['nyerah']=$math->where('result', 2)->where('bagian', $i+1)->count('result');
                $mathPoin[$i]['totalScore']=2*$mathPoin[$i]['true']-$mathPoin[$i]['false']-$mathPoin[$i]['ngarang'];
            }
        }

        if($maxBagianEng!="[]"){ //JIKA SDH ADA TO
            $eng = Engstat::where([['userId', $user[0]['userId']], ['tryOutId', 4], ['bagian','<=',$maxBagianEng[0]['bagian']]])->get();
            for ($i=0; $i < $maxBagianEng[0]['bagian']; $i++) {
                $engPoin[$i]['true']=$eng->where('result', 1)->where('bagian', $i+1)->count('result');
                $engPoin[$i]['false']=$eng->where('result', 0)->where('bagian', $i+1)->count('result');
                $engPoin[$i]['ngarang']=$eng->where('result', 3)->where('bagian', $i+1)->count('result');
                $engPoin[$i]['nyerah']=$eng->where('result', 2)->where('bagian', $i+1)->count('result');
                $engPoin[$i]['totalScore']=2*$engPoin[$i]['true']-$engPoin[$i]['false']-$engPoin[$i]['ngarang'];
            }
        }

        //UNTUK MENU TO (BAGIAN)
        $bagian = 0;
        switch (date("m")) {
            case '11':
                $bagian = 1;
                break;
            case '12':
                $bagian = 2;
                break;
            case '1':
                $bagian = 3;
                break;
            case '2':
                $bagian = 4;
                break;
            case '3':
                $bagian = 5;
                break;
            case '4':
                $bagian = 6;
                break;
            case '5':
                $bagian = 7;
                break;
            case '6':
                $bagian = 8;
                break;
            case '7':
                $bagian = 9;
                break;
            case '8':
                $bagian = 10;
                break;
            case '9':
                $bagian = 11;
                break;
            case '10':
                $bagian = 12;
                break;
            default:
                $bagian = 1;
                break;
        }

        //AMBIL SEMUA TO YG PERNAH
        $to = UserTryOut::where([['userId', $user[0]['userId']], ['status', 1]])->orderBy('userTOId', 'asc')->get();

        // return $to;


        return view('profile',['mathPoin'=>$mathPoin, 'engPoin'=>$engPoin, 'bagian'=>$bagian, 'user'=>$user[0]['name'], 'to'=>$to]);
    }

    public function signup(Request $request)
    {
        $input = $request->all();
        $provinces = DB::table('province')->get();


        //VALIDASI SEBAGIAN INPUT
        $checkEmail = User::where('email', $input['email'])->get();
        //return $checkEmail;
        if($checkEmail!="[]"){
            return view('signup',['input'=>$input, 'provinces'=>$provinces, 'warning'=>'<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf!</strong> Email sudah terdaftar.
                </div>']);
        } elseif($input['provinceId']==0){
            return view('signup',['input'=>$input, 'provinces'=>$provinces, 'warning'=>'<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf!</strong> Mohon pilih Propinsi yang benar.
                </div>']);
        } elseif ($input['bulan']==0) {
            return view('signup',['input'=>$input, 'provinces'=>$provinces, 'warning'=>'<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf!</strong> Mohon pilih Bulan Lahir yang benar.
                </div>']);;
        } elseif (empty($input['status'])) {
            return view('signup',['input'=>$input, 'provinces'=>$provinces, 'warning'=>'<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf!</strong> Mohon pilih status (Mahasiswa/SMA/Lainnya).
                </div>']);;
        }


        $newsub = User::Create($input);

        return view('login',['email'=>$input['email'], 'warning'=>'<div class="alert alert-success" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Selamat!</strong> Pendaftaran berhasil..
                </div>', 'user'=>"Profil"]);
    }

    public function settingFirstTime(Request $request)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        if($email==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('login');
        };

        //AMBIL SEMUA DATA USER
        $user = User::where('email', $email)->get();

        //TAMPILKAN HALAMAN
        $provinces = DB::table('province')->get();
        return view('setting',['user'=>$user[0]['name'], 'provinces'=>$provinces, 'name'=>$user[0]['name'], 'email'=>$user[0]['email'], 'status'=>$user[0]['status'], 
            'tanggal'=>$user[0]['tanggal'], 'bulan'=>$user[0]['bulan'], 'tahun'=>$user[0]['tahun'], 'provinceId'=>$user[0]['provinceId']]);
    }

    public function setting(Request $request)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        if($email==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('login');
        };

        //INISIALISASI INPUT
        $input = NULL;

        //JIKA PASSWORD TDK DIISI
        if($request['password']==""){
            $input = $request->except('password');
            User::where('email', $email)->update(['name'=>$input['name'], 'email'=>$input['email'], 'status'=>$input['status'], 
            'provinceId'=>$input['provinceId'], 'tanggal'=>$input['tanggal'], 'bulan'=>$input['bulan'], 'tahun'=>$input['tahun']]);
        } else{
            $input = $request->all();
            User::where('email', $email)->update(['name'=>$input['name'], 'email'=>$input['email'], 'password'=>$input['password'], 'status'=>$input['status'], 
            'provinceId'=>$input['provinceId'], 'tanggal'=>$input['tanggal'], 'bulan'=>$input['bulan'], 'tahun'=>$input['tahun']]);
        }

        return redirect()->route('setting')->with('warning', '<div class="alert alert-success" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Selamat!</strong> Data kamu berhasil disimpan.
                </div>')->cookie('email', $input['email'], 300);
    }

    public function signupfirsttime()
    {
        $provinces = DB::table('province')->get();
        $input = null;
        return view('signup',['provinces'=>$provinces, 'input'=>$input, 'user'=>"Profil"]);
    }

    public function login(Request $request)
    {
        $check = DB::table('user')
                    ->where('email', $request->email)
                    ->where('password', $request->password)->get();
        if($check=='[]'){
            return view('login')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf,</strong> Email atau Password Anda salah.
                </div>')->with('user', "Profil")->with('email', $request->email);
        } else{            
            User::where('email', $request->email)->update(['lastActive'=>date('H:i:sa d-m-Y')]);
            return redirect()->route('home')->cookie('email', $request->email, 300);
        }
    }

    public function logout()
    {
        return redirect()->route('home')->cookie('email', "", 1);
    }

    public function admin(Request $request)
    {
        $check = DB::table('admin')
                    ->where('username', $request->email)
                    ->where('password', $request->password)->get();
        if($check=='[]'){
            return view('login')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Perhatian!</strong> Username atau Password salah.
                </div>');
        } else{
            return redirect()->route('insert')->cookie('username', $request->email, 300);
        }
    }
}

// return $request->cookie('username');
// return response('Hello World')->cookie(
//             'username', 'muhshamadcookie', 5
//         );