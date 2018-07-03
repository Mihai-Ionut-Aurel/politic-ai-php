<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
include('../../config.php');
include('../simple_html_dom.php');
require_once("../vendor/autoload.php");
require_once('../gburtini/Distributions/Normal.php');
$begin = new DateTime('2017-04-20');
$end = new DateTime('2018-06-16');
set_time_limit(36000);

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);
$intervalperiod = new DateInterval('P1D');
ini_set('memory_limit', '200000000');

function mean($array){
	return array_sum($array) / count($array);
}
// Function to calculate square of value - mean
function sd_square($x, $mean) { return pow($x - $mean,2); }

// Function to calculate standard deviation (uses sd_square)    
function sd($array) {
    // square root of sum of squares devided by N-1
    return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
}
function getdutchdate($date){
	$resultdate = '';
	$datearray = explode(' ', $date);
	$day = $datearray[0];
	$month = '';
	if($datearray[1] == 'januari'){
		$month = '01';
	}
	elseif($datearray[1] == 'februari'){
		$month = '02';
	}
	elseif($datearray[1] == 'maart'){
		$month = '03';
	}
	elseif($datearray[1] == 'april'){
		$month = '04';
	}
	elseif($datearray[1] == 'mei'){
		$month = '05';
	}
	elseif($datearray[1] == 'juni'){
		$month = '06';
	}
	elseif($datearray[1] == 'juli'){
		$month = '07';
	}
	elseif($datearray[1] == 'augustus'){
		$month = '08';
	}
	elseif($datearray[1] == 'september'){
		$month = '09';
	}
	elseif($datearray[1] == 'oktober'){
		$month = '10';
	}
	elseif($datearray[1] == 'november'){
		$month = '11';
	}
	elseif($datearray[1] == 'december'){
		$month = '12';
	}	
	$year = trim($datearray[2]);
	$resultdate = $year . '-' . $month . '-' . $day;
	echo $resultdate;
	return $resultdate;
}

// Persons

function loadpersons()
{
    global $link;
    $persons = [];
    $query = 'SELECT id, name, party_id AS party FROM person;';
    if ($result = pg_query($link, $query)) {
        if (pg_num_rows($result) > 0) {
            while ($person = pg_fetch_assoc($result)) {
                $persons[$person['id']]['name'] = $person['name'];
                $persons[$person['id']]['party_id'] = $person['party'];
            }
        }
    }
    return $persons;
}

$persons = loadpersons();

function findperson($entry){
    global $link;
    global $persons;
	$result = -1;
	foreach($persons as $id => $person){
		if($person['name'] === $entry['name'] && $person['party_id']==$entry['party_id']){
			$result = $id;
            break;
        }
	}
	if($result >= 0){
		return $result;
	}
    else{
        $query = 'INSERT INTO person(id, name, party_id) VALUES(DEFAULT, \'' . pg_escape_string($link, $entry['name']) . '\', \'' . $entry['party_id'] . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $persons[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
	return false;
}


// Parties
function loadparties(){
    global $link;
    $parties = [];
    $query = 'SELECT id, name FROM party;';
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($party =pg_fetch_assoc($result)){
                $parties[$party['id']]['name'] = $party['name'];
            }
        }
    }
    return $parties;
}

$parties = loadparties();

function findparty($entry){
    global $link;
    global $parties;
	$result = -1;
	echo $entry['name'];
	foreach($parties as $id => $party){
		if(strtolower($party['name']) === strtolower($entry['name'])){
			$result = $id;
            break;
        }
	}
	if($result >= 0){
		return $result;
	}
    else{
        $query = 'INSERT INTO party(id, name) VALUES(DEFAULT, \'' . pg_escape_string($link, $entry['name']) . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $parties[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
	return false;
}


// Subjects

function loadsubjects(){
    global $link;
    $subjects = [];
    $query = 'SELECT id, name, minute_id AS minute FROM subject;';
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($subject =pg_fetch_assoc($result)){
                $subjects[$subject['id']]['name'] = $subject['name'];
                $subjects[$subject['id']]['minute_id'] = $subject['minute'];
            }
        }
    }
    return $subjects;
}

$subjects = loadsubjects();

function findsubject($entry){
    global $subjects;
    global $link;
	$result = -1;
	foreach($subjects as $id => $subject){
		if(strcasecmp($subject['name'], $entry['name']) && $subject['minute_id']==$entry['minute_id']){
			$result = $id;
            break;
        }
	}
	if($result >= 0){
		return $result;
	}
    else {
        if(isUseful($entry)){
            $query = 'INSERT INTO subject(id, name, minute_id, dossier_id) VALUES(DEFAULT, \'' . pg_escape_string($link, $entry['name']) . '\', \'' .
                $entry['minute_id'] . '\' ,\'' . $entry['dossier_id'] . '\') RETURNING id;';
            if ($result = pg_query($link, $query)) {
                $id = pg_fetch_assoc($result)['id'];
                $subjects[$id] = $entry;
                return $id;
            } else {
                echo '<br>' . $query . '<br>';
                echo pg_errormessage($link);
            }
        }
    }
	return false;
}

function isUseful($subject){
    if(strpos($subject['name'], 'Opening')!== false || strpos($subject['name'], 'Sluiting')!== false || strpos($subject['name'], 'Hamerstukken')!== false || strpos($subject['name'], 'Mededeling')!== false || strpos($subject['name'], 'Regeling van werkzaamheden') !== false || strpos($subject['name'], 'Afscheid van het lid') !== false){
        return false;
    }
    else{
        return true;
    }
}

// Minutes

function loadminutes(){
    global $link;
    $query = 'SELECT id, name, date FROM minute;';
    $minutes = [];
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($minute =pg_fetch_assoc($result)){
                $minutes[$minute['id']]['name'] = $minute['name'];
                $minutes[$minute['id']]['date'] = $minute['date'];
            }
        }
    }
    return $minutes;
}
$minutes = loadminutes();

function findminute($entry){
    global $link;
    global $minutes;
    $result = -1;
    foreach($minutes as $id => $minute){
        if(strcasecmp($minute['name'], $entry['name']) && $minute['date']==$entry['date']){
            $result = $id;
            break;
        }
    }
    if($result >= 0){
        return $result;
    }
    else{
        $query = 'INSERT INTO minute(id, name, date) VALUES(DEFAULT, \'' . pg_escape_string($link, $entry['name']) . '\', \'' . $entry['date'] . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $minutes[$id] = $entry;
            return $id;
        }
        else {
            echo $query;
            echo pg_errormessage($link);
        }
    }
    return false;
}


// Statements
function loadstatements(){
    global $link;
    $statements = [];
    $query = 'SELECT * FROM statement;';
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($statement =pg_fetch_assoc($result)){
                $statements[$statement['id']]['subject_id'] = $statement['subject_id'];
                $statements[$statement['id']]['person_id'] = $statement['person_id'];
                $statements[$statement['id']]['text'] = $statement['text'];
            }
        }
    }
    return $statements;
}
$statements = loadstatements();

function findstatement($entry){
	$result = -1;
	global $link;
	global $statements;
	foreach($statements as $id => $statement){
		if($statement['subject_id']==$entry['subject_id'] && $statement['person_id']==$entry['person_id'] &&
        strcasecmp($statement['text'], $entry['text'])){
			$result = $id;
            break;
        }
	}
	if($result >= 0){
		return $result;
	}
    else{
        $query = 'INSERT INTO statement(subject_id, person_id, text) VALUES(\'' . $entry['subject_id'] . '\', \'' .
            $entry['person_id'] . '\' ,\'' . pg_escape_string($link, $entry['text']) . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $statements[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
	return false;
}


// Votes

function loadvotes(){
    global $link;
    $query = 'SELECT id, name, voteindex, subject_id FROM vote;';
    $votes = [];
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($vote =pg_fetch_assoc($result)){
                $votes[$vote['id']]['name'] = $vote['name'];
                $votes[$vote['id']]['voteindex'] = $vote['voteindex'];
                $votes[$vote['id']]['subject_id'] = $vote['subject_id'];
            }
        }
    }
    return $votes;
}
$votes = loadvotes();

function findvote($entry){
    $result = -1;
    global $link;
    global $votes;
    foreach($votes as $id => $vote){
        echo strcasecmp($vote['voteindex'],$entry['voteindex']);
        echo strcasecmp($vote['name'],$entry['name']);
        echo $vote['subject_id'] == $entry['subject_id'];
        if(strcasecmp($vote['name'],$entry['name'])
            && strcasecmp($vote['voteindex'],$entry['voteindex'])
            && $vote['subject_id'] == $entry['subject_id']){
            $result = $id;
            break;
        }
    }
    if($result >= 0){
        return $result;
    }
    else{
        $query = 'INSERT INTO vote(name, voteindex, subject_id, pdflink) VALUES(\'' . pg_escape_string($link, $entry['name']) . '\', \'' .
            pg_escape_string($link, $entry['voteindex']) . '\' ,\'' . $entry['subject_id'] . '\',\'' . pg_escape_string($link, $entry['pdflink']) . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id =  pg_fetch_assoc($result)['id'];
            $votes[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
    return false;
}

// Voteresults

function loadvoteresults(){
    global $link;
    $query = 'SELECT id, vote_id, party_id FROM voteperparty;';
    $voteresults = [];
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($voteresult =pg_fetch_assoc($result)){
                $voteresults[$voteresult['id']]['vote_id'] = $voteresult['vote_id'];
                $voteresults[$voteresult['id']]['party_id'] = $voteresult['party_id'];
            }
        }
    }
    return $voteresults;
}
$voteresults = loadvoteresults();

function findvoteresult($entry){
    $result = -1;
    global $link;
    global $voteresults;
    foreach($voteresults as $id => $voteresult){
        if($voteresult['vote_id'] == $entry['vote_id'] && $voteresult['party_id'] == $entry['party_id']){
            $result = $id;
            break;
        }
    }
    if($result >= 0){
        return $result;
    }
    else{
        $query = 'INSERT INTO voteperparty(vote_id, party_id, pro, against) VALUES(\'' . $entry['vote_id'] . '\', \'' .
            $entry['party_id'] . '\' ,\'' . $entry['pro'] . '\',\'' . $entry['against'] . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $voteresults[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
    return false;
}


//Dossiers

function loaddossiers(){
    global $link;
    $query = 'SELECT id, name, onlineid FROM dossier WHERE NOT onlineid IS NULL;';
    $dossiers = [];
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($dossier =pg_fetch_assoc($result)){
                $dossiers[$dossier['id']]['name'] = $dossier['name'];
                $dossiers[$dossier['id']]['onlineid'] = $dossier['onlineid'];
            }
        }
    }
    return $dossiers;
}
$dossiers = loaddossiers();

function finddossier($entry){
    global $link;
    global $dossiers;
    $result = -1;
    foreach($dossiers as $id => $dossier){
        if($dossier['onlineid'] == $entry['onlineid']){
            $result = $id;
            break;
        }
    }
    if($result >= 0){
        return $result;
    }
    else{
        $query = 'INSERT INTO dossier(name, onlineid) VALUES(\'' . pg_escape_string($link, $entry['name']) . '\', \'' .
            $entry['onlineid'] . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id = pg_fetch_assoc($result)['id'];
            $dossiers[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
    return false;
}


function loadmoties(){
    global $link;
    $query = 'SELECT id, vote_id FROM motie;';
    $moties = [];
    if($result = pg_query($link,$query)){
        if(pg_num_rows($result) > 0){
            while($motie =pg_fetch_assoc($result)){
                $moties[$motie['id']]['vote_id'] = $motie['vote_id'];
            }
        }
    }
    return $moties;
}
$moties = loadmoties();

function findmotie($entry){
    global $link;
    global $moties;
    $result = -1;
    foreach($moties as $id => $motie){
        if($motie['vote_id'] == $entry['vote_id']){
            $result = $id;
            break;
        }
    }
    if($result >= 0){
        return $result;
    }
    else{
        $query = 'INSERT INTO motie(vote_id, title, subject, content) VALUES(\'' . $entry['vote_id'] . '\', \'' . pg_escape_string($link, $entry['title']) . '\' ,\'' . $entry['subject'] . '\',\'' . pg_escape_string($link, $entry['content']) . '\') RETURNING id;';
        if($result = pg_query($link, $query)){
            $id= pg_fetch_assoc($result)['id'];
            $moties[$id] = $entry;
            return $id;
        }
        else {
            echo pg_errormessage($link);
        }
    }
    return false;
}
$parser = new \Smalot\PdfParser\Parser();

foreach ($period as $dt) {
    //echo $dt->format("Ymd");
	$url = 'https://zoek.officielebekendmakingen.nl/zoeken/resultaat/?zkt=Uitgebreid&pst=ParlementaireDocumenten&dpr=AnderePeriode&spd=' . $dt->format("Ymd") . '&epd=' . $dt->format("Ymd") . '&kmr=TweedeKamerderStatenGeneraal&sdt=KenmerkendeDatum&par=Handeling&dst=Onopgemaakt%7cOpgemaakt%7cOpgemaakt+na+onopgemaakt&isp=true&pnr=30&rpp=10&_page=1&sorttype=1&sortorder=4';
	try{
		//echo $url . '</br>';
		if(!$html = file_get_contents($url)){
			echo $url;
		}
		else{
			$htmlobj = str_get_html($html);
			$divs = $htmlobj->find('a[class=hyperlink]');
			foreach($divs as $div){
				//echo $url2;
				$url2='https://zoek.officielebekendmakingen.nl/' . $div->href;
				$innerhtml = file_get_contents($url2);
				$innerhtmlobj = str_get_html($innerhtml);
				$type='';
				if(!$innerhtmlobj){
					echo $url2;
					echo 'test!<br>';
				}
				else{
				    //SOME BASIC INFORMATION FROM DOCUMENT
                    $dateobj = $innerhtmlobj->find('div[class=nummer]');
                    $date = explode(' Gepubliceerd',explode('Datum vergadering ',$dateobj[0]->plaintext)[1])[0];
                    $minute = 'Plenaire vergadering ' . $date;
                    //GET MINUTE ID
                    $minute_id = findminute(Array('name' => $minute, 'date' => getdutchdate($date)));

                    $subject = $innerhtmlobj->find('span[class=item-titel]')[0]->plaintext;
                    if(trim($subject) == ''){
                        $subject = 'Opening';
                    }
                    $dossierobj = $innerhtmlobj->find('a[id=behandelddossierCtl_replinks_hlRelInfo_0]');
                    $dossier_id = -1;
                    if(count($dossierobj) > 0){
                        $dossier = $dossierobj[0]->plaintext;
                        $dossier_id = finddossier(Array('name' => $subject, 'onlineid' => $dossier));
                    }
                    else{
                        $dossier_id = 0;
                    }
                    //GET DOSSIER ID
                    //GET SUBJECT ID
                    $subject_id = findsubject(Array('name'=> $subject, 'minute_id' => $minute_id, 'dossier_id' => $dossier_id));
                    if($subject_id > 0) {

                        //If it is a vote
                        if (count($innerhtmlobj->find('ul[class=expliciet whitespace-small]')) > 0) {
                            $type = 'vote';
                            $votesobject = $innerhtmlobj->find('ul[class=expliciet whitespace-small]');
                            $votelist = $votesobject[0]->find('li');
                            foreach ($votelist as $voteitem) {
                                if (count($voteitem->find('a')) > 0) {
                                    $votelink = 'https://www.tweedekamer.nl/kamerstukken?qry=' . explode('.', substr($voteitem->find('a')[0]->href, 4))[0] . '&fld_tk_categorie=Kamerstukken&srt=date%3Adesc%3Adate&fld_prl_kamerstuk=Stemmingsuitslagen&Type=Kamerstukken&clusterName=Stemmingsuitslagen';
                                    $votelist = file_get_contents($votelink);
                                    $innervoteresult = str_get_html($votelist);
                                    if ($innervoteresult) {
                                        $votesearchresult = $innervoteresult->find('a[class=card__title]');
                                        if (count($votesearchresult) > 0) {
                                            $voteresultpageurl = 'https://www.tweedekamer.nl/' . $votesearchresult[0]->href;
                                            $votelist2 = file_get_contents($voteresultpageurl);
                                            $innervoteresult = str_get_html($votelist2);
                                            if ($innervoteresult) {
                                                $voteresultlist = $innervoteresult->find('div[class=card-container]');
                                                foreach($voteresultlist as $voteresultitem){
                                                    $votename = $voteresultitem->find('div[class=card__content]')[1]->find('p')[0]->plaintext;
                                                    $pdflink = 'https://www.tweedekamer.nl' . $voteresultitem->find('a[class=card-container-download]')[0]->href;
                                                    $voteindex = $voteresultitem->find('span[class=code-nummer]')[0]->plaintext;
                                                    echo $votename . ' ; ' . $voteindex . ' ; ' . $pdflink;
                                                    //GET VOTE ID: name, voteindex, subject_id, pdflink
                                                    $vote_id = findvote(Array('name' => $votename, 'voteindex' => $voteindex
                                                    , 'subject_id' => $subject_id, 'pdflink' => $pdflink));

                                                    $title = '';
                                                    $subject = '';
                                                    $content = '';
                                                    $voteindex = '';
                                                    $pdf = $parser->parseFile($pdflink);
                                                    $text = $pdf->getText();
                                                    $textwithbr = nl2br($text);
                                                    $lines = explode('<br />', $textwithbr);
                                                    $i = 0;
                                                    $contentlines = false;
                                                    foreach ($lines as $line) {
                                                        $i++;
                                                        if (strpos($line, 'Nr.') !== false) {
                                                            $title = $line;

                                                        } elseif (intval(substr($line, 1, 2)) && substr($line, 3, 1) == ' ' && intval(substr($line, 4, 3))) {
                                                            $subject = $line;
                                                            for ($j = 0; $j < 30; $j++) {
                                                                if (strpos($lines[$i + $j], 'Nr.') == false) {
                                                                    $subject .= $lines[$i + $j];
                                                                } else {
                                                                    break;
                                                                }
                                                            }
                                                        } elseif (strpos($line, 'De Kamer,') !== false) {
                                                            $content .= $line;
                                                            $contentlines = true;
                                                        } elseif ($contentlines) {
                                                            if (trim($line) != '') {
                                                                $content .= $line;
                                                            } else {
                                                                $contentlines = false;
                                                            }
                                                        } elseif (strpos($line, 'kst-') !== false) {
                                                            //echo $line . '<br>';
                                                            $voteindex = explode('ISSN', substr($line, 5))[0];
                                                        }
                                                    }
                                                    $subjectreal = '';
                                                    $start = false;
                                                    for ($i = 0; $i < strlen($subject); ++$i) {
                                                        if ($start) {
                                                            $subjectreal .= $subject[$i];
                                                        } else {
                                                            if (ctype_alpha($subject[$i])) {
                                                                $start = true;
                                                                $subjectreal .= $subject[$i];
                                                            }
                                                        }
                                                    }
                                                    if ($vote_id > -1) {
                                                        findmotie(Array('vote_id' => $vote_id, 'title' => $title, 'subject' => $subjectreal, 'content' => $content));
                                                    }

                                                    //Insert vote results per party
                                                    $votepagehtml = str_get_html(file_get_contents('https://www.tweedekamer.nl/' . $voteresultitem->find('a[class=card]')[0]->href));
                                                    $voteresultdivs = $votepagehtml->find('table[class=vote-result-table]');
                                                    echo '<br><strong>';
                                                    echo ($voteresultdivs[0]->plaintext) . '<br>';
                                                    echo ($voteresultdivs[1]->plaintext);
                                                    echo '</strong>';
                                                    if (count($voteresultdivs) > 1) {
                                                        for($i = 0; $i <=1; $i++){
                                                            $table = $voteresultdivs[$i];
                                                            $rows = $table->find('tr');
                                                            if (count($rows) > 0) {
                                                                for($j = 1; $j < count($rows); $j++) {
                                                                    $voteings = 0;
                                                                    $cells = $rows[$j]->find('td');
                                                                    if (count($cells) > 0) {
                                                                        $partyname = trim($cells[0]->plaintext);
                                                                        $party_id = findparty(Array('name' => $partyname));
                                                                        echo '<br><br>' . $partyname . $party_id . '<br><br>';
                                                                        $voteings = $cells[1]->plaintext;
                                                                    }
                                                                    if ($party_id >= 2) {
                                                                        if($i == 0){
                                                                            findvoteresult(Array('vote_id' => $vote_id,
                                                                                'party_id' => $party_id, 'pro' => $voteings, 'against' => 0));
                                                                        }
                                                                        else{
                                                                            findvoteresult(Array('vote_id' => $vote_id,
                                                                                'party_id' => $party_id, 'pro' => 0, 'against' => $voteings));
                                                                        }
                                                                    }
                                                                    else{
                                                                        echo 'PARTY ID NOT HIGH ENOUGH' . $party_id;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $spreekbeurten = $innerhtmlobj->find('div[class=spreekbeurt]');
                            foreach ($spreekbeurten as $spreekbeurt) {
                                $speaker = $spreekbeurt->find('p[class=spreker]');
                                $spreker = trim(explode(":", explode("(", $speaker[0]->plaintext)[0])[0]);
                                $party = '';
                                if (count(explode("(", $speaker[0]->plaintext)) > 1) {
                                    $party = explode(")", explode("(", $speaker[0]->plaintext)[1])[0];
                                    $party_id = findparty(array('name' => $party));
                                } else {
                                    $party_id = 1;
                                }
                                $text = '';
                                foreach ($spreekbeurt->find('div[class=alineagroep]') as $alinea) {
                                    $text .= $alinea->plaintext;
                                }
                                //Insert Person
                                $person_id = findperson(Array('name' => $spreker, 'party_id' => $party_id));

                                //Insert statement
                                $statement_id = findstatement(array('subject_id' => $subject_id, 'person_id' => $person_id,
                                    'text' => $text));
                            }
                        }
                    }
				}
			}
		}
	}
	catch(Exception $ex){
		echo 'fail';
	}
}

//Insert new statements
/*$values = [];
foreach( $newstatements as $statement ){
	$values[] = '("' . $statement['type'] . '",' . $statement['minute_id'] . ',' . $statement['subject_id'] . ',' . $statement['person_id'] . ', "' . $statement['text'] . '")' ;
}
if(count($values) > 0){
	$query = 'INSERT INTO statement(type, minute_id, subject_id, person_id, text) VALUES ' . join(', ', $values) . ';';
	echo $query;
	if($result = pg_query($link,$query)){
		echo 'Statements inserted (at once)';
	}
	else{
		echo 'Statements not inserted: ' . $link->error ;
	}
}*/














//
// Find Keywords in all current text in the DB
//

// Keywords overall
/*
$subjectwordcount = [];
$query = 'SELECT id, name FROM subject;';
$j = 0;
if($result = pg_query($link,$query)){
	if(pg_num_rows($result) > 0){
		while($subject =pg_fetch_assoc($result)){
			$text = '';
			$query2 = 'SELECT text FROM statement WHERE subject_id=' . $subject['id'] . ';';
			if($result2 = pg_query($link,$query2)){
				while($statementtext = pg_fetch_assoc($result2)){
					$text .= $statementtext['text'];
				}
			}
			else{
				echo $query2;
			}
			$wordcount = array_count_values(str_word_count(strtolower($text), 1));
			$totalwordcount = str_word_count($text,0);
			foreach($wordcount as $word => $frequency){
				$subjectwordcount[$word][$subject['id']] = $frequency/$totalwordcount;
			}
		}
	}
}
/*foreach($subjectwordcount as $subject => $words){
	foreach($words as $word => $frequency){
		$wordlist[] = $word;
		if($index = array_search($word, $wordlist)){
		$frequencyperword[$index] = [];
		}
	}
$frequencyperword = [];
echo count($subjectwordcount) . '<br>';
$i = 0;
$query = 'SELECT id, name FROM subject;';
if($result = pg_query($link,$query)){
	if(pg_num_rows($result) > 0){
		$subjects = [];
		while($subject =pg_fetch_assoc($result)){
			$subjects[] = $subject['id'];
		}
		foreach($subjectwordcount as $word => $subjectsword){
			$frequencyperword[$word] = [];
			echo $i;
			$i += 1;
			foreach($subjects as $subject){
				if(in_array($subject, array_keys($subjectsword))){
					$frequencyperword[$word][$subject]=$subjectsword[$subject];
					//echo $subject . ' : ' . $word . ' : ' . $words[$word] . '<br>';
				}else{
					$frequencyperword[$word][$subject] = 0;
				}
			}
		}
	}
}*/

// GET ACTUAL KEYWORDS

/*
$keywordstotal=[];
use gburtini\Distributions\Normal;
echo '<br>' . count($subjectwordcount) . '<br>';

$i=0;
$query = 'SELECT value FROM config WHERE name="keywordindex";';
if($result = pg_query($link,$query)){
	$i =pg_fetch_assoc($result)['value'];
}
else{
	echo $query;
}

foreach(array_slice($subjectwordcount, $i) as $word=>$frequencylist){
	echo $i . $word . '<br>';
	foreach($frequencylist as $subject => $frequency){
		$mean = mean(fill($frequencylist));
		$sd = sd(fill($frequencylist));
		if($sd > 0 && $mean > 0){
			$normaldist = new gburtini\Distributions\Normal(mean(fill($frequencylist)),sd(fill($frequencylist)));
			$prob = $normaldist->cdf($frequency);
			if($prob > 0.8){
				$query = 'INSERT INTO keyword(subject_id, keyword) VALUES(' . $subject . ',"' . $word . '");';
				if($result = pg_query($link,$query)){
					
				}
				else{
					echo $query;
				}
			}
		}
	}
	$query2 = 'UPDATE config SET value=' . $i . ' WHERE name="keywordindex"';
	if($result2 = pg_query($link,$query2)){
		
	}
	else{
		echo $query2;
	}
	$i += 1;
}	
$query2 = 'UPDATE config SET value=0 WHERE name="keywordindex"';
if($result2 = pg_query($link,$query2)){
	
}
else{
	echo $query2;
}

//Keywords per person

//Keyword persons

//Get word frequencies
/*$personsubjectwordcount = [];
$query = 'SELECT id, name FROM person;';
if($result = pg_query($link,$query)){
	if(pg_num_rows($result) > 0){
		while($person =pg_fetch_assoc($result)){
			$query2 = 'SELECT DISTINCT subject.id AS id, subject.name as name FROM statement, subject WHERE person_id=' . $person['id'] . ' AND statement.subject_id = subject.id;';
			if($result2 = pg_query($link,$query2)){
				$personpersubjectwordcount = [];
				while($subject = pg_fetch_assoc($result2)){
					$text = '';
					$query3 = 'SELECT text FROM statement WHERE person_id=' . $person['id'] . ' AND subject_id=' . $subject['id'] . ';';
					if($result3 = $link->query($query3)){
						while($statementtext = $result3->fetch_assoc()){
							$text .= $statementtext['text'];
						}
					}
					else{
						echo $query3;
					}				
					$wordcount = array_count_values(str_word_count(strtolower($text), 1));
					$totalwordcount = str_word_count($text,0);
					foreach($wordcount as $word => $frequency){
						$wordcount[$word] = $frequency/$totalwordcount;
					}
					$personpersubjectwordcount[$subject['name']] = $wordcount;
				}
			}
			else{
				echo $query2;
			}
			$personsubjectwordcount[$person['name']] = $personpersubjectwordcount; 
		}
	}
}
$frequencyperson = [];
$keywordspersontotal = [];
foreach($personsubjectwordcount as $person => $subjectwords){
	foreach($subjectwords as $subject => $words){
		foreach($words as $word => $frequency){
			$frequencyperson[$person][$word] = [];
		}
	}
	foreach($frequencyperson[$person] as $word => $frequency){
		foreach($personsubjectwordcount[$person] as $subject => $words){
			if(in_array($word, array_keys($words))){
				$frequencyperson[$person][$word][$subject]=$words[$word];
			}else{
				$frequencyperson[$person][$word][$subject] = 0;
			}
		}
	}
	foreach($frequencyperson[$person] as $word=>$frequencylist){
		if(count($frequencylist) > 1){
			foreach($frequencylist as $subject => $frequency){
				$mean = mean($frequencylist);
				$sd = sd($frequencylist);
				if($sd > 0 && $mean > 0){
					$normaldist = new gburtini\Distributions\Normal($mean,$sd);
					$prob = $normaldist->cdf($frequency);
					if($prob > 0.53){
						$keywordspersontotal[$person][$subject][$word] = $prob;
					}
				}
			}
		}
	}
}
//print_r($keywordspersontotal);
//Get keywords

//print_r($personsubjectwordcount);

//Keywords per minute
$minutesubjectwordcount = [];
$query = 'SELECT id, name FROM minute;';
if($result = pg_query($link,$query)){
	if(pg_num_rows($result) > 0){
		while($minute =pg_fetch_assoc($result)){
			$query2 = 'SELECT subject.id AS id, subject.name as name FROM subject WHERE minute_id=' . $minute['id'] . ';';
			if($result2 = pg_query($link,$query2)){
				$minutepersubjectwordcount = [];
				while($subject = pg_fetch_assoc($result2)){
					$text = '';
					$query3 = 'SELECT text FROM statement WHERE subject_id=' . $subject['id'] . ';';
					if($result3 = $link->query($query3)){
						while($statementtext = $result3->fetch_assoc()){
							$text .= $statementtext['text'];
						}
					}
					else{
						echo $query3;
					}				
					$wordcount = array_count_values(str_word_count(strtolower($text), 1));
					$totalwordcount = str_word_count($text,0);
					foreach($wordcount as $word => $frequency){
						$wordcount[$word] = $frequency/$totalwordcount;
					}
					$minutepersubjectwordcount[$subject['name']] = $wordcount;
				}
			}
			else{
				echo $query2;
			}
			$minutesubjectwordcount[$minute['name']] = $minutepersubjectwordcount;
		}
	}
}
$frequencyminute = [];
$keywordsminutetotal = [];
foreach($minutesubjectwordcount as $minute => $subjectwords){
	foreach($subjectwords as $subject => $words){
		foreach($words as $word => $frequency){
			$frequencyminute[$minute][$word] = [];
		}
	}
	foreach($frequencyminute[$minute] as $word => $frequency){
		foreach($minutesubjectwordcount[$minute] as $subject => $words){
			if(in_array($word, array_keys($words))){
				$frequencyminute[$minute][$word][$subject]=$words[$word];
				//echo $subject . ' : ' .  $word . ' : ' . $words[$word] . '<br>';
			}else{
				$frequencyminute[$minute][$word][$subject] = 0;
			}
		}
	}
	foreach($frequencyminute[$minute] as $word=>$frequencylist){
		if(count($frequencylist) > 1){
			foreach($frequencylist as $subject => $frequency){
				$mean = mean($frequencylist);
				$sd = sd($frequencylist);
				if($sd > 0 && $mean > 0){
					$normaldist = new gburtini\Distributions\Normal($mean,$sd);
					$prob = $normaldist->cdf($frequency);
					if($prob > 0.6){
						$keywordsminutetotal[$minute][$subject][$word] = $prob;
					}
				}
			}
		}
	}
}
//print_r($keywordsminutetotal);




/*if (($handle = fopen("sortedtext.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		if(count($data) == 5){
			$subject_id = -1;
			$query = 'SELECT * FROM subject WHERE name="' . addslashes($data[2]) . '";';
			if($result = pg_query($link,$query)){
				if(pg_num_rows($result) > 0){
					$subject_id =pg_fetch_assoc($result)['id'];
					$query2 = 'SELECT * FROM minute WHERE name="' . addslashes($data[1]) . '";';
					if($result2 = pg_query($link,$query2)){
						if(pg_num_rows($result2) > 0){
							$minute_id = pg_fetch_assoc($result2);
							$query3 = 'UPDATE subject SET minute_id=' . $minute_id['id'] . ' WHERE id=' . $subject_id . ';';
							if($link->query($query3)){
								
							}
							else{
								echo $query3;
							}
						}
					}
				}
			}
			else{
				echo $query;
			}
		}
	}
    fclose($handle);
}
if (($handle = fopen("keywords.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		if(count($data) == 2){
			foreach(explode(',',$data[1] ) as $keyword){
				$subject_id = -1;
				if($result = $link->query('SELECT * FROM subject WHERE name="' . addslashes($data[0]) . '";')){
					if(pg_num_rows($result) > 0){
						$subject_id =pg_fetch_assoc($result)['id'];
						$query = 'INSERT INTO keyword(subject_id, keyword) VALUES("' . $subject_id . '","'  . $keyword . '");';
						if($subject_id >= 0 && $result = $link->query( $query )){
			
						}
						else{
							echo $query;
						}
					}
				}
			}
		}
	}
}
if (($handle = fopen("speakers.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		if(count($data) == 2){
			foreach(explode(',',$data[1] ) as $person){
				$person_id = -1;
				if($result = $link->query('SELECT * FROM person WHERE name="' . addslashes($person) . '";')){
					if(pg_num_rows($result) > 0){
						$person_id =pg_fetch_assoc($result)['id'];
						$subject_id = -1;
						if($result2 = $link->query('SELECT * FROM subject WHERE name="' . addslashes($data[0]) . '";')){
							if(pg_num_rows($result2) > 0){
								$subject_id = pg_fetch_assoc($result2)['id'];
								$query = 'INSERT INTO speakersubject(person_id, subject_id) VALUES("' . $person_id . '", "' . $subject_id . '");';
								if($person_id >= 0 && $subject_id >= 0 && $result = $link->query($query )){
									echo $data[0];
								}
								else{
									echo $query;
								}
							}
						}
						
					}
				}
				else{
					echo $person;
				}
			}
		}
	}
}*/


?>