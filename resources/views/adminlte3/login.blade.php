<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Aplikasi Absensi Indah Jaya - Login</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Ionicons -->
    <!--<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">-->
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('bower_components/admin-lte/dist/css/adminlte.min.css')}}">
    <!-- Google Font: Source Sans Pro -->
    <link href="{{asset('fonts/vendor/google-font/SourceSansPro/sourcesanspro.css')}}" rel="stylesheet">
    <style>
        .login-page
        {
            background: url({{asset('images/wall_hr2.jpg')}}) no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
    </style>
    
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page layout-top-nav">
    @if($errors->any())
  <h4>{{$errors->first()}}</h4>
@endif
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <a href="#" class="navbar-brand">
                    <img src="{{asset('images/ij.jpg')}}" alt="IJ Logo" class="brand-image img-square elevation-3"
                         style="opacity: .8">
                    <span class="brand-text font-weight-light">Absen APP</span>
                </a>
            </div>
        </nav>
        <!-- /.navbar -->
        <div class="login-box">
            <div class="login-logo">
                <a href="#"><b>Absen</b> APP</a>
            </div>
            <!-- /.login-logo -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>
                    <form action="{{route('login')}}" method="post">
                        {{csrf_field()}}
                        <div class="input-group mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" autofocus="">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user-shield"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-4">
                                <button type="submit"
                                        class="btn btn-primary btn-block btn-flat">Sign In</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.login-box-body -->
        </div><!-- /.login-box -->

        <!-- jQuery -->
        <script src="{{asset('bower_components/admin-lte/plugins/jquery/jquery.min.js')}}"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="{{asset('bower_components/admin-lte/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{asset('bower_components/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <!-- AdminLTE App -->
        <script src="{{asset('dist/js/adminlte.min.js')}}"></script>

    </div>       
</body>
</html>
