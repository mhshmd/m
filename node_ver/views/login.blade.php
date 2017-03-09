@extends('layouts.layout')

@section('title') Login - toSTIS.net @stop

@section('body')
    <body style="background-color:grey;">
@stop

@section('mainBody')
    <div class="container">
        <div class="row">
            {!!$warning or ""!!}           
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center text-uppercase"><span class="fa fa-lock"></span> Please Login</h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" action={{route('login')}} method="POST">
                            {{ csrf_field() }}  
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Email" name="email" type="text" autofocus value="{{$email or ""}}">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <button type="submit" class="btn btn-lg btn-info btn-block">Login</button>
                                <a class="btn btn-default btn-block" href={{route('signup')}}>Daftar</a>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop