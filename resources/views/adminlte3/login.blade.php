<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" type="image/png" href="{{asset('favicon.ico')}}"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('plugins/loginv06/fonts/icomoon/style.css')}}">

    <link rel="stylesheet" href="{{asset('plugins/loginv06/css/owl.carousel.min.css')}}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('plugins/loginv06/css/bootstrap.min.css')}}">
    
    <!-- Style -->
    <link rel="stylesheet" href="{{asset('plugins/loginv06/css/style.css')}}">

    <title>{{config('global.app_name').' - '.config('global.perusahaan')}}</title>
  </head>
  <body>
  
  <div class="d-lg-flex half">
    <div class="bg order-1 order-md-2" style="background-image: url('{{asset('images/wall_hr2.jpg')}}');"></div>
    <div class="contents order-2 order-md-1">

      <div class="container">
        <div class="row align-items-center justify-content-center">
          <div class="col-md-7">
			  <div class="mb-3">
			  	<img src="{{asset('images/ij.jpg')}}"></img>
			  </div>
            <div class="mb-4">			  
              <h3>Silakan Masuk</h3>
              <p class="mb-4">Aplikasi absensi untuk {{config('global.perusahaan')}}. Bagi anda yang sudah memiliki akun, silakan masuk dengan username dan password yang dimiliki.</p>
            </div>
            <form action="{{route('login')}}" method="post">
			{{csrf_field()}}
              <div class="form-group first">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username">
				{!! $errors->first('username','<span class="badge badge-danger">:message</span>') !!}
              </div>
              <div class="form-group last mb-3">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                {!! $errors->first('password','<span class="badge badge-danger">:message</span>') !!}
              </div>

              <input type="submit" value="Log In" class="btn btn-block btn-primary">
              
            </form>
			<div class="mb-4">		
              <p class="mb-4">Copyright &copy; 2019, {{config('global.perusahaan')}}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    
  </div>
    
    

    <script src="{{asset('plugins/loginv06/js/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('plugins/loginv06/js/popper.min.js')}}"></script>
    <script src="{{asset('plugins/loginv06/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('plugins/loginv06/js/main.js')}}"></script>
  </body>
</html>