@extends('layouts.layout')

@section('title') Entry Sub Materi - toSTIS.net @stop

@section('body')
    <body>
@stop
@section('mainBody')
    <div class="container">
        <div class="row" style="margin-top:10pt">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-lock"></span> Entry Sub Materi</h3>
                    </div>
                    <div class="panel-body">
                        <form role="form"  action={{route('submaterial')}} method="POST">
                          {{ csrf_field() }}
                            <div class="form-group">
                                <input class="form-control" id="subjectId" name="subjectId" placeholder="Subject" value="{{$subjectId or "1"}}">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" id="subMaterialName" name="subMaterialName" placeholder="Sub Material" autofocus>
                            </div>                       
                            <button type="submit" class="btn btn-default">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop