<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Submaterial;
use App\Http\Controllers\Controller;
use DB;

class SubmaterialController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function index()
    {
        return view('insertsub');
    }

    public function insert(Request $request)
    {
        $input = $request->all();
        $newsub = Submaterial::Create($input);
        return view('insertsub', ['subjectId'=>$input['subjectId']]);
    }
}