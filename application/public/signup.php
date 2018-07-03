<?php
/**
 * Created by PhpStorm.
 * User: Yannick Mijsters
 * Date: 03/06/2018
 * Time: 13:30
 */
session_start();
include('../config.php');
$emailerror='';
if(isset($_POST['inputEmail'])) {
    if (isset($_POST['inputPassword']) && isset($_POST['firstname'])
        && isset($_POST['lastname']) && isset($_POST['check1'])) {
        if($_POST['inputPassword'] == $_POST['inputPasswordRepeat']){
            $query = 'SELECT * FROM "user" WHERE email=\'' . $_POST['inputEmail'] . '\';';
            if ($result = pg_query($link, $query)) {
                if (pg_num_rows($result) == 0) {
                    $query2 = 'INSERT INTO "user"(firstname, lastname, email, password, storedata, dateaccept, acceptemail) 
                  VALUES(\'' . $_POST['firstname'] . '\', \'' . $_POST['lastname'] . '\', \'' .
                        $_POST['inputEmail'] . '\', \'' . password_hash($_POST['inputPassword'], PASSWORD_DEFAULT) . '\', \'' .
                        (isset($_POST['check1'])?1:0) . '\', DEFAULT,\'' . (isset($_POST['check2'])?1:0) . '\')' . ' RETURNING id;';
                    if ($result2 = pg_query($link, $query2)) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_id'] = pg_fetch_assoc($result2)['id'];
                        $_SESSION['email'] = $_POST['inputEmail'];
                        header('Location:/backoffice.php');
                    } else {
                        echo $query2;
                        echo pg_errormessage($link);
                    }
                } else {
                    $emailerror = 'Dit e-mailadres is al in gebruik.';
                }
            } else {
                var_dump(pg_last_error($link));
                echo 'TEST: ' . pg_errormessage($link);
            }
        }else{
            $emailerror = 'De wachtwoorden komen niet overeen';
        }
    } else {
        $emailerror = 'Je hebt niet alle verplichte velden ingevuld';
    }
}
include('../header.php');
?>
<main class="container">
    <img class="mb-4" src="/img/logoblack.png" alt="" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Registreer</h1>
    <form method="post" action="">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="firstname">Voornaam</label>
                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Voornaam" required>
            </div>
            <div class="form-group col-md-6">
                <label for="lastname">Achternaam</label>
                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Achternaam" required>
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail">E-mail</label>
            <input type="email" class="form-control" id="inputEmail" name="inputEmail" placeholder="E-mail" required>
            <?php //echo ($emailerror !='' ? '<p>'. $emailerror . '</p>' :'');?>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputPassword">Wachtwoord</label>
                <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="Wachtwoord" required>
            </div>
            <div class="form-group col-md-6">
                <label for="inputPassword">Herhaal wachtwoord</label>
                <input type="password" class="form-control" id="inputPasswordRepeat" name="inputPasswordRepeat" placeholder="Herhaal wachtwoord" required>
            </div>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="check1" name="check1" required>
            <label class="form-check-label" for="check1">Ik geef toestemming voor het opslaan van mijn gegevens.</label>
            <br />
            <input type="checkbox" class="form-check-input" id="check2" name="check2">
            <label class="form-check-label" for="check2">Ik wil graag op de hoogte gehouden worden over het product.</label>
        </div>
        <p style="color:#ff0000"><?php echo $emailerror;?></p>
        <button type="submit" class="btn btn-primary">Registreer</button>
    </form>
</main>