<?php
session_start();
$type='dossier';
include('./loginscript.php');
include('../header.php');
include('./subscription.php');

function print_debates($dossier){
    global $link;
    $query = 'SELECT subject.id, subject.name, minute.date FROM subject, minute, subject_to_dossier WHERE subject.minute_id=minute.id AND subject.id=subject_to_dossier.subject_id AND subject.id NOT IN (SELECT subject_id FROM vote) AND  subject_to_dossier.dossier_id=' . $dossier . ' ORDER BY minute.date DESC;';
    $print = '';
    if($result = pg_query($link, $query)){
        while($row = pg_fetch_assoc($result)){
            $print .= 	'
                <div class="col-md-12 p-2">
                    <div class="card">
                        <div class="card-body">
                            <a href="/text.php?subject=' . $row['id'] . '"><h4>' .  $row['name'] . '</h4></a>
                            <h6 class="card-subtitle mb-2 text-muted">' . $row['date'] . '</h6>
                            <small></small>
                            <p class="card-text">' . getSubtext($row['id']) . '</p><br />
                            <p class="card-text keywords">' . getKeywords($row['id']) . '</p>
                        </div>
                    </div>
                </div>';
        }
    }
    return $print;
}
function print_votes($dossier){
    global $link;
    $query = 'SELECT subject.id, subject.name FROM subject, minute, subject_to_dossier WHERE subject.minute_id=minute.id AND subject.id=subject_to_dossier.subject_id AND subject.id IN (SELECT subject_id FROM vote) AND  subject_to_dossier.dossier_id=' . $dossier . ' ORDER BY minute.date DESC;';
    $print = '';
    if($result = pg_query($link, $query)){
        while($row = pg_fetch_assoc($result)){
            $print .= 	'<div class="col-md-12 p-2">
                    <div class="card">
                        <div class="card-body">
                        	<a href="/text.php?subject=' . $row['id'] . '"><h4>' .  $row['name'] . '</h4></a>
                        </div>
                    </div>
                </div>';
        }
    }
    return $print;
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

function getKeywords($id){
    global $link;
    $query = 'SELECT * FROM keyword WHERE subject_id=' . $id .' LIMIT 10;';
    $keywords = [];
    if($result = pg_query($link, $query)){
        while($row = pg_fetch_assoc($result)){
            $keywords[] = '<a href=\'/backoffice.php?search=' . $row['keyword'] . '\'>' . $row['keyword'] . '</a>';
        }
    }
    return join($keywords, ', ');
}

function getRelatedDossiers($id){
    global $link;
    $resultstring = '<h5>Related Dossiers</h5><ul class=\'list-group\'>';
    $query = 'SELECT dossier.id AS id, dossier.name AS name FROM related_dossiers, dossier 
                WHERE dossier.id=related_dossiers.right_dossier_id AND related_dossiers.left_dossier_id=' . $id . ' Limit 5;';
    if ($result = pg_query($link, $query)) {
        while($row = pg_fetch_assoc($result)){
            $href = (isset($_GET['filter']) && $_GET['filter']=='dossier' && $_GET['dossier']==$row['id'] ? '/dashboard.php'
                : '/dossier.php?dossier=' . $row['id']);
            $resultstring .= '<a class=\'list-group-item list-group-item-action ' . (isset($_GET['filter']) &&
                $_GET['filter']=='dossier' && $_GET['dossier']==$row['id'] ?'active'  : '') . '\' href=\'' . $href . '\'>' .
                $row['name']  . '</a>';
        }
    } else {
        echo pg_errormessage($link);
    }
    $resultstring .= '</ul>';
    return $resultstring;
}
?>


<main role="main" class="container">
    <?php
	$query = '';
	$subject = '';
	if(isset($_GET['dossier'])){
		$query = 'SELECT * FROM dossier WHERE id=' . $_GET['dossier']. ';';
		if($result = pg_query($link,$query)){
			$dossier =pg_fetch_assoc($result);
			?>
			<div class="starter-template">
				<?php echo '<h2>' .  $dossier['name'] .  '</h2><a class="subscribe-button ' . (in_array($_GET['dossier'],
                        $subscriptions['dossier']) ? 'subscribe' : 'inverse') . '" href="?dossier='. $_GET['dossier'] . '&subscribe">' .
                    (in_array($_GET['dossier'], $subscriptions['dossier']) ? 'Unsubscribe' : 'Subscribe') . '</a>'?>
				<?php
				if(isset($_GET['person'])){
					$query2 = 'SELECT person.name AS name, person.id AS id FROM person WHERE id=' . $_GET['person'] . 'ORDER BY name ASC';
					if($result = pg_query($link,$query2)){
						$person =pg_fetch_assoc($result);
				?>
						<h2><?php echo $person['name'];?></h2>
				<?php
					}
				}
				?>
			</div>
			<div class='row'>
				<div class='col-lg-12'>
					<p>
					<?php echo $dossier['description'];?>
					</p>
				</div>
			</div>
			<div class='row results'>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6 result-group">
                            <h2>Debatten</h2>
                            <div class="row">
                            <?php echo print_debates($_GET['dossier']) ?>
                            </div>
                        </div>
                        <div class="col-md-6 result-group">
                            <h2>Stemmingen</h2>
                            <div class="row">
                            <?php echo print_votes($_GET['dossier']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class='col-lg-12'>
                        <?php
                        echo getRelatedDossiers($_GET['dossier'])
                        ?>
                    </div>
                </div>
			</div>

	<?php
		}
		else{
			echo $query;
		}
	}
	?>
</main>