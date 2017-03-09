@extends('layouts.layout')

@section('title') Setting - toSTIS.net @stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')
    <div class="container">
        {!!session('warning')!!}
        <div class="row" style="margin-top:10pt">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-lock"></span> Setting</h3>
                    </div>
                    <div class="panel-body">
                            <form role="form" action={{route('setting')}} method="POST">
                                {{ csrf_field() }}  
                                <div class="form-group">
                                    <input value="{{$name}}" type="text" class="form-control" name='name' placeholder="Nama Lengkap" data-validation="required">
                                </div>
                                <div class="form-group">
                                    <input value="{{$email}}" class="form-control" data-validation="email" placeholder="Email" name="email" data-validation="email" value="">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="password" placeholder="Password">
                                </div>  
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="2" {{($status==2)?'checked="checked"':""}}>Mahasiswa</label>
                                </div>
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="1" {{($status==1)?'checked="checked"':""}}>SMA</label>
                                </div>  
                                <div class="radio-inline">
                                  <label><input type="radio" name="status" value="3" {{($status==3)?'checked="checked"':""}}>Lainnya</label>
                                </div>
                                <div class="form-group">
                                      <select class='form-control' id="provinceId" name="provinceId">
                                        <option value="0" selected>Provinsi</option>
                                        @foreach ($provinces as $key=>$province)
                                            <option value="{{$province->provinceId}}"  {{($province->provinceId==$provinceId)?"selected":""}}>{{$province->name}}</option>
                                        @endforeach
                                      </select>
                                </div>
                                <div class="row" style="padding:0 14px">
                                  <label>Tanggal lahir</label><br>
                                  <div class="form-group col-md-3">
                                      <input value="{{$tanggal}}" type="text" class="form-control" name='tanggal' placeholder="Tgl"  data-validation="number" data-validation-allowing="range[1;31]">
                                  </div>
                                  <div class="form-group col-md-5" style="padding:0 0">
                                      <select class='form-control subMaterialInit' id="bulan" name="bulan">
                                        <option value="0">Bulan</option>
                                        <option value="1" {{($bulan==1)?"selected":""}}>Januari</option>
                                        <option value="2" {{($bulan==2)?"selected":""}}>Februari</option>
                                        <option value="3" {{($bulan==3)?"selected":""}}>Maret</option>
                                        <option value="4" {{($bulan==4)?"selected":""}}>April</option>
                                        <option value="5" {{($bulan==5)?"selected":""}}>Mei</option>
                                        <option value="6" {{($bulan==6)?"selected":""}}>Juni</option>
                                        <option value="7" {{($bulan==7)?"selected":""}}>Juli</option>
                                        <option value="8" {{($bulan==8)?"selected":""}}>Agustus</option>
                                        <option value="9" {{($bulan==9)?"selected":""}}>September</option>
                                        <option value="10" {{($bulan==10)?"selected":""}}>Oktober</option>
                                        <option value="11" {{($bulan==11)?"selected":""}}>November</option>
                                        <option value="12" {{($bulan==12)?"selected":""}}>Desember</option>
                                      </select>
                                  </div>
                                  <div class="form-group col-md-4">
                                      <input value="{{$tahun}}" type="text" class="form-control" name='tahun' placeholder="Tahun"  data-validation="number" data-validation-allowing="range[1945;2016]">
                                  </div>              
                                </div>
                                <div class="text-center">  
                                <button type="submit" class="btn btn-default">Simpan</button>
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

    <script>
      $.validate({
        modules : 'security', 
          onModulesLoaded : function() {
            var optionalConfig = {
              fontSize: '12pt',
              padding: '4px',
              bad : 'Sangat lemah',
              weak : 'Lemah',
              good : 'Baik',
              strong : 'Sangat kuat'
            };

            $('input[name="password_confirmation"]').displayPasswordStrength(optionalConfig);
          }
      });
    </script>
@stop