<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
use Illuminate\Http\Request;
use Illuminate\Http\Response;

//Route::get('/replacePng', 'QuestController@replacePng');

Route::get('/', 'HomeController@index')->name('home');
Route::get('/monitor', 'HomeController@monitor');
Route::get('/test', function () {
    return view('test');
});

Route::get('/daftar', 'UserController@signupfirsttime')->name('signupfirsttime');
Route::post('/daftar', 'UserController@signup')->name('signup');



Route::post('/login', 'UserController@login')->name('login');
Route::get('/login', function () {
    return view('login')->with('user', "Profil");
});

Route::get('/pesan', function () {
    return view('pesan')->with('user', "Profil");
})->name('pesan');
Route::post('/pesan', 'PaymentController@pesan')->name('pesankode');

Route::get('/logout', 'UserController@logout')->name('logout');



Route::get('/profile', 'UserController@index')->name('profile');
Route::get('/setting', 'UserController@settingFirstTime')->name('setting');
Route::post('/setting', 'UserController@setting')->name('settingPOST');


Route::get('/latihan/{id}', 'QuestController@latihan')->name('latihan');
Route::get('/latihan/{sub}/{id}', 'QuestController@latihanNow')->name('latihanNow');

Route::get('/tryout/{id}/{bagian}', 'QuestController@loadForTO')->name('tryout');
Route::post('/tryout/periksaTo/realtime', 'QuestController@realTimeSubmit')->name('realtimesubmit');
Route::post('/tryout/periksaTo', 'QuestController@periksaTO')->name('periksaTO');
Route::get('/result', function () {
    return view('result');
})->name('result');
Route::get('/check/{id}/{bagian}', 'QuestController@loadForCheck')->name('check');


Route::post('/admin', 'UserController@admin')->name('admin');
Route::get('/admin', function () {
    return view('admin')->with('user', "Profil");
});
Route::get('/admin/insert/ajax', 'QuestController@ajax')->name('insertQuestAjax');
Route::get('/admin/insert', function (Request $request) {
    //CEK USER AKTIF
    $username = $request->cookie('username');
    if($username==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
        return redirect()->route('admin');
    };
    return view('insert', ['subMaterialName'=>"",'lastSubjectSelected'=>"", 'tryOutId'=>"", 'forWhat'=>"0", 'user'=>"Profil", 'tryOutName'=>"", 'startDate'=>"", 'endDate'=>""]);
})->name('insert');
Route::get('/admin/insertMath', function (Request $request) {
    //CEK USER AKTIF
    $username = $request->cookie('username');
    if($username==""){ //SURUH LOGIN JIKA TDK ADA USER AKTIF
        return redirect()->route('admin');
    };
    return view('math')->with('user',"Profil");
})->name('insert');
Route::post('/admin/insert', 'QuestController@insert')->name('insertQuest');
Route::get('/admin/insertSub', function () {
    return view('insertsub')->with('user', "Profil");
});
Route::post('/admin/insertSub', 'SubmaterialController@insert')->name('submaterial');

Route::post('/payment', 'PaymentController@index')->name('payment');
Route::post('/wa', 'KuotaController@wa')->name('wa');
Route::post('/smsInject', 'KuotaController@smsInject')->name('smsInject');
Route::get('/xmpp','KuotaController@xmpp');

Route::get('/admin/kuota','PaymentController@kuota')->name('kuota');
Route::post('/admin/kuota', 'PaymentController@insertKuota')->name('insertKuota');

Route::get('/admin/editKuota','PaymentController@editKuotaGet');
Route::post('/admin/editKuota', 'PaymentController@editKuota')->name('editKuota');
Route::post('/admin/editKuotaAvailable', 'PaymentController@isAvailable')->name('isAvailable');

Route::get('/admin/editSoal','QuestController@editSoalGet');
Route::post('/admin/editSoal', 'QuestController@editSoal')->name('editSoal');