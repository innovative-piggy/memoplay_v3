<?php

ob_start();
session_start();

if (isset($_SESSION["admin"])) {
    header('location: index.php');
}

if (isset($_POST['login']) && !empty($_POST['password'])) {
  // julvir:MEMOPLAY
	if ($_POST['login'] == 'admin' && $_POST['password'] == '000000') {
		$_SESSION['admin'] = 'admin';
		// if (!isset($_POST["remember"])) {
      $_SESSION['start'] = time();
			$_SESSION['expire'] = $_SESSION['start'] + (60 * 60 * 24);
    // }
    $_SESSION['lowquality'] = true;
		header('location: index.php');
	} else {
		echo "<span style='color: red;background: white;text-align: center;display: block;line-height: 40px;font-weight: 600;margin-top: 20px;'>Wrong username or password</span>";
	}
}

 ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Sign in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">




    <link rel="stylesheet" href="https://getbootstrap.com/2.3.2/assets/css/bootstrap.css" /> 
    <style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }

    </style>


  </head>

  <body>

    <div class="container">

      <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <input type="text" class="input-block-level" name="login" placeholder="Login">
        <input type="text" class="input-block-level" name="password" placeholder="Pass code">

        <label class="checkbox">
          <input type="checkbox" name="remember" value="remember-me"> Remember me
        </label>


        <button class="btn btn-large btn-primary" type="submit">Sign in</button>
      </form>

    </div> <!-- /container -->


  </body>
</html>
