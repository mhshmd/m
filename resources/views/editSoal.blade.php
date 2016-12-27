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
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-sign-in"></span> Edit Soal </h3>
                    </div>
                    <div class="panel-body">
                                <table id="myTable" class="table table-striped tablesorter">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Soal</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>Ans</th>
                                        <th>Pic</th>
                                        <th>Subject</th>
                                        <th>SubID</th>
                                        <th>
                                            <div class="text-center">
                                                  <input id="checkAll" type="checkbox" id="all">
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Soal</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>Ans</th>
                                        <th>Pic</th>
                                        <th>Subject</th>
                                        <th>SubID</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                <tbody id="fbody" class="table-hover">
                                @foreach($quests as $key=>$quest)
                                  <tr id="{{$quest->questId}}">
                                      <td id="{{$quest->questId}}questId" class="questid">{{$quest->questId}}</td>
                                      <td id="{{$quest->text}}text" class="text">{{$quest->text}}</td>
                                      <td id="{{$quest->a}}a" class="a">{{$quest->a}}</td>
                                      <td id="{{$quest->b}}b" class="b">{{$quest->b}}</td>
                                      <td id="{{$quest->c}}c" class="c">{{$quest->c}}</td>
                                      <td id="{{$quest->d}}d" class="d">{{$quest->d}}</td>
                                      <td id="{{$quest->e}}e" class="e">{{$quest->e}}</td>
                                      <td id="{{$quest->answer}}answer" class="answer">{{$quest->answer}}</td>
                                      <td id="{{$quest->qPictPath}}qPictPath" class="qPictPath">{{$quest->qPictPath}}</td>
                                      <td id="{{$quest->subjectId}}subjectId" class="subjectId">{{$quest->subjectId}}</td>
                                      <td id="{{$quest->subMaterialId}}subMaterialId" class="subMaterialId">{{$quest->subMaterialId}}</td>
                                      <td width="5px" class="none">
                                        <div class="text-center">
                                          <input class="cekbox" type="checkbox" id="{{$quest->questid}}checkbox">
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
    <script type="text/javascript">
        $('table td').on('change', function(evt, newValue) {
            // do something with the new cell value
            var kode1 = $(this).closest('tr').attr('id');
            var targetUbah = $(this).attr('class');
            if(targetUbah=="none") return;
            var token = "{!! csrf_token() !!}";
            $.post("{{route('editSoal')}}",
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
@stop