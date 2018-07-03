<?php
require_once("./vendor/autoload.php");
include('config.php');
set_time_limit(36000);

$query = 'SELECT * FROM vote WHERE pdflink<>"";';
if($result = $link->query($query)){
	$parser = new \Smalot\PdfParser\Parser();
	while($row = $result->fetch_assoc()){
		$title = '';
		$subject = '';
		$content = '';
		$voteindex = '';
		$links  = $row['pdflink'];
		$pdf    = $parser->parseFile($links);
		$text = $pdf->getText();
		$textwithbr = nl2br($text);
		$lines = explode('<br />',$textwithbr);
		$i = 0;
		$contentlines = false;
		foreach($lines as $line){
			$i++;
			if(strpos($line, 'Nr.') !== false){
				$title = $line; 
				
			}
			elseif(intval(substr($line,1,2)) && substr($line,3,1) == ' ' && intval(substr($line,4,3))) {
				$subject = $line;
				for( $j = 0; $j < 30; $j++){
					if(strpos($lines[$i + $j], 'Nr.') == false ){
						$subject .= $lines[$i+$j];
					}
					else{
						break;
					}
				}
			}
			elseif(strpos($line, 'De Kamer,') !== false){
				$content .= $line;
				$contentlines = true;
			}
			elseif($contentlines){
				if(trim($line) != ''){
					$content .= $line;
				}
				else{
					$contentlines = false;
				}
			}
			elseif(strpos($line, 'kst-') !== false){
				//echo $line . '<br>';
				$voteindex = explode('ISSN',substr($line,5))[0];
			}
		}
		$subjectreal = '';
		$start = false;
		for($i = 0; $i < strlen($subject); ++$i){
			if($start){
				$subjectreal .= $subject[$i];
			}
			else{
				if(ctype_alpha($subject[$i])){
					$start = true;
					$subjectreal .= $subject[$i];
				}
			}
		}
		$query2 = 'SELECT * FROM motie WHERE vote_id=' . $row['id'] . ' AND subject="' . $subjectreal .'";';
		if($result3 = $link->query($query2)){
			if($result3->num_rows == 0){
				$query = 'INSERT INTO motie(vote_id, title, subject, content) VALUES(' . $row['id'] . ', "' . $title . '", "' . $subjectreal . '", "' . $link->real_escape_string($content) . '");';
				if($result2 = $link->query($query)){
					echo 'Gelukt';
				}
				else{
					echo $link->error;
				}
			}
		}
		//echo $subject . ' : ' . $title . ' |TEXT| ' . $content .  '<br>';
	}
}
else{
	echo 'fail';
}



?>