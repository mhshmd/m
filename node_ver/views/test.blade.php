@extends('layouts.layout')

@section('title') {{($subject==1)?"MATEMATIKA":"BAHASA INGGRIS"}} - toSTIS.net @stop

@section('customCSS')
    <link rel="stylesheet" href={{url("css/countdown.demo.css")}} type="text/css">
@stop

@section('customStyle')
    ol div, .cover {padding: 14% 14% 14% 12%; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        margin: 1% 0; background-color:{{($subject==1)?"white":"#fcc4f7"}};}
    .soal {font-family:{{($subject==1)?"Times New Roman":"Cambria"}}; font-size: {{($subject==1)?"16px":"17px"}};}
    .pagenumber {font-family:"Arial"; font-size: 13px; margin-top: 30pt;}
    label {font-weight: normal !important;}
    .sizeSet {max-width: 500px; max-height: 500px;}
    .check {color:#1bd622;}
    .cross {color:red;}
    .sembarang {color:yellow;}
    table, th, td {border: 1px solid black;border-collapse: collapse;}
    img.center {
        display: block;
        margin: 0 auto;
    }
@stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')
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
                                Lembar jawaan tidak boleh kotor atau terlipat
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
                                                <img class="sizeSet center img-responsive" src="{!!url('upload/qPictPath').'/'.$quest->qPictPath!!}">
                                            @endif
                                            <ol type="A">
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="1" {{($quest->selected==1)?"checked='checked'":""}}> {!!$quest->a!!} @if($quest->answer==1)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==1) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="2" {{($quest->selected==2)?"checked='checked'":""}}> {!!$quest->b!!} @if($quest->answer==2)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==2) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="3" {{($quest->selected==3)?"checked='checked'":""}}> {!!$quest->c!!} @if($quest->answer==3)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==3) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="4" {{($quest->selected==4)?"checked='checked'":""}}> {!!$quest->d!!} @if($quest->answer==4)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==4) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                                <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="5" {{($quest->selected==5)?"checked='checked'":""}}> {!!$quest->e!!} @if($quest->answer==5)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==5) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                                <label style="color:#c1bbc1"><input type="radio" name="{{$quest->questId}}" class="quest" value="0" {{($quest->selected==0)?"checked='checked'":""}}> Tidak menjawab
                                            </ol> 
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
                        <div class="row timer" style="padding : 0 50px;">
                            <h1 class="alt-2" style="position : fixed; top : -30px;">0h {{$time}}m {{$secon}}s</h1>
                        </div>
                        <div class="row" style="padding : 0 17%;">
                            <a class="btn btn-default btn-outline hideTimer" style="position : fixed; top : 75%;" title="Sembunyikan jam">
                                Show/Hide Timer
                            </a>
                        </div>
                        <div class="row" style="padding : 0 17%;">
                            <a class="btn btn-default btn-outline" style="position : fixed; top : 82%;"   href="#" class="back-to-top" title="Scroll ke atas">
                                <span class="fa fa-arrow-up"></span> Scroll Up</i>
                            </a>
                        </div>
                        <div class="row" style="padding : 0 17%;">
                            <a class="btn btn-info btn-outline" style="position : fixed; top : 90%;"  data-target="#finishModal" data-toggle="modal" data-placement="top" title="Kumpul hasil ujian.">
                                <span class="fa fa-check"></span> SELESAI</i>
                            </a>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">SELESAI</h4>
                              </div>
                              <div class="modal-body">
                                <p>Selesai dan Lihat Hasil Ujian?</p>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">BATAL</button>
                                <button type="button" class="btn btn-primary confirmKumpulTO" data-dismiss="modal">YA</button>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-2">
                        <div class="row" style="padding : 0 35%;">
                            <a class="btn btn-default" style="position : fixed; top : 90%;"   href="#" class="back-to-top" title="Scroll ke atas">
                                <span class="fa fa-arrow-up"></span> Up</i>
                            </a>
                        </div>
                    </div>
                @endif
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
    <script type="text/javascript" src={{url("js/jquery.countdown.js")}}></script>

    <script type="text/x-mathjax-config">
          MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});
    </script>
    <script type="text/javascript" async
          src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_CHTML">
    </script>

    <script type="text/javascript">
    var amountScrolled = 300;

    $(window).scroll(function() {
        if ( $(window).scrollTop() > amountScrolled ) {
            $('a.back-to-top').fadeIn('slow');
        } else {
            $('a.back-to-top').fadeOut('slow');
        }
    });
    </script>

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

        <script type="text/javascript">
            $(".confirmKumpulTO").click(function(){
                $(window).unbind('beforeunload');
                $("#form").hide(1000);
                $("#process").show(1000);
                jQuery(function(){
                      jQuery('.kumpulTO').click();
                });
                $(".progress-bar").animate({
                    width: "100%"
                }, 4500);
            });
        </script>

        <script type="text/javascript">
            $(".hideTimer").click(function(){
                $(".timer").toggle(1000);
            });
        </script>

        <script type="text/javascript">
            $('.alt-2').countDown({
                    css_class: 'countdown-alt-2'
                });
            $('.alt-2').on('time.elapsed', function () {
                $(window).unbind('beforeunload');
                $("#form").hide(1000);
                $("#process").show(1000);
                jQuery(function(){
                      jQuery('.kumpulTO').click().delay(5000);
                });
                $(".progress-bar").animate({
                    width: "100%"
                }, 4500);
            });
            $(function () {
                  $('[data-toggle="modal"]').tooltip()
                })
            $(window).bind('beforeunload', function(){
              return 'Hasil tryout belum disubmit. Tetap tinggalkan?';
            });
        </script>
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
    @endif
@stop
======================================================
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tryout {{($subject==1)?"MATEMATIKA":"BAHASA INGGRIS"}} - Try Out STIS</title>

    <!-- Bootstrap Core CSS #F9FFDB-->

    <style list-style-type: e="text/css">
        ol div, .cover {padding: 14% 14% 14% 12%; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
            margin: 1% 0; background-color:{{($subject==1)?"white":"#fcc4f7"}};}
        .soal {font-family:{{($subject==1)?"Times New Roman":"Cambria"}}; font-size: {{($subject==1)?"16px":"17px"}};}
        .pagenumber {font-family:"Arial"; font-size: 13px; margin-top: 30pt;}
        label {font-weight: normal !important;}
        .sizeSet {max-width: 500px; max-height: 500px;}
        .check {color:#1bd622;}
        .cross {color:red;}
        .sembarang {color:yellow;}
        table, th, td {border: 1px solid black;border-collapse: collapse;}
    </style>
    <link href={{url("vendor/bootstrap/css/bootstrap.min.css")}} rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href={{url("vendor/metisMenu/metisMenu.min.css")}} rel="stylesheet">

    <!-- Custom CSS -->
    <link href={{url("dist/css/sb-admin-2.css")}} rel="stylesheet">

    <!-- Custom Fonts -->
    <link href={{url("vendor/font-awesome/css/font-awesome.min.css")}} rel="stylesheet" type="text/css">
    <!--
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:600,700' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Righteous" rel="stylesheet" type="text/css"> -->
    <link rel="stylesheet" href={{url("css/countdown.demo.css")}} type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body style="background-color:grey;">
    <nav class="navbar navbar-default navbar-static-top navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href={{url("/")}}><span class="label label-info">Try Out STIS</span></a>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href={{route('profile')}}><i class="fa fa-user fa-fw"></i> {{$user or "Unknown"}}</a>
                        </li>
                        <li><a href="{{url('setting')}}"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href={{route('logout')}}><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
    </nav>
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
                            Lembar jawaan tidak boleh kotor atau terlipat
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
                                            <img style="margin-left:15%" src="{!!url('upload/qPictPath').'/'.$quest->qPictPath!!}" class="sizeSet img-responsive">
                                        @endif
                                        <ol type="A">
                                            <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="1" {{($quest->selected==1)?"checked='checked'":""}}> {!!$quest->a!!} @if($quest->answer==1)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==1) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                            <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="2" {{($quest->selected==2)?"checked='checked'":""}}> {!!$quest->b!!} @if($quest->answer==2)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==2) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                            <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="3" {{($quest->selected==3)?"checked='checked'":""}}> {!!$quest->c!!} @if($quest->answer==3)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==3) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                            <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="4" {{($quest->selected==4)?"checked='checked'":""}}> {!!$quest->d!!} @if($quest->answer==4)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==4) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                            <li><label><input type="radio" name="{{$quest->questId}}" class="quest" value="5" {{($quest->selected==5)?"checked='checked'":""}}> {!!$quest->e!!} @if($quest->answer==5)<span class="check"><i class="fa fa-check fa-fw"></i></span>@elseif($quest->selected==5) <span class="cross"><i class="fa fa-times fa-fw"></i></span> @endif</label></li>
                                            <label style="color:#c1bbc1"><input type="radio" name="{{$quest->questId}}" class="quest" value="0" {{($quest->selected==0)?"checked='checked'":""}}> Tidak menjawab
                                        </ol> 
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
                <div class="row timer" style="padding : 0 50px;">
                    <h1 class="alt-2" style="position : fixed; top : -30px;">0h {{$time}}m {{$secon}}s</h1>
                </div>
                <div class="row" style="padding : 0 35%;">
                    <div>
                        <button type="button" class="btn btn-secondary hideTimer" style="position : fixed; top : 80%;">Show/Hide Timer</button>
                    </div>
                    <a class="btn btn-default" style="position : fixed; top : 90%;"  data-target="#finishModal" data-toggle="modal" data-placement="top" title="Kumpul hasil ujian.">
                        <span class="fa fa-check"></span> SELESAI</i>
                    </a>

                    <!-- Modal -->
                    <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="myModalLabel">SELESAI</h4>
                          </div>
                          <div class="modal-body">
                            <p>Selesai dan Lihat Hasil Ujian?</p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">BATAL</button>
                            <button type="button" class="btn btn-primary confirmKumpulTO" data-dismiss="modal">YA</button>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            @else
            <div class="col-md-2">
                <div class="row" style="padding : 0 35%;">
                    <a class="btn btn-default" style="position : fixed; top : 90%;"   href="#" class="back-to-top" title="Scroll ke atas">
                        <span class="fa fa-arrow-up"></span> Up</i>
                    </a>
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

    <!-- jQuery -->
    <script src={{url("vendor/jquery/jquery.min.js")}}></script>

    <!-- Bootstrap Core JavaScript -->
    <script src={{url("vendor/bootstrap/js/bootstrap.min.js")}}></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src={{url("vendor/metisMenu/metisMenu.min.js")}}></script>

    <!-- Custom Theme JavaScript -->
    <script src={{url("dist/js/sb-admin-2.js")}}></script>

    <script type="text/javascript" src={{url("js/jquery.countdown.js")}}></script>

    <script type="text/x-mathjax-config">
          MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});
    </script>
    <script type="text/javascript" async
          src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_CHTML">
    </script>

    <script type="text/javascript">
    var amountScrolled = 300;

    $(window).scroll(function() {
        if ( $(window).scrollTop() > amountScrolled ) {
            $('a.back-to-top').fadeIn('slow');
        } else {
            $('a.back-to-top').fadeOut('slow');
        }
    });
    </script>

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

    <script type="text/javascript">
        $(".confirmKumpulTO").click(function(){
            $(window).unbind('beforeunload');
            $("#form").hide(1000);
            $("#process").show(1000);
            jQuery(function(){
                  jQuery('.kumpulTO').click();
            });
            $(".progress-bar").animate({
                width: "100%"
            }, 4500);
        });
    </script>

    <script type="text/javascript">
        $(".hideTimer").click(function(){
            $(".timer").toggle(1000);
        });
    </script>

    <script type="text/javascript">
        $('.alt-2').countDown({
                css_class: 'countdown-alt-2'
            });
        $('.alt-2').on('time.elapsed', function () {
            $(window).unbind('beforeunload');
            $("#form").hide(1000);
            $("#process").show(1000);
            jQuery(function(){
                  jQuery('.kumpulTO').click().delay(5000);
            });
            $(".progress-bar").animate({
                width: "100%"
            }, 4500);
        });
        $(function () {
              $('[data-toggle="modal"]').tooltip()
            })
        $(window).bind('beforeunload', function(){
          return 'Hasil tryout belum disubmit. Tetap tinggalkan?';
        });
    </script>
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
    @endif
</body>

</html>
===================
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Bootstrap Admin Theme</title>

    <!-- Bootstrap Core CSS -->
    <link href={{url("vendor/bootstrap/css/bootstrap.min.css")}} rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href={{url("vendor/metisMenu/metisMenu.min.css")}} rel="stylesheet">

    <!-- Custom CSS -->
    <link href={{url("dist/css/sb-admin-2.css")}} rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href={{url("vendor/morrisjs/morris.css")}} rel="stylesheet">

    <!-- Custom Fonts -->
    <link href={{url("vendor/font-awesome/css/font-awesome.min.css")}} rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <script src={{url("vendor/jquery/jquery.min.js")}}></script>

    <script>
    $(document).ready(function(){
            var questId1 = "12";
            var selected1 = "5";
            var token = "{!! csrf_token() !!}";
            $.post("{{route('realtimesubmit')}}",
            {
              _token : token,
              userId : "11",
              tryOutId : "2",
              questId : questId1,
              selected : selected1,
            });
    });
    </script>

</body>

</html>
