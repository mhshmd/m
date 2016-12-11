<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Tryout;
use App\Quest;
use App\UserTryout;
use App\Http\Controllers\Controller;
use DB;

class HomeController extends Controller
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
        $user = null; //INISIALISASI USER
        if($email==""){ //JIKA BLM LOGIN TDK APA-APA
            $user = 'Profil';
        } else{ //JIKA ADA AMBIL DATA USER DI DB
            $user1 = User::where('email', $email)->get();
            if($user1!="[]") {
                $user = $user1[0]['name']; //UTK MENU
            } else{
                $user = "Profil";
            }
        }

        //AMBIL SUBMATERI
        // $mathSubMaterialName = DB::table('submaterial')->select('subMaterialName', 'subMaterialId')->where('subjectId', 1)->get();
        // $engSubMaterialName = DB::table('submaterial')->select('subMaterialName', 'subMaterialId')->where('subjectId', 2)->get();

        //AMBIL DATA SEMUA TO/USM
        // $math = Tryout::where('subjectId', 1)->get();
        // $eng = Tryout::where('subjectId', 2)->get();

        //UNTUK TO
        // $mathTO = $math->where('type', 1);
        // return $mathTO[1];

        //TENTUKAN BAGIAN YG BELUM
        $bagian = 0; //INISIALISASI BAGIAN
        // $userTOStatus = NULL;//INISIALISASI USERTO
        // //CEK TIAP BAGIAN
        // for ($i=1; $i<100; $i++) {
        //     $bagian = $i; //SET BAGIAN = PERULANGAN KE 
        //     $userTOStatus = UserTryOut::where([['tryOutId', 3],['userId', $user1[0]['userId']],['bagian', $i]])->first();
        //     //JIKA BAGIAN KE-I BELUM ADA
        //     if($userTOStatus==''){
        //         break;
        //     }
        // }

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
        //return $bagian;

        //BERAPA HARI LAGI TO
        $TORemaining = date("t")-date('d');
        //return $TORemaining;


        //CEK TO TERBARU
        // $newto1 = Tryout::where('type', 1)->max('tryOutId');
        // $newTO1Name = Tryout::where('tryOutId', $newto1)->value('name');
        // $newTO1dl = Tryout::where('tryOutId', $newto1)->value('endDate');
        // $newTO1deadline = "Deadline : ".date("d-m-Y", strtotime($newTO1dl));
        // $newTO1Action = Tryout::where('tryOutId', $newto1)->value('status');
        // ($newTO1Action==1)?($newTO1Action = 'Ikut Sekarang'):($newTO1Action = 'More info');

        // $newto2 = Tryout::where('type', 1)
        //                 ->where('tryOutId','<',$newto1)
        //                 ->max('tryOutId');
        // $newTO2Name = Tryout::where('tryOutId', $newto2)->value('name');
        // $newTO2dl = Tryout::where('tryOutId', $newto2)->value('endDate');
        // $newTO2deadline = "Deadline : ".date("d-m-Y", strtotime($newTO2dl));
        // $newTO2Action = Tryout::where('tryOutId', $newto2)->value('status');
        // ($newTO2Action==1)?($newTO2Action = 'Ikut Sekarang'):($newTO2Action = 'More info');

        // $freeUmum = Tryout::where('type', 0)->max('tryOutId');
        // $freeUjian = Tryout::where('tryOutId', $freeUmum)->value('name');

        return view('home',['user'=>$user, 'TORemaining'=>$TORemaining, 'bagian'=>$bagian]);
            // 'mathSubMaterialName'=>$mathSubMaterialName, 'engSubMaterialName'=>$engSubMaterialName,
            // 'engTO'=>$engTO, 'mathTO'=>$mathTO, 'newTO1Name'=>$newTO1Name, 'newTO1deadline'=>$newTO1deadline, 'newTO1Action'=>$newTO1Action, 
            // 'newTO2Name'=>$newTO2Name, 'newTO2deadline'=>$newTO2deadline, 'newTO2Action'=>$newTO2Action, 'freeUjian'=>$freeUjian, 
            // 'newto1'=>$newto1, 'newto2'=>$newto2, 'usm'=>$freeUmum]);
    }

    public function monitor()
    {
        $sqls = DB::table('submaterial')->select('subMaterialName', 'subMaterialId')->where('subjectId', 2)->get();
        // $sqls1 = DB::table('submaterial')->where('subjectId', 2)->sum('model1');
        // return $sqls1;
        // return $sqls;
        $data = NULL;
        foreach ($sqls as $key => $sub) {
            $data[$key]['name'] = $sub->subMaterialName;
            $data[$key]['count'] = Quest::where([['tryOutId', 4], ['subMaterialId', $sub->subMaterialId]])->count();
        }
        // $a = array(1,2,3,4,5,6);
        // $sampled = array_rand($a, 7);
        // return $sampled;

        // return $data;
        return view('monitor', ['data'=>$data, 'user'=>"Profil"]);
    }
}

// return $request->cookie('username');
// return response('Hello World')->cookie(
//             'username', 'muhshamadcookie', 5
//         );