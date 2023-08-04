<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 12:58:18
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-03 13:29:13
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
    <section class="pt-5 mt-5">
        <div class="container">
            <div class="row w-50">
                <h4>Register</h4>
                <form action="{{route('register-user')}}" method="POST">
                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            {{Session::get('success')}}
                        </div>
                    @endif
                    @if(Session::has('fail'))
                        <div class="alert alert-danger">
                            {{Session::get('fail')}}
                        </div>
                    @endif
                    @csrf
                    <div class="form-group pt-2 pb-2">
                        <label class="pb-2" for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" aria-describedby="name" placeholder="Enter full name" value="{{ old('name') }}" name="name" required>
                        <span class="text-danger pt-2 pb-2">@error('name'){{ $message }} @enderror</span>
                    </div>
                    <!-- email form group -->
                    <div class="form-group pb-2">
                        <label class="pb-2" for="email">Email address</label>
                        <input type="email" name="email"class="form-control" id="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ old('email') }}" name="name" required>
                        <span class="text-danger pt-2 pb-2">@error('email'){{ $message }} @enderror</span>
                    </div>
                    <!--password form group -->
                    <div class="form-group pb-2">
                        <label class="pb-2" for="password">Password</label>
                        <input type="password" name="password"class="form-control" id="password" aria-describedby="password" placeholder="Enter password" required>
                        <span class="text-danger pt-2 pb-2">@error('password'){{ $message }} @enderror</span>
                    </div>
                     <!--submit button -->
                     <div class="form-group pt-2">
                      <button type="submit" class="btn btn-primary">Register</button>
                          <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</html>
