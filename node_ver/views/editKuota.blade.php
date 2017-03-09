@extends('layouts.layout')

@section('title') Edit Quota @stop

@section('customCSS')
    <link rel="stylesheet" href={{url("css/tablesorter/style.css")}} type="text/css">
    <link rel="stylesheet" href={{url("css/jquery.dataTables.min.css")}} type="text/css">
@stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')
    <div >
        <div class="row" style="margin-top:10pt">
            {!!$warning or ""!!}
            <div class="col-md-12">
                <div class="panel panel-default" style="margin: 0; font-size: 8pt">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-sign-in"></span> Edit Kuota </h3>
                    </div>
                    <div class="panel-body">  
                                <div class="text-center inline">  
                                    <button id="g" class="btn btn-warning status">Gangguan</button> 
                                    <button id="k" class="btn btn-danger status">Kosong</button>
                                    <button id="a" class="btn btn-success btn-lg status">Available</button>
                                </div>
                                <table id="myTable" class="table table-striped tablesorter">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Operator</th>
                                        <th>Harga Dasar</th>
                                        <th>Harga Jual</th>
                                        <th>Margin</th>
                                        <th>Tersedia?</th>
                                        <th>Promo?</th>
                                        <th>Deskripsi</th>
                                        <th>Kuota 3G</th>
                                        <th>Kuota 4G</th>
                                        <th>Masa Berlaku (hari)</th>
                                        <th>24Jam?</th>
                                        <th>
                                            <div class="text-center">
                                                  <input id="checkAll" type="checkbox" id="all">
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Operator</th>
                                        <th>Harga Dasar</th>
                                        <th>Harga Jual</th>
                                        <th>Margin</th>
                                        <th>Tersedia?</th>
                                        <th>Promo?</th>
                                        <th>Deskripsi</th>
                                        <th>Kuota 3G</th>
                                        <th>Kuota 4G</th>
                                        <th>Masa Berlaku (hari)</th>
                                        <th>24Jam?</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody id="fbody" class="table-hover">
                                @foreach($allKuota as $key=>$kuota)
                                  <tr id="{{$kuota->kode}}">
                                      <td id="{{$kuota->kode}}kode" class="kode">{{$kuota->kode}}</td>
                                      <td id="{{$kuota->kode}}name" class="name">{{$kuota->name}}</td>
                                      <td id="{{$kuota->kode}}operator" class="operator">{{$kuota->operator}}</td>
                                      <td id="{{$kuota->kode}}hargaDasar" class="hargaDasar">{{$kuota->hargaDasar}}</td>
                                      <td id="{{$kuota->kode}}hargaJual" class="hargaJual">{{$kuota->hargaJual}}</td>
                                      <td id="{{$kuota->kode}}margin" class="margin">{{$kuota->margin}}</td>
                                      <td id="{{$kuota->kode}}isAvailable" class="isAvailable">{{$kuota->isAvailable}}</td>
                                      <td id="{{$kuota->kode}}isPromo" class="isPromo">{{$kuota->isPromo}}</td>
                                      <td id="{{$kuota->kode}}deskripsi" class="deskripsi">{{$kuota->deskripsi}}</td>
                                      <td id="{{$kuota->kode}}gb3g" class="gb3g">{{$kuota->gb3g}}</td>
                                      <td id="{{$kuota->kode}}gb4g" class="gb4g">{{$kuota->gb4g}}</td>
                                      <td id="{{$kuota->kode}}days" class="days">{{$kuota->days}}</td>
                                      <td id="{{$kuota->kode}}is24jam" class="is24jam">{{$kuota->is24jam}}</td>
                                      <td width="5px" class="none">
                                        <div class="text-center">
                                          <input class="cekbox" type="checkbox" id="{{$kuota->kode}}checkbox">
                                        </div>
                                      </td>
                                  </tr>
                                @endforeach
                                </tbody></thead>
                              </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('customJS')
    <script type="text/javascript" src="{{url('js/jquery.tablesorter.min.js')}}"></script>
    <script type="text/javascript" src="{{url('js/mindmup-editabletable.js')}}"></script>
    <script type="text/javascript" src="{{url('js/jquery.toaster.js')}}"></script>
    <script type="text/javascript" src="{{url('js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript">
        $('#myTable').editableTableWidget();
        $('#myTable').editableTableWidget({editor: $('<textarea>')});
        $('#myTable').editableTableWidget({
            cloneProperties: ['background', 'border', 'outline']
        });
    </script>
    <script>
        $(".hargaJual").on('change', function(){
                var kode1 = $(this).closest('tr').attr('id');
                var jual = $(this).html();
                var dasar = $("#"+kode1+"hargaDasar").html();
                var total = jual-dasar;
                $("#"+kode1+"margin").html(total);
                var token = "{!! csrf_token() !!}";
                $.post("{{route('editKuota')}}",
                {
                  _token : token,
                  kode : kode1,
                  kolom : "margin",
                  nilai : total,
                },
                function(data, status){
                    if(status=="success"){
                        $.toaster({ priority : 'success', title : 'Sukses', message : kode1+' sudah diubah'});
                    } else{
                        $.toaster({ priority : 'warning', title : 'Gagal', message : kode1+' belum diubah'});                }
                });
        });
    </script>
    <script>
        $(".hargaDasar").on('change', function(){
                var kode1 = $(this).closest('tr').attr('id');
                var dasar = $(this).html();
                var jual = $("#"+kode1+"hargaJual").html();
                var total = jual-dasar;
                $("#"+kode1+"margin").html(total);
                var token = "{!! csrf_token() !!}";
                $.post("{{route('editKuota')}}",
                {
                  _token : token,
                  kode : kode1,
                  kolom : "margin",
                  nilai : total,
                },
                function(data, status){
                    if(status=="success"){
                        $.toaster({ priority : 'success', title : 'Sukses', message : kode1+' sudah diubah'});
                    } else{
                        $.toaster({ priority : 'warning', title : 'Gagal', message : kode1+' belum diubah'});                }
                });
        });
    </script>
    <script type="text/javascript">
      $(".kode").bind('keyup', function (e) {
          if (e.which >= 97 && e.which <= 122) {
              var newKey = e.which - 32;
              // I have tried setting those
              e.keyCode = newKey;
              e.charCode = newKey;
          }

          $("#kode").val(($("#kode").val()).toUpperCase());
      });
    </script>  
    <script type="text/javascript">
        $('table td').on('change', function(evt, newValue) {
            // do something with the new cell value
            var kode1 = $(this).closest('tr').attr('id');
            var targetUbah = $(this).attr('class');
            if(targetUbah=="none") return;
            var token = "{!! csrf_token() !!}";
            $.post("{{route('editKuota')}}",
            {
              _token : token,
              kode : kode1,
              kolom : targetUbah,
              nilai : newValue,
            },
            function(data, status){
                if(status=="success"){
                    $.toaster({ priority : 'success', title : 'Sukses', message : kode1+' sudah diubah'});
                } else{
                    $.toaster({ priority : 'warning', title : 'Gagal', message : kode1+' belum diubah'});                }
            });
            // alert($(this).attr('class'));
            // if (....) { 
            //     return false; // reject change
            // }
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Setup - add a text input to each footer cell
            $('#myTable tfoot th').each( function () {
                var title = $(this).text();
                $(this).html( '<input type="text" style="padding:0;margin:0;" class="form-control" placeholder="'+title+'" />' );
            } );
         
            // DataTable
            var table = $('#myTable').DataTable({"paging":true,"ordering": true,"info":false, "iDisplayLength": 25});         
            // Apply the search
            table.columns().every( function () {
                var that = this;
         
                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                            .search( this.value )
                            .draw();
                    }
                } );
            } );
        } );
    </script>
    <script type="text/javascript">
        $('#checkAll').on('click', function() {
            if ($(this).is(':checked')) {
                $('.cekbox').prop('checked', true);
            } else{
                $('.cekbox').prop('checked', false);
            }
        });
    </script>
    <script type="text/javascript">
        $('.status').on('click', function() {
                var status = $(this).attr('id');
                $(".cekbox:checkbox:checked").each(function(){
                    if(status=="g"){
                        $(this).closest('tr').find('.isAvailable').html(2);
                    } else if(status=="k"){
                        $(this).closest('tr').find('.isAvailable').html(0);
                    } else{
                        $(this).closest('tr').find('.isAvailable').html(1);
                    }                    
                    var kode1 = $(this).closest('tr').attr('id');
                    var value = $(this).closest('tr').find('.isAvailable').html();
                    var token = "{!! csrf_token() !!}";
                    $.post("{{route('isAvailable')}}",
                    {
                      _token : token,
                      kode : kode1,
                      nilai : value,
                    },
                    function(data, status){
                        if(status=="success"){
                            $.toaster({ priority : 'success', title : 'Sukses', message : 'isAvailable sudah diubah'});
                        } else{
                            $.toaster({ priority : 'warning', title : 'Gagal', message : 'isAvailable belum diubah'});                }
                    });
                });                
        });
    </script>
@stop