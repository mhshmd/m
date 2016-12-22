@extends('layouts.layout')

@section('title') Insert Quota @stop

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
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-sign-in"></span> Insert Quota </h3>
                    </div>
                    <div class="panel-body">
                            <form role="form" action={{route('insertKuota')}} method="POST">
                                {{ csrf_field() }}  
                                <div>
                                    <div class="form-group col-md-12" style="padding:0">
                                        <select class='form-control subMaterialInit' name="operator">
                                          @foreach($operator as $key=>$op)
                                            <option value="{{$op->id}}" {{($idOperator==$key+1)?"selected":""}}>{{$op->name}}</option>
                                          @endforeach
                                        </select>
                                    </div>                                 
                                    <div class="form-group">
                                        <input type="text" id="kode" class="form-control" name="kode" placeholder="Kode" autofocus>
                                    </div>                                 
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="name" placeholder="Nama Kuota" value="{{$name}}">
                                    </div> 
                                    <div class="form-group">
                                        <input type="text" class="form-control dasar" name="hargaDasar" placeholder="Harga Dasar">
                                    </div> 
                                    <div class="form-group">
                                        <input type="text" class="form-control jual" name="hargaJual" placeholder="Harga Jual">
                                    </div> 
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="margin" name="margin" placeholder="Margin">
                                    </div> 
                                    <div class="form-group">
                                        <label class="control-label">Deskripsi</label>
                                        <textarea class="form-control" name='deskripsi' rows="3" placeholder="Deskripsi">{{$deskripsi}}</textarea>
                                    </div> 
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="gb3g" placeholder="Kuota 3G Full">
                                    </div> 
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="gb4g" placeholder="Kuota 4G Full">
                                    </div> 
                                    <div class="form-group">
                                        <label class="control-label">Tidak bagi-bagi?</label>
                                        <input type="text" class="form-control" name="is24jam" placeholder="Tidak bagi-bagi?" value="{{$is24jam}}">
                                    </div>  
                                    <div class="form-group">
                                        <label class="control-label">Promo?</label>
                                        <input type="text" class="form-control" name="isPromo" placeholder="Promo?" value="{{$isPromo}}">
                                    </div> 
                                    <div class="form-group">
                                        <label class="control-label">Tersedia?</label>
                                        <input type="text" class="form-control" name="isAvailable" placeholder="Tersedia" value="{{$isAvailable}}">
                                    </div> 
                                    <div class="form-group">
                                        <label class="control-label">Jumlah Hari Berlaku</label>
                                        <input type="text" class="form-control" name="days" placeholder="Jumlah Hari Berlaku" value="{{$days}}">
                                    </div>                                   
                                </div>
                                <div class="text-center">  
                                <button type="submit" class="btn btn-default">Tambah</button>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('customJS')
    <script>
        $(".jual").keyup(function(){
                var jual = $(this).val();
                var dasar = $(".dasar").val();
                var total = jual-dasar;
                $("#margin").val(total);
            });
    </script>
    <script type="text/javascript">
      $("#kode").bind('keyup', function (e) {
          if (e.which >= 97 && e.which <= 122) {
              var newKey = e.which - 32;
              // I have tried setting those
              e.keyCode = newKey;
              e.charCode = newKey;
          }

          $("#kode").val(($("#kode").val()).toUpperCase());
      });
    </script>
@stop