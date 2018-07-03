<?php
/**
 * Created by PhpStorm.
 * User: yanni
 * Date: 07/06/2018
 * Time: 17:12
 */

$subscriptions = array('person' => array(), 'dossier' => array(), 'party' => array(), 'search' => array());
$query = 'SELECT * FROM subscriptions WHERE user_id=' . $_SESSION['user_id'] .';';
if($result = pg_query($link,$query)){
    while($row = pg_fetch_assoc($result)){
        $subscriptions[$row['type']][] = $row['target_id'];
    }
}
else{
    echo 'Not Subscribed: ' . pg_errormessage($link);
}
if(isset($_GET['subscribe'])){
    $typeinsert = '';
    $target = '';
    if(isset($_GET['type'])){
        $typeinsert = $_GET['type'];
        $target=$_GET[$typeinsert];
    }
    else{
        $typeinsert = $type;
        $target=$_GET[$type];
    }
    if(in_array($target,$subscriptions[$typeinsert])){
        $query = 'DELETE FROM subscriptions WHERE target_id=\'' . $target . '\' AND type=\'' . $typeinsert . '\' AND user_id=' . $_SESSION['user_id'] . ';';
        if($result = pg_query($query)) {
            unset($subscriptions[$typeinsert][array_search($target,$subscriptions[$typeinsert])]);
        }
        else{
            echo pg_errormessage($link);
        }

    }
    else{
        if($target != ''){
            $query = 'INSERT INTO subscriptions(target_id, type, user_id) VALUES(\'' . $target . '\',\'' . $typeinsert . '\',' . $_SESSION['user_id'] . ');';
            if($result = pg_query($query)) {
                $subscriptions[$typeinsert][]=$target;
            }else{
                echo pg_errormessage($link);
            }
        }
        else{
            echo 'Subscription failed';
        }
    }
}