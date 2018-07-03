<?php
set_time_limit('600');
include('../config.php');

$query='DELETE FROM keyword;';
if($result = pg_query($link, $query)){echo 'Deleted';}

$keywords = file_get_contents('newtfidfkeywords.txt');
foreach(explode(';',$keywords) as $subject){
    echo $subject . 'TEXTTEXTTEXT';
    $elements = explode(':',$subject);
    foreach(explode(',',$elements[1]) as $keyword){
        $query = 'INSERT INTO keyword(subject_id, keyword) VALUES(' . $elements[0] . ',\'' . $keyword . '\');';
        if($result = pg_query($link, $query)){
            echo 'Inserted';
        }
        else{
            echo pg_errormessage($link);
        }
    }
}

?>