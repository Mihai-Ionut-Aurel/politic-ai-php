<?php
/**
 * Created by PhpStorm.
 * User: yanni
 * Date: 12/06/2018
 * Time: 22:56
 */
include('../../config.php');
require_once('../lib/twitterLib/TweetPHP.php');
/*function getTwitterFeed($name, $next)
{
    $params = array('screen_name' => $name, 'count' => 20);
    if($next > 0){
        $params['max_id'] = $next;
    }
    if ($name != '') {
        $settings = array(
            'consumer_key' => '34QJdk2RY410WVGAjz84leda5',
            'consumer_secret' => '3YcKHBc9pAyJSsFfWnfRs1RXA547J4Ec2XiTKB5MCEBCPjqGeB',
            'access_token' => '388168312-w8pGxFwvWgqtCIPpaHAynVV0xtN6kpbIFYyQdezB',
            'access_token_secret' => 'BEtoT1waHki5uy8nVVTEf525KrIdMJvHWpWzEKlPR18N5',
            'api_endpoint' => 'statuses/user_timeline',
            'api_params' => $params
        );
        $TweetPHP = new TweetPHP($settings);
        return $TweetPHP->get_tweet_array();
    }
    else{
        return [];
    }
}
*/

$query = 'SELECT id, dossier_id AS dossier FROM subject;';
if($result = pg_query($link, $query)){
    while($row = pg_fetch_assoc($result)){
        $query2 = 'INSERT INTO subject_to_dossier(subject_id, dossier_id) VALUES(' . $row['id'] . ',' . $row['dossier'] . ')';
        if($result2 = pg_query($link, $query2)){
            echo 'INSERTED';
        }
        else{
            echo pg_errormessage($link);
        }
    }
}
else{
    echo pg_errormessage($link);
}