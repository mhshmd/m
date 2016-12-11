@extends('layouts.layout')

@section('title') {{$user or 'Profil'}} - toSTIS.net @stop

@section('customCSS')
    <link href={{url("css/metisMenu.min.css")}} rel="stylesheet">
@stop

@section('customStyle')
    canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }
    .note{font-size:14px;}
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
                <div class="col-lg-12">
                    <h1 class="page-header titleFont text-uppercase">{{$user}}</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-check fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge count">{{$mathPoin[sizeof($mathPoin)-1]['true'] or '0'}}</div>
                                    <div class="note">Last True (Math)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-check fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge count">{{$engPoin[sizeof($engPoin)-1]['true'] or '0'}}</div>
                                    <div class="note">Last True (English)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-asterisk fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge count">{{$mathPoin[sizeof($mathPoin)-1]['totalScore'] or '0'}}</div>
                                    <div class="note">Last Point (Math)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-asterisk fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge count">{{$engPoin[sizeof($engPoin)-1]['totalScore'] or '0'}}</div>
                                    <div class="note">Last Point (English)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> KOMPOSISI HASIL TRYOUT
                            
                        </div>
                        <!-- /.panel-heading -->
                        <div style="width: 100%">
                            <canvas id="canvas" height="438px"></canvas>
                        </div>
                        <div style="width: 100%">
                            <canvas id="canvasBI" height="438px"></canvas>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart-o fa-fw"></i> PERKEMBANGAN TRYOUT
                            
                        </div>
                        <!-- /.panel-heading -->
                        <div>
                            <canvas id="jumlahBenar" style="width: 50%" height="438px"></canvas>
                        </div>
                        <div>
                            <canvas id="totalPoin" style="width: 50%" height="438px"></canvas>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bell fa-fw"></i> Riwayat Ujian
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="list-group">
                                @foreach ($to as $key=>$t)
                                    @if($t->tryOutId==3)
                                        <a href="{{url('/check/3')}}/{{$t->bagian}}" class="list-group-item">
                                            <i class="fa fa-file-text-o fa-fw"></i> Tryout Matematika {{$t->bagian}}
                                            <span class="pull-right text-muted small"><em>{{substr($t->created_at,0,10)}}</em></span>
                                        </a>
                                    @elseif($t->tryOutId==4)
                                        <a href="{{url('/check/4')}}/{{$t->bagian}}" class="list-group-item">
                                            <i class="fa fa-file-text-o fa-fw"></i> Tryout Bhs Inggris {{$t->bagian}}
                                            <span class="pull-right text-muted small"><em>{{substr($t->created_at,0,10)}}</em></span>
                                        </a>
                                    @elseif($t->tryOutId==1)
                                        <a href="{{url('/check/1/0')}}" class="list-group-item">
                                            <i class="fa fa-file-text-o fa-fw"></i> USM 2016 Mtk
                                            <span class="pull-right text-muted small"><em>{{substr($t->created_at,0,10)}}</em></span>
                                        </a>
                                    @elseif($t->tryOutId==2)
                                        <a href="{{url('/check/2/0')}}" class="list-group-item">
                                            <i class="fa fa-file-text-o fa-fw"></i> USM 2016 Bhs Inggris
                                            <span class="pull-right text-muted small"><em>{{substr($t->created_at,0,10)}}</em></span>
                                        </a>  
                                    @else
                                        Kosong                                    
                                    @endif
                                @endforeach                             
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                </div>
            </div>
        </div>
        <!-- /#page-wrapper -->
    </div>
@stop
@section('customJS')
    <script src={{url("js/metisMenu.min.js")}}></script>
    <script src={{url("js/Chart.bundle.min.js")}}></script>
    <script src={{url("js/utils.js")}}></script>

    <script type="text/javascript">
        var matematika = {
            labels: ["November", "Desember", "Januari", "Februari", "Maret", "April", "Mei"],
            datasets: [{
                label: 'Benar',
                backgroundColor: window.chartColors.green,
                data: [
                @if(sizeof($mathPoin)>0)
                    @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['true']}}",
                        @else
                            "{{$dat['true']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Salah',
                backgroundColor: window.chartColors.red,
                data: [
                @if(sizeof($mathPoin)>0)
                    @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['false']}}",
                        @else
                            "{{$dat['false']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Tidak dijawab',
                backgroundColor: window.chartColors.blue,
                data: [
                @if(sizeof($mathPoin)>0)
                    @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['nyerah']}}",
                        @else
                            "{{$dat['nyerah']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Isi sembarang',
                backgroundColor: window.chartColors.black,
                data: [
                @if(sizeof($mathPoin)>0)
                    @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['ngarang']}}",
                        @else
                            "{{$dat['ngarang']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }]

        };

        var ctx = document.getElementById("canvas").getContext("2d");
        window.myBar = new Chart(ctx, {
            type: 'bar',
            data: matematika,
            options: {
                title:{
                    display:true,
                    text:"TRYOUT MATEMATIKA"
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
                        stacked: true,
                    }]
                }
            }
        });
    </script>

    <script type="text/javascript">
        var bhsinggris = {
            labels: ["November", "Desember", "Januari", "Februari", "Maret", "April", "Mei"],
            datasets: [{
                label: 'Benar',
                backgroundColor: window.chartColors.green,
                data: [
                @if(sizeof($engPoin)>0)
                    @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['true']}}",
                        @else
                            "{{$dat['true']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Salah',
                backgroundColor: window.chartColors.red,
                data: [
                @if(sizeof($engPoin)>0)
                    @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['false']}}",
                        @else
                            "{{$dat['false']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Tidak dijawab',
                backgroundColor: window.chartColors.blue,
                data: [
                @if(sizeof($engPoin)>0)
                    @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['nyerah']}}",
                        @else
                            "{{$dat['nyerah']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }, {
                label: 'Isi sembarang',
                backgroundColor: window.chartColors.black,
                data: [
                @if(sizeof($engPoin)>0)
                    @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['ngarang']}}",
                        @else
                            "{{$dat['ngarang']}}"
                        @endif
                    @endforeach
                @endif
                ]
            }]

        };

        var ctx2 = document.getElementById("canvasBI").getContext("2d");
        window.myBar = new Chart(ctx2, {
            type: 'bar',
            data: bhsinggris,
            options: {
                title:{
                    display:true,
                    text:"TRYOUT BAHASA INGGRIS"
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
                        stacked: true,
                    }]
                }
            }
        });
    </script>

    <script type="text/javascript">
        var ctx = document.getElementById("jumlahBenar");
        var data1 = {
            labels: ["Oktober", "November", "Desember", "Januari", "Februari", "Maret", "April", "Mei"],
            datasets: [
                {
                    label: "Matematika",
                    fill: false,
                    lineTension: 0.3,
                    backgroundColor: window.chartColors.blue,
                    borderColor: window.chartColors.blue,
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderWidth: 5,
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: window.chartColors.blue,
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: [0, 
                @if(sizeof($mathPoin)>0) @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['true']}}",
                        @else
                            "{{$dat['true']}}"
                        @endif
                    @endforeach @endif],
                    spanGaps: false,
                },
                {
                    label: "Bahasa Inggris",
                    fill: false,
                    lineTension: 0.3,
                    backgroundColor: window.chartColors.red,
                    borderColor: window.chartColors.red,
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderWidth: 5,
                    pointHoverRadius: 10,
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: [0, 
                @if(sizeof($engPoin)>0) @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['true']}}",
                        @else
                            "{{$dat['true']}}"
                        @endif
                    @endforeach @endif],
                    spanGaps: false,
                }
            ]
        };
        var myChart = new Chart(ctx, {
            type: 'line',
            data: data1,
            options: {
                title:{
                        display:true,
                        text:"JUMLAH BENAR"
                    },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:false,
                            max: 60,
                            min: 0,
                            stepSize: 5
                        }
                    }]
                },
                legend: {
            // Overrides the global setting
                    position: 'top'
                },

                maintainAspectRatio: false,
                tooltips: {
                        mode: 'index',
                        intersect: false
                    },
            }
        });
    </script>

    <script type="text/javascript">
        var ctx = document.getElementById("totalPoin");
        var data1 = {
            labels: ["Oktober", "November", "Desember", "Januari", "Februari", "Maret", "April", "Mei"],
            datasets: [
                {
                    label: "Matematika",
                    fill: false,
                    lineTension: 0.3,
                    backgroundColor: window.chartColors.blue,
                    borderColor: window.chartColors.blue,
                    borderCapStyle: 'butt',
                    borderDash: [5, 5],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderWidth: 5,
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: window.chartColors.blue,
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: [0, 
                @if(sizeof($mathPoin)>0) @foreach ($mathPoin as $key=>$dat)
                        @if($key!=(sizeof($mathPoin)-1))
                            "{{$dat['totalScore']}}",
                        @else
                            "{{$dat['totalScore']}}"
                        @endif
                    @endforeach @endif],
                    spanGaps: false,
                },
                {
                    label: "Bahasa Inggris",
                    fill: false,
                    lineTension: 0.3,
                    backgroundColor: window.chartColors.red,
                    borderColor: window.chartColors.red,
                    borderCapStyle: 'butt',
                    borderDash: [5, 5],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: window.chartColors.red,
                    pointBorderWidth: 5,
                    pointHoverRadius: 10,
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: [0, 
                @if(sizeof($engPoin)>0) @foreach ($engPoin as $key=>$dat)
                        @if($key!=(sizeof($engPoin)-1))
                            "{{$dat['totalScore']}}",
                        @else
                            "{{$dat['totalScore']}}"
                        @endif
                    @endforeach @endif],
                    spanGaps: false,
                }
            ]
        };
        var myChart = new Chart(ctx, {
            type: 'line',
            data: data1,
            options: {
                title:{
                        display:true,
                        text:"TOTAL POIN"
                    },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:false,
                            stepSize: 10
                        }
                    }]
                },
                legend: {
            // Overrides the global setting
                    position: 'top'
                },

                maintainAspectRatio: false,
                tooltips: {
                        mode: 'index',
                        intersect: false
                    },
            }
        });
    </script>

    <script type="text/javascript">
            $('.count').each(function () {
                $(this).prop('Counter',0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 4000,
                    easing: 'swing',
                    step: function (now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });
    </script>
@stop