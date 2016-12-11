@extends('layouts.layout')

@section('title') Pembayaran Tryout - toSTIS.net @stop
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
            <ul class="timeline" id="timeline">
              <li class="li complete">
                <div class="timestamp">
                  <span class="author">toSTIS.net</span>
                </div>
                <div class="status">
                  <h4> isi data </h4>
                </div>
              </li>
              <li class="li">
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
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-shopping-cart"></span> PEMBAYARAN TRYOUT</h3>
                    </div>
                    <div class="panel-body">   
                        <form role="form" action={{route('pesankode')}} method="POST">
                            {{ csrf_field() }}  
                            <fieldset>
                                <div class="form-group">
                                    <label>Nomor handphone untuk info pembayaran</label>
                                    <input class="form-control input-lg" id="phone" placeholder="Nomor handphone" name="phone" type="text" value="08" title="Nomor handphone untuk status pembayaran">
                                </div>
                                <div class="form-group">
                                    <label>Jumlah Tryout</label>
                                    <input class="count" type="number" name="lifetime" min="1" max="7" oninvalid="setCustomValidity('Maaf, minimal 1 dan maksimal 7.')" title="Minimal 1 dan maksimal 7" value="1">
                                </div>
                                <div class="form-group">
                                    <label>Biaya : Rp <span class="price">20.000</span></label>
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <button type="submit" class="btn btn-lg btn-info btn-block">Lanjut</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('customJS')
    <script>
        $(document).change(function(){
            $(".count").bind("click keyup", function(){
                var count = $(this).val();
                var total = 0;
                switch(count) {
                    case '1':
                        total = count*20000;
                        break;
                    case '2':
                        total = count*20000-5000;
                        break;
                    case '3':
                        total = (count*20000)-10000;
                        break;
                    case '4':
                        total = (count*20000)-15000;
                        break;
                    case '5':
                        total = (count*20000)-20000;
                        break;
                    case '6':
                        total = (count*20000)-25000;
                        break;
                    case '7':
                        total = (count*20000)-30000;
                        break;
                    default:
                        total = 0;
                }
                function commaSeparateNumber(val){
                    while (/(\d+)(\d{3})/.test(val.toString())){
                      val = val.toString().replace(/(\d+)(\d{3})/, '$1'+'.'+'$2');
                    }
                    return val;
                }
                $(".price").html(commaSeparateNumber(total));
            });
        });
    </script>
    <script type="text/javascript">
        $('#phone').keyup(function(e) {
          if ((e.keyCode > 47 && e.keyCode < 58) || (e.keyCode < 106 && e.keyCode > 95)) {
            this.value = this.value.replace(/(\d{4})\-?/g, '$1-');
            this.value = this.value.replace(/\-$/g, '');
            return true;
          }
          
          //remove all chars, except dash and digits
          this.value = this.value.replace(/[^\-0-9]/g, '');
        });
    //     var completes = document.querySelectorAll(".complete");
    //     var toggleButton = document.getElementById("toggleButton");


    //     function toggleComplete(){
    //       var lastComplete = completes[completes.length - 1];
    //       lastComplete.classList.toggle('complete');
    //     }

    //     toggleButton.onclick = toggleComplete;
    </script>
    <script type="text/javascript">
        $(document).ready(function() {  
            var input = $("#phone");
            var len = input.val().length;
            input[0].focus();
            input[0].setSelectionRange(len, len);
        });
    </script>
@stop