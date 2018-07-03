<?php
/**
 * Created by PhpStorm.
 * User: yanni
 * Date: 07/06/2018
 * Time: 16:03
 */
session_start();
include('./loginscript.php');
include('../config.php');
include('./subscription.php');

if(!isset($_SESSION['filter']) || !isset($_GET['filter'])){
    $_SESSION['filter'] = array('person' => [], 'dossier' => [], 'party' => [], 'search' => []);
}
else{
    updateFilter();
}

function updateFilter(){
    if(isset($_GET['filter']) && $_GET['filter']=='party'){
        if(!in_array($_GET['person'], $_SESSION['filter']['person'])){
            $_SESSION['filter']['person'][] = $_GET['person'];
        }
        else{
            unset($_SESSION['filter']['person'][array_search($_GET['person'],$_SESSION['filter']['person'])]);
        }
    }
    elseif(isset($_GET['filter']) && $_GET['filter']=='person'){
        if(!in_array($_GET['person'], $_SESSION['filter']['person'])){
            $_SESSION['filter']['person'][] = $_GET['person'];
        }
        else{
            unset($_SESSION['filter']['person'][array_search($_GET['person'],$_SESSION['filter']['person'])]);
        }
    }
    elseif(isset($_GET['filter']) && $_GET['filter']=='dossier'){
        if(!in_array($_GET['dossier'], $_SESSION['filter']['dossier'])){
            $_SESSION['filter']['dossier'][] = $_GET['dossier'];
        }
        else{
            unset($_SESSION['filter']['dossier'][array_search($_GET['dossier'],$_SESSION['filter']['dossier'])]);
        }
    }
    elseif(isset($_GET['filter']) && $_GET['filter']=='search'){
        if(!in_array($_GET['search'], $_SESSION['filter']['search'])){
            $_SESSION['filter']['search'][] = $_GET['search'];
        }
        else{
            unset($_SESSION['filter']['search'][array_search($_GET['search'],$_SESSION['filter']['search'])]);
        }
    }
}


function getDashboardDossiers()
{
    global $link;
    if (!empty($_SESSION['filter']['person']) || !empty($_SESSION['filter']['party']) || !empty($_SESSION['filter']['dossier'])) {
        $wheredossier=' dossier.id=' . join(' OR dossier.id=',$_SESSION['filter']['dossier']);
        $whereperson=' person.id=' . join(' OR person.id=',$_SESSION['filter']['person']);
        $whereparty=' party.id=' . join(' OR party.id=',$_SESSION['filter']['party']);
        $whereparty=' keyword.keyword=' . join(' OR keyword.keyword=',$_SESSION['filter']['search']);
        $subqueries = array();
        if(count($_SESSION['filter']['dossier']) > 0){
            $subqueries[] = '(SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id AS dossier_id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier WHERE subscriptions.target_id::integer=dossier.id AND subscriptions.type=\'dossier\' AND
       minute.id=subject.minute_id AND dossier.id=subject_to_dossier.dossier_id  AND subject.id=subject_to_dossier.subject_id  
        AND (' . $wheredossier . ')  GROUP BY dossier.id, subscriptions.type, subscriptions.target_id)';
        }
        if(count($_SESSION['filter']['person']) > 0){
        $subqueries[] = '(SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id AS dossier_id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, person, statement WHERE minute.id=subject.minute_id AND dossier.id=subject_to_dossier.dossier_id
        AND subject.id=subject_to_dossier.subject_id AND person.id=statement.person_id
        AND statement.subject_id=subject.id AND (' . $whereperson . ') AND subscriptions.target_id::integer=person.id AND subscriptions.type=\'person\'  
      GROUP BY dossier.id, subscriptions.type, subscriptions.target_id)';
       }
       if(count($_SESSION['filter']['party']) > 0) {
           $subqueries[] = '(SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id AS dossier_id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, person, statement, party WHERE minute.id=subject.minute_id AND dossier.id=subject_to_dossier.dossier_id
        AND subject.id=subject_to_dossier.subject_id AND person.id=statement.person_id 
      AND statement.subject_id=subject.id AND (' . $whereparty . ') AND person.party_id=party.id AND subscriptions.target_id::integer=party.id 
      AND subscriptions.type=\'party\' GROUP BY dossier.id, subscriptions.type, subscriptions.target_id)';
       }
       if(count($_SESSION['filter']['search']) > 0) {
           $subqueries[] = '(SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id AS dossier_id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, keyword     WHERE minute.id=subject.minute_id AND dossier.id=subject_to_dossier.dossier_id
        AND subject.id=subject_to_dossier.subject_id AND keyword.subject_id=subject.id AND (' . $whereparty . ') 
        AND person.party_id=party.id AND subscriptions.target_id ILIKE statement.text 
        AND subscriptions.type=\'search\' GROUP BY dossier.id, subscriptions.type, subscriptions.target_id)';
       }

        $query = join(' UNION ALL ',$subqueries) . ' ORDER BY maxdate DESC, dossier_id LIMIT 16' ;
    } else {
        $query = '(SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id AS dossier_id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier WHERE minute.id=subject.minute_id AND dossier.id=subject_to_dossier.dossier_id
      AND subject.id=subject_to_dossier.subject_id AND subscriptions.user_id=' . $_SESSION['user_id'] . ' AND
      subscriptions.target_id::integer=dossier.id AND subscriptions.type=\'dossier\' GROUP BY dossier.id, subscriptions.type, subscriptions.target_id) UNION ALL 
      (SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, statement, party, person WHERE minute.id=subject.minute_id AND 
      dossier.id=subject_to_dossier.dossier_id AND subject.id=subject_to_dossier.subject_id AND 
      subscriptions.user_id=' . $_SESSION['user_id'] . ' AND subscriptions.target_id::integer=party.id AND subscriptions.type=\'party\' 
      AND party.id=person.party_id AND person.id=statement.person_id AND statement.subject_id=subject.id GROUP BY dossier.id, subscriptions.type, subscriptions.target_id) UNION ALL
      (SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, statement, person WHERE minute.id=subject.minute_id AND 
      dossier.id=subject_to_dossier.dossier_id AND subject.id=subject_to_dossier.subject_id AND 
      subscriptions.user_id=' . $_SESSION['user_id'] . ' AND subscriptions.target_id::integer=person.id AND person.id=statement.person_id 
      AND statement.subject_id=subject.id AND subscriptions.type=\'person\' GROUP BY dossier.id, subscriptions.type, subscriptions.target_id) UNION ALL
      (SELECT DISTINCT MAX(minute.date) AS maxdate, dossier.id, dossier.name AS dossier_name, subscriptions.type, subscriptions.target_id, MAX(subject.id) AS subject FROM subscriptions,
      dossier, minute, subject, subject_to_dossier, keyword WHERE minute.id=subject.minute_id AND 
      dossier.id=subject_to_dossier.dossier_id AND subject.id=subject_to_dossier.subject_id AND 
      subscriptions.user_id=' . $_SESSION['user_id'] . ' AND subscriptions.target_id ILIKE \'%\' || keyword.keyword || \'%\' AND 
      keyword.subject_id=subject.id AND subscriptions.type=\'search\' GROUP BY dossier.id, subscriptions.type, subscriptions.target_id)
      ORDER BY maxdate DESC, dossier_id  LIMIT 16';
    }
    if($result = pg_query($link, $query)){
        if(pg_num_rows($result) > 0){
            while($row = pg_fetch_assoc($result)){
                echo '<div class="col-md-6 p-2"><div class="card">
                        <div class="card-body">
                            <a href="/dossier.php?dossier=' . $row['dossier_id'] . '"><h4 class="card-title">' . $row['dossier_name'] . '</h4></a>
                            <h6 class="card-subtitle mb-2 text-muted">' . ($row['type']=='party' ? 'Partij' : ($row['type'] == 'dossier' ? 'Dossier' : ($row['type']==='person' ?'Persoon' : 'Zoekterm'))) . ': ' . getSubscriptionName($row['type'], $row['target_id']) . '</h6>
                            <small>' . $row['maxdate'] . '</small><br>';

                $query3 = 'SELECT category.name AS name, category.id AS id FROM category, dossier_category 
                                    WHERE dossier_category.category_id=category.id
                                    AND dossier_category.dossier_id=' . $row['dossier_id'] . ';';
                     if($result3 = pg_query($link, $query3)){
                          while($categories = pg_fetch_assoc($result3)){
                              echo '<span class="badge badge-info">' . $categories['name'] . '</span>';
                          }
                     }
                echo '<p class="card-text">' . getSubtext($row['subject']) . '</p>
                        </div></div></div>';
            }
        }
        else{
            echo '<div class="row">
                    <div class="col-md-12">
                        <h5>Geen subscriptions gevonden.</h5>
                        <p>Gebruik subscriptions om verschillende onderwerpen te volgen waar je geïntereseerd in bent.<br> 
                        Je kan alles volgen: dossiers, politici, partijen en zelfs je persoonlijke zoektermen.<br>
                        Je kan altijd een element volgen door op het bladwijzer icoontje <i class="far fa-bookmark"></i> te drukken, wanneer deze groen is 
                        <i class="far fa-bookmark" style="color:#28a745"></i> volg je het element. Druk in dit geval nogmaals op het icoon om te stoppen met volgen.</p>
                    </div>
                  </div>';
        }
    }
    else{
        echo pg_errormessage($link) . $query;
    }
}

function printParties(){
    global $link;
    global $subscriptions;
    $resultstring = '';
    $query = 'SELECT party.id AS id, party.name AS name FROM subscriptions, party WHERE subscriptions.type =\'party\' 
      AND party.id=subscriptions.target_id::integer AND subscriptions.user_id=' . $_SESSION['user_id'] . ';';
    if ($result = pg_query($link, $query)) {
        $resultstring .= '<h5>Partijen</h5><ul class=\'list-group\'>';
        while($row = pg_fetch_assoc($result)){
            $href = (isset($_GET['filter']) && $_GET['filter']=='party' && $_GET['party']==$row['id'] ? '/dashboard.php'
                : '/dashboard.php?filter=party&party=' . $row['id']);
            $resultstring .= '<li class=\'list-group-item list-group-item-action ' . (in_array($row['id'], $_SESSION['filter']['party']) ?'active'  : '') . '\'><a href=\'' . $href . '\'>' .
                $row['name']  . '</a><a href="/dashboard.php?person=' . $row['id'] . '&type=person&subscribe">
        <i class="far fa-bookmark subscribeicon ' . (in_array($row['id'], $subscriptions['party'])? 'subscribed' : '' ) . '"></i></a></li>';
        }
        $resultstring .= '</ul>';
    } else {
        echo pg_errormessage($link) . $query;
    }
    return $resultstring;
}

function printPersons(){
    global $link;
    global $subscriptions;
    $resultstring = '';
    $query = 'SELECT person.id AS id, person.name AS name FROM subscriptions, person WHERE subscriptions.type =\'person\' 
      AND person.id=subscriptions.target_id::integer AND subscriptions.user_id=' . $_SESSION['user_id'] . ';';
    if ($result = pg_query($link, $query)) {
        $resultstring .= '<h5>Personen</h5><ul class=\'list-group\'>';
        while($row = pg_fetch_assoc($result)){
            $href = (isset($_GET['filter']) && $_GET['filter']=='person' && $_GET['person']==$row['id'] ? '/dashboard.php'
                : '/dashboard.php?filter=person&person=' . $row['id']);
            $resultstring .= '<li class=\'list-group-item list-group-item-action ' . (in_array($row['id'], $_SESSION['filter']['person']) ?'active'  : '') . '\'><a href=\'' . $href . '\'>' .
                $row['name']  . '</a><a href="/dashboard.php?person=' . $row['id'] . '&type=person&subscribe">
        <i class="far fa-bookmark subscribeicon ' . (in_array($row['id'], $subscriptions['person'])? 'subscribed' : '' ) . '"></i></a></li>';
        }
        $resultstring .= '</ul>';
    } else {
        echo pg_errormessage($link) . $query;
    }
    return $resultstring;
}
function printSearch(){
    global $link;
    global $subscriptions;
    $resultstring = '';
    $query = 'SELECT target_id AS name FROM subscriptions WHERE subscriptions.type =\'search\' 
      AND subscriptions.user_id=' . $_SESSION['user_id'] . ';';
    if ($result = pg_query($link, $query)) {
        $resultstring .= '<h5>Zoektermen</h5><ul class=\'list-group\'>';
        while($row = pg_fetch_assoc($result)){
            $href = (isset($_GET['filter']) && $_GET['filter']=='search' && $_GET['search']==$row['name'] ? '/dashboard.php'
                : '/dashboard.php?filter=search&search=' . $row['name']);
            $resultstring .= '<li class=\'list-group-item list-group-item-action ' . (in_array($row['name'], $_SESSION['filter']['search']) ?'active'  : '') . '\'><a href=\'' . $href . '\'>' .
            $row['name']  . '</a><a href="/dashboard.php?search=' . $row['name'] . '&type=search&subscribe">
        <i class="far fa-bookmark subscribeicon ' . (in_array($row['name'], $subscriptions['search'])? 'subscribed' : '' ) . '"></i></a></li>';
        }
        $resultstring .= '</ul>';
    } else {
        echo pg_errormessage($link);
    }
    return $resultstring;
}

function getDossiersFromSearch(){
    global $link;
    global $subscriptions;
    $resultstring = '<h5>Dossiers</h5><ul class=\'list-group\'>';
    $query = 'SELECT dossier.id AS id, dossier.name AS name FROM subscriptions, dossier WHERE subscriptions.type =\'dossier\' 
      AND dossier.id=subscriptions.target_id::integer AND subscriptions.user_id=' . $_SESSION['user_id'] . ';';
    if ($result = pg_query($link, $query)) {
        while($row = pg_fetch_assoc($result)){
            $href = (isset($_GET['filter']) && $_GET['filter']=='dossier' && $_GET['dossier']==$row['id'] ? '/dashboard.php'
                : '/dashboard.php?filter=dossier&dossier=' . $row['id']);
            $resultstring .= '<li class=\'list-group-item list-group-item-action ' . (in_array($row['id'], $_SESSION['filter']['dossier']) ?'active'  : '') . '\'><a href=\'' . $href . '\'>' .
            $row['name']  . '</a><a href="/dashboard.php?dossier=' . $row['id'] . '&type=dossier&subscribe">
        <i class="far fa-bookmark subscribeicon ' . (in_array($row['id'], $subscriptions['dossier'])? 'subscribed' : '' ) . '"></i></a></li>';
        }
    } else {
        echo pg_errormessage($link) . $query;
    }
    $resultstring .= '</ul>';
    return $resultstring;
}

function getSubjectString($dossier){
    global $link;
    $subjectstring = '';
    $query2 = 'SELECT subject.id, subject.name FROM subject, dossier, subject_to_dossier WHERE subject.id=subject_to_dossier.subject_id 
        AND dossier.id=subject_to_dossier.dossier_id AND subject.dossier_id=' . pg_escape_string($link, $dossier) . ' LIMIT 2;';
    if ($result2 = pg_query($link, $query2)) {
        $subjectstring = '';
        while ($subject = pg_fetch_assoc($result2)) {
            $subjectstring .= '<a href=/text.php?subject=' . $subject['id'] . '>' . $subject['name'] . '</a><br />';
        }
    } else {
        echo pg_errormessage($link);
    }
    return $subjectstring;
}

function getSubtext($subject){
    global $link;
    $statements = [];
    $substringcount = [];
    $query2 = 'SELECT keyword.keyword FROM politicalai_ict.keyword, politicalai_ict.subject WHERE keyword.subject_id=subject.id AND subject.id=' . $subject . ' GROUP BY keyword ORDER BY COUNT(keyword.keyword) DESC LIMIT 1';
    if($result2 = pg_query($link, $query2)){
        $keyword = pg_fetch_assoc($result2)['keyword'];
        $query = 'SELECT statement.id AS id, statement.subject_id AS subject, statement.text AS text FROM statement, subject WHERE subject.id=' . $subject . ' AND statement.subject_id=subject.id AND text LIKE \'%' . $keyword . '%\';';
        if ($result = pg_query($link, $query)) {
            while ($row = pg_fetch_assoc($result)) {
                $statements[] = $row;
                $wordcount = 0;
                $wordcount += substr_count($row['text'], $keyword);
                $substringcount[] = $wordcount;
            }
        } else {
            echo pg_errormessage($link);
        }
        $maxkey = array_keys($substringcount, max($substringcount));
        $resultstring = '';
        $resultstring .= highlightKeywords($keyword, $statements[$maxkey[0]]) . '<br>';
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

function getSubscriptionName($type, $target){
    global $link;
    if($type !== 'search'){
        $query = 'SELECT ' . $type . '.id, ' . $type . '.name FROM ' . $type . ' WHERE id=' . $target . ';';
        if($result = pg_query($link, $query)){
            $row = pg_fetch_assoc($result);
            return '<a href="/' . $type . '.php?' . $type . '=' . $target . '">' . $row['name'] . '</a>';
        }
        else{
            return null;
        }
    }
    else{
        return '<a href="/backoffice.php?' . $type . '=' . $target . '">' . $target . '</a>';
    }
}



include('../header.php');
?>
<main role="main" class="container">
  <div class="row">
    <div class="col-md-3 dashboard-sidebar">
      <?php
        echo printParties();
        echo printPersons();
        echo getDossiersFromSearch();
        echo printSearch();
      ?>
    </div>
    <div class="col-md-9">
      <h3>Nieuwste Dossiers in je abonnementen.</h3>
      <div class="row dashboard-dossiers">
        <?php
        echo getDashboardDossiers();
        ?>
      </div>
    </div>
  </div>
</main>
<?php
include('../footer.php');
?>