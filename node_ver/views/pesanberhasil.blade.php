@extends('layouts.layout')

@section('title') Informasi Pembayaran - toSTIS.net @stop

@section('customStyle')
    .timeline {
      list-style-type: none;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-bottom:0;
      margin-bottom:0;
    }

    .li {
      transition: all 200ms ease-in;
    }

    .timestamp {
      margin-bottom: 20px;
      padding: 0px 35px;
      display: flex;
      flex-direction: column;
      align-items: center;
      font-weight: 100;
    }

    .status {
      padding: 0px 10px;
      display: flex;
      justify-content: center;
      border-top: 2px solid #D6DCE0;
      position: relative;
      transition: all 200ms ease-in;
    }
    .status h4 {
      font-weight: 600;
    }
    .status:before {
      content: "";
      width: 20px;
      height: 20px;
      background-color: white;
      border-radius: 25px;
      border: 1px solid #ddd;
      position: absolute;
      top: -13px;
      left: 42%;
      transition: all 200ms ease-in;
    }

    .li.complete .status {
      border-top: 2px solid #66DC71;
    }
    .li.complete .status:before {
      background-color: #66DC71;
      border: none;
      transition: all 200ms ease-in;
    }
    .li.complete .status h4 {
      color: #66DC71;
    }

    .timeline:before {
      content: " ";
      position: absolute;
      top: 0;
      bottom: 0;
      left: 50%;
      width: 3px;
      margin-left: -1.5px;
      background-color: transparent;
    }

    @media (min-device-width: 320px) and (max-device-width: 700px) {
      .timelines {
        list-style-type: none;
        display: block;
      }

      .li {
        transition: all 200ms ease-in;
        display: flex;
        width: inherit;
      }

      .timestamp {
        width: 100px;
      }

      .status:before {
        left: -8%;
        top: 30%;
        transition: all 200ms ease-in;
      }
    }
@stop

@section('body')
    <body>
@stop

@section('mainBody')
    <div class="container">
        <div class="row">
            <div class="row">
            <ul class="timeline" id="timeline">
              <li class="li complete">
                <div class="timestamp">
                  <span class="author">toSTIS.net</span>
                </div>
                <div class="status">
                  <h4> isi data </h4>
                </div>
              </li>
              <li class="li complete">
                <div class="timestamp">
                  <span class="author">ATM/Bank</span>
                </div>
                <div class="status">
                  <h4> transfer biaya </h4>
                </div>
              </li>
              <li class="li">
                <div class="timestamp">
                  <span class="author">toSTIS.net</span>
                </div>
                <div class="status">
                  <h4> ikut tryout </h4>
                </div>
              </li>
            </ul>  
        </div> 
        <div class="row">
            {!!$warning or ""!!}           
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default text-center">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-shopping-cart"></span> INFORMASI PEMBAYARAN</h3>
                    </div>
                    <div class="panel-body text-left">  
                        <p class="text-justify">
                            Data sudah disimpan. Silahkan transfer <strong>Rp{{$price or "Tdk tersedia"}}</strong> ke rekening: 
                        </p>                      
                        <blockquote>
                            <p>
                                BRI : <strong>1257-01-004085-50-9</strong> <br> Atas nama : <strong>MUH. SHAMAD</strong>
                                <br> Batas transfer : <strong>pukul {{$expire}}</strong>
                            </p>
                        </blockquote>
                        <p>Data pesanan :</p>
                        <blockquote>
                            <p>
                                Nomor hp : <strong>{{$phone}}</strong> <br> Jumlah Tryout : <strong>{{$count}}</strong>
                                <br> Biaya : <strong>Rp{{$total}} - <abbr title="Potongan biaya">Rp{{$sand}}</abbr> = Rp{{$price}}</strong>
                            </p>
                        </blockquote>
                        <p>
                            Tryout otomatis dapat diikuti, setelah transfer berhasil.
                        </p>
                    </div>
                    <div class="row">
                            <!--<p>Rank <span class="badge">1</span></p>-->
                            <div class="btn-group" style="margin: 0 0 10px 5px;">
                                <a class="btn btn-default" href={{route('home')}}><span class="fa fa-home"></span> Beranda</i></a>
                                <a class="btn btn-default" href={{route('pesan')}}><span class="fa fa-edit"></span> Edit</i></a>
                                <a class="btn btn-default" href={{route('pesan')}}><span class="fa fa-plus"></span> Pesan Lagi</i></a>
                                <a class="btn btn-info" href={{route('profile')}}><span class="fa fa-bar-chart-o"></span> Profil</i></a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop