<?php
/**
 * Created by PhpStorm.
 * User: yanni
 * Date: 14/06/2018
 * Time: 17:38
 */
include('../config.php');
echo 'text';
$query = 'SELECT * FROM politicalai_ict.person WHERE id > 275;';
if($result = pg_query($link, $query)){
    while($row = pg_fetch_assoc($result)){
        $query2 = 'SELECT person.id FROM politicalai_ict.person WHERE name ILIKE \'%' . $row['name'] . '%\' ORDER BY id LIMIT 1;';
        if($result2 = pg_query($link, $query2)){
            $person = pg_fetch_assoc($result2);
            $query3 = 'UPDATE statement SET person_id=' . $person['id'] . ' 
            WHERE person_id=' . $row['id'] . ';';
            if($result3 = pg_query($link, $query3)){
                echo 'fixed';
            }
            else{
                echo pg_errormessage($link);
            }
        }
        else{
            echo pg_errormessage($link);
        }
    }
}
else{
    echo pg_errormessage($link);
}


