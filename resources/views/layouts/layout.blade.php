<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title')</title>

    @yield('customStyleUp')

    <link rel="shortcut icon" href="{{url('upload/thumbnail.png')}}">
    <link href={{url("css/bootstrap.min.css")}} rel="stylesheet">
    <link href={{url("css/sb-admin-2.min.css")}} rel="stylesheet">
    <link href={{url("css/font-awesome.min.css")}} rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">

    @yield('customCSS')
    <link href={{url("css/layout.css")}} rel="stylesheet" type="text/css">
</head>
@yield('body')
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button id="mobcollapse" type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href={{url("/")}}><img class="img-responsive" style="max-width: 150px;" src="{{url('upload/logo.png')}}" alt="toSTIS.net logo" title="Tryout USM STIS"></a><!--<span class="label label-info">toSTIS.net</span></a>-->
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                @if($user=='Profil')
                    <li>
                        <a href={{route('login')}}>
                            Masuk/Daftar
                        </a>
                    </li>
                @else
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href={{route('profile')}}><i class="fa fa-user fa-fw"></i> {{$user}}</a>
                            </li>
                            <li><a href="{{url('setting')}}"><i class="fa fa-gear fa-fw"></i> Settings</a>
                            </li>
                            <li class="divider"></li>
                            <li><a href={{route('logout')}}><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                            </li>
                        </ul>
                        <!-- /.dropdown-user -->
                    </li>
                @endif
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            @yield('sidebar')
        </nav>
        @yield('mainBody')
    <script src={{url("js/jquery.min.js")}}></script>
    <script src={{url("js/bootstrap.min.js")}}></script>
    <script src={{url("js/sb-admin-2.min.js")}}></script>
    @yield('customJS')
</body>

</html>
