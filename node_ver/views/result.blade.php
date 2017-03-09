@extends('layouts.layout')

@section('title') Hasil Ujian - toSTIS.net @stop

@section('body')
    <body>
        <div id="wrapper"><body style="background-color:grey;">
@stop

@section('mainBody')
    {!!$warning or ''!!}

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
                        <div class="row">
                            <!--<p>Rank <span class="badge">1</span></p>-->
                            <div class="btn-group" style="margin-top: 3px;">
                                <a class="btn btn-default" href={{route('home')}}><span class="fa fa-home"></span> Beranda</i></a>
                                <a class="btn btn-info" href={{route('profile')}}><span class="fa fa-bar-chart-o"></span> Profil</i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('customJS')
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

    <!-- ChartJS Charts JavaScript -->
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
@stop