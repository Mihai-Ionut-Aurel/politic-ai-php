<?php
include('../config.php');
$texts = array();
$query = 'SELECT statement.subject_id, statement.text FROM `statement`, subjectcategories WHERE statement.subject_id = subjectcategories.subject_id AND subjectcategories.category_id=17';
if($result = pg_query($link,$query)){
	while($row =pg_fetch_assoc($result)){
		if(!array_key_exists($row['subject_id'],$texts)){
			$texts[$row['subject_id']] = '';
		}
		$texts[$row['subject_id']] .= $row['text'];
	}
}
else{

}

print_r($texts);
?>