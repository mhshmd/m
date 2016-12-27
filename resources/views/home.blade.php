@extends('layouts.layout')

@section('title') Tryout USM STIS - toSTIS.net @stop

@section('customCSS')
    <link href={{url("css/metisMenu.min.css")}} rel="stylesheet">
    <link href={{url("css/countdown.demo.css")}} rel="stylesheet">
@stop

@section('body')
    <body>
        <div id="wrapper">
@stop

@section('sidebar')
    <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <!--<li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                        </li>-->
                        <li>
                            <a href={{route('profile')}}><i class="fa fa-dashboard fa-fw"></i> {{$user or 'Profil'}}</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-edit fa-fw"></i> Tryout<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{url('tryout')}}/3/{{$bagian}}">MATEMATIKA</a>
                                </li>
                                <li>
                                    <a href="{{url('tryout')}}/4/{{$bagian}}">BAHASA INGGRIS</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-edit fa-fw"></i> USM STIS<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{url('tryout')}}/1/0">MATEMATIKA 2016</a>
                                </li>
                                <li>
                                    <a href="{{url('tryout')}}/2/0">BAHASA INGGRIS 2016</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
@stop

@section('mainBody')
        <div id="page-wrapper">
            <div class="row">                
                {!!session('warning')!!}
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header titleFont text-uppercase">TRYOUT BULANAN</h1>
                        </div>
                        <!-- /.col-lg-12 -->
                    </div>
                    <!-- /.row -->
                    <div class="row">
                        <!-- Modal -->
                          <div class="modal fade" id="noTOModal" role="dialog">
                            <div class="modal-dialog modal-sm">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">BELUM TERSEDIA</h4>
                                </div>
                                <div class="modal-body">
                                  <p>Tryout dibuka {{($TORemaining-1)." hari lagi."}}</p>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
                                </div>
                              </div>
                            </div>
                          </div>
                        <!-- Modal -->
                            <div class="modal fade" id="mulaiTO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h4 class="modal-title" id="myModalLabel">IKUT SEKARANG</h4>
                                  </div>
                                  <div class="modal-body">
                                    <p>Soal hanya dikerjakan 1 kali kesempatan. Ikut sekarang?</p>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">BATAL</button>
                                    <button type="button" id="1" class="btn btn-primary lanjutkan" data-dismiss="modal">YA</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                        <div class="col-lg-6 col-md-6">
                            <a id="3" class="TOsource" data-target='{{($TORemaining>1)?"#noTOModal":"#mulaiTO"}}' data-toggle="modal" href="#">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-edit fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="titleFont" style="font-weight: normal;">MATEMATIKA</div>
                                                <div>{{($TORemaining>1)?(($TORemaining-1)." hari lagi"):"Tersedia"}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        <span class="pull-left">{{($TORemaining>1)?"Belum tersedia":"Ikut sekarang"}}</span>
                                        <span class="pull-right"><i class="fa fa-{{($TORemaining>1)?'warning':'check'}}"></i></span>
                                        <div class="clearfix"></div>
                                    </div>                            
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <a id="4" class="TOsource" data-target='{{($TORemaining>1)?"#noTOModal":"#mulaiTO"}}' data-toggle="modal" href="#">
                                <div class="panel panel-yellow">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-edit fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="titleFont" style="font-weight: normal;">BAHASA INGGRIS</div>
                                                <div>{{($TORemaining>1)?(($TORemaining-1)." hari lagi"):"Tersedia"}}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        <span class="pull-left">{{($TORemaining>1)?"Belum tersedia":"Ikut sekarang"}}</span>
                                        <span class="pull-right"><i class="fa fa-{{($TORemaining>1)?'warning':'check'}}"></i></span>
                                        <div class="clearfix"></div>
                                    </div>                          
                                </div>
                            </a>
                        </div>              
                    </div>
                    <div class="row">        
                        <div class="col-lg-12">
                            <h1 class="page-header titleFont text-uppercase">USM STIS</h1>
                        </div>
                        <!-- /.col-lg-12 -->
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <a href={{url('tryout')}}/1/0>
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-edit fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="titleFont" style="font-weight: normal;"><h4>MATEMATIKA 2016</h4></div>
                                                <div>Gratis kapanpun</div>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="panel-footer">
                                            <span class="pull-left">Ikut sekarang</span>
                                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                            <div class="clearfix"></div>
                                        </div>
                                    
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <a href={{url('tryout')}}/2/0>
                                <div class="panel panel-yellow">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-edit fa-3x"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="titleFont" style="font-weight: normal;">BAHASA INGGRIS 2016</div>
                                                <div>Gratis kapanpun</div>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="panel-footer">
                                            <span class="pull-left">Ikut sekarang</span>
                                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                            <div class="clearfix"></div>
                                        </div>                       
                                </div>
                            </a>
                        </div> 
                    </div>
                </div>
            </div>
            <!-- /.row -->
            <div class="row"><span style="color: white">$2x\pi$</span></div>
        </div>
        <!-- /#page-wrapper -->
    </div>
@stop
@section('customJS')
    <script src={{url("js/metisMenu.min.js")}}></script>

    <script type="text/javascript">
        $(".TOsource").click(function(){
            var id = $(this).attr('id');
            $(".lanjutkan").attr("id",id);
        });
        $(".lanjutkan").click(function(){
            var basic = "{{url('tryout')}}/";
            var idTO = $(this).attr('id')+"/{{$bagian}}";
            window.location.href = basic+idTO;
        });
    </script>
    <script type="text/javascript" src={{url("js/jquery.countdown.min.js")}}></script>  

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