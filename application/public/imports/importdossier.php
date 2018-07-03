<?php 
include('../config.php');
/*
$query = 'SELECT DISTINCT dossier FROM subject WHERE dossier <> 0;';
if($result = pg_query($link,$query)){
	while($dossier =pg_fetch_assoc($result)){
		$namequery = 'SELECT subject.name AS name FROM subject, minute WHERE dossier=' . $dossier['dossier'] . ' AND subject.minute_id = minute.id ORDER BY minute.date ASC LIMIT 1;';
		if($nameresult = $link->query($namequery)){
			$queryinsert = 'INSERT INTO dossier(name, onlineid) VALUES("' . $link->real_escape_string($nameresult->fetch_assoc()['name']) . '",' . $dossier['dossier'] . ');';
			if($link->query($queryinsert)){
			
			}
			else{
				echo $queryinsert;
			}
		}
	}
}
*/
$query = 'SELECT subject.id AS subject, subject.dossier_id AS dossier FROM subject WHERE subject.dossier_id > 500;';
if($result = pg_query($link,$query)){
	while($subject =pg_fetch_assoc($result)){
		$query2 = 'SELECT dossier.id FROM dossier WHERE dossier.onlineid=' . $subject['dossier'] . ';';
		if($result2 = pg_query($link,$query2)){
			if(pg_num_rows($result2) > 0){
				$updatequery = 'UPDATE subject SET dossier=' . pg_fetch_assoc($result2)['id'] . ' WHERE id=' . $subject['subject'] . ';';
				if($result2 = $link->query($updatequery)){
					
				}
				else{
					echo $updatequery;
				}
			}
		}

	}
}
else{
	echo $query;
}
?>