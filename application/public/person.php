<?php
session_start();
$type = 'person';
include('./loginscript.php');
include('../header.php');
include('./subscription.php');
echo $query;
require_once('./lib/twitterLib/TweetPHP.php');



function debug_to_console($data)
{
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }

    echo "<script>console.log( 'Debug Objects: \'".$output."\'' );</script>";
}


function getTwitterFeed($name){
    $settings = array(
        'consumer_key'        => '34QJdk2RY410WVGAjz84leda5',
        'consumer_secret'     => '3YcKHBc9pAyJSsFfWnfRs1RXA547J4Ec2XiTKB5MCEBCPjqGeB',
        'access_token'        => '388168312-w8pGxFwvWgqtCIPpaHAynVV0xtN6kpbIFYyQdezB',
        'access_token_secret' => 'BEtoT1waHki5uy8nVVTEf525KrIdMJvHWpWzEKlPR18N5',
        'api_endpoint'        => 'statuses/user_timeline',
        'api_params'          => array('screen_name' => ($name != '' ? $name : 'MinPres'))
    );
    $TweetPHP = new TweetPHP($settings);
    $tweet_array =  $TweetPHP->get_tweet_array();
    //echo $tweet_array;
    //debug_to_console($tweet_array);
    $tweets = "<br>";
    $count = 0;
    foreach ($tweet_array  as $key => $json) {
        debug_to_console("Loading tweet ".$count);
        $embeded_tweet = file_get_contents(
            "https://publish.twitter.com/oembed?url=".rawurlencode(
                "https://twitter.com/Interior/status/".$json['id_str']
            )
        );
        $embeded_tweet = json_decode($embeded_tweet);
        $tweets = $tweets.$embeded_tweet->html;
        $tweets = $tweets."<br>";
        $count +=1;
        if($count>=10)
            return $tweets;
    }
    return $tweets;
}

function getSubtext($dossier){
    global $link;
    $resultstring = '';
    $query2 = 'SELECT keyword.keyword FROM politicalai_ict.keyword, politicalai_ict.subject, politicalai_ict.subject_to_dossier, politicalai_ict.dossier
	WHERE keyword.subject_id=subject.id AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND dossier.id=' . $dossier . '
    GROUP BY keyword ORDER BY COUNT(keyword.keyword) DESC LIMIT 1';
    if($result2 = pg_query($link, $query2)){
        $statements = [];
        $substringcount = [];
        $word = pg_fetch_assoc($result2)['keyword'];
        $query = 'SELECT statement.id AS id, statement.subject_id AS subject, statement.text AS text FROM statement, dossier, subject, subject_to_dossier WHERE dossier.id=' . $dossier . ' AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id
                AND statement.subject_id=subject.id AND text LIKE \'%' . $word . '%\';';
        if($result = pg_query($link, $query)){
            while($row = pg_fetch_assoc($result)){
                $statements[] = $row;
                $wordcount = 0;
                    $wordcount += substr_count($row['text'], $word);
                $substringcount[] = $wordcount;
            }
        }
        else{
            echo pg_errormessage($link);
        }
        $maxkey = array_keys($substringcount, max($substringcount));
        $resultstring .= highlightKeywords($word, $statements[$maxkey[0]]) . '<br>' ;
    }
    return $resultstring;
}

function highlightKeywords($searchterm, $topstatement){
    foreach(explode(' ', $searchterm) as $word){
        if(strpos($topstatement['text'], $word) !== false) {
            $pos = strpos($topstatement['text'], $word)-80;
            if($pos < 0){
                $pos=0;
            }
            $resultstring = '<a href=\'/text.php?subject=' . $topstatement['subject'] . '\'>...' .
                substr($topstatement['text'], $pos, 160) . '...</a>';
        }
    }
    foreach(explode(' ', $searchterm) as $word) {
        $resultstring = str_replace($word, '<strong>'. $word . '</strong>' , $resultstring);
    }
    return $resultstring;
}
function lastUpdated($id){
    global $link;
    $query = 'SELECT MAX(minute.date) AS date FROM minute, subject, dossier, subject_to_dossier WHERE minute.id=subject.minute_id
      AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND dossier.id=' . $id .';';
    if($result = pg_query($link, $query)){
        $row = pg_fetch_assoc($result);
        return $row['date'];
    }
}

function printPerson($id, $name){
    global $link;
    $query = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, statement, subject, subject_to_dossier
        , minute WHERE minute.id=subject.minute_id AND subject.id=subject_to_dossier.subject_id AND statement.person_id=' . $id . '  
        AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 2';
    if($result = pg_query($link, $query)){
        $returnstring = '<div class=\'col-lg-6\'><div class="card"><div class="card-body"><a href="/person.php?person=' . $id . '">
        <h4>' . $name . '</h4></a>';
        while($row = pg_fetch_assoc($result)){
            $returnstring .= '<h6 class="card-subtitle mb-2 text-muted">' . $row['maxdate'] . '</h6><a href="/dossier.php?dossier=' . $row['id'] .'"><p class="card-text">' . $row['name'] . '</p></a>';
        }
        $returnstring .='</p></div></div></div>';
    }
    return $returnstring;
}

?>
    ?>
    <main role="main" class="container">
        <?php
        if(isset($_GET['person'])){
            $query = 'SELECT person.id AS id, person.name AS name, person.firstname AS firstname, person.lastname AS lastname,
        person.twitter_screen_name AS twitter, party.id AS party_id, party.name AS party_name FROM person,party 
        WHERE person.party_id=party.id AND person.id=' . $_GET['person'] . ';';
            if($result = pg_query($link,$query)){
                $person =pg_fetch_assoc($result);
                if($person['name'] != ''){
                    echo '<div class="starter-template"><h1>' . $person['name'] . '</h1><a class="subscribe-button ' . (in_array($_GET['person'], $subscriptions['person']) ? 'subscribe' : 'inverse') . '" href="?person='. $_GET['person'] . '&subscribe">' . (in_array($_GET['person'], $subscriptions['person']) ? 'Unsubscribe' : 'Subscribe') . '</a></div>';
                }
//            ?>
                <h2 style='width:100%; text-align:center;'>Dossier</h2>
                <div class="row">
                    <div class="col-md-3">
                        <div class="headline person-info">
                            <p><strong>Naam:</strong> <?php echo (is_null($person['firstname']) ? $person['name'] :
                                    $person['firstname'] . ' ' . $person['lastname']); ?> </p>
                            <p><strong>Partij:</strong> <?php echo $person['party_name']; ?> </p>
                        </div>
                        <ul>
                            <?php
//                            $query = 'SELECT * FROM tweets WHERE person_id=' . $person['id'] . ' ORDER BY datetime DESC LIMIT 5';
//                            if($result = pg_query($link, $query)){
//                                while($row = pg_fetch_assoc($result)){
//                                    echo '<li><strong>' . $row['datetime'] . ' |
//                                <a href="https://www.twitter.com/' . $person['twitter'] . '">@' . $person['twitter'] .
//                                        '</a></strong>: ' . $row['tweet'] . '</li>';
//                                }
//                            }
                            echo getTwitterFeed($person['twitter']);
                            ?>
                        </ul>
                    </div>
                    <?php $query = 'SELECT DISTINCT dossier.name AS name, dossier.id AS id, minute.date FROM dossier, statement, subject, subject_to_dossier, minute
                    WHERE minute.id=subject.minute_id AND statement.subject_id=subject.id AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND statement.person_id=' . $_GET['person'] . ' ORDER BY minute.date DESC LIMIT 12;';
                    if($result = pg_query($link,$query)){?>
                        <div class='col-md-9 results'>
                            <div class="row">
                                <?php
                                while($row = pg_fetch_assoc($result)){
                                    ?>
                                    <div class='col-lg-6 col-md-6 col-xs-12'>
                                        <div class="card">
                                            <div class="card-body">
                                                <?php
                                                $dossierlink = '/dossier.php?dossier=' . $row['id'] . '&person=' . $_GET['person'];
                                                ?>
                                                <a href="/person.php?person=<?php echo $_GET['person'];?>dossier=<?php echo $row['id'] ?>&type=dossier&subscribe">
                                                    <i class="far fa-bookmark subscribeicon <?php echo (in_array($row['id'], $subscriptions['dossier'])? 'subscribed' : '' );?>"></i></a>
                                                <a href=<?php echo $dossierlink; ?>><h4><?php echo $row['name']; ?></h4></a>
                                                <h6 class="card-subtitle mb-2 text-muted"><?php echo lastUpdated($row['id']);?></h6><br>
                                                <?php
                                                $query3 = 'SELECT category.name AS name, category.id AS id FROM category, dossier_category 
                                    WHERE dossier_category.category_id=category.id
                                    AND dossier_category.dossier_id=' . $row['id'] . ';';
                                                if($result3 = pg_query($link, $query3)){
                                                    while($categories = pg_fetch_assoc($result3)){
                                                        ?>
                                                        <span class="badge badge-info"><?php echo $categories['name'];?></span>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <p><?php echo getSubtext($row['id']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <?php
                    }
                    else{
                        echo $query;
                    }?>
                </div>
            <?php    }
        }
        else{
            $query2= 'SELECT * FROM party ORDER BY party.name ASC;';
            if($result = pg_query($link,$query2)){
                ?>
                <div class='row results'>
                    <?php
                    while($party =pg_fetch_assoc($result)){
                        $query = 'SELECT person.id AS person_id, person.name AS person FROM person WHERE party_id=' . $party['id'] . ' ORDER BY person.name ASC;';
                        if($result2 = pg_query($link,$query)){
                            if(pg_num_rows($result2) > 0){
                                ?>
                                <div class='row party'>
                                    <h3><?php echo $party['name'];?></h3>
                                    <div class='row members'>
                                        <?php
                                        while($row = pg_fetch_assoc($result2)){
                                            echo printPerson($row['person_id'], $row['person']);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
                <?php
            }
        }
        ?>
        </div>
    </main><!-- /.container -->

<?php
include('footer.php');
?>