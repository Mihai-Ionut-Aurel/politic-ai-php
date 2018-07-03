<?php
/**
 * Created by PhpStorm.
 * User: yannick
 * Date: 02/06/2018
 * Time: 20:13
 */
session_start();
include('../config.php');
$error = '';
function checkForLogin($email, $password){
    global $link;
    global $error;
    $query = 'SELECT * FROM "user" WHERE email=\'' . $email . '\';';
    if($result = pg_query($link, $query)){
        if(pg_num_rows($result)>0){
            $row = pg_fetch_assoc($result);
            if(password_verify($password, $row['password'])){
                return $row['id'];
            }
            else{
                $error = 'Emailadres of wachtwoord is onjuist';
            }
        }
        else{
            $error = 'Emailadres of wachtwoord is onjuist';
        }
    }
    else{
        echo pg_errormessage($link);
    }
    return -1;
}

function sendResetMail($email){
    global $link;
    $query = 'SELECT * FROM "user" WHERE email=\''. $email . '\';';
    if($result = pg_query($link, $query)){
        $row = pg_fetch_assoc($result);
        $token = $token = bin2hex(random_bytes(32));
        $query2 = 'INSERT INTO password_reset(user_id, token) VALUES(' . $row['id'] . ',\'' . $token . '\')';
        if($result2 = pg_query($link, $query2)){
            $mailtext ='';
            $mailworked = mail($row['email'], 'PoliticAI | Reset je wachtwoord', $mailtext);
            if(!$mailworked){
                echo 'Mail verzenden mislukt.';
                return false;
            }
        }
        else{
            echo pg_errormessage($link);
        }
    }
    else{
        echo pg_errormessage($link);
    }
    return true;
}

$error = '';
if(isset($_POST['inputEmail'])){
    if($id = checkForLogin($_POST['inputEmail'], $_POST['inputPassword']) >= 0){
        echo $id;
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $_POST['inputEmail'];
        header('Location:/backoffice.php');
    }
    else{
        $error = 'Incorrect e-mailadres of wachtwoord.';
    }
}
elseif(isset($_POST['resetEmail'])){
    if(sendResetMail($_POST['resetEmail'])){
    }
    else{

    }
}

include('../header.php');

?>
<?php
if(!isset($_GET['action'])){
?>
    <div class="wrapper">
        <form class="form-signin" method="post" action="">
            <img class="mb-4" src="/img/logoblack.png" alt="" height="72">
            <h1 class="h3 mb-3 font-weight-normal">Inloggen</h1>
            <label for="inputEmail" class="sr-only">E-mailadres</label>
            <input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="E-mailadres" required autofocus>
            <label for="inputPassword" class="sr-only">Wachtwoord</label>
            <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Wachtwoord" required>
            <div class="checkbox mb-3">
                <p><?php echo $error; ?></p>
            </div>
            <!--div class="g-signin2" data-onsuccess="onSignIn"></div-->
            <button class="btn btn-lg btn-primary btn-block" type="submit">Inloggen</button><br>
            <a href="/login.php?action=reset" class="btn btn-block btn-link">Wachtwoord vergeten?</a>
            <a href="/signup.php" class="btn btn-block btn-link">Registreer</a>
            <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
        </form>
    </div>

<?php
}
else{
?>
    <div class="wrapper">
        <form class="form-signin" method="post" action="">
            <img class="mb-4" src="/img/logoblack.png" alt="" height="72">
            <h1 class="h3 mb-3 font-weight-normal">Wachtwoord wijzigen</h1>
            <label for="inputEmail" class="sr-only">E-mailadres</label>
            <input type="email" id="inputEmail" name="resetEmail" class="form-control" placeholder="E-mailadres" required autofocus>
            <div class="checkbox mb-3">
                <p><?php echo $error; ?></p>
            </div>
            <!--div class="g-signin2" data-onsuccess="onSignIn"></div-->
            <button class="btn btn-lg btn-primary btn-block" type="submit">Wachtwoord Wijzigen</button><br>
            <a href="/login.php" class="btn btn-block btn-link">Terug</a>
            <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
        </form>
    </div>
<?php
}
?>

<script>
    function onSignIn(googleUser) {
        var profile = googleUser.getBasicProfile();
        console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
        console.log('Name: ' + profile.getName());
        console.log('Image URL: ' + profile.getImageUrl());
        console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
    }
</script>

<?php
include('footer.php');
?>