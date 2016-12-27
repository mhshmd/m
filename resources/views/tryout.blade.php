@extends('layouts.layout')

@section('title') {{($subject==1)?"MATEMATIKA":"BAHASA INGGRIS"}} - toSTIS.net @stop

@section('customStyleUp')
    <style type="text/css">
        ol div, .cover {padding: 14% 14% 14% 12%; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
            margin: 1% 0; background-color:{{($subject==1)?"white":"#fcc4f7"}};}
        .soal {font-family:{{($subject==1)?"Times New Roman":"Cambria"}}; font-size: {{($subject==1)?"17px":"17px"}};}
        .pagenumber {font-family:"Arial"; font-size: 13px; margin-top: 30pt;}
        label {font-weight: normal !important;}
        .sizeSet {max-width: 500px; max-height: 500px;}
        .check {color:#1bd622;}
        .cross {color:red;}
        .sembarang {color:orange;}
        table, th, td {border: 1px solid black;border-collapse: collapse;}
        img {display: block; margin: 0 auto;}
        .tombolto{margin: 7px 0;}
    </style>
@stop

@section('customCSS')
    <link rel="stylesheet" href={{url("css/countdown.demo.css")}} type="text/css">
@stop

@section('customJSup')
    <script type="text/x-mathjax-config">
        MathJax.Hub.Config({showMathMenu: false, tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}, messageStyle: 'none', CommonHTML: { linebreaks: { automatic: true } }, "HTML-CSS": { linebreaks: { automatic: true } },
         SVG: { linebreaks: { automatic: true } }});
        if (MathJax.Hub.Browser.isMSIE && (document.documentMode||0) < 9) {
          MathJax.Hub.Register.StartupHook("End Config",function () {
            var settings = MathJax.Hub.config.menuSettings;
            if (!settings.renderer) {settings.renderer = "HTML-CSS"}
          });
        }
    </script>
    <script type="text/javascript" async
          src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_CHTML">
    </script>
@stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')

@if($time=="noTime")
    <div class="container" style="margin-top:10px">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-file-text-o"></span> HASIL UJIAN</h3>
                    </div>
                    <div class="panel-body text-center">
                        <div class="row">
                            <div class="col-lg-6">
                                <div>
                                    <canvas id="stackedBar" height="438px"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div id="canvas-holder" style="width:100%">
                                    <canvas id="chart-area" />
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:50px">
                                <div  class="center-block" style="width:50%">
                                    <canvas id="scoreBar" height="438px"/>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    <div class="container-fluid" style="background-color:grey;" id="form">
        <div class="row">
            <div class="col-md-8 col-md-offset-2 text-justify soal"  style="padding:0;">
                <div class="cover">
                    <div class="text-center">
                        <h3>SEKOLAH TINGGI ILMU STATISTIK</h3>
                        <h3>BADAN PUSAT STATISTIK</h3>
                        <br>
                        <h4>SOAL UJIAN MASUK PROGRAM D-III DAN D-IV</h2>
                        <H4>TAHUN AKADEMIK 2017/2018</H2>
                        <h4>SABTU, 13 MEI 2017</h2>
                        <br>
                        <h2>{{($subject==1)?"MATEMATIKA":"BAHASA INGGRIS"}}</h4>
                        <h4>{{($subject==1)?"90":"60"}} MENIT</h2>
                    </div><br>
                    <p>Petunjuk:</p>
                    <ul>
                        <li>
                            Di bawah setiap soal dicantumkan 5 kemungkinan jawaban yang berisi kode A, B, C,
                            D, atau E. Gunakan pensil 2B untuk menghitamkan lingkaran yang berisi kode
                            jawaban yang Saudara anggap benar pada Lembar Jawaban Komputer (LJK).
                        </li> 
                         <li>
                            Contoh pengisian Lembar Jawaban Komputer (LJK).
                            <ol>
                                <li>
                                    {sengaja dikosongkan}
                                </li>
                                <li>
                                    {sengaja dikosongkan}
                                </li>
                            </ol>
                        </li> 
                         <li>
                            <strong>Nilai jawaban tiap soal adalah:<br>
                                2 untuk jawaban benar,<br>
                                0 untuk tidak ada jawaban,<br>
                                -1 untuk jawaban yang salah atau jawaban lebih dari satu.
                            </strong>
                        </li> 
                         <li>
                            Lembar jawaban tidak boleh kotor atau terlipat
                        </li> 
                         <li>
                            Hanya lembar jawaban yang dikumpulkan, sedangkan soal bisa dibawa pulang
                        </li>                                            
                    </ul>
                </div>
                <form action={{route('periksaTO')}} method="POST">
                    {{ csrf_field() }}
                    <input type="text" name="tryOutId" value="{{$tryOutId}}" style="display: none;">
                    <input type="text" name="bagian" value="{{$bagian}}" style="display: none;">
                    <input type="text" name="userId" value="{{$userId}}" style="display: none;">
                    <ol style="padding:0;">
                        @foreach ($quests as $key=>$quest)
                            @if($key%4==0)
                                <div> 
                            @endif                           
                                    <span id="{{$quest->questId}}">{!!$quest->questIntro!!}</span>
                                    <li class="quest intro{{$quest->questIntroId}}">
                                        {!!$quest->text!!}
                                        @if($quest->qPictPath!==null)
                                            <br>
                                            <img SRC="data:image/jpg;base64,<?php echo base64_encode(file_get_contents("upload/qPictPath/".$quest->qPictPath)) ?>" class="sizeSet img-responsive">
                                        @endif
                                        <ol type="A">
                                            @if($time!="noTime")
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="1" {{($quest->selected==1)?"checked='checked'":""}}> {!!$quest->a!!}</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="2" {{($quest->selected==2)?"checked='checked'":""}}> {!!$quest->b!!}</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="3" {{($quest->selected==3)?"checked='checked'":""}}> {!!$quest->c!!}</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="4" {{($quest->selected==4)?"checked='checked'":""}}> {!!$quest->d!!}</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="5" {{($quest->selected==5)?"checked='checked'":""}}> {!!$quest->e!!}</label></li>
                                                <label style="color:#c1bbc1"><input type="radio" name="{{$quest->questId}}" class="quest" value="0" {{($quest->selected==0)?"checked='checked'":""}}> Tidak menjawab
                                            @else
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="1" {{($quest->selected==1)?"checked='checked'":""}}> {!!$quest->a!!} @if($quest->answer==1)<span class="check" title="jawaban benar"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==1) @if($quest->answer==0) <span class="sembarang" title="isi sembarang"><i class="fa fa-warning fa-fw"></i></span> @else <span class="cross" title="jawaban salah"><i class="fa fa-times fa-fw"></i></span> @endif @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="2" {{($quest->selected==2)?"checked='checked'":""}}> {!!$quest->b!!} @if($quest->answer==2)<span class="check" title="jawaban benar"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==2) @if($quest->answer==0) <span class="sembarang" title="isi sembarang"><i class="fa fa-warning fa-fw"></i></span> @else <span class="cross" title="jawaban salah"><i class="fa fa-times fa-fw"></i></span> @endif @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="3" {{($quest->selected==3)?"checked='checked'":""}}> {!!$quest->c!!} @if($quest->answer==3)<span class="check" title="jawaban benar"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==3) @if($quest->answer==0) <span class="sembarang" title="isi sembarang"><i class="fa fa-warning fa-fw"></i></span> @else <span class="cross" title="jawaban salah"><i class="fa fa-times fa-fw"></i></span> @endif @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="4" {{($quest->selected==4)?"checked='checked'":""}}> {!!$quest->d!!} @if($quest->answer==4)<span class="check" title="jawaban benar"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==4) @if($quest->answer==0) <span class="sembarang" title="isi sembarang"><i class="fa fa-warning fa-fw"></i></span> @else <span class="cross" title="jawaban salah"><i class="fa fa-times fa-fw"></i></span> @endif @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="5" {{($quest->selected==5)?"checked='checked'":""}}> {!!$quest->e!!} @if($quest->answer==5)<span class="check" title="jawaban benar"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==5) @if($quest->answer==0) <span class="sembarang" title="isi sembarang"><i class="fa fa-warning fa-fw"></i></span> @else <span class="cross" title="jawaban salah"><i class="fa fa-times fa-fw"></i></span> @endif @endif</label></li>
                                                <label style="color:#c1bbc1"><input type="radio" name="{{$quest->questId}}" class="quest" value="0" {{($quest->selected==0)?"checked='checked'":""}}> Tidak menjawab
                                            @endif
                                        </ol> 
                                        <br>
                                    </li>
                            @if($key%4==3)
                                <p class="text-right pagenumber">Halaman {{floor(($key+1)/4)}} dari {{floor((sizeof($quests)+1)/4)}} halaman</p>
                                </div>
                            @else
                                @if(sizeof($quests)<4 && $key==(sizeof($quests)-1))
                                    <p class="text-right pagenumber">Halaman 1 dari 1 halaman</p>
                                    </div>
                                @elseif($key==(sizeof($quests)-1))
                                    <p class="text-right pagenumber">Halaman {{ceil((sizeof($quests)+1)/4)}} dari {{floor((sizeof($quests)+1)/4)}} halaman</p>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </ol>
                    <button type="submit" class="kumpulTO" style="display: none;"></button>
                </form>
            </div>

            <!--Timer-->
            @if($time!="noTime")
                <div class="col-md-2">
                    <div class="row timer" style="margin : 0;">
                        <h1 id="timer" class="alt-2" style="position : fixed; top : 3%; right: 1.5%; width: 100px">0h {{$time}}m {{$secon}}s</h1>
                    </div>
                    <div style="width: 200px; position : fixed; top : 67%; right: 3%;padding-left: 155px">
                        <div class="row">
                            <a class="btn btn-default btn-outline hideTimer btn-circle btn-lg tombolto" data-placement="left" title="Sembunyikan waktu"><i class="fa fa-clock-o"></i>
                            </a>
                        </div>
                        <div class="row">
                            <a class="btn btn-info btn-circle btn-lg btn-outline tombolto" data-target="#finishModal" data-toggle="modal" data-placement="left" title="Kumpul hasil ujian">
                                <i class="fa fa-check"></i>
                            </a>
                        </div>
                        <div class="row">
                            <a class="btn btn-default btn-outline btn-circle btn-lg tombolto" href="#" id="back-to-top" data-placement="left" title="Scroll ke atas">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                        </div>
                    </div>

                        <!-- Modal -->
                        <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">Selesai</h4>
                              </div>
                              <div class="modal-body">
                                <p>Selesai dan Lihat Hasil Ujian?</p>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-outline" data-dismiss="modal">Kembali</button>
                                <button type="button" class="btn btn-info confirmKumpulTO btn-outline" style="width: 98px" data-dismiss="modal">YA</button>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-2">
                    <div style="width: 200px; position : fixed; top : 77%; right: 3%;padding-left: 155px">
                        <div class="row">
                            <a class="btn btn-default btn-outline hideTimer btn-circle btn-lg tombolto" data-placement="left" href="{{url('/profile')}}" title="Kembali ke Profil"><i class="fa fa-mail-reply"></i>
                            </a>
                        </div>
                        <div class="row">
                            <a class="btn btn-default btn-outline btn-circle btn-lg tombolto" href="#" id="back-to-top" data-placement="left" title="Scroll ke atas">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>

    <div class="container" style="display: none;" id="process">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-pencil"></span> Sedang Memeriksa...</h3>
                    </div>
                    <div class="panel-body">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('customJS')
    <script type="text/javascript" src={{url("js/jquery.countdown.min.js")}}></script>

    @if($time!="noTime")
        <script>
        $(document).ready(function(){
            $(".quest").change(function(){
                var questId1 = $(this).attr('name');
                var selected1 = $(this).attr('value');
                var token = "{!! csrf_token() !!}";
                $.post("{{route('realtimesubmit')}}",
                {
                  _token : token,
                  userId : "{{$userId}}",
                  tryOutId : "{{$tryOutId}}",
                  bagian : "{{$bagian}}",
                  questId : questId1,
                  selected : selected1,
                });
            });
        });
        </script>  
        <script type="text/javascript" src={{url("js/to1.js")}}></script>
    @endif
        
        @if(sizeof($questIntro)>0)
            @foreach ($questIntro as $key=>$intro)
                <script>
                $(document).ready(function(){
                    var jumlah = $(".intro{{$intro['questIntroId']}}").length;//hitung jumlah ber id 9 and catat nomor terendah
                    @if($subject==1)
                        $("#{{$intro['questId']}}").find('span').text({{$intro['nomor']}}+"-"+({{$intro['nomor']}}+jumlah-1));
                    @else
                        $("#{{$intro['questId']}}").find('span').text("{{$key+1}}"+" (For questions "+{{$intro['nomor']}}+" - "+({{$intro['nomor']}}+jumlah-1+")                                 "));
                    @endif                
                });
                </script>
            @endforeach
        @endif
    @if($time=="noTime")
        <script src={{url("js/Chart.bundle.min.js")}}></script>
        <script src={{url("js/utils.js")}}></script>

        <script type="text/javascript">
            var matematika = {
                labels: ["{{$tryOutName}}"],
                datasets: [{
                    label: 'Benar',
                    backgroundColor: window.chartColors.green,
                    data: [
                        {{$true}}
                    ]
                }, {
                    label: 'Salah',
                    backgroundColor: window.chartColors.red,
                    data: [
                        {{$false}}
                    ]
                }, {
                    label: 'Tidak dijawab',
                    backgroundColor: window.chartColors.blue,
                    data: [
                        {{$nyerah}}
                    ]
                }, {
                    label: 'Isi sembarang',
                    backgroundColor: window.chartColors.black,
                    data: [
                        {{$ngarang}}
                    ]
                }]

            };

            var ctx = document.getElementById("stackedBar").getContext("2d");
            window.myBar = new Chart(ctx, {
                type: 'bar',
                data: matematika,
                options: {
                    title:{
                        display:true,
                        text:"{{$tryOutName}}"
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            stacked: true,
                            barThickness: 75,
                        }],
                        yAxes: [{
                            stacked: true,
                        }]
                    }
                }
            });
        </script>

        <script>
            var config = {
                type: 'pie',
                data: {
                    datasets: [{
                        data: [
                            Math.round({{$true}}*100/60),
                            Math.round({{$false}}*100/60),
                            Math.round({{$nyerah}}*100/60),
                            Math.round({{$ngarang}}*100/60),
                        ],
                        backgroundColor: [
                            window.chartColors.green,
                            window.chartColors.red,
                            window.chartColors.blue,
                            window.chartColors.black,
                        ],
                        label: 'Dataset 1'
                    }],
                    labels: [
                        "Persen Benar",
                        "Persen Salah",
                        "Persen Tidak dijawab",
                        "Persen Isi sembarang"
                    ]
                },
                options: {
                    title:{
                        display:true,
                        text:"PASSING GRADE (dalam persen)"
                    },
                    responsive: true
                }
            };

            var ctx = document.getElementById("chart-area").getContext("2d");
            window.myPie = new Chart(ctx, config);
        </script>

        <script type="text/javascript">
            var color = Chart.helpers.color;
            var matematika = {
                labels: ["{{$tryOutName}}"],
                datasets: [{
                    label: 'Skor ',
                    backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                    data: [
                        {{$true*2-$false}}
                    ]
                }]

            };

            var ctx = document.getElementById("scoreBar").getContext("2d");
            window.myBar = new Chart(ctx, {
                type: 'bar',
                data: matematika,
                options: {
                    title:{
                        display:true,
                        text:"SKOR {{$tryOutName}} (BENAR*2 - SALAH)"
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            barThickness: 75,
                        }],
                        yAxes: [{
                            ticks: {
                                stepSize: 5
                            }
                        }]
                    }
                }
            });
        </script>
        <script type="text/javascript">
            $('#back-to-top').click(function(){
                    $("body").animate({scrollTop:0});
                });
            $(window).scroll(function() {
                if ($(this).scrollTop() > 1000) {
                    $('#back-to-top').fadeIn(1000);
                } else {
                    $('#back-to-top').fadeOut(1000);
                }
            });
            $(function () {
              $('[data-placement]').tooltip()
            })
        </script>
    @endif
@stop