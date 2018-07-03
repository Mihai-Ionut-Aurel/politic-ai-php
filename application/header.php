<?php 
include('config.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/img/favicon.ico">

    <title>PoliticAI</title>

    <!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <!--Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">

      <!-- Custom styles for this template -->
    <link href="starter-template.css" rel="stylesheet">

    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="196301484825-b606es55hvf8odqsmi7gjlcrs3u8oobn.apps.googleusercontent.com">



      <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-120203190-1"></script>
    <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-120203190-1');
    </script>


    <script id="mcjs">!function(c,h,i,m,p){m=c.createElement(h),p=c.getElementsByTagName(h)[0],m.async=1,m.src=i,p.parentNode.insertBefore(m,p)}(document,"script","https://chimpstatic.com/mcjs-connected/js/users/e162e3b4a1304a161249feafa/e3d31f6d93afcc2ef96df4216.js");</script>
  </head>

  <body>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <a class="navbar-brand" href="/"><img src='/img/logo.png' class='logo'></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item <?php echo ($_SERVER['PHP_SELF'] == "/backoffice.php" ? "active" : "");?>">
            <a class="nav-link" href="/backoffice.php">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item <?php echo ($_SERVER['PHP_SELF'] == "/dashboard.php" ? "active" : "");?>">
            <a class="nav-link" href="/dashboard.php">Dashboard<span class="sr-only">(current)</span></a>
          </li>
        </ul>
        <form autocomplete="false" method='get' action='/backoffice.php' class="form-inline my-3 my-lg-0">
            <div class="autocomplete">
                <input autocomplete="off" class="form-control mr-sm-2 searchfield" type="text" name='search' id="search" placeholder="Zoeken" <?php echo (isset($_GET['search']) ? 'value="' . $_GET['search'] . '"' : '' ); ?> aria-label="Search">
            </div>
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" >Zoek</button>
        </form>
		<?php echo ($_SESSION['loggedin'] ? '' : '<a href=\'/signup.php\' class=\'loginbutton\'><button class="btn btn-success my-2 my-sm-0" type="submit">Registreer</button></a>') ?>
		<a href='<?php echo ($_SESSION['loggedin'] ? '/?logout' : '/login.php' ) ?>' class='loginbutton'><button class="btn btn-success my-2 my-sm-0" type="submit"><?php echo ($_SESSION['loggedin'] == 1 ? 'Uitloggen' : 'Login' ) ?></button></a>
      </div>
    </nav>