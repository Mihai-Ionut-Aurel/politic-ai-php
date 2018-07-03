<?php
/**
 * Created by PhpStorm.
 * User: yannick
 * Date: 02/06/2018
 * Time: 22:22
 */
if($_SESSION['loggedin'] == true){
}
else{
    header('Location:/login.php');
//    $_SESSION['user_id'] = 1;
//    $_SESSION['loggedin'] = 1;
}
?>