<?php
/**
 * Created by PhpStorm.
 * User: Yannick Mijsters
 * Date: 03/06/2018
 * Time: 13:30
 */
session_start();
include('./loginscript.php');
include('../config.php');
$emailerror='';
if($result = pg_query($link, $query)){
    if(pg_num_rows($result) == 0){
        $query2 = 'UPDATE "user" SET firstname, lastname, email, password, storedata, dateaccept, acceptemail) 
          VALUES(\'' . $_POST['firstname'] . '\', \'' . $_POST['lastname'] . '\', \'' .
            $_POST['inputEmail'] . '\', \'' . password_hash($_POST['inputPassword'], PASSWORD_DEFAULT ) . '\', \'' .
            $_POST['check1'] . '\', DEFAULT,\'' . $_POST['check2'] . '\')' .' RETURNING id;';
        if($result2 = pg_query($link, $query2)){
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = pg_fetch_assoc($result2)['id'];
            $_SESSION['email'] = $_POST['inputEmail'];
        }
        else{
            echo pg_errormessage($link);
        }
    }
    else{
        $emailerror = 'Dit e-mailadres is al in gebruik.';
    }
}
else{
    echo pg_errormessage($link);
}
$query = 'SELECT * FROM "user" WHERE id=' . $_SESSION['user_id'] . ';';
$account = [];
if($result = pg_query($link, $query)){
    $account = pg_fetch_assoc($result);
}
include('../header.php');
?>
<main class="container">
    <img class="mb-4" src="/img/logoblack.png" alt="" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Account</h1>
    <form method="post" action="">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="firstname">Voornaam</label>
                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Voornaam" value="<?php echo $account['firstname'] ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="lastname">Achternaam</label>
                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Achternaam" value="<?php echo $account['lastname'] ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail">E-mail</label>
            <input type="email" class="form-control" id="inputEmail" name="inputEmail" placeholder=E-mail" value="<?php echo $account['email'] ?>" required>
            <?php echo ($emailerror !='' ? '<p>'. $emailerror . '</p>' :'');?>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputPassword">Huidig Wachtwoord</label>
                <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="Wachtwoord" required>
            </div>
            <div class="form-group col-md-6">
                <label for="inputPassword">Nieuw Wachtwoord</label>
                <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="Wachtwoord" required>
            </div>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="check1" name="check1" required <?php echo ($account['storedata'] ? 'checked' : '')?>>
            <label class="form-check-label" for="check1">Ik geef akkoord voor het opslaan van mijn gegevens.</label>
            <br />
            <input type="checkbox" class="form-check-input" id="check2" name="check2" <?php echo ($account['acceptemail'] ? 'checked' : '')?>>
            <label class="form-check-label" for="check2">Ik wil graag helpen met het ontwikkelen van deze website
                en ontvang graag e-mails met vragen en informatie over deze website.</label>
        </div>

        <button type="submit" class="btn btn-primary">Registreer</button>
    </form>
</main>