<?php
include('../config.php');
include('simple_html_dom.php');
require_once('gburtini/Distributions/Normal.php');
$begin = new DateTime('2017-01-01');
$end = new DateTime('2017-11-09');
set_time_limit(36000);

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);
$intervalperiod = new DateInterval('P1D');
ini_set('memory_limit', '100000000');

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
						$personsubjectwordcount[$person['id']][$word][$subject['id']] = $frequency/$totalwordcount;
					}
				}
			}
			else{
				echo $query2;
			}
		}
	}
}
$frequencyperson = [];
$keywordspersontotal = [];
foreach($personsubjectwordcount as $person => $wordlist){
	foreach($wordlist as $word=>$frequencylist){
		if(count($frequencylist) > 1){
			foreach($frequencylist as $subject => $frequency){
				$mean = mean(fill($frequencylist));
				$sd = sd(fill($frequencylist));
				if($sd > 0 && $mean > 0){
					$normaldist = new gburtini\Distributions\Normal($mean,$sd);
					$prob = $normaldist->cdf($frequency);
					if($prob > 0.5){
						$query = 'INSERT INTO keywordperson(keyword, subject_id, person_id) VALUES("' . $word . '", ' . $subject . ', ' . $person . ');';
						if($result = pg_query($link,$query)){
							
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
?>