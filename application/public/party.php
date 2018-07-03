<?php
session_start();
$type = 'party';
include('./loginscript.php');
include('../header.php');
include('./subscription.php');

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

function printParty($id, $name){
    global $link;
    global $subscriptions;
    $query = 'SELECT dossier.id, dossier.name, MAX(minute.date) AS maxdate FROM dossier, statement, subject, subject_to_dossier
        , minute, person WHERE statement.person_id=person.id AND person.party_id=' . $id . ' AND minute.id=subject.minute_id AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id AND statement.subject_id=subject.id GROUP BY dossier.id ORDER BY maxdate DESC, dossier.id LIMIT 2';
    if($result = pg_query($link, $query)){
        $returnstring = '<div class=\'col-lg-6\'><div class="card"><div class="card-body">
        <a href="/party.php?party=' . $_GET['party'] . '&dossier=' . $id . '&type=dossier&subscribe">
        <i class="far fa-bookmark subscribeicon' . (in_array($id, $subscriptions['dossier'])? 'subscribed' : '' ) . '"></i></a>
        <a href="/party.php?party=' . $id . '">
        <h4>' . $name . '</h4></a>';
        while($row = pg_fetch_assoc($result)){
            $returnstring .= '<h6 class="card-subtitle mb-2 text-muted">' . $row['maxdate'] . '</h6><a href="/dossier.php?dossier=' . $row['id'] .'"><p class="card-text">' . $row['name'] . '</p></a>';
        }
        $returnstring .='</p></div></div></div>';
    }
    else{
        echo pg_errormessage($link);
    }
    return $returnstring;
}

?>
<main role="main" class="container">
    <?php
	$query = '';
	$subject = '';
	if(isset($_GET['party'])){
		$query = 'SELECT * FROM party WHERE id=' . $_GET['party']. ';';
		if($result = pg_query($link,$query)){
			$name =pg_fetch_assoc($result)['name'];
			?>
			<div class="starter-template">
				<h1><?php echo $name;?></h1><?php echo '<a class="subscribe-button ' . (in_array($subscriptions['party'], $_GET['party']) ? 'subscribe' : 'inverse') . '" href="?party='. $_GET['party'] . '&subscribe">' . (in_array($subscriptions['party'], $_GET['party']) ? 'Unsubscribe' : 'Subscribe') . '</a>'?>
			</div>
			<div class='row results'>
				<?php
				$query2 = 'SELECT * FROM person WHERE party_id=' . $_GET['party'] . ';';
				if($result2 = pg_query($link,$query2)){
					while($row = pg_fetch_assoc($result2)){
					?>
						<?php echo printPerson($row['id'], $row['name']) ?>
					<?php
					}
				}
				?>
			</div>
	<?php
		}
	}
	else
	{
		?>
		<div class="starter-template">
		</div>
		<div class='row results'>
			<?php
			$query2 = 'SELECT * FROM party;';
			if($result2 = pg_query($link,$query2)){
				while($party = pg_fetch_assoc($result2)){
				    echo printParty($party['id'], $party['name']);
				}
			}
			?>
		</div>
		<?php
	}
	?>
</main>