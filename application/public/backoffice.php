<?php
session_start();
$type='search';
include('./loginscript.php');
include('../header.php');
include('./subscription.php');

/*
 * Get IDs of dossiers that should be printed
 * Return string with the DIVs prepared with the search results.
 */
function getDossiersFromSearch($searchterm){
    global $link;
    global $subscriptions;
    $searchterm = pg_escape_string($searchterm);
    $searchids = array_unique(getDossierId($searchterm));
    $resultstring = '<h2 style=\'width:100%; text-align:center;\'>Dossiers</h2><div class=\'row results\'>';
    $length = count(findKeywordsInTierOne($searchterm));
    $i = 0;
    foreach($searchids as $id) {
        $query = 'SELECT id, name FROM dossier WHERE id=' . $id . ';';
        if ($result = pg_query($link, $query)) {
            $row = pg_fetch_assoc($result);
            $href = '/dossier.php?dossier=' . $row['id'];
            $resultstring .= '
                <div class=\'col-lg-6 col-md-6 col-xs-12 p-2\'>
                    <div class="card">
                        <div class="card-body">
                            <a href="/backoffice.php?search=' . $_GET['search'] . '&type=dossier&dossier=' . $row['id'] . '&subscribe">
                            <i class="far fa-bookmark subscribeicon ' . (in_array($row['id'], $subscriptions['dossier'])? 'subscribed' : '' ) . '"></i></a>
                            <a href=\'' . $href . '\'><h4>' .
                ( $i < $length ? '<span class="badge badge-secondary">Beste Resultaat</span><br />' : '') .
                $row['name']  . '</h4></a>
                            <h6 class="card-subtitle mb-2 text-muted">' . lastUpdated($row['id']) . '</h6>
                            <br>';

            $query3 = 'SELECT category.name AS name, category.id AS id FROM category, dossier_category 
                                    WHERE dossier_category.category_id=category.id
                                    AND dossier_category.dossier_id=' . $row['id'] . ';';
            if($result3 = pg_query($link, $query3)){
                 while($categories = pg_fetch_assoc($result3)){
                     $resultstring .= '<span class="badge badge-info">' . $categories['name'] . '</span>';
                 }
            }
        $resultstring .= '<p class="card-text">' . getSubtext($searchterm,   $row['id']) . '</p></div></div></div>';
        } else {
            echo pg_errormessage($link);
        }
        $i++;
    }
    $resultstring .= '</div>';
    return $resultstring;
}
/*
 * TODO: Check which dossier should be displayed (max 12)
 * Create the tiers:
 * 1. Keywords
 * 2. Title
 * 3. Text
 */
function getDossierId($searchterm){
    $ids = [];
    $ids=array_merge($ids, findKeywordsInTierOne($searchterm));
    $max = 12 - count($ids);
    $ids=array_merge($ids, findKeywordsInTierTwo($searchterm, $max));
    $max = 12 - count($ids);
    $ids = array_merge($ids, findKeywordsInTierThree($searchterm,$max));
    return $ids;
}

function findKeywordsInTierOne($searchterm){
    global $link;
    $ids = [];
    $parties = checkForParties($searchterm);
    $persons = checkForPersons($searchterm);
    $personwherearray = array();
    $partywherearray = array();
    foreach(explode(' ', $searchterm) as $word){
        foreach($persons[$word] as $person){
            $personwherearray[] = ' statement.person_id=' . $person['id'];
        }
        foreach($parties[$word] as $party){
            $partywherearray[] = ' person.party_id=' . $party['id'];
        }
    }
    $personwhere = join(' OR ', $personwherearray);
    $partywhere = join(' OR ', $partywherearray);
    foreach(explode(' ', $searchterm) as $word){
        if(!in_array($word,array_keys($parties)) && !in_array($word,array_keys($persons))){
            $query = 'SELECT DISTINCT dossier.id AS id FROM keyword, dossier, subject_to_dossier, subject, statement' .
                (count($parties) > 0 ? ', person' : '')  . ' WHERE keyword.subject_id=subject.id AND 
                  subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND keyword LIKE \'%' .$word . '%\'
                  AND statement.subject_id=subject.id ' . ($personwhere != '' ? '  AND (' . $personwhere . ')' : '') .
                (count($parties) > 0 ? ' AND person.id=statement.person_id AND (' . $partywhere . ')' : '') . ' LIMIT 12;';
            if($result = pg_query($link, $query)){
                while($row = pg_fetch_assoc($result)){
                    $ids[] = $row['id'];
                }
            }
            else{
                pg_errormessage($link);
                echo $query;
            }
            $query = 'SELECT DISTINCT dossier.id AS id FROM keyword, dossier, subject_to_dossier, subject 
                  WHERE keyword.subject_id=subject.id AND subject.id=subject_to_dossier.subject_id 
                  AND dossier.id=subject_to_dossier.dossier_id AND keyword LIKE \'%' .$word . '%\' LIMIT 12;';
            if($result = pg_query($link, $query)){
                while($row = pg_fetch_assoc($result)){
                    if(!in_array($row['id'],$ids)){
                        $ids[] = $row['id'];
                    }
                }
            }
            else{
                pg_errormessage($link);
            }
        }
    }
    return $ids;
}

function findKeywordsInTierTwo($searchterm, $max){
    global $link;
    $ids = [];
    $i=0;
    $parties = checkForParties($searchterm);
    $persons = checkForPersons($searchterm);
    $personwherearray = array();
    $partywherearray = array();
    foreach(explode(' ', $searchterm) as $word){
        foreach($persons[$word] as $person){
            $personwherearray[] = ' statement.person_id=' . $person['id'];
        }
        foreach($parties[$word] as $party){
            $partywherearray[] = ' person.party_id=' . $party['id'];
        }
    }
    $personwhere = join(' OR ', $personwherearray);
    $partywhere = join(' OR ', $partywherearray);
    foreach(explode(' ', $searchterm) as $word){
        if(!in_array($word,array_keys($parties)) && !in_array($word,array_keys($persons))){
            $query = 'SELECT DISTINCT dossier.id AS id FROM dossier, subject_to_dossier, subject, statement' .
                (count($parties) > 0 ? ', person' : '')  . ' WHERE subject.id=subject_to_dossier.subject_id 
                AND dossier.id=subject_to_dossier.dossier_id AND subject.name ILIKE \'%' .$word . '%\'
                  AND statement.subject_id=subject.id ' . ($personwhere != '' ? '  AND (' . $personwhere . ')' : '') .
                (count($parties) > 0 ? ' AND person.id=statement.person_id AND (' . $partywhere . ')' : '') . ' LIMIT 12;';
            if($result = pg_query($link, $query)){
                while($row = pg_fetch_assoc($result)){
                    if(!in_array($row['id'],$ids)){
                        $ids[] = $row['id'];
                    }
                }
            }
            else{
                pg_errormessage($link);
                echo $query;
            }
            $query = 'SELECT DISTINCT dossier.id AS id FROM dossier, subject_to_dossier, subject 
                  WHERE subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id 
                  AND subject.name LIKE \'%' .$word . '%\' LIMIT 12;';
            if($result = pg_query($link, $query)){
                while($row = pg_fetch_assoc($result)){
                    if(!in_array($row['id'],$ids)){
                        $ids[] = $row['id'];
                    }
                }
            }
            else{
                echo pg_errormessage($link);
            }
        }
    }
    return $ids;
}

function findKeywordsInTierThree($searchterm, $max){
    global $link;
    $ids = [];
    $parties = checkForParties($searchterm);
    $persons = checkForPersons($searchterm);
    $personwherearray = array();
    $partywherearray = array();
    foreach(explode(' ', $searchterm) as $word){
        foreach($persons[$word] as $person){
            $personwherearray[] = ' statement.person_id=' . $person['id'];
        }
        foreach($parties[$word] as $party){
            $partywherearray[] = ' person.party_id=' . $party['id'];
        }
    }
    $personwhere = join(' OR ', $personwherearray);
    $partywhere = join(' OR ', $partywherearray);
    $wordwhere = array();
    foreach(explode(' ', $searchterm) as $word) {
        if (!in_array($word, array_keys($parties)) && !in_array($word, array_keys($persons))) {
            $wordwhere[] = 'lower(statement.text) LIKE LOWER(\'%' . $word . '%\')';
        }
    }
    $wordwherestring = join(' OR ', $wordwhere);
    $query = 'SELECT DISTINCT dossier.id AS id FROM dossier, subject_to_dossier, subject, statement' .
        (count($parties) > 0 ? ', person' : '') . ' WHERE subject.id=subject_to_dossier.subject_id 
        AND dossier.id=subject_to_dossier.dossier_id ' . ($wordwherestring !=''? ' AND (' . $wordwherestring  . ')' : '' ) . '
          AND statement.subject_id=subject.id' . ($personwhere != '' ? '  AND (' . $personwhere . ')' : '') .
        (count($parties) > 0 ? ' AND person.id=statement.person_id AND (' . $partywhere . ')' : '') . ' LIMIT 12;';
    if ($result = pg_query($link, $query)) {
        while ($row = pg_fetch_assoc($result)) {
            if (!in_array($row['id'], $ids)) {
                $ids[] = $row['id'];
            }
        }
    } else {
        echo pg_errormessage($link);
        echo $query;
    }

    /*foreach(explode(' ', $searchterm) as $word) {
        if (!in_array($word, array_keys($parties)) && !in_array($word, array_keys($persons))) {
            $query = 'SELECT DISTINCT dossier.id AS id FROM dossier, subject_to_dossier, subject, statement 
                  WHERE subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND
                  statement.subject_id=subject.id AND lower(statement.text) LIKE \'%' .$word . '%\';';
            echo $query;
            if($result = pg_query($link, $query)){
                while($row = pg_fetch_assoc($result)){
                    if(!in_array($row['id'],$ids)){
                        $ids[] = $row['id'];
                    }
                }
            }
            else{
                echo pg_errormessage($link);
                echo $query;
            }
        }
    }*/
    return $ids;
}
/*
 * Check if there was a person in the searchterm.
 */
function checkForPersons($searchterm){
    global $link;
    $persons=[];
    foreach(explode(' ', $searchterm) as $word) {
        $query = 'SELECT person.id AS id, person.name AS name, party.id AS party_id, party.name AS party_name FROM party, person WHERE 
        party.id=person.party_id AND person.name ILIKE \'%' . $word . '%\';';
        if ($result = pg_query($query)) {
            while ($row = pg_fetch_assoc($result)) {
                if(!array_key_exists($word,$persons)){
                    $persons[$word] = [];
                }
                $persons[$word][] = $row;
            }
        } else {
            echo pg_errormessage($link);
        }
    }
    return $persons;
}

/*
 * Get the new subtext for the dossier, indicating where the searchterm was used.
 */
function getSubtext($searchterm, $dossier){
    global $link;
    $statements = [];
    $substringcount = [];
    foreach(explode(' ', $searchterm) as $word){
        $query = 'SELECT statement.id AS id, statement.subject_id AS subject, statement.text AS text FROM statement, dossier, subject, subject_to_dossier WHERE dossier.id=' . $dossier . ' AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id
            AND statement.subject_id=subject.id AND text LIKE \'%' . $word . '%\';';
        if($result = pg_query($link, $query)){
            while($row = pg_fetch_assoc($result)){
                $statements[] = $row;
                $wordcount = 0;
                foreach(explode(' ', $searchterm) as $word){
                    $wordcount += substr_count($row['text'], $word);
                }
                $substringcount[] = $wordcount;
            }
        }
        else{
            echo pg_errormessage($link);
        }
    }
    $maxkey = array_keys($substringcount, max($substringcount));
    $resultstring = '';
    //foreach($maxkey as $key){
    $resultstring .= highlightKeywords($searchterm, $statements[$maxkey[0]]) . '<br>' ;
    //}

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

/*
 * Print the persons if there are any in the searchterm.
 */
function printPersons($searchterm){
    global $link;
    global $subscriptions;
    $persons = checkForPersons($searchterm);
    if(count($persons) > 0){
        $resultstring = '<h2 style=\'width:100%; text-align:center;\'>Personen</h2><div class=\'row results\'>';
        foreach(array_values($persons) as $personlist){
            foreach($personlist as $person){
                $personlink = '/person.php?person=' . $person['id'];
                $resultstring .= '
                    <div class=\'col-lg-6 col-md-12 col-xs-12\'>
                       <div class="card">
                           <div class="card-body">
                           		<a href="/backoffice.php?search=' . $_GET['search'] . '&type=person&person=' . $person['id'] . '&subscribe">
                           		<i class="far fa-bookmark subscribeicon  ' . (in_array($person['id'],$subscriptions['person'])? 'subscribed' : '' ) . '"></i></a>
						        <a href=\'' . $personlink . '\'><h4>' . $person['name'] . '</h4></a>
						        <a href="/party.php?party=' . $person['party_id'] . '"><h5>' . $person['party_name'] . '</h5></a>';
                $query2 = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, statement, subject, subject_to_dossier
        , minute WHERE minute.id=subject.minute_id AND subject.id=subject_to_dossier.subject_id AND statement.person_id=' . $person['id'] . '  
        AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 2';
                if($result = pg_query($link, $query2)){
                    while($row = pg_fetch_assoc($result)){
                        $resultstring .= '<h6 class="card-subtitle mb-2 text-muted">' . $row['maxdate'] . '</h6><a href="/dossier.php?dossier=' . $row['id'] . '"><p>' . $row['name'] . '</p></a>';
                    }
                }
				$resultstring.='</div>
						</div>
					</div>';
            }
        }
        $resultstring .= '</div>';
        return $resultstring;
    }
    return '';
}
/*
 * Check if there is a party in the searchterm.
 */
function checkForParties($searchterm){
    global $link;
    $parties=[];
    foreach(explode(' ', $searchterm) as $word) {
        $query = 'SELECT id, name FROM party WHERE name ILIKE \'%' . $word . '%\';';
        if ($result = pg_query($query)) {
            while ($row = pg_fetch_assoc($result)) {
                if(!array_key_exists($word,$parties)){
                    $parties[$word] = [];
                }
                $parties[$word][] = $row;
            }
        } else {
            echo pg_errormessage($link);
        }
    }
    return $parties;
}
/*
 * Print parties in the searchterm.
 */
function printParties($searchterm){
    global $link;
    global $subscriptions;
    $parties = checkForParties($searchterm);
    if(count($parties) > 0){
        $resultstring = '<h2 style=\'width:100%; text-align:center;\'>Parties</h2><div class=\'row results\'>';
        foreach(array_values($parties) as $partylist){
            foreach($partylist as $party){
                $partylink = '/party.php?party=' . $party['id'];
                $resultstring .= '
                    <div class=\'col-lg-6 col-md-12 col-xs-12\'>
                       <div class="card">
                           <div class="card-body">
                               <a href="/backoffice.php?search=' . $_GET['search'] . '&type=party&party=' . $party['id'] . '&subscribe">
                               <i class="far fa-bookmark subscribeicon  ' . (in_array($party['id'],$subscriptions['party'])? 'subscribed' : '' ) . '"></i></a>
						        <a href=\'' . $partylink . '\'><h4>' . $party['name'] . '</h4></a>';
                $query2 = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, person, statement, subject, subject_to_dossier
        , minute WHERE minute.id=subject.minute_id AND subject.id=subject_to_dossier.subject_id AND person.party_id=' . $party['id'] . '  
        AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id AND statement.person_id=person.id 
        GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 2';
                if($result = pg_query($link, $query2)){
                    while($row = pg_fetch_assoc($result)){
                        $resultstring .= '<h6 class="card-subtitle mb-2 text-muted">' . $row['maxdate'] . '</h6><a href="/dossier.php?dossier=' . $row['id'] . '"><p>' . $row['name'] . '</p></a>';
                    }
                }
                $resultstring.='</div>
						</div>
					</div>';
            }
        }
        $resultstring .= '</div>';
        return $resultstring;
    }
    return '';
}
/*
 * Get 3 subjects in the dossier to display below it.
 */
function getSubjectString($dossier){
    global $link;
    $subjectstring = '';
    $query2 = 'SELECT subject.id, subject.name FROM subject, dossier, subject_to_dossier WHERE subject.id=subject_to_dossier.subject_id 
        AND dossier.id=subject_to_dossier.dossier_id AND subject.dossier_id=' . pg_escape_string($link, $dossier) . ' LIMIT 3;';
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

function lastdossiers($type, $id){
    global $link;
    if($type == 'person'){
        $query = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, statement, subject, subject_to_dossier
        , minute WHERE minute.id=subject.minute_id AND subject.id=subject_to_dossier.subject_id AND statement.person_id=' . $id . '  
        AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 3';
    }
    else{
        $query = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, statement, subject, subject_to_dossier
        , minute WHERE minute.id=subject.minute_id AND statement.person_id=person.id AND person.party_id=' . $id . ' AND subject.id=subject_to_dossier.subject_id 
        AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 3';
    }
    if($result = pg_query($link, $query)){
        return $result;
    }
}


// array of words to check against
$words  = array('pechtold','pineapple','banana','orange',
    'radish','carrot','pea','bean','potato');

// no shortest distance found, yet
$shortest = -1;

// loop through words to find the closest
foreach ($words as $word) {

    // calculate the distance between the input word,
    // and the current word
    $lev = levenshtein($_GET['search'], $word);

    // check for an exact match
    if ($lev == 0) {

        // closest word is this one (exact match)
        $closest = $word;
        $shortest = 0;

        // break out of the loop; we've found an exact match
        break;
    }

    // if this distance is less than the next found shortest
    // distance, OR if a next shortest word has not yet been found
    if ($lev <= $shortest || $shortest < 0) {
        // set the closest match, and shortest distance
        $closest  = $word;
        $shortest = $lev;
    }
}


?>

<main role="main" class="container">
  <div class='searchdiv'>
    <div class='col-md-12'>
      <form method="get" action="/backoffice.php" class="form-inline my-12 my-lg-0">
        <div class='col-md-10'>
          <input class="searchbar form-control" type="text" name='search' placeholder="Zoek hier voor personen, dossiers of onderwerpen." aria-label="Zoek hier voor personen, dossiers of onderwerpen.">
        </div>
        <div class='col-md-2'>
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Zoeken</button>
        </div>
      </form>
    </div>
  </div>

  <?php
  if(isset($_GET['search'])){

  ?>
  <div  class='row search'>
      <?php /* if($shortest == 0 || $shortest > 3){
          */?>
      <h1>Zoekterm: <?php echo $_GET['search'];?></h1> <?php echo '<a class="subscribe-button ' . (in_array($_GET['search'], $subscriptions['search'])==1 ? 'subscribe' : 'inverse') . '" href="?search='. $_GET['search'] . '&type=search&subscribe">' . (in_array($_GET['search'], $subscriptions['search'])==1 ? 'Unsubscribe' : 'Subscribe') . '</a>';?>
    <?php
    /*}else{
          ?>
      <h1>Bedoelde je? <a href="/backoffice.php?search=<?php echo $closest?>"><?php echo $closest?></a></h1>
      <?php
    }*/

    //GET KEYWORDS
    echo printPersons($_GET['search']);
    echo printParties($_GET['search']);
    echo getDossiersFromSearch($_GET['search']);
    ?>
  </div>
  <?php
  }
  else
  {
    $query = 'SELECT subject.id AS id, subject.name AS subject, minute.name AS minute, dossier.name AS dossier_name, dossier.id AS dossier, dossier.onlineid AS onlineid FROM subject, minute, dossier, subject_to_dossier
      WHERE subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND subject.dossier_id <> 0 AND subject.minute_id=minute.id ORDER BY date DESC, dossier LIMIT 10;';
    if($result = pg_query($link,$query)){
          ?>
      <h2 class='pagetitle' style='width:100%; text-align:center;'>Nieuwste Dossiers</h2>
      <div class='row results'>
        <?php
        while($subject =pg_fetch_assoc($result)){
          $query2 = 'SELECT keyword.keyword FROM politicalai_ict.keyword, politicalai_ict.subject, politicalai_ict.subject_to_dossier, politicalai_ict.dossier
	WHERE keyword.subject_id=subject.id AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND dossier.id=' . $subject['dossier'] . '
    GROUP BY keyword ORDER BY COUNT(keyword.keyword) DESC LIMIT 1';
          if($result2 = pg_query($link, $query2)){
              $subjectlink = '/dossier.php?dossier=' . $subject['dossier'];
              $keyword = pg_fetch_assoc($result2)['keyword'];
              ?>
              <div class='col-lg-6 col-md-6 col-xs-12 p-2'>
                  <div class="card">
                      <div class="card-body">
                          <a href="/backoffice.php?type=dossier&dossier=<?php echo $subject['dossier']; ?>&subscribe">
                          <i class="far fa-bookmark subscribeicon <?php echo (in_array($subject['dossier'], $subscriptions['dossier'])? 'subscribed' : '' );?>"></i></a>
                          <a href=<?php echo $subjectlink ?>><h4><?php echo $subject['dossier_name']; ?></h4></a>
                          <h6 class="card-subtitle mb-2 text-muted"><?php echo lastUpdated($subject['dossier']) ?></h6><br>
                          <?php
                          $query3 = 'SELECT category.name AS name, category.id AS id FROM category, dossier_category 
                                    WHERE dossier_category.category_id=category.id
                                    AND dossier_category.dossier_id=' . $subject['dossier'] . ';';
                          if($result3 = pg_query($link, $query3)){
                              while($categories = pg_fetch_assoc($result3)){
                                  ?>
                                  <span class="badge badge-info"><?php echo $categories['name'];?></span>
                                  <?php
                              }
                          }
                          ?>
                          <p><?php echo getSubtext($keyword, $subject['dossier']); ?></p>
                      </div>
                  </div>
              </div>
              <?php
          }
        }?>
      </div>
    <?php
    }
    else{
      echo $query;
    }
  }
    ?>
</main><!-- /.container -->
<?php
include('footer.php')
?>