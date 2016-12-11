@extends('layouts.layout')

@section('title') MONITORING SOAL - toSTIS.net @stop

@section('body')
    <body>
@stop
@section('mainBody')
    <div class="container" style="margin-top:10px">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-file-text-o"></span> MONITORING SOAL</h3>
                    </div>
                    <div class="panel-body text-center">
                        <div class="row">
                            <div>
                                <canvas id="monitor" height="1000px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('customJS')
    <script src={{url("js/Chart.bundle.min.js")}}></script>
    <script src={{url("js/utils.js")}}></script>

    <script type="text/javascript">
        var matematika = {
            labels: [
                @foreach ($data as $key=>$dat)
                    @if($key!=(sizeof($data)-1))
                        "{{$dat['name']}}",
                    @else
                        "{{$dat['name']}}"
                    @endif
                @endforeach
            ],
            datasets: [{
                label: 'Jumlah',
                backgroundColor: window.chartColors.green,
                data: [
                    @foreach ($data as $key=>$dat)
                    @if($key!=(sizeof($data)-1))
                        {{$dat['count']}},
                    @else
                        {{$dat['count']}}
                    @endif
                @endforeach
                ]
            }]

        };

        var ctx = document.getElementById("monitor").getContext("2d");
        window.myBar = new Chart(ctx, {
            type: 'horizontalBar',
            data: matematika,
            options: {
                title:{
                    display:true,
                    text:"MONITORING SOAL"
                },
                tooltips: {
                    mode: 'index',
                    intersect: true
                },
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero:false,
                            max: 70,
                            min: 0,
                            stepSize: 5
                        },
                        stacked: false,
                    }],
                    yAxes: [{
                        stacked: false,
                    }]
                }
            }
        });
    </script>
@stop