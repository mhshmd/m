@extends('layouts.layout')

@section('title') Daftar - toSTIS.net @stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')
    <div class="container">
        <div class="row" style="margin-top:10pt">
            {!!$warning or ""!!}
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-sign-in"></span> Daftar Try Out STIS</h3>
                    </div>
                    <div class="panel-body">
                            <form role="form" action={{route('signup')}} method="POST">
                                {{ csrf_field() }}  
                                <div class="form-group">
                                    <input type="text" value=@if($input!=null) {{$input['name'] or ""}} @else "" @endif class="form-control text-capitalize" name='name' placeholder="Nama Lengkap" data-validation="required">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" value=@if($input!=null) {{$input['email'] or ""}}  @else "" @endif data-validation="email" placeholder="Email" name="email" data-validation="email" value="">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="password" data-validation="strength" data-validation-strength="2" placeholder="Password" name="password_confirmation" data-validation="length" data-validation-length="min8">
                                </div> 
                                <div class="form-group">
                                    <input class="form-control" type="password" placeholder="Ulangi Password" name="password" data-validation="confirmation">
                                </div>    
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="2">Mahasiswa</label>
                                </div>
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="1">SMA</label>
                                </div>  
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="3">Lainnya</label>
                                </div>
                                <div class="form-group">
                                      <select class='form-control' id="provinceId" name="provinceId">
                                        <option value="0" selected>Provinsi</option>
                                        @foreach ($provinces as $key=>$province)
                                            <option value="{{$province->provinceId}}" @if($input!=null) {{($input['provinceId']==$province->provinceId)?"selected":""}} @endif>{{$province->name}}</option>
                                        @endforeach
                                      </select>
                                </div>
                                <div class="row" style="padding:0 14px">
                                  <label>Tanggal lahir</label><br>
                                  <div class="form-group col-md-3">
                                      <input type="text" value=@if($input!=null) {{$input['tanggal'] or ""}} @else "" @endif class="form-control" name='tanggal' placeholder="Tgl"  data-validation="number" data-validation-allowing="range[1;31]">
                                  </div>
                                  <div class="form-group col-md-5" style="padding:0 0">
                                      <select class='form-control subMaterialInit' id="bulan" name="bulan">
                                        <option value="0">Bulan</option>
                                        <option value="1" {{($input['bulan']==1)?"selected":""}}>Januari</option>
                                        <option value="2" {{($input['bulan']==2)?"selected":""}}>Februari</option>
                                        <option value="3" {{($input['bulan']==3)?"selected":""}}>Maret</option>
                                        <option value="4" {{($input['bulan']==4)?"selected":""}}>April</option>
                                        <option value="5" {{($input['bulan']==5)?"selected":""}}>Mei</option>
                                        <option value="6" {{($input['bulan']==6)?"selected":""}}>Juni</option>
                                        <option value="7" {{($input['bulan']==7)?"selected":""}}>Juli</option>
                                        <option value="8" {{($input['bulan']==8)?"selected":""}}>Agustus</option>
                                        <option value="9" {{($input['bulan']==9)?"selected":""}}>September</option>
                                        <option value="10" {{($input['bulan']==10)?"selected":""}}>Oktober</option>
                                        <option value="11" {{($input['bulan']==11)?"selected":""}}>November</option>
                                        <option value="12" {{($input['bulan']==12)?"selected":""}}>Desember</option>
                                      </select>
                                  </div>
                                  <div class="form-group col-md-4">
                                      <input type="text" value=@if($input!=null) {{$input['tahun'] or ""}} @else "" @endif class="form-control" name='tahun' placeholder="Tahun"  data-validation="number" data-validation-allowing="range[1945;2016]">
                                  </div>              
                                </div>
                                <div class="text-center">  
                                <button type="submit" class="btn btn-default">Daftar</button>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('customJS')
<script src={{url("js/jquery.form-validator.min.js")}}></script>
<script src={{url("js/daftar.js")}}></script>
@stop