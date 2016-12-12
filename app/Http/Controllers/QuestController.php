<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Quest;
use App\QuestIntro;
use App\User;
use App\Latihan;
use App\Tryout;
use App\UserLatihan;
use App\UserTryOut;
use App\Submaterial;
use App\Mathstat;
use App\Engstat;
use App\Http\Controllers\Controller;
use DB;

class QuestController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function index()
    {
        return view('insertQuest');
    }

    public function insert(Request $request)
    {
        
        //UNTUK QUEST
        $input = $request->except('tryOutName', 'startDate', 'endDate', 'questIntroId');

        //UNTUK TO/USM
        $input2 = $request->only('name', 'subjectId', 'type','startDate', 'endDate');

        //AMBIL ID SUBMATERIAL
        $a = Submaterial::where('subMaterialName', $input['subMaterialId'])->get();        

        //TEMP SUBMATERIAL NAME
        $subM = $input['subMaterialId'];

        //ASSIGN SUB MATERIAL ID KE INPUT        
        $input['subMaterialId'] = (string)$a[0]['subMaterialId'];

        //ASSIGN TRYOUT ID KE QUEST JIKA TYPE = 2 (TO)
        $b = null; //INISIALISASI DATA TO AKTIF
        $input['tryOutId']=NULL;
        if($input['forWhat']==2) {
            //PILIHAN AMBIL DATA TO
            if($input2['name']!="" && $request['tryOutId']==""){ //JIKA NAMA ADA
                $b = Tryout::Create($input2); //BUAT TO BARU
            } else{ //JIKA ID ADA
                $b = Tryout::where('tryOutId', $request['tryOutId'])->get();
            }
            $input['tryOutId']=(string)$b[0]['tryOutId'];
        }

        //ASSIGN LATIHAN ID KE QUEST JIKA TYPE = 0 (LATIHAN)
        $C = null; //INISIALISASI DATA LATIHAN AKTIF
        $input['latihanId']=NULL; //INISIALISASI NILAI LATIHAN ID UTK SOAL
        if($input['forWhat']==0) {
            //PILIHAN AMBIL DATA LATIHAN
            if($input['latihanName']!="" && $request['latihanId']==""){ //JIKA NAMA ADA
                //INPUT UNTUK LATIHAN
                $input3 = $request->only('latihanName', 'subjectId');
                $input3['subMaterialId'] = $input['subMaterialId'];
                //return $input3['subMaterialId'];
                $C = Latihan::Create($input3); //BUAT LATIHAN BARU
            } else{ //JIKA ID ADA
                $C = Latihan::where('latihanId', $request['latihanId'])->get();
            }
            $input['latihanId']=(string)$C[0]['latihanId'];
        }

        //UPLOAD GAMBAR
        if($request->hasFile('qPictPath')) {
            // //AMBIL ID QUEST AKTIF
            // $quest = Quest::where('name', $input2['name'])->get();

            //TANGKAP GAMBAR
            $file = $request->qPictPath;

            //PENAMAAN GAMBAR            
            $extension = $file->getClientOriginalExtension();
            $fileName = (string)$a[0]['subMaterialId'].'-'.date("Y-m-d h-i-sa") . '.' . $extension;

            //SIMPAN GAMBAR KE HARDDISK
            $file->move(public_path().'\upload\qPictPath/', $fileName);

            //UPDATE NAMA GAMBAR KE DB
            $input['qPictPath']=$fileName;
        }

        //QUEST INTRO HANDLER
        $questIntro = NULL;
        $input['questIntroId']=NULL;
        if($request['questIntroId']==""){ //JIKA QUESTINTRO ID TIDAK ADA
            if($request['questIntro']!=""){ //JIKA QUEST INTRO TEXT ADA
                $new['text'] = $request['questIntro'];
                $questIntro = QuestIntro::Create($new); //BUAT QUEST INTRO BARU
                //ASSIGN QUESTINTRO ID KE INPUT
                $input['questIntroId']=$questIntro['questIntroId'];
            }
        } else{ //JIKA QUEST INTRO ID ADA
            $questIntro = QuestIntro::findOrFail($request['questIntroId']);
            $input['questIntroId']=$request['questIntroId'];
        }

        //BUAT QUEST BARU
        $newsub = Quest::Create($input);

        //TAMPILKAN KEMBALI PAGE INSERT + DATA SEBELUMNYA
        if($input['forWhat']==2) {
            return view('insert', ['subMaterialName'=>$subM,'lastSubjectSelected'=>$input['subjectId'], 'forWhat'=>$input['forWhat'], 'tryOutName'=>(string)$b[0]['name'], 
                'tryOutId'=>(string)$b[0]['tryOutId'], 'startDate'=>(string)$b[0]['startDate'], 'endDate'=>(string)$b[0]['endDate'], 
                'type'=>(string)$b[0]['type'], 'questIntroText'=>$request['questIntro'], 'questIntroId'=>$input['questIntroId']]);
        } elseif($input['forWhat']==0) {
            return view('insert', ['subMaterialName'=>$subM,'lastSubjectSelected'=>$input['subjectId'], 'forWhat'=>$input['forWhat'], 'questIntroText'=>$request['questIntro'], 
                'questIntroId'=>$input['questIntroId'], 'latihanId'=>$input['latihanId'], 'latihanName'=>$input['latihanName']]);
        }else {
            return view('insert', ['subMaterialName'=>$subM,'lastSubjectSelected'=>$input['subjectId'], 'forWhat'=>$input['forWhat'],
                'questIntroText'=>$questIntro['text'], 'questIntroId'=>$questIntro['questIntroId']]);
        }
        
    }

    public function ajax()
    {
        $subjectId = $_GET['subject'];
        $sqls = DB::table('submaterial')->select('subMaterialName')->where('subjectId', $subjectId)->get();
        $ajax = '';
        foreach ($sqls as $sql) {
            $ajax.='<option class="del" value="'.$sql->subMaterialName.'">';
        }
        return $ajax;
    }

    public function loadForCheck(Request $request, $id, $bagian)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        //JIKA USER BELUM LOGIN
        if($email==""){
            return redirect()->route('login');
        }

        //AMBIL DATA USER AKTIF
        $user = User::where('email', $email)->get();

        //AMBIL DATA TRYOUT
        $tryOut = TryOut::where('tryOutId', $id)->get();
        //AMBIL TYPE TO
        $typeTO = $tryOut[0]['type']; 
        
        //CEK STATUS KEPERNAHAN TRYOUT
        $userTOStatus = null;
        if($typeTO==1){ //JIKA TO
            $userTOStatus = UserTryOut::where([['tryOutId', $id],['userId', $user[0]['userId']],['bagian', $bagian]])->first();
        } else{
            $userTOStatus = UserTryOut::where([['tryOutId', $id],['userId', $user[0]['userId']]])->first();
        }

        // return $userTOStatus;

        //JIKA BELUM PERNAH MENGERJAKAN
        if($userTOStatus==''){
            return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>Maaf</strong>, Anda belum pernah mengerjakan soal tersebut.
                    </div>');
        }

        //AMBIL SEMUA SOAL TO/USM
        $quests = NULL;
        $jumlahSoal = 0;

        //MODEL USM
        //MTK
        if($id==3){ //JIKA TO MTK
                $quests = Quest::where('mathstat.bagian', $bagian)
                                ->where('mathstat.userId', $user[0]['userId'])
                                ->join('mathstat', 'mathstat.questId', '=', 'quest.questId')->orderBy('mathstat.mathStatId', 'asc')
                                ->get();        
        } elseif($id==4){//JIKA TO BHS INGG
                $quests = Quest::where('engstat.bagian', $bagian)
                                ->where('engstat.userId', $user[0]['userId'])
                                ->join('engstat', 'engstat.questId', '=', 'quest.questId')->orderBy('engstat.engStatId', 'asc')
                                ->get();
        } else{ //JIKA USM
            $quests = Quest::where('tryOutId', $id)->get();
        }

        //MENGAMBIL SUBJECT ID
        $subjectId = $quests[0]['subjectId'];

        //CEK TIAP SOAL TO
        //INISIALISASI QUEST INTRO
        $questIntro = NULL;
        $introIndex = 0;
        foreach ($quests as $key => $quest) {
            //AMBIL SOAL DI STAT
            $umum = NULL;
            if($subjectId == 1){
                $umum = Mathstat::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['questId', $quest['questId']], ['bagian', $bagian]])->get();
            } else{
                $umum = Engstat::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['questId', $quest['questId']], ['bagian', $bagian]])->get();
            } 

            //TAMBAH DAN UBAH KE PILIHAN USER SEBELUMNYA
            $quest['selected'] = $umum[0]['selected'];
            $quest['result'] = $umum[0]['result'];

            //QUEST INTRO
            if($quest['questIntroId']==""){//JIKA QUEST INTRO TDK ADA

            }else{ //JIKA QUEST INTRO ADA
                if($key>0){ //BIAR TIDAK MINES, MINIMAL DARI INDEX 1
                    if($quests[$key]['questIntroId']==$quests[$key-1]['questIntroId']){//JIKA SAMA DGN SBLMNYA, TDK USAH SIMPAN

                    } else{ //SIMPAN JIKA BEDA
                        $qiText = QuestIntro::where([['questIntroId', $quest['questIntroId']]])->value('text');
                        $quest['questIntro'] = $qiText;
                        //SIMPAN UNTUK MENGETAHUI NOMOR
                        $questIntro[$introIndex]['nomor'] = $key+1;
                        $questIntro[$introIndex]['questId'] = $quest['questId'];
                        $questIntro[$introIndex]['questIntroId'] = $quest['questIntroId'];   
                        $introIndex++;            
                    }
                    
                } else{ //KLO INDEX O SAJA SUDAH ADA
                    $qiText = QuestIntro::where("questIntroId", $quest['questIntroId'])->value('text');
                    $quest['questIntro'] = $qiText;
                        // return $qiText;
                    $questIntro[$introIndex]['nomor'] = $key+1;
                    $questIntro[$introIndex]['questId'] = $quest['questId'];
                    $questIntro[$introIndex]['questIntroId'] = $quest['questIntroId'];
                    $introIndex++;     
                }
                
            }
        }

        //AMBIL DATA UNTUK MENGHITUNG BENAR SALAH
        $umum = NULL; //INISIALISASI UTK QUERY UMUM
        //AMBIL QUERY UMUM BERDASARKAN MAPEL (KARENA DATABASE BEDA)
        if($subjectId == 1){
            $umum = DB::table('mathstat')->where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->get();
        } else{
            $umum = DB::table('engstat')->where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->get();
        }  


        //MENGHITUNG JUMLAH BENAR SALAH
        $true=$umum->where('result', 1)->count('result');
        $false=$umum->where('result', 0)->count('result');
        $nyerah=$umum->where('result', 2)->count('result');
        $ngarang=$umum->where('result', 3)->count('result');
        
        //HITUNG POIN
        $totalPoin = $true*2-$false;

        //AMBIL NAMA TRYOUT
        $tryOutName = Tryout::where('tryOutId', $id)->value('name');
        
        //TAMPILKAN LEMBAR SOAL
        return view('tryout',['tryOutName'=>$tryOutName, 'totalPoin'=>$totalPoin, 'true'=>$true, 'false'=>$false, 'nyerah'=>$nyerah, 'ngarang'=>$ngarang, 'questIntro'=>$questIntro, 'quests'=>$quests,'subject'=>$quests[0]['subjectId'], 'user'=>$user[0]['name'], 
            'userId'=>$user[0]['userId'], 'tryOutId'=>$id, 'bagian'=>$bagian, 'time'=>"noTime"]);
    }

    public function loadForTO(Request $request, $id, $bagian)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        //JIKA USER BELUM LOGIN
        if($email==""){
            return redirect()->route('login');
        };

        //AMBIL DATA USER AKTIF
        $user = User::where('email', $email)->get();

        //AMBIL DATA TRYOUT
        $tryOut = TryOut::where('tryOutId', $id)->get();
        //AMBIL TYPE TO
        $typeTO = $tryOut[0]['type']; 
        
        //CEK STATUS KEPERNAHAN TRYOUT
        $userTOStatus = null;
        if($typeTO==1){ //JIKA TO
            $userTOStatus = UserTryOut::where([['tryOutId', $id],['userId', $user[0]['userId']],['bagian', $bagian]])->first();
        } else{
            $userTOStatus = UserTryOut::where([['tryOutId', $id],['userId', $user[0]['userId']]])->first();
        }

        //return $userTOStatus;

        if($userTOStatus==''){ // JIKA BELUM PERNAH TO INI MAKA BUAT BARU
            if($tryOut[0]['type']==1 && (date("t")-date('d'))>1){ //JIKA TO & JADWAL BLM MASUK
                return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>Maaf</strong>, Tryout tersebut belum tersedia.
                    </div>');
            }
            switch (date("m")) { //CEK BAGIAN SEBENARNYA
                case '11':
                    $bagian1 = 1;
                    break;
                case '12':
                    $bagian1 = 2;
                    break;
                case '1':
                    $bagian1 = 3;
                    break;
                case '2':
                    $bagian1 = 4;
                    break;
                case '3':
                    $bagian1 = 5;
                    break;
                case '4':
                    $bagian1 = 6;
                    break;
                case '5':
                    $bagian1 = 7;
                    break;
                case '6':
                    $bagian1 = 8;
                    break;
                case '7':
                    $bagian1 = 9;
                    break;
                case '8':
                    $bagian1 = 10;
                    break;
                case '9':
                    $bagian1 = 11;
                    break;
                case '10':
                    $bagian1 = 12;
                    break;
                default:
                    $bagian1 = 1;
                    break;
            }
            if($tryOut[0]['type']==1&&$bagian!=$bagian1){ //JIKA ILEGAL BAGIAN
                return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>Maaf</strong>, Tryout tersebut belum tersedia.
                    </div>');
            } else{ //JIKA JUJUR
                $new['userId'] = $user[0]['userId'];
                $new['tryOutId'] = $id;
                $new['bagian'] = $bagian;
                $temp = UserTryOut::Create($new);
            }            
        } elseif($userTOStatus['status']==1 && $tryOut[0]['type']==1){ //JIKA PERNAH DAN BUKAN SOAL USM MAKA TAMPILKAN SDH PERNAH 
            return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf</strong>, Anda sudah pernah mengerjakan.
                </div>');
        } else if($tryOut[0]['type']==0){ //JIKA SOAL USM
        }

         //return $temp;

        //INISIALISASI WAKTU DEADLINE
        $year = NULL;
        $month = NULL;
        $day = NULL;

        //AMBIL WAKTU DEADLINE TO
        // if($tryOut[0]['type']==1){ //JIKA TO BENERAN
        //     $year = substr($tryOut[0]['endDate'], 0,4);
        //     $month = substr($tryOut[0]['endDate'], 5,2);
        //     $day = substr($tryOut[0]['endDate'], 8,2);

        //     if (($year-date("Y"))<0) {
        //         return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
        //                 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        //                 <strong>Maaf</strong>, Jadwal TryOut sudah lewat.
        //             </div>');
        //     } elseif(($year-date("Y"))==0){
        //         if (($month-date("m"))<0) {
        //             return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
        //                 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        //                 <strong>Maaf</strong>, Jadwal TryOut sudah lewat.
        //             </div>');
        //         } elseif (($month-date("m"))==0) {
        //             if (($day-date("d"))<0) {
        //                 return redirect()->route('home')->with('warning', '<div class="alert alert-warning" style="margin-top:3px">
        //                 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        //                 <strong>Maaf</strong>, Jadwal TryOut sudah lewat.
        //             </div>');
        //             }
        //         }
        //     }   
        // } else{ //JIKA SOAL USM

        // }

        //AMBIL SEMUA SOAL TO/USM
        $quests = NULL;
        $jumlahSoal = 0;

        //MODEL USM
        //MTK
        if($id==3){ //JIKA TO MTK
            if($userTOStatus==''){ //JIKA TO BLM PERNAH ADA
                //AMBIL DATA SUBMATERI MTK
                $mathSub = Submaterial::where('subjectId', 1)->get();
                //CEK SOAL YG SDH ADA
                $reservedQuestId = Mathstat::where([['tryOutId', 3], ['userId', $user[0]['userId']]])->select('questId')->get();

                //RANDOM MODEL
                $model = array("2013","2015","2016");
                $selectedModel = array_rand($model, 1);
                $m = null;

                if ($selectedModel==2) { // JIKA MODEL 2
                    //CEK SETIAP SUB
                    foreach ($mathSub as $key => $sub) {
                        //AMBIL SEMUA SOAL SESUAI SUBM
                        //AMBIL SOAL YG BLM DIKERJA
                        $temp = Quest::where([['subMaterialId', $sub['subMaterialId']], ['tryOutId', 3]])
                                        ->where('questIntroId', '=' , NULL)
                                        ->whereNotIn('questId', $reservedQuestId)
                                        ->get();                    

                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $temp2 = $temp->toArray();
                        // return $sub['model2']." <> ".sizeof($temp2);
                        if($sub['model2']<1){
                            continue;
                        }

                        //JIKA JLH ALOKASI SOAL SUBM >0, ACAK
                        if(sizeof($temp2)>=$sub['model2']){
                           $sampled = array_rand($temp2, $sub['model2']);
                           if($sub['model2']==1){
                                $quests[$jumlahSoal]= $temp[$sampled];
                                $jumlahSoal++;
                           } else{
                           //TAMBAH SOAL KE QUESTS
                            foreach ($sampled as $key => $idxSample) {
                                $quests[$jumlahSoal]= $temp[$idxSample];
                                $jumlahSoal++;
                            }}
                        }
                    }

                    //ACAK SOAL!!!
                    shuffle($quests);

                    //UTK SOAL INTRO
                    $introQuest = Quest::where([['tryOutId', 3]])
                                    ->where('questIntroId', '!=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)
                                    ->get();

                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayIntroQuest = $introQuest->toArray();

                    //JIKA JLH SOAL SUBM >2, ACAK!
                    if(sizeof($arrayIntroQuest)>=2){
                       $sampled = array_rand($arrayIntroQuest, 1);
                        //AMBIL SEMUA SOAL DG QUEST INTRO TERPILIH
                        $introQuest = Quest::where('questIntroId', $introQuest[$sampled]['questIntroId'])
                                        ->whereNotIn('questId', $reservedQuestId)->get();
                        
                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI SOAL QUESINTRO TERPILIH
                        $arrayIntroQuest = $introQuest->toArray();
                        //SAMPLING LAGI!!! 2 SOAL QUEST INTRO  YG SAMA
                        $sampled = array_rand($arrayIntroQuest, 2);
                        foreach ($sampled as $key => $idxSample) {
                            $quests[$jumlahSoal]= $introQuest[$idxSample];
                            $jumlahSoal++;
                        }
                    }
                } elseif ($selectedModel==1) { //MODEL 1
                    //CEK SETIAP SUB
                    foreach ($mathSub as $key => $sub) {
                        //AMBIL SEMUA SOAL SESUAI SUBM
                        //AMBIL SOAL YG BLM DIKERJA
                        $temp = Quest::where([['subMaterialId', $sub['subMaterialId']], ['tryOutId', 3]])
                                        ->where('questIntroId', '=' , NULL)
                                        ->whereNotIn('questId', $reservedQuestId)
                                        ->get();                    

                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $temp2 = $temp->toArray();
                        // return $sub['model2']." <> ".sizeof($temp2);
                        if($sub['model1']<1){
                            continue;
                        }

                        //JIKA JLH ALOKASI SOAL SUBM >0, ACAK
                        if(sizeof($temp2)>=$sub['model1']){
                           $sampled = array_rand($temp2, $sub['model1']);
                           if($sub['model1']==1){
                                $quests[$jumlahSoal]= $temp[$sampled];
                                $jumlahSoal++;
                           } else{
                           //TAMBAH SOAL KE QUESTS
                            foreach ($sampled as $key => $idxSample) {
                                $quests[$jumlahSoal]= $temp[$idxSample];
                                $jumlahSoal++;
                            }}
                        }
                    }

                    //ACAK SOAL!!!
                    shuffle($quests);



                    //INTRO ANALITIK
                    $introQuest = Quest::where([['tryOutId', 3]])
                                    ->where('questIntroId', '!=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)
                                    ->get();

                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayIntroQuest = $introQuest->toArray();

                    //JIKA JLH SOAL SUBM >2, ACAK!
                    if(sizeof($arrayIntroQuest)>=2){
                       $sampled = array_rand($arrayIntroQuest, 1);
                        //AMBIL SEMUA SOAL DG QUEST INTRO SAMPLED
                        $introQuest = Quest::where('questIntroId', $introQuest[$sampled]['questIntroId'])
                                            ->whereNotIn('questId', $reservedQuestId)
                                            ->get();
                        
                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $arrayIntroQuest = $introQuest->toArray();
                        //SAMPLING LAGI!!! 2 SOAL FIX
                        $sampled = array_rand($arrayIntroQuest, 2);
                        foreach ($sampled as $key => $idxSample) {
                            $quests[$jumlahSoal]= $introQuest[$idxSample];
                            $jumlahSoal++;
                        }
                    }                
                } else{
                    //CEK SETIAP SUB
                    foreach ($mathSub as $key => $sub) {
                        //AMBIL SEMUA SOAL SESUAI SUBM
                        //AMBIL SOAL YG BLM DIKERJA
                        $temp = Quest::where([['subMaterialId', $sub['subMaterialId']], ['tryOutId', 3]])
                                        ->where('questIntroId', '=' , NULL)
                                        ->whereNotIn('questId', $reservedQuestId)
                                        ->get();                    

                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $temp2 = $temp->toArray();
                        // return $sub['model2']." <> ".sizeof($temp2);
                        if($sub['model0']<1){
                            continue;
                        }

                        //JIKA JLH ALOKASI SOAL SUBM >0, ACAK
                        if(sizeof($temp2)>=$sub['model0']){
                           $sampled = array_rand($temp2, $sub['model0']);
                           if($sub['model0']==1){
                                $quests[$jumlahSoal]= $temp[$sampled];
                                $jumlahSoal++;
                           } else{
                           //TAMBAH SOAL KE QUESTS
                            foreach ($sampled as $key => $idxSample) {
                                $quests[$jumlahSoal]= $temp[$idxSample];
                                $jumlahSoal++;
                            }}
                        }
                    }

                    //ACAK SOAL!!!
                    shuffle($quests);

                    //UTK SOAL INTRO
                    $introQuest = Quest::where([['tryOutId', 3]])
                                    ->where('questIntroId', '!=' , NULL)
                                    ->get();

                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayIntroQuest = $introQuest->toArray();

                    //JIKA JLH SOAL SUBM >2, ACAK!
                    if(sizeof($arrayIntroQuest)>=2){
                       $sampled = array_rand($arrayIntroQuest, 1);
                        //AMBIL SEMUA SOAL DG QUEST INTRO SAMPLED
                        $introQuest = Quest::where('questIntroId', $introQuest[$sampled]['questIntroId'])
                                                ->whereNotIn('questId', $reservedQuestId)
                                                ->get();
                        
                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $arrayIntroQuest = $introQuest->toArray();
                        //SAMPLING LAGI!!! 2 SOAL FIX
                        $sampled = array_rand($arrayIntroQuest, 2);
                        foreach ($sampled as $key => $idxSample) {
                            $quests[$jumlahSoal]= $introQuest[$idxSample];
                            $jumlahSoal++;
                        }
                    }
                }
            } else{ //JIKA SUDAH PERNAH ADA
                $quests = Quest::where('mathstat.bagian', $bagian)
                                ->where('mathstat.userId', $user[0]['userId'])
                                ->join('mathstat', 'mathstat.questId', '=', 'quest.questId')->orderBy('mathstat.mathStatId', 'asc')
                                ->get();
            }            
        } elseif($id==4){//JIKA TO BHS INGG
            if($userTOStatus==''){ //JIKA TO BLM PERNAH ADA
                //AMBIL DATA SUBMATERI MTK
                $mathSub = Submaterial::where('subjectId', 2)->get();
                //CEK SOAL YG SDH ADA
                $reservedQuestId = Engstat::where([['tryOutId', 4], ['userId', $user[0]['userId']]])->select('questId')->get();

                //RANDOM MODEL
                $model = array("2011","2016");
                $selectedModel = array_rand($model, 1);
                $m = null;

                if ($selectedModel==1) { // JIKA MODEL 1
                    //CEK SETIAP SUB
                    foreach ($mathSub as $key => $sub) {
                        if($sub['subMaterialId']==51){ //ABAIKAN VOCAB
                            continue;
                        }
                        //AMBIL SEMUA SOAL SESUAI SUBM
                        //AMBIL SOAL YG BLM DIKERJA
                        $temp = Quest::where([['subMaterialId', $sub['subMaterialId']], ['tryOutId', 4]])
                                        ->where('questIntroId', '=' , NULL)
                                        ->whereNotIn('questId', $reservedQuestId)
                                        ->get();                    

                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $temp2 = $temp->toArray();
                        // return $sub['model2']." <> ".sizeof($temp2);
                        if($sub['model1']<1){
                            continue;
                        }

                        //JIKA JLH ALOKASI SOAL SUBM >0, ACAK
                        if(sizeof($temp2)>=$sub['model1']){
                           $sampled = array_rand($temp2, $sub['model1']);
                           if($sub['model1']==1){
                                $quests[$jumlahSoal]= $temp[$sampled];
                                $jumlahSoal++;
                           } else{
                           //TAMBAH SOAL KE QUESTS
                            foreach ($sampled as $key => $idxSample) {
                                $quests[$jumlahSoal]= $temp[$idxSample];
                                $jumlahSoal++;
                            }}
                        }
                    }

                    //ACAK SOAL!!!
                    shuffle($quests);

                    //UTK SOAL VOCAB
                    $vocabQuest = Quest::where([['tryOutId', 4], ['subMaterialId', 51]])
                                    ->where('questIntroId', '=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)
                                    ->get();
                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayVocabQuest = $vocabQuest->toArray();
                    $sampled = array_rand($arrayVocabQuest, 8);
                    foreach ($sampled as $key => $idxSample) {
                        $quests[$jumlahSoal] = $vocabQuest[$idxSample];
                        $jumlahSoal++;
                    }



                    //UTK SOAL INTRO
                    $introQuest = Quest::where([['tryOutId', 4]])
                                    ->where('questIntroId', '!=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)->select('questIntroId')->distinct()
                                    ->get();

                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayIntroQuest = $introQuest->toArray();
                    // return $introQuest[0]['questIntroId'];

                    //ACAK! AMBIL 3
                    $sampled = array_rand($arrayIntroQuest, 3);

                    foreach ($sampled as $key => $idxSample) {
                        //AMBIL SEMUA SOAL DG QUEST INTRO TERPILIH
                        $introQuest1 = Quest::where('questIntroId', $introQuest[$idxSample]['questIntroId'])
                                        ->whereNotIn('questId', $reservedQuestId)->get();
                        foreach ($introQuest1 as $key => $que) {
                            $quests[$jumlahSoal] = $que;
                            $jumlahSoal++;
                        }
                    }
                } else{ //MODEL 0
                    //CEK SETIAP SUB
                    foreach ($mathSub as $key => $sub) {
                        if($sub['subMaterialId']==51){ //ABAIKAN VOCAB
                            continue;
                        }
                        //AMBIL SEMUA SOAL SESUAI SUBM
                        //AMBIL SOAL YG BLM DIKERJA
                        $temp = Quest::where([['subMaterialId', $sub['subMaterialId']], ['tryOutId', 4]])
                                        ->where('questIntroId', '=' , NULL)
                                        ->whereNotIn('questId', $reservedQuestId)
                                        ->get();                    

                        //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                        $temp2 = $temp->toArray();
                        // return $sub['model2']." <> ".sizeof($temp2);
                        if($sub['model0']<1){
                            continue;
                        }

                        //JIKA JLH ALOKASI SOAL SUBM >0, ACAK
                        if(sizeof($temp2)>=$sub['model0']){
                           $sampled = array_rand($temp2, $sub['model0']);
                           if($sub['model0']==1){
                                $quests[$jumlahSoal]= $temp[$sampled];
                                $jumlahSoal++;
                           } else{
                           //TAMBAH SOAL KE QUESTS
                            foreach ($sampled as $key => $idxSample) {
                                $quests[$jumlahSoal]= $temp[$idxSample];
                                $jumlahSoal++;
                            }}
                        }
                    }

                    //ACAK SOAL!!!
                    shuffle($quests);

                    //UTK SOAL VOCAB
                    $vocabQuest = Quest::where([['tryOutId', 4], ['subMaterialId', 51]])
                                    ->where('questIntroId', '=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)
                                    ->get();
                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayVocabQuest = $vocabQuest->toArray();
                    $sampled = array_rand($arrayVocabQuest, 10);
                    foreach ($sampled as $key => $idxSample) {
                        $quests[$jumlahSoal] = $vocabQuest[$idxSample];
                        $jumlahSoal++;
                    }



                    //UTK SOAL INTRO
                    $introQuest = Quest::where([['tryOutId', 4]])
                                    ->where('questIntroId', '!=' , NULL)
                                    ->whereNotIn('questId', $reservedQuestId)->select('questIntroId')->distinct()
                                    ->get();

                    //UBAH OBJEK KE ARRAY UTK RANDOMISASI
                    $arrayIntroQuest = $introQuest->toArray();
                    // return $introQuest[0]['questIntroId'];

                    //ACAK! AMBIL 3
                    $sampled = array_rand($arrayIntroQuest, 2);

                    foreach ($sampled as $key => $idxSample) {
                        //AMBIL SEMUA SOAL DG QUEST INTRO TERPILIH
                        $introQuest1 = Quest::where('questIntroId', $introQuest[$idxSample]['questIntroId'])
                                        ->whereNotIn('questId', $reservedQuestId)->get();
                        foreach ($introQuest1 as $key => $que) {
                            $quests[$jumlahSoal] = $que;
                            $jumlahSoal++;
                        }
                    }     
                }
            } else{ //JIKA SUDAH PERNAH ADA
                $quests = Quest::where('engstat.bagian', $bagian)
                                ->where('engstat.userId', $user[0]['userId'])
                                ->join('engstat', 'engstat.questId', '=', 'quest.questId')->orderBy('engstat.engStatId', 'asc')
                                ->get();
            }
        } else{ //JIKA USM
            $quests = Quest::where('tryOutId', $id)->get();
        }
        // $a=$quests->toArray();
        // $sampled = array_rand($a, 2);
        // return $sampled;


        //INISIALISASI WAKTU TIMER
        $time = 0;
        $secon = 0;
        //$a = NULL;        
        $subjectId = $quests[0]['subjectId'];
        if($subjectId == 1){ //JIKA MTK
            $time = 90;
            //$a = MathStat::where([['userId', $user[0]['userId']], ['tryOutId', $tryOutId]])->get();
        } else{ //JIKA BINGG
            $time = 60;
            //$a = Engstat::where([['userId', $user[0]['userId']], ['tryOutId', $tryOutId]])->get();
        }
        //SIMPAN WKT TIMER SUTK PENGURANGAN
        $timeTemp = $time;

        //AMBIL DATA TO AKTIF SESUAI USER
        $account = UserTryOut::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->get();
        //return $account;

        //AMBIL STATUS SELESAI TO
        $userTOStatus = $account[0]['status'];

        //MENGHITUNG DURASI USER TO
        $duration = 0; //INISIALISASI
        //AMBIL WAKTU PER SATUAN WAKTU (NOW & TO USER)
        $adjustSec = substr($account[0]['created_at'],17,2); //DETIK DB
        $dbsec = 0; //reset detik ke 00
        $nsec = date ('sa') - $adjustSec;//mundur detik saat ini ke -detik db
        if($nsec<0) $nsec+=60; //mundur detik saat ini ke -detik db
        $adjustMin = substr($account[0]['created_at'],14,2); //MENIT DB
        $dbmin = 0; //reset menit db ke 00
        $nmin = date ('i') - $adjustMin;//mundur detik saat ini ke -detik db
        if($nmin<0) $nmin+=60;//mundur menit saat ini ke -menit db
        if($nmin>0&&$adjustSec>date ('sa')) $nmin-=1;//mundur menit saat ini ke -menit db
        // return "Menit: ".$nmin." Detik: ".$nsec;
        $adjustH = substr($account[0]['created_at'],11,2); //JAM DB
        $dbh = 0;
        $nh = date ('H') - $adjustH; //JAM SAAT INI
        if($nh<0) $nh+=23;
        if($nh>0&&$adjustMin>date ('i')) $nh-=1;

        if($nsec<$dbsec){ //HITUNG DETIK, JIKA DETIK SAAT INI < DETIK DB
            $duration = 60 - $dbsec + $nsec;
            // return "1 : ". $duration;
        } else{ // DETIK SAAT INI LEBIH BESAR
            $duration = $nsec - $dbsec;
            // return "2 : ". $duration;
        }

        if($nmin<$dbmin){ //HITUNG MENIT KE DETIK
            $duration += (60 - $dbmin + $nmin)*60;
        } else{
            $duration += ($nmin - $dbmin)*60;
        }

        if($nh<$dbh){ //HITUNG JAM KE DETIK
            $duration += (24 - $dbh + $nh)*3600;
        } else{
            $duration += ($nh - $dbh)*3600;
        }

        //CEK DURASI USER MENGERJAKAN TO
        if($userTOStatus!=1){ //JIKA STATUS DI DB BLM SELESAI
            if($duration>($timeTemp*60)){ //JIKA DURASI HABIS
                //UPDATE STATUS SELESAI DI DB
                UserTryOut::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->update(['status'=>1]);

                // TAMPILKAN RESULT DENGAN ERROR
                //AMBIL SEMUA HASIL SOAL TO
                if($subjectId == 1){
                    $umum = DB::table('mathstat')->where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->get();
                } else{
                    $umum = DB::table('engstat')->where([['userId', $user[0]['userId']], ['tryOutId', $id], ['bagian', $bagian]])->get();
                }  

                //HITUNG NILAI
                $true=$umum->where('result', 1)->count('result');
                $false=$umum->where('result', 0)->count('result');
                $nyerah=$umum->where('result', 2)->count('result');
                $ngarang=$umum->where('result', 3)->count('result');
                $totalPoin = $true*2-$false;
                //AMBIL NAMA LENGKAP
                $userName = $user[0]['name'];
                //PANGGIL VIEW RESULT
                return view('result',['tryOutName'=>$tryOut[0]['name'], 'totalPoin'=>$totalPoin, 'true'=>$true, 'false'=>$false, 'nyerah'=>$nyerah, 'ngarang'=>$ngarang, 'user'=>$userName,
                    'warning'=>'<div class="alert alert-warning" style="margin-top:3px">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Maaf</strong>, Waktu sudah habis.
                </div>']);
            } else{ //JIKA DURASI BELUM HABIS                
                $time = floor(($timeTemp*60 - $duration)/60);
                $secon = ($timeTemp*60 - $duration)%60;
            }
        } else{ //JIKA STATUS DI DB SUDAH SELESAI & SOAL USM (PASTI)
            //PERBAHARUI CATATAN USM SEBELUMNYA
            // return 'biang kerok';
            UserTryOut::where([['userId', $user[0]['userId']], ['tryOutId', $id]])->delete();
            $new['userId'] = $user[0]['userId'];
            $new['tryOutId'] = $id;
            UserTryOut::Create($new);
        }   

        //CEK TIAP SOAL TO
        //INISIALISASI QUEST INTRO
        $questIntro = NULL;
        $introIndex = 0;
        foreach ($quests as $key => $quest) {
            //AMBIL SOAL DI STAT
            $umum = NULL;
            if($subjectId == 1){
                $umum = Mathstat::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['questId', $quest['questId']], ['bagian', $bagian]])->get();
            } else{
                $umum = Engstat::where([['userId', $user[0]['userId']], ['tryOutId', $id], ['questId', $quest['questId']], ['bagian', $bagian]])->get();
            } 

            //JIKA SOAL DIDAPAT
            if($umum!="[]") {
                //TAMBAH DAN UBAH KE PILIHAN USER SEBELUMNYA
                $quest['selected'] = $umum[0]['selected'];
            } else{ //SOAL TIDAK ADA
                //TAMBAH SOAL KE STAT
                $forStat['questId'] = $quest['questId'];
                $forStat['tryOutId'] = $quest['tryOutId'];
                $forStat['bagian'] = $bagian;
                $forStat["userId"] = $user[0]['userId'];
                $forStat["result"] = "2";
                $forStat["selected"] = "0";

                if($subjectId == 1){
                    Mathstat::Create($forStat);
                } else{
                    Engstat::Create($forStat);
                }      
            } 

            //QUEST INTRO
            if($quest['questIntroId']==""){//JIKA QUEST INTRO TDK ADA

            }else{ //JIKA QUEST INTRO ADA
                if($key>0){ //BIAR TIDAK MINES, MINIMAL DARI INDEX 1
                    if($quests[$key]['questIntroId']==$quests[$key-1]['questIntroId']){//JIKA SAMA DGN SBLMNYA, TDK USAH SIMPAN

                    } else{ //SIMPAN JIKA BEDA
                        $qiText = QuestIntro::where([['questIntroId', $quest['questIntroId']]])->value('text');
                        $quest['questIntro'] = $qiText;
                        //SIMPAN UNTUK MENGETAHUI NOMOR
                        $questIntro[$introIndex]['nomor'] = $key+1;
                        $questIntro[$introIndex]['questId'] = $quest['questId'];
                        $questIntro[$introIndex]['questIntroId'] = $quest['questIntroId'];   
                        $introIndex++;            
                    }
                    
                } else{ //KLO INDEX O SAJA SUDAH ADA
                    $qiText = QuestIntro::where("questIntroId", $quest['questIntroId'])->value('text');
                    $quest['questIntro'] = $qiText;
                        // return $qiText;
                    $questIntro[$introIndex]['nomor'] = $key+1;
                    $questIntro[$introIndex]['questId'] = $quest['questId'];
                    $questIntro[$introIndex]['questIntroId'] = $quest['questIntroId'];
                    $introIndex++;     
                }
                
            }
        }

        //TAMPILKAN LEMBAR SOAL
        return view('tryout',['questIntro'=>$questIntro, 'quests'=>$quests,'subject'=>$quests[0]['subjectId'], 'user'=>$user[0]['name'], 
            'userId'=>$user[0]['userId'], 'tryOutId'=>$id, 'bagian'=>$bagian, 'time'=>$time, 'secon'=>$secon]);
    }



    public function periksaTO(Request $request)
    {
        //TANGKAP SEMUA DATA REQUEST
        $inputs = $request->all();

        //AMBIL DATA UNTUK MENGHITUNG BENAR SALAH
        $umum = NULL; //INISIALISASI UTK QUERY UMUM
        //AMBIL QUERY UMUM BERDASARKAN MAPEL (KARENA DATABASE BEDA)
        $subjectId = Quest::where('tryOutId', $inputs['tryOutId'])->value('subjectId');
        if($subjectId == 1){
            $umum = DB::table('mathstat')->where([['userId', $request['userId']], ['tryOutId', $request['tryOutId']], ['bagian', $request['bagian']]])->get();
        } else{
            $umum = DB::table('engstat')->where([['userId', $request['userId']], ['tryOutId', $request['tryOutId']], ['bagian', $request['bagian']]])->get();
        }  


        //MENGHITUNG JUMLAH BENAR SALAH
        $true=$umum->where('result', 1)->count('result');
        $false=$umum->where('result', 0)->count('result');
        $nyerah=$umum->where('result', 2)->count('result');
        $ngarang=$umum->where('result', 3)->count('result');
        
        //HITUNG POIN
        $totalPoin = $true*2-$false;

        //UPDATE STATUS TO USER
        UserTryOut::where([['userId', $request['userId']], ['tryOutId', $inputs['tryOutId']], ['bagian', $request['bagian']]])->update(['status'=>1]);

        //AMBIL NAMA TRYOUT
        $tryOutName = Tryout::where('tryOutId', $inputs['tryOutId'])->value('name');

        //TUNGGU 5 DETIK
        sleep(5);

        //AMBIL NAMA USER UTK MENU
        $userName = DB::table('user')->where([['userId', $request['userId']]])->value('name');

        //TAMPILKAN HALAMAN RESULT
        return view('result',['tryOutName'=>$tryOutName, 'totalPoin'=>$totalPoin, 'true'=>$true, 'false'=>$false, 'nyerah'=>$nyerah, 'ngarang'=>$ngarang, 'user'=>$userName]);
    }

    public function realTimeSubmit(Request $request)
    {
        $inputs = $request->all();

        $userTOStatus = UserTryOut::where([['tryOutId', $inputs['tryOutId']],['userId', $inputs['userId']], ['bagian', $inputs['bagian']]])->first();

        if($userTOStatus==''){
            //return 'ok';
        } elseif($inputs['bagian']==0){
            
        }elseif($userTOStatus['status']==1){
            return 'error';
        }

        $quest = Quest::where('questId', $inputs['questId'])->get();
        $result = "2"; //2=blm isi
        if($inputs['selected']!=0){
            if ($quest[0]['answer']=="0") {
                $result = "3";
            } else{
                if($quest[0]['answer']==$inputs['selected']) {
                    $result = "1";
                } else{
                    $result = "0";
                }
            }
        }

        $subjectId = $quest[0]['subjectId'];
        if($subjectId == 1){
                $a = Mathstat::where([['userId', $inputs['userId']], ['questId', $inputs['questId']], ['tryOutId', $inputs['tryOutId']], ['bagian', $inputs['bagian']]])
                    ->update(['result' => (string)$result, 'selected' => (string)$inputs['selected']]);
            } else{
                $a = Engstat::where([['userId', $inputs['userId']], ['questId', $inputs['questId']], ['tryOutId', $inputs['tryOutId']], ['bagian', $inputs['bagian']]])
                    ->update(['result' => (string)$result, 'selected' => (string)$inputs['selected']]);
            }   
    }

    // public function sementara()
    // {
    //     $selected = Mathstat::where([['tryOutId', 1],['userId', 3]])->get();
    //     foreach ($selected as $key => $quest) {
    //         if($quest['selected']==0) continue;
    //         Quest::where('questId', $quest['questId'])
    //                 ->update(['answer' => $quest['selected']]);
    //     }
    // }

    public function latihan(Request $request, $id)
    {
        //CEK USER AKTIF
        $email = $request->cookie('email');
        if($email==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
            return redirect()->route('login');
        };

        //AMBIL SEMUA DATA USER
        $user = User::where('email', $email)->get();

        //AMBIL NAMA SUBM
        $subm = Submaterial::where('subMaterialId', $id)->get();

        //AMBIL SEMUA NAMA SUBM
        $mathSubMaterialName = DB::table('submaterial')->select('subMaterialName', 'subMaterialId')->where('subjectId', 1)->get();
        $engSubMaterialName = DB::table('submaterial')->select('subMaterialName', 'subMaterialId')->where('subjectId', 2)->get();

        //AMBIL SEMUA NAMA TO
        $engTO = Tryout::where('subjectId', 2)->get();
        $mathTO = Tryout::where('subjectId', 1)->get();

        return view('latihan', ['user'=>$user[0]['name'], 'currentSubMaterialName'=>$subm[0]['subMaterialName'], 'currentSubMaterialId'=>$subm[0]['subMaterialId'], 'mathSubMaterialName'=>$mathSubMaterialName,
            'engSubMaterialName'=>$engSubMaterialName, 'engTO'=>$engTO, 'mathTO'=>$mathTO]);
    }

    public function replacePng()
    {
        //AMBIL SEMUA INPUT
        $path    = '\\xampp\\htdocs\\blog\\public_html\\upload\\qPictPath';
        $files = scandir($path);
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $key => $img) {
            if(preg_match_all("/png/i", $img, $match)){
                continue;
            }
            $name = preg_replace("/jpg/i", "png", $img);
            Quest::where([['qPictPath', $name]])->update(['qPictPath'=>$img]);
        }
        return $files;
    }
}