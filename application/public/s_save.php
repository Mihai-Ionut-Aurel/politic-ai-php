<?php
    session_start();
    // *** Index
	// *** Copyright DesignConsult (www.designconsult.nl), all rights reserved

	// *** Offline halen voor een domein: if($_SERVER['SERVER_NAME'] == "www.foreverlamp.eu") die("Nog niet online, kom aub later terug!");	$_SESSION["customtemplate"] = "";
	$_SESSION["customperson"]   = "";

		
	include("./admin/databaseconnectie.php");
	include("./includes/whitelabel.php");	
	include("./includes/global.php");
	require('passwordlib/password.php');
	require_once("./includes/phpmailer/PHPMailerAutoload.php");

	function errorDie($message)
	{
		$errorHeader = "<center><div style='background-color: #666666; margin: 30px; padding: 15px; color: #FFFFFF; font-family: Arial; font-size: 15px; width: 250px;'>";
		$errorFooter = "<div style='padding-top: 15px; margin-top: 15px; border-top: 1px solid #FFFFFF;'><a href='/' style='display: block; width: 150px; color: #FFFFFF; background-color: #73be57; padding: 5px; text-decoration: none;'><b>naar voorpagina</b></a></div></div></center>";
		
		die($errorHeader . $message . $errorFooter);
	}
	
			
	/*		
	// *** Company
	$company = trim(strtolower($_GET["c"]));
	$query = mysql_query("SELECT * FROM mobilecard_companies WHERE naam = '" . $company . "'");
	$row_c   = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (1)");

	// *** Person		
	$person = str_replace("-", ".", str_replace(" ", ".", trim(strtolower($_GET["p"]))));
	$query = mysql_query("SELECT * FROM mobilecard_persons WHERE company = '" . $row_c["id"] . "' AND naam = '" . $person . "'");
	$row_p   = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (2)");
	*/
	
	
	// *** E-mail
	$emailTemplate = getEmailTemplate($whitelabel_array);
	$headers = getEmailHeaders($whitelabel_array);
	function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
	}

	function getToken($length=32){
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		for($i=0;$i<$length;$i++){
			$token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
		}
		return $token;
	}

	function checkGDPR(){
		if(isset($_SESSION['ingelogd_id']) && $_SESSION["ingelogd"] == 1){
			$query = "SELECT display_confirm FROM mobilecard_GDPR_confirmation WHERE person_id = " . $_SESSION['ingelogd_id'] . ";";
			$result = mysql_query($query);
			if(mysql_num_rows($result) == 1){
				if(mysql_fetch_array($result)[0]==1){
					return true;
				}
				else{
					return false;
				}
			}
			elseif (mysql_num_rows($result) == 0){
			    $query2= 'SELECT GDPR_accepted FROM mobilecard_companies AS company, mobilecard_persons AS person WHERE person.company=company.id AND person.id=' . $_SESSION['ingelogd_id'] . ';';
				$result2 = mysql_query($query2);
				if(mysql_num_rows($result2) > 0){
					if(mysql_fetch_array($result2)[0]==1){
                        return true;
					}
					else{
						return false;
					}
				}
				else{
					return false;
				}
			}
			else{
				return true;
			}
		}
		else{
			return true;
		}
    }

	function showGDPRDialog(){
	    global $html;
        $html .= '
        <-- Start of GDPR Modal -->
<div class="modal active" id="modal-id">
      <a href="#close" class="modal-overlay" aria-label="Close"></a>
      <div class="modal-container">
	   <form method=\'post\' action=\'\'>
        <div class="modal-header">
          <div class="modal-title h2">Algemene verordening gegevensbescherming (AVG)</div>
        </div>
        <div class="modal-body">
          <div class="content-gdpr">
          
          <p>Met de Algemene verordening gegevensbescherming krijgen bizzerd-gebruikers meer mogelijkheden om voor zichzelf op te komen bij de verwerking van persoonsgegevens; de privacyrechten van alle bizzerd-gebruikers worden versterkt en uitgebreid.</p>
<p>Een artikel in de AVG beslaat specifieke toestemming van de gebruiker. Wij van bizzerd hebben vanaf 25 mei 2018 expliciete toestemming van jou nodig om onze diensten aan jou te verlenen.</p><p>Om onze dienst aan jou te kunnen leveren, verwerken wij - enkel indien noodzakelijk - jouw \'gewone\' persoonsgegevens. Dit zijn voor de hand liggende gegevens zoals je naam, je adres, je telefoonnummer, functietitel en links naar social media. Met verwerken bedoelen wij: deze gegevens worden opgeslagen in ons systeem om vervolgens getoond te kunnen worden op jouw digitale visitekaartje. Bij bizzerd verwerken wij nooit bijzondere persoonsgegevens als ras, godsdienst of gezondheid. Daarnaast verwerken wij ook nooit strafrechtelijke persoonsgegevens als veroordelingen of mogelijk gegronde verdenkingen.
</p>
<p>Om van de diensten van bizzerd gebruik te maken, is elke bizzerd-gebruiker verplicht zijn/haar expliciete toestemming te verlenen aan bizzerd om \'gewone\' persoonsgegevens te mogen verwerken.
</p>
<p>Concreet betekent dit dat je vanaf 25 mei 2018 geen gebruik meer kunt maken van jouw digitale visitekaartje, tot je expliciete toestemming verleend hebt.</p>
          <label class="form-checkbox">
            <input type="checkbox" name=\'confirmation\' onchange="document.getElementById(\'accept\').disabled=!this.checked;">
            <i class="form-icon"></i> Hiermee verleen ik expliciete toestemming aan bizzerd voor het verwerken van \'gewone\' persoonsgegevens, zoals hierboven beschreven.
          </label>
          </div>
        </div>
        <div class="modal-footer">
          <button id="accept" class="button--gdpr button--primary" disabled>Ik ga akkoord</button>
          <a href="?cancel" class="button--gdpr button--text" >Annuleren</a>
        </div>
		</form>
      </div>
    </div>
<-- End of GDPR Modal -->';
    }

    function confirmGDPR(){
        $query1 = 'SELECT * FROM mobilecard_GDPR_confirmation WHERE person_id = ' . $_SESSION['ingelogd_id'] .';';
        $result1 = mysql_query($query1);
        if(mysql_num_rows($result1) < 1){
            $query2 = 'INSERT INTO mobilecard_GDPR_confirmation(person_id,display_confirm, time_confirm) VALUES('. $_SESSION['ingelogd_id'] .',' . isset($_POST['confirmation']) . ', "' . date('H:i d-m-y') . '" );';
            $result2 = mysql_query($query2);
            if(!$result2){
                errorDie($query2);
            }
        }
        else{

        }
    }
	
	function checkforneedverification($p_id){
		$query = 'SELECT * FROM mobilecard_persons WHERE id=' . $p_id . ';';
		$result = mysql_query($query);
		if($result && mysql_num_rows($result) > 0){
			if(mysql_fetch_array($result)['verificationneeded'] == 1){
				return true;
			}
		}
		else{
			return false;
		}
	}
	
	function sendverificationemail($name, $email, $id){
		global $headers;
		global $emailTemplate;		
		$mail = new PHPMailer;
		//Set who the message is to be sent from
		$mail->setFrom('noreply@bizzerd.com', 'bizzerd NoReply');
		//Set an alternative reply-to address
		$mail->addReplyTo('noreply@bizzerd.com', 'bizzerd NoReply');
		//Set who the message is to be sent to
		$mail->addAddress($email, $name);
		$mail->addBCC('yannick@bizzerd.com', 'Yannick Mijsters');
		$mail->Subject = 'bizzerd | Verificatie email';
		$html = file_get_contents('verificatie.html');
		$html = str_replace('/name', $name ,$html);
		$html = str_replace('/link', getlink($id), $html);
		$mail->msgHTML($html, __DIR__);
		$mail->addAttachment($invoice);
		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} 
		else {
			
		}
	}
	
	function getlink($id){
		echo 'Test';
		$token = getToken();
		$query = 'INSERT INTO mobilecard_passwordreset(id, keycode, person_id) VALUES(DEFAULT, "' . $token . '",' . $id . ')';
		$result = mysql_query($query);
		if($result){
			return 'https://app.bizzerd.com/s_save.php?a=verificatie&token=' . $token;
		}
		else{
			echo $query;
		}
	}



    if(isset($_POST['confirmation'])){
	    confirmGDPR();
    }

	// *** Gebruiker komt van stappenplan voor 1e keer en card moet worden aangemaakt
	if(isset($_POST["c_naam"]))
	{		
		$fout = "";
		
		$_POST["naam"] = strtolower($_POST["naam"]);
		
		// *** Checks
		if($_POST["e"] == "")
		{
			$n = skipWeirdCharacters(strtolower($_POST["c_naam"]));
			
			$query  = mysql_query("SELECT count(*) AS aantal FROM mobilecard_companies WHERE naam = '" . $n . "';");
			$row    = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Could not select count in database.");
			$aantal = $row["aantal"];
		
			if($aantal > 0)
			{
				$fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>" . $whitelabel_naam . " domein '" . $n . "' is helaas bezet!</b></div>";
			}
		
			if($n == "")
			{
				$fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen " . $whitelabel_naam . " domein ingevuld!</b></div>";
			}

			if($_POST["template_pakket"] == "") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen pakket gekozen.</b></div>";
			
			if($_POST["template_voorwaarden"] != "1") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U bent niet akkoord gegeaan met de algemene voorwaarden.</b></div>";

		}
		
		$query = mysql_query('SELECT * FROM mobilecard_persons WHERE email="' . $_POST['email'] .'";');
		
		
		if($_POST["e"] == "" || $_POST["e"] == "2")
		{
			if($_POST["naam"] == "") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen " . $whitelabel_naam . " naam ingevuld.</b></div>";

			if($_POST["email"] == "") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen e-mail adres ingevuld.</b></div>";
		
			if(mysql_num_rows($query) > 1){
				$fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>Uw e-mailadres is al in gebruik voor een andere bizzerd.</b></div>";
			}
		
		}
			
			
		


		// *** ID's van gebruiker/company achterhalen als het om een ingelogde gebruiker gaat + checks
		if($_POST["e"] != "" && $_POST["e"] != "registreer" )
		{
			$query = mysql_query("SELECT * FROM mobilecard_persons WHERE id = '" . $_SESSION["ingelogd_id"] . "'");
			$row_p_temp   = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (3)");
		
			$p_id = $row_p_temp["id"];
			$c_id = $row_p_temp["company"];

			$thisID = $p_id;
			if($_POST["card"] != "") $thisID = $_POST["card"];

			// *** Company/template bewerken
			if($_POST["e"] == "1" && $row_p_temp["superuser"] != "1") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen bevoegdheid om deze bewerking te doen.</b></div>"; 

			// *** Gegevens bewerken
			if($_POST["e"] == "2" && $row_p_temp["id"] != $_POST["card"] && $_POST["card"] != "")
			{
				echo 'Card' . $_POST['card'];
				$query = mysql_query("SELECT * FROM mobilecard_persons WHERE id = '" . $_POST["card"] . "'");
				$row_p_card = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (4)" . $_POST['card']);				
			
				if($row_p_card["company"] != $row_p_temp["company"]) $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen bevoegdheid om deze bewerking te doen.</b></div>"; 
			}
			
			if($_POST["e"] == "2")
			{
				$query  = mysql_query("SELECT count(*) AS aantal FROM mobilecard_persons WHERE company = '" . $c_id . "' AND naam = '" . $_POST["naam"] . "' AND id != '" . $thisID . "';");
				$row    = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Could not select count in database.");
				$aantal = $row["aantal"];
			
				if($aantal > 0)	$fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>De door u gekozen " . $whitelabel_naam . " naam bestaat helaas al!</b></div>"; 	
			
			
			}

			// *** Pakket kiezen
			if($_POST["e"] == "4" && $row_p_temp["superuser"] != "1") $fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft geen bevoegdheid om deze bewerking te doen.</b></div>"; 

			
		}
		
		if($fout == "")
		{			
			// *** Insert company
			if($_POST["e"] == "")
			{
				
				$query = mysql_query("INSERT INTO mobilecard_companies (datum, naam, template, whitelabel) VALUES ('" . date("Y-m-d") . "', '" . $_POST["c_naam"] . "', '1', '" . str_replace("'", "`", $whitelabel_naam) . "')");
				$c_id = mysql_insert_id ();
				
				$mailtext = "<a href='https://" . $_POST["c_naam"] . "." . $whitelabel_domein . "/" . $_POST["naam"] . "'>https://" . $_POST["c_naam"] . "." . $whitelabel_domein . "/" . $_POST["naam"] . "</a><br><br>Gekozen pakket: " . $_POST["template_pakket"];
								
				mail("martin@initiumdesign.nl", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers); 
				mail("johan@bizzerd.com", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers);
				mail("yannick@bizzerd.com", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers);				
				//mail("kirsten@initiumdesign.nl", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers); 
				mail("robbertjan@sabelcommunicatie.nl", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers); 

				if($whitelabel_naam == "bizzerd") mail("robbertjan@sabelcommunicatie.nl", "Nieuw card " . $_POST["c_naam"] . " (" . $whitelabel_naam . ") aangemaakt!", $mailtext, $headers); 

				//mail("anders@initiumnet.nl", "Nieuw card " . $_POST["c_naam"] . " aangemaakt!", $mailtext, $headers_temp); 
				
			}

			// *** Update company
			if($_POST["e"] == "" || $_POST["e"] == "1")
			{						
				if($_POST["template_pakket"] == "1") $pakket = "pakket = '" . $_POST["template_pakket"] . "', ";
				
				//$temp_vol_naam = "volledige_naam = '" . $_POST["c_volledige_naam"] . "', ";
				
				$query = mysql_query("UPDATE mobilecard_companies SET 
				
				volledige_naam = '" . $_POST["c_volledige_naam"] . "',				
				
				" . $pakket . "
				
				
				template_iconen = '" . $_POST["template_iconen"] . "',
				template_achtergrond = '" . $_POST["template_achtergrond"] . "',
				template_voorgrond = '" . $_POST["template_voorgrond"] . "',
				template_titels = '" . $_POST["template_titels"] . "',
				template_tekst = '" . $_POST["template_tekst"] . "',
				template_links = '" . $_POST["template_links"] . "',
				template_logo = '" . $_POST["template_logo"] . "',
				template_artwork = '" . $_POST["template_artwork"] . "',
				template_wachtwoord = '" . $_POST["template_wachtwoord"] . "',
				template_pakket = '" . $_POST["template_pakket"] . "',
				template_voorwaarden = '" . $_POST["template_voorwaarden"] . "'		
				
				WHERE id = '" . $c_id . "';") or errorDie("Error while updating database.");
			}
			
			// *** Insert person
			if($_POST["e"] == "" || $_POST["e"]=="registreer")
			{		
				$query = mysql_query("INSERT INTO mobilecard_persons (datum, superuser) VALUES ('" . date("Y-m-d H:i:s") . "', '" . ($_POST['e']=="registreer" ? '0' : '1') . "')");
				$p_id = mysql_insert_id ();
			}
			
			// *** Update person
			if($_POST["e"] == "" || $_POST["e"] == "2" || $_POST["e"]=="registreer")
			{
				$thisID = $p_id;
				if($_POST["card"] != "") $thisID = $_POST["card"];
								
				$_POST["email"] = strtolower($_POST["email"]);
				
				if($_POST["e"] == "registreer"){ 
					$c_id = $_POST['c_naam']; 
		
				}				
			
				$query = mysql_query("SELECT * FROM mobilecard_companies WHERE id = '" . $c_id . "'");
				$row_c   = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found.");

				$temp = explode("~||~", $row_c["vertalingen"]);
				$vertalingen_count = 0;
				
				$wachtwoord = '';
				if(password_needs_rehash($_POST['wachtwoord'], PASSWORD_DEFAULT)){
					$wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
				}
				else{
					$wachtwoord = $_POST['wachtwoord'];
				}
				
				$query2 = mysql_query("SELECT * FROM mobilecard_persons WHERE id<>'" . $thisID . "' AND email = '" . $_POST['email'] . "';");
				if(mysql_num_rows($query2) >  0){
					$fout .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>Uw e-mailadres is al in gebruik voor een andere bizzerd.</b></div>";
				}
				
				for($i = 0; $i < count($temp); $i++)
				{
					if($temp[$i] != "")
					{			
						$query_temp = mysql_query("SELECT afkorting FROM mobilecard_vertalingen WHERE id = '" . $temp[$i] . "'");
						$row_temp = mysql_fetch_array($query_temp, MYSQL_ASSOC) or $notfound = 1;			
						
						if($notfound != 1)
						{
							$vertalingen_count++;
						}
					}
				}	
				
				//echo "Vertalingen " . $row_c["vertalingen"] . ": " . $vertalingen_count;
				

				if($vertalingen_count > 1)
				{
					$temp = explode("~||~", $row_c["vertalingen"]);
					$functie = "";
					$adresveld = $_POST["adresveld"];
					
					for($i = 0; $i < count($temp); $i++)
					{
						if($temp[$i] != "")
						{		
							$notfound = 0;
							
							$query_temp = mysql_query("SELECT naam_website FROM mobilecard_vertalingen WHERE id = '" . $temp[$i] . "'");
							$row_temp = mysql_fetch_array($query_temp, MYSQL_ASSOC) or $notfound = 1;			
							
							if($notfound != 1)
							{
								$functie .= "~||~" . $temp[$i] . ":" . $_POST["functie_" . $temp[$i]];
								$adresveld .= "~||~" . $temp[$i] . ":" . $_POST["adresveld_" . $temp[$i]];
								
							}
						}
					}				
				
				}				
				else
				{
					$functie = $_POST["functie"];
					$adresveld = $_POST["adresveld"];
				}
				
				//echo "functie: " . $functie . "<BR>";
				//echo "adresveld: " . $adresveld . "<BR>";
								
				if($fout==''){
				
					$query = mysql_query("UPDATE mobilecard_persons SET 
					
					company = '" . $c_id . "',
							
					naam = '" . $_POST["naam"] . "',
			
					voorvoegsel = '" . $_POST["voorvoegsel"] . "',
					voornaam = '" . $_POST["voornaam"] . "',
					tussenvoegsel = '" . $_POST["tussenvoegsel"] . "',
					achternaam = '" . $_POST["achternaam"] . "',
					
					email = '" . $_POST["email"] . "',
					". ($_POST['wachtwoord'] !='' ? "wachtwoord = '" . $wachtwoord . "'," : "") . "
					token = '',

					notificatie = '1',
					
					functie = '" . $functie . "',
					mobiel = '" . $_POST["mobiel"] . "',
					whatsapp = '" . $_POST["whatsapp"] . "',
					tel = '" . $_POST["tel"] . "',
					belmij = '" . $_POST["belmij"] . "',
					fax = '" . $_POST["fax"] . "',
					website = '" . $_POST["website"] . "',
					website_icoon = '" . $_POST["website_icoon"] . "',
					website_link = '" . $_POST["website_link"] . "',

					skype = '" . $_POST["skype"] . "',
					linkedin = '" . $_POST["linkedin"] . "',
					twitter = '" . $_POST["twitter"] . "',
					facebook = '" . $_POST["facebook"] . "',
					youtube = '" . $_POST["youtube"] . "',
					googleplus = '" . $_POST["googleplus"] . "',
					blog = '" . $_POST["blog"] . "',
					pinterest = '" . $_POST["pinterest"] . "',
					instagram = '" . $_POST["instagram"] . "',

					qrcode = '" . $_POST["qrcode"] . "',
					adresveld = '" . $adresveld . "',
					googlemaps = '" . $_POST["googlemaps"] . "',
					kvk = '" . $_POST["kvk"] . "',
					btw = '" . $_POST["btw"] . "',
					bankrekening = '" . $_POST["bankrekening"] . "',
					pasfoto = '" . $_POST["pasfoto"] . "',
					contactfoto = '" . $_POST["contactfoto"] . "',
					cv = '" . $_POST["cv"] . "',
					
					straat = '" . $_POST["straat"] . "',
					huisnummer = '" . $_POST["huisnummer"] . "',
					toevoeging = '" . $_POST["toevoeging"] . "',
					postbus = '" . $_POST["postbus"] . "',
					postcode = '" . $_POST["postcode"] . "',
					plaats = '" . $_POST["plaats"] . "',
					land = '" . $_POST["land"] . "',
					
					text_deel_sms = '" . $_POST["text_deel_sms"] . "',
					text_deel_email_subject = '" . $_POST["text_deel_email_subject"] . "',
					text_deel_email = '" . $_POST["text_deel_email"] . "',
					
					extralink1_titel = '" . $_POST["extralink1_titel"] . "',
					extralink2_titel = '" . $_POST["extralink2_titel"] . "',
					extralink3_titel = '" . $_POST["extralink3_titel"] . "',
					extralink4_titel = '" . $_POST["extralink4_titel"] . "',

					extralink1_type = '" . $_POST["extralink1_type"] . "',
					extralink2_type = '" . $_POST["extralink2_type"] . "',
					extralink3_type = '" . $_POST["extralink3_type"] . "',
					extralink4_type = '" . $_POST["extralink4_type"] . "',
					
					extralink1_html = '" . $_POST["extralink1_html"] . "',
					extralink2_html = '" . $_POST["extralink2_html"] . "',
					extralink3_html = '" . $_POST["extralink3_html"] . "',
					extralink4_html = '" . $_POST["extralink4_html"] . "',
					
					extralink1 = '" . $_POST["extralink1"] . "',
					extralink2 = '" . $_POST["extralink2"] . "',
					extralink3 = '" . $_POST["extralink3"] . "',
					extralink4 = '" . $_POST["extralink4"] . "',

					extratekst1 = '" . $_POST["extratekst1"] . "',
					extratekst2 = '" . $_POST["extratekst2"] . "',
					extratekst3 = '" . $_POST["extratekst3"] . "'" . (isset($_POST['variatie_id'])?",
					variatie_id = '" . $_POST["variatie_id"] . "'": '') . "
							
					WHERE id = '" . $thisID . "';") or errorDie("Error while updating database.");
		
				
					// *** Links		
					$link4 = "https://" . $whitelabel_server_name . $port . "/s_edit.php?e=" . $_POST["email"];	
					$www = $whitelabel_www;
					if(strstr($whitelabel_server_name, "office.online4you.nl")) { $port = ":8380"; $www = $whitelabel_wwwdev; }
					$link = "https://" . $whitelabel_server_name . $port . "/cardoptions.php?c=" . $company . "&p=" . $person . "&";	
					$link2 = "" . str_replace($www . ".", $_POST["c_naam"] . ".", $whitelabel_server_name) . $port . "/";

					if($_POST["e"] == "registreer"){
						$query = 'UPDATE mobilecard_persons SET verificationneeded=1 WHERE id='.$p_id . ';';
						$result = mysql_query($query);
						if(checkforneedverification($p_id)){
							//verificationemail
							sendverificationemail($_POST['voornaam'], $_POST['email'], $p_id);
							$_SESSION['ingelogd'] = -1;
						}
						else{
							$_SESSION['ingelogd_id'] = $p_id;
							$_SESSION['ingelogd_email'] = $_POST['email'];
							$_SESSION['ingelogd'] = 1;
						}
					}					
					
				}
				else{
					// *** Toon fout op scherm
					$html = "
					
					<h1>Fout ontstaan!</h1>
					
					" . $fout . "
					
					Ga a.u.b. terug om het ontbrekende te corrigeren en aan te vullen.
					
					<br><br>
					
					<a href='javascript: history.go(-1);'>Terug</a>			
					
					";
					
					$geenverdereactie = 1;
				}
				
				/*			
				if($_POST["send_intromail"] != "1")	
				{
					// *** Standaard mail sturen
					
					if($_POST["e"] == "")
					{
						$subject = "Uw " . $whitelabel_naam_card . " is aangemaakt";
						
						$intro = "
						
						Er is zojuist een " . $whitelabel_naam_card . " aangemaakt voor u!
						
						<br><br>
						
						Een " . $whitelabel_naam_card . " is een digitaal visitekaartje. U kunt hier de contactgegevens inzien, de locatie bekijken op Google Maps, de contactgegevens opslaan in uw adresboek (vCard) en het profiel bekijken op social media zoals LinkedIn, Facebook en Twitter.
						
						<br><br>
						
						";
					}
					else
					{
						$subject = "Uw " . $whitelabel_naam_card . " is gewijzigd";
					
						$intro = "
						
						Uw " . $whitelabel_naam_card . " is zojuist gewijzigd.
						
						<br><br>
						
						";
					}				
	
									
					$mailtext = "
					
					<b>Beste " . $_POST["voorvoegsel"] . " " . $_POST["voornaam"] . " " . $_POST["tussenvoegsel"] . " " . $_POST["achternaam"] . "</b><br><br>
					
					" . $intro . "
									
					Bekijk nu uw " . $whitelabel_naam_card . " en controleer of alle gegevens kloppen. Mocht dit niet het geval zijn, dan kunt u op de link 'Card bewerken' klikken onderaan deze e-mail.
			
					<br><br>
					
					Uw e-mail: <b>" . $_POST["email"] . "</b><br>
					Uw wachtwoord is: <b>" . $_POST["wachtwoord"] . "</b>
							
					<br><br>
					
					<a href='https://" . $link2 . $_POST["naam"] . "'>Uw " . $whitelabel_naam_card . " bekijken</a> - 
					<a href='" . $link4 . "'>Card bewerken</a>
					
					<br><br>
					
					";
				
				
					$mailtext = str_replace("~html~", $mailtext, $emailTemplate);
				
				}
				*/



				if($_POST["send_intromail"] != "1")	
				{

					$subject = "Welkom bij bizzerd!";
					
					$mailtext = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
					<html xmlns='http://www.w3.org/1999/xhtml'>
					 <head>
					  <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
					  <title>Welkom bij bizzerd!</title>
					  <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
					</head>
					<body style='margin: 0; padding: 0;'>
					 <table border='0' cellpadding='0' cellspacing='0' width='100%'>
					  <tr>
					   <td>
					    <table align='center' border='0' cellpadding='0' cellspacing='0' width='600' style='border-collapse: collapse;'>
					     <tr>
					      <td>
					       <a href='https://www.bizzerd.com'><img src='https://www.bizzerd.com/wp-content/themes/bizzerd/images/logo.png'></a>
					      </td>
					     </tr>
						 <tr>
						  <td>
						   <b>Beste ~naam~,</b><br><br>

Gefeliciteerd met het maken van bizzerd, jouw digitale visitekaartje.
In dit bericht geven we een paar handige tips voor het gebruik. <br><br>
<b>Bekijken?</b><br>
<a style='color:#f7a823;' href='~linkCard~'>Bekijk hier jouw bizzerd.</a>  <br><br>
<b>Delen</b><br>
Jouw bizzerd kun je zo veel delen als je wilt. Dat kan door het linkje naar jouw bizzerd te verzenden via e-mail, WhatsApp of sms. Of gebruik de app (<a style='color:#f7a823;' href='https://play.google.com/store/apps/details?id=com.bizzerd'>Android</a>). Tip: met de app kun je ook papieren visitekaartjes scannen en meteen opslaan in je telefoon. En jouw bizzerd kun je ook meteen sturen naar de afzender van het papieren visitekaartje. <br><br>
<b>Bewerken</b><br>
Wil je jouw bizzerd wijzigen, klik dan op '<a href='~linkEdit~' style='color:#f7a823;'>bizzerd bewerken</a>'.<br><br>
<b>Wachtwoord vergeten?</b><br>
Ben je je wachtwoord vergeten? Ga dan naar <a href='https://www.bizzerd.com/' style='color:#f7a823;'>bizzerd.com</a> en klik op 'Wachtwoord vergeten?’. Volg verder de instructies. <br><br>
<b>Meer informatie</b><br>
Leer meer over bizzerd met de <a style='color:#f7a823;' href='https://www.bizzerd.com/wp-content/uploads/2016/05/productsheet.pdf'>productsheet</a>. Of bekijk de <a style='color:#f7a823;' href='https://www.bizzerd.com/wp-content/uploads/2016/02/2016_01_25-bizzerd-handleiding_links.pdf'>handleiding.</a><br><br>

Veel succes met bizzerd, jouw digitale visitekaartje!<br><br>

Met vriendelijke groet,<br>
Martin Peters - <a href='https://www.bizzerdcard.com/peters' style='color:#f7a823;'>Bekijk mijn digitale visitekaartje.</a>

					
					</br></br>
					
					       </p>  
						  </td>
						 </tr>
						 <tr>
						  <td>
						  </td>
						 </tr>
					    </table>
					   </td>
					  </tr>
					 </table>
					</body>
					</html>";

					if($whitelabel_naam == "MobiCard")
					{
						$subject = "Welkom bij MobiCard!";
					
						$mailtext = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
						<html xmlns='http://www.w3.org/1999/xhtml'>
						 <head>
						  <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
						  <title>Welkom bij MobiCard!</title>
						  <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
						</head>
						<body style='margin: 0; padding: 0;'>
						 <table border='0' cellpadding='0' cellspacing='0' width='100%'>
						  <tr>
						   <td>
						    <table align='center' border='0' cellpadding='0' cellspacing='0' width='600' style='border-collapse: collapse;'>
						     <tr>
						      <td>
						       <a href='https://www.bizzerd.com'><img src='https://www.bizzerd.com/wp-content/themes/bizzerd/images/logo.png'></a>
						      </td>
						     </tr>
							 <tr>
							  <td>
							   <p style='font-family:Open Sans; font-size:18px;'>
							   <b>Beste ~naam~,</b><br><br>

Gefeliciteerd met het maken van jouw digitale visitekaartje via MobiCard.nl.
In dit bericht geven we een paar handige tips voor het gebruik. <br><br>
<b>bizzerd</b><br>
MobiCard heeft een nieuwe naam: bizzerd. Deze naam gebruiken we daarom verder in dit bericht.<br><br>
<b>Bekijken?</b><br>
<a style='color:#f7a823;' href='~linkCard~'>Bekijk hier jouw bizzerd.</a>  <br><br>
<b>Delen</b><br>
Jouw bizzerd kun je zo veel delen als je wilt. Dat kan door het linkje naar jouw bizzerd te verzenden via e-mail, WhatsApp of sms. Of gebruik de app (<a style='color:#f7a823;' href='https://play.google.com/store/apps/details?id=com.bizzerd'>Android</a>). Tip: met de app kun je ook papieren visitekaartjes scannen en meteen opslaan in je telefoon. En jouw bizzerd kun je ook meteen sturen naar de afzender van het papieren visitekaartje. <br><br>
<b>Bewerken</b><br>
Wil je jouw bizzerd wijzigen, klik dan op '<a href='~linkEdit~' style='color:#f7a823;'>bizzerd bewerken</a>'.<br><br>
<b>Wachtwoord vergeten?</b><br>
Ben je je wachtwoord vergeten? Ga dan naar <a href='https://www.bizzerd.com/' style='color:#f7a823;'>bizzerd.com</a> en klik op 'Wachtwoord vergeten?’. Volg verder de instructies. <br><br>
<b>Meer informatie</b><br>
Leer meer over bizzerd met de <a style='color:#f7a823;' href='https://www.bizzerd.com/wp-content/uploads/2016/05/productsheet.pdf'>productsheet</a>. Of bekijk de <a style='color:#f7a823;' href='https://www.bizzerd.com/wp-content/uploads/2016/02/2016_01_25-bizzerd-handleiding_links.pdf'>handleiding.</a><br><br>

Veel succes met bizzerd, jouw digitale visitekaartje!<br><br>

Met vriendelijke groet,<br>
Martin Peters - <a href='https://www.bizzerdcard.com/peters' style='color:#f7a823;'>Bekijk mijn digitale visitekaartje.</a>
						
						</br></br>
						 
						
						       </p>  
							  </td>
							 </tr>
							 <tr>
							  <td>
							  </td>
							 </tr>
						    </table>
						   </td>
						  </tr>
						 </table>
						</body>
						</html>";
				
					}
					

				
				}				
				else
				{
					// *** Custom welkomst mail versturen
					$query_temp = mysql_query("SELECT * FROM mobilecard_companies WHERE id = '" . $c_id . "'");
					$row_temp   = mysql_fetch_array($query_temp, MYSQL_ASSOC);

					$subject = $row_temp["welkomst_mail_subject"];
					$mailtext = $row_temp["welkomst_mail"];
									
				}
				
				if(1 == 1)
				{

					if($row_temp["toegewijde_url"] != "")
					{
						$linkCard = "https://" . $row_temp["toegewijde_url"] . "/" . $_POST["naam"];
						$linkCardShort = $row_temp["toegewijde_url"] . "/" . $_POST["naam"];
					}
					else
					{
						$linkCard = "https://" . $link2 . $_POST["naam"] . "";
						$linkCardShort = "" . $link2 . $_POST["naam"] . "";					
					}
					if(strstr($whitelabel_server_name, "office.online4you.nl")) { $port = ":8380"; $www = $whitelabel_wwwdev; }
		
					$resetcode = getToken(32);
					$query1 = mysql_query("INSERT INTO mobilecard_passwordreset (keycode, person_id, timestamp, intromail) VALUES ('" . $resetcode . "','" . $thisID . "','" . date(c) . "', '1');" );

					$resetlink = "https://" . $whitelabel_server_name . $port . "/s_edit.php?key=" . $resetcode ."&action=reset";	
					if($query1 != 1){
						errorDie('Er is iets misgegaan met het generen van een password resetcode.');
					}
					$mailtext = str_replace("~linkCard~", $linkCard, $mailtext);
					$mailtext = str_replace("~linkCardShort~", $linkCardShort, $mailtext);
					$mailtext = str_replace("~naam~", $_POST["voorvoegsel"] . " " . $_POST["voornaam"] . " " . $_POST["tussenvoegsel"] . " " . $_POST["achternaam"], $mailtext);
					$mailtext = str_replace("~email~", $_POST["email"], $mailtext);
					$mailtext = str_replace("~wachtwoord~", $resetlink, $mailtext);
					$mailtext = str_replace("~linkEdit~", $link4, $mailtext);
				}
				
				//echo $mailtext;
												
				$query_temp = mysql_query("SELECT * FROM mobilecard_companies WHERE id = '" . $c_id . "'");
				$row_temp   = mysql_fetch_array($query_temp, MYSQL_ASSOC);
							
				//die($mailtext);
				
				if($row_temp["test_fase"] != "1")
				{
					$do_mail = 1;
					
					if($_POST["e"] == "")
					{
						// *** Nieuwe card mail
					}
					else
					{
						// *** Updatemail

						if(!isset($_POST["c_naam"]) || $_POST["send_intromail"] != 1){ 
							$do_mail = 0;
						}
					}
										
					if($do_mail == 1)
					{
						mail($_POST["email"], $subject, $mailtext, $headers, "-f " . $whitelabel_email); 
						
						$extraMeldingen .= "Een introductiemail is verzonden naar " . $_POST["email"] . "";
						//$extraMeldingen .= "Een introductiemail is verzonden naar " . $_POST["email"] . "";
						//$extraMeldingen .= " (<a href='javascript: toggleMe(\"welkomstMailDiv\"); void(0);'>mail bekijken</a>)<div id='welkomstMailDiv' style='display: none; border: 1px solid #6d839b; padding: 10px;'>" . $mailtext . "</div><br><br>";
						//echo $_POST["email"] . "<hr>" . $subject . "<hr>" . $mailtext . "<hr>" . $headers . "<hr>" . "-f " . $whitelabel_email;
					}
				}
			
			}
			if($_POST['e'] != 'registreer'){
				$this_pakket = $_POST["template_pakket"];
				if($_POST["template_pakket"] == "") $this_pakket = $row_c["pakket"];
				
				$query_pakket = mysql_query("SELECT * FROM mobilecard_pakketten WHERE id = '" . $this_pakket . "'");
				$row_pakket   = mysql_fetch_array($query_pakket, MYSQL_ASSOC) or errorDie("Error when selecting row in database (Mollie pakket: " . $this_pakket . "/" . $_POST["template_pakket"] . "/" . $row_c["pakket"] . ").");
				
				$query = "SELECT * FROM mobilecard_mkbklanten WHERE company=" . $c_id . " && status='valid';";
				$result = mysql_query($query) or die($query);
				
				//Hier ergens fixen? Update naar 1 persoon werkt niet.
				if(mysql_num_rows($result) > 0 && $pakketpayment == 1){
					$fout = "Het pakket dat je gekozen hebt, gebruik je al.";
				}
				else if(($_POST["e"] == "" || $_POST["e"] == "4") && $row_pakket["bedrag"] != 0)
				{
					// *** Redirect naar Mollie				
					$query = mysql_query("INSERT INTO mobilecard_betalingen (company, beschrijving, bedrag, bestelcode) VALUES ('" . $c_id . "', '" . str_replace("'", "`", $row_pakket["titel"]) . " voor " . str_replace("'", "`", $_POST["c_volledige_naam"]) . "', '" . $row_pakket["bedrag"] . "', 'PAKKET" . $row_pakket["id"] . "')");
					$_SESSION["mollie_id"] = mysql_insert_id ();
					
					header("Location: /Mollie/prepare.php");
					die();

					//errorDie("Redirect naar Mollie");	
				}
				else if(($_POST["e"] == "" || $_POST["e"] == "4") && $row_pakket["bedrag_maand"] != 0){
					$result = mysql_query("UPDATE mobilecard_companies SET pakket=" . $row_pakket['id'] . ' WHERE id=' . $c_id . ';');
					mail("yannick@bizzerd.com", "Nieuwe betaling voor kaartje op maat " . $_POST["c_naam"] . " (" . $whitelabel_naam . ")!", $mailtext, $headers, '-f ' . $whitelabel_email);
					if(isset($c_id) && isset($_POST['naam']) && isset($_POST['email'])){
						$_SESSION["ingelogd"] = 1;
						$_SESSION["ingelogd_email"] = $_POST["email"];
						$_SESSION["ingelogd_wachtwoord"] = $_POST["wachtwoord"];
						$_SESSION['ingelogd_id'] = $c_id;
						$link = "/Mollie/recurring/create-first-payment.php?id=" . $c_id . "&name=" . $_POST['naam'] . "&email=" . $_POST['email'];
						//echo $link;
						header("Location: " . $link);
					}
				}
				else if($_POST['template_pakket'] !="" && $row_pakket['bedrag']==0 && $row_pakket['bedrag_maand'] == 0){
					$result = mysql_query("UPDATE mobilecard_companies SET pakket=" . $row_pakket['id'] . ' WHERE id=' . $c_id . ';');

				}
				if($_POST["e"] == "" && $row_pakket["bedrag"] == 0)
				{
					// *** Auto login
					$_POST["a"] = "login";
				}

				if( $_POST['template_pakket']=='' || $fout=='' || ($_POST["e"] == "1" || $_POST["e"] == "2" || $_POST["e"] == "3"))
				{
					// *** Toon melding van opslag
					$html = "
					
					<h1>Gegevens opgeslagen</h1>
					
					De gegevens zijn succesvol opgeslagen!
					
					<br><br>
					
					" . $extraMeldingen . "
					
					<a href='s_save.php' class='button'>doorgaan</a>			
					
					";
					
					$geenverdereactie = 1;
				}else{
					// *** Toon fout op scherm
					$html = "
					
					<h1>Fout ontstaan!</h1>
					
					" . $fout . $_POST['template_pakket'] . $_POST['e'] . "<br>
					
					Ga a.u.b. terug om het ontbrekende te corrigeren en aan te vullen.
					
					<br><br>
					
					<a href='javascript: history.go(-1);'>Terug</a>			
					
					";
					
					$geenverdereactie = 1;
				}
			}		
		}
		else
		{
			// *** Toon fout op scherm
			$html = "
			
			<h1>Fout ontstaan!</h1>
			
			" . $fout . "
			
			Ga a.u.b. terug om het ontbrekende te corrigeren en aan te vullen.
			
			<br><br>
			
			<a href='javascript: history.go(-1);'>Terug</a>			
			
			";
			
			$geenverdereactie = 1;
			
		}
	}
	if($_GET['a'] == 'verificatie'){
		$query = 'SELECT * FROM mobilecard_passwordreset WHERE keycode="' . $_GET['token'] . '";';
		$result = mysql_query($query);
		if($result){
			$row = mysql_fetch_array($result);
			$query2 = 'UPDATE mobilecard_persons SET verificationneeded=0 WHERE id=' . $row['person_id'] . ';';
			$result2 = mysql_query($query2);
			if($result2){
				$query3 = 'DELETE FROM mobilecard_passwordreset WHERE id=' . $row['person_id'] . ';'; 
				$result3 = mysql_query($query3);
				if($result3){
					$query4 = 'SELECT * FROM mobilecard_persons WHERE id=' . $row['person_id'] . ';';
					$row_p = mysql_fetch_array(mysql_query($query4));
					$_SESSION["ingelogd"] = 1;
					$_SESSION['ingelogd_id'] = $row_p['id'];
					$_SESSION["ingelogd_email"] = $row_p["email"];
					$_SESSION["ingelogd_wachtwoord"] = $row_p["wachtwoord"];
					
				}
			}
			else{
				errorDie('Verificatie mislukt (1)');
			}
		}
		else{
			errorDie('Verificatie mislukt');
		}
	}
	
	if(($_POST["a"] == "login" || $_SESSION["ingelogd"] == "1" || $_POST["a"] == "passwordreset") && $geenverdereactie != 1)
	{
		// *** Inloggen
		if($_POST["a"] == "login")
		{
		    $row = Array();
			$query  = mysql_query("SELECT id, wachtwoord, count(*) AS aantal FROM mobilecard_persons WHERE username = '" . $_POST["email"] . "';");
			$row    = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Could not select count in database. (1)");
			if($row['aantal'] == 0){   
			    $query2 = mysql_query("SELECT id, wachtwoord, count(*) AS aantal FROM mobilecard_persons WHERE email = '" . $_POST["email"] . "';");
			    $row = mysql_fetch_array($query2, MYSQLI_ASSOC) or errorDie("Could not select count in database");
            		}
			$aantal = $row["aantal"];
			if($aantal > 0 && password_verify($_POST['wachtwoord'], $row['wachtwoord']))
			{
				// Login successful.
				//if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
					// Recalculate a new password_hash() and overwrite the one we stored previously
				//}
				if(checkforneedverification($row['id'])){
					$_SESSION['ingelogd'] = -1;
				}
				else{
					$_SESSION["ingelogd"] = 1;
					$_SESSION["ingelogd_email"] = $_POST["email"];
					$_SESSION["ingelogd_wachtwoord"] = $_POST["wachtwoord"];
					$_SESSION['ingelogd_id'] = $row['id'];
				}
			}
			else $_SESSION["ingelogd"] = 0;
		}
		if($_SESSION["ingelogd"] == 1)
		{
			// *** Ingelogd

			// *** Init
			
			// *** Person		
			$query = mysql_query("SELECT * FROM mobilecard_persons WHERE id = '" . $_SESSION["ingelogd_id"] . "'");
			$row_p = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (5)");

			// *** Company
			$query = mysql_query("SELECT * FROM mobilecard_companies WHERE id = '" . $row_p["company"] . "'");
			$row_c = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Company or person not found (6)");
		
			// *** Pakket
			if($row_c["pakket"] == "")
			{
				$row_pakket["titel"] = "Geen pakket";
				$row_pakket["beschrijving"] = "U heeft nog geen pakket gekozen of het pakket nog niet betaald.";
			}
			else
			{
				$query = mysql_query("SELECT * FROM mobilecard_pakketten WHERE id = '" . $row_c["pakket"] . "'");
				$row_pakket = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Pakket niet gevonden.");
			}

		
			// *** Links
			$www = $whitelabel_www;
			if(strstr($whitelabel_server_name, "office.online4you.nl")) { $port = ":8380"; $www = $whitelabel_wwwdev; }
			$link = "https://" . $whitelabel_server_name . $port . "/cardoptions.php?c=" . $company . "&p=" . $person . "&";	
			$link2 = "" . str_replace($www . ".", $row_c["naam"] . ".", $whitelabel_server_name) . $port . "/";	
			
					
			//die($_SERVER["HTTP_X_FORWARDED_HOST"] . ">" . $link2);
			
			if($row_p["pasfoto"] != "") $pasfoto = "<div class='pasfoto' style='padding-left: 20px;'><div><img src='" . $row_p["pasfoto"] . "' style='max-height: 200px'></div></div>";
			
			$row_p["volledige_naam"] = trim($row_p["voorvoegsel"] . " " . $row_p["voornaam"] . " " . $row_p["tussenvoegsel"] . " " . $row_p["achternaam"]);

			$html = "<h1>Welkom " . $row_p["volledige_naam"] . "</h1>";

			
			if($row_p["superuser"] == "1")
			{
				if($_GET["del"] != "")
				{
					$query  = mysql_query("SELECT count(*) AS aantal FROM mobilecard_persons WHERE company = '" . $row_c["id"] . "' AND id = '" . $_GET["del"] . "';");
					$row    = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Could not select count in database.");
					$aantal = $row["aantal"];
					
					if($aantal == 0) errorDie("U heeft geen bevoegdheid om deze bewerking te doen.");
										
					$query = mysql_query("SELECT * FROM mobilecard_persons WHERE company = '" . $row_c["id"] . "' AND id = '" . $_GET["del"] . "'");
					$row_temp   = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Error when selecting row in database.");
					
					if($row_temp["superuser"] == "1")	
					{
						$cardmelding .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>Beheerders kunnen niet verwijderd worden.</b></div>"; 
					}
					else
					{									
						$query = mysql_query("DELETE FROM mobilecard_persons WHERE company = '" . $row_c["id"] . "' AND id = '" . $_GET["del"] . "'") or errorDie("Er ging iets fout tijdens het verwijderen");
					}
				}
				
				if($_GET["a"] == "new")
				{
					$query  = mysql_query("SELECT count(*) AS aantal FROM mobilecard_persons WHERE company = '" . $row_c["id"] . "';");
					$row    = mysql_fetch_array($query, MYSQL_ASSOC) or errorDie("Could not select count in database.");
					$aantal = $row["aantal"];
					
					
					if($aantal >= $row_pakket["cards_max"])
					{
						$cardmelding .= "<div style='display: block; padding: 10px; background-color: #FF0000; color: #FFFFFF; margin-bottom: 10px;'><b>U heeft al het maximum aantal " . $whitelabel_naam_card . "s van dit pakket! <a href='~pakketkiezen~' style='color: #FFFFFF'>Neem een groter</a> pakket om meer " . $whitelabel_naam_card . "s toe te voegen</b></div>"; 
					}
					else
					{				
						$query = mysql_query("INSERT INTO mobilecard_persons (datum, company, naam) VALUES ('" . date("Y-m-d H:i:s") . "', '" . $row_c["id"] . "', '')");
						$newid_p = mysql_insert_id ();
						$query2  = mysql_query("SELECT * FROM mobilecard_persons WHERE id = '" . $row_c["standaardcard"] . "';");
						$row_standaard = mysql_fetch_array($query2, MYSQL_ASSOC) or $row_standaard = $row_p;
						// tel = '" . $row_p["tel"] . "',
						
						$query = mysql_query("UPDATE mobilecard_persons SET 
						
						token = '',
						superuser = '',
						notificatie = '1',
												
						belmij = '" . $row_standaard["belmij"] . "',
						fax = '" . $row_standaard["fax"] . "',
						website = '" . $row_standaard["website"] . "',
						website_icoon = '" . $row_standaard["website_icoon"] . "',
						website_link = '" . $row_standaard["website_link"] . "',
						
						linkedin = '" . $row_standaard['linkedin'] . "',
						twitter = '" . $row_standaard['twitter'] . "',
						facebook = '" . $row_standaard['facebook'] . "',
						youtube = '" . $row_standaard["youtube"] . "',
						blog = '" . $row_standaard["blog"] . "',
						
						qrcode = '" . $row_standaard["qrcode"] . "',
						adresveld = '" . $row_standaard["adresveld"] . "',
						googlemaps = '" . $row_standaard["googlemaps"] . "',
						kvk = '" . $row_standaard["kvk"] . "',
						btw = '" . $row_standaard["btw"] . "',
						bankrekening = '" . $row_standaard["bankrekening"] . "',
						
						cv = '" . $row_standaard["cv"] . "',
						
						straat = '" . $row_standaard["straat"] . "',
						huisnummer = '" . $row_standaard["huisnummer"] . "',
						toevoeging = '" . $row_standaard["toevoeging"] . "',
						postbus = '" . $row_standaard["postbus"] . "',
						postcode = '" . $row_standaard["postcode"] . "',
						plaats = '" . $row_standaard["plaats"] . "',
						land = '" . $row_standaard["land"] . "',
						
						text_deel_sms = '" . $row_standaard["text_deel_sms"] . "',
						text_deel_email_subject = '" . $row_standaard["text_deel_email_subject"] . "',
						text_deel_email = '" . $row_standaard["text_deel_email"] . "',
						
						extralink1_titel = '" . $row_standaard["extralink1_titel"] . "',
						extralink2_titel = '" . $row_standaard["extralink2_titel"] . "',
						extralink3_titel = '" . $row_standaard["extralink3_titel"] . "',
						extralink4_titel = '" . $row_standaard["extralink4_titel"] . "',
						extralink1 = '" . $row_standaard["extralink1"] . "',
						extralink2 = '" . $row_standaard["extralink2"] . "',
						extralink3 = '" . $row_standaard["extralink3"] . "',
						extralink4 = '" . $row_standaard["extralink4"] . "',
						extralink1_type = '" . $row_standaard["extralink1_type"] . "',
						extralink2_type = '" . $row_standaard["extralink2_type"] . "',
						extralink3_type = '" . $row_standaard["extralink3_type"] . "',
						extralink4_type = '" . $row_standaard["extralink4_type"] . "',

						extratekst1 = '" . $row_standaard["extratekst1"] . "',
						extratekst2 = '" . $row_standaard["extratekst2"] . "',
						extratekst3 = '" . $row_standaard["extratekst3"] . "'
								
						WHERE id = '" . $newid_p . "';") or errorDie("Error while updating database.");
									
						
						$cardmelding .= "<div style='display: block; padding: 10px; background-color: #73be57; color: #FFFFFF; margin-bottom: 10px;'><b>Er is een " . $whitelabel_naam_card . " toegevoegd aan de lijst. U wordt aangeraden om deze gelijk te bewerken!</b></div>"; 
					}	
				}				
				
				// *** Superuser scherm tonen
				$blz = $_GET["blz"];
				if($blz == "") $blz = 0;
				
				if(isset($_POST["search"])) $_GET["search"] = $_POST["search"];
				$search = $_GET["search"];
				$search = str_replace("'", "`", $search);
				$search = str_replace('"', "`", $search);
				$search = trim($search);
				
							
				// *** Where query samenstellen
				$whereQuery = " company = '" . $row_c["id"] . "' ";
				
				if($search != "")
				{
					$like = " LIKE '%" . $search . "%' ";
					
					$whereQuery .= " AND (naam" . $like . " OR voorvoegsel" . $like . " OR voornaam" . $like . " OR tussenvoegsel" . $like . " OR 	achternaam" . $like . " OR email" . $like . " OR functie" . $like . " OR mobiel" . $like . " OR tel" . $like . " OR skype" . $like . ")";
				
				
				}
				
				
				
				$query  = "SELECT * FROM mobilecard_persons WHERE " . $whereQuery . " ORDER BY achternaam LIMIT " . ($blz * 10) . ", 10;";
				$result = mysql_query($query);
				
				$link2_temp = $link2;
				if($row_c["toegewijde_url"] != "") $link2_temp = $row_c["toegewijde_url"] . "/";
				$card_count = 0;
				
				while($r = mysql_fetch_assoc($result))
				{
					$temp_naam = trim($r["voorvoegsel"] . " " . $r["voornaam"] . " " . $r["tussenvoegsel"] . " " . $r["achternaam"]);
					$temp_link = $row_c["naam"] . "." . $whitelabel_domein . "/" . $r["naam"];
					
					if($row_c["toegewijde_url"] != "") $temp_link = $row_c["toegewijde_url"] . "/" . $r["naam"];
					
					
					if($temp_naam == "") $temp_naam = "<font style='color: #73be57'><b>Nieuwe card</b></font>";
					if($r["naam"] == "") $temp_link = "";
					
					$cards .= "
					
					<div class='editcard'>

						<a href='https://" . $link2_temp . $r["naam"] . "' title='Bekijken'    style='display: table-cell; padding: 5px; border-bottom: 1px solid #FFFFFF;' target='_blank'><img src='images/icons/bekijken" . $whitelabel . ".png' width='40' height='39' align='absmiddle'></a>
						<a href='/s.php?e=2&card=" . $r["id"] . "' title='Bewerken'    style='display: table-cell; padding: 5px; border-bottom: 1px solid #FFFFFF;'><img src='images/icons/bewerken" . $whitelabel . ".png' width='40' height='39' align='absmiddle'></a>
						<a href=\"javascript: if(confirm('Weet u zeker dat u deze " . $whitelabel_naam_card . " wilt verwijderen?') == true) { document.location = 's_save.php?del=" . $r["id"] . "'; }\" title='Verwijderen' style='display: table-cell; padding: 5px; border-bottom: 1px solid #FFFFFF;'><img src='images/icons/verwijderen" . $whitelabel . ".png' width='40' height='39' align='absmiddle'></a>
					
						<div style='display: table-cell; padding: 5px; border-bottom: 1px solid #FFFFFF;'><b>" . $temp_naam . "</b></div>
						<div style='display: table-cell; padding: 5px; border-bottom: 1px solid #FFFFFF;'><i>" . $temp_link . "</i></div>
						
					</div>
					
					";
					
					$card_count++;
				}
				
				
				// *** Option bar boven		
				if($search != "") $stop_search = "<td style='vertical-align: middle;'>&nbsp;<a href='/s_save.php#new' class='button_alt' style='width: 50px;'>Terug</a></td>";
						
				$nieuwe_card_boven = "

				<div style='float: right'>
				
					<form id='search_form' method='post' action='/s_save.php?blz=0#new'>

					<table cellspacing='0' cellpadding='0'>
					<tr>
					
						<td style='vertical-align: middle;'><input type='text' name='search' id='search' value='" . $search . "' placeholder='Zoeken...' class='form_input' style='padding: 7px;' onKeyPress='return submitenter(this,event)'>&nbsp;</td>
						<td style='vertical-align: middle;'><a href='javascript: document.getElementById(\"search_form\").submit();' class='button' style='width: 50px;'>&nbsp;<img src='/images/icons/search_white.png' align='absmiddle'>&nbsp;</a></td>
						" . $stop_search . "
					</tr>
					</table>

					</form>
					
					<script>
					
					function submitenter(myfield,e)
					{
					var keycode;
					if (window.event) keycode = window.event.keyCode;
					else if (e) keycode = e.which;
					else return true;
					
					if (keycode == 13)
					   {
					   myfield.form.submit();
					   return false;
					   }
					else
					   return true;
					}
					
					</script>
					
				</div>
				
				<a href='~nieuwecard~' class='button' style='width: 160px;'>Nieuwe " . $whitelabel_naam_card . "</a> 
				
								
				";
				
				
				$nieuwe_card_boven .= "<br><br>";




				// *** Option bar onder
				$nieuwe_card_onder .= "
				
				<table cellspacing='0' cellpadding='0'>
				<tr>
					<td style='vertical-align: middle;'>
				";		
				
				if($blz == 0)
				{
					$nieuwe_card_onder .= "<span class='button_disabled' style='width: 120px;'>< Vorige</span> ";
				}
				else
				{
					$nieuwe_card_onder .= "<a href='/s_save.php?blz=" . ($blz - 1) . "&search=" . $search . "#new' class='button' style='width: 120px;'>< Vorige</a> ";
				}
				
				
				$nieuwe_card_onder .= "</td><td style='vertical-align: middle;'>";
				

				// $nieuwe_card_onder .= "<a href='/s_save.php?blz=" . ($blz + 1) . "&search=" . $search . "#new' class='button' style='width: 160px;'>Volgende ></a> ";

				$query  = mysql_query("SELECT count(*) AS aantal FROM mobilecard_persons WHERE " . $whereQuery . ";");
				$row    = mysql_fetch_array($query, MYSQL_ASSOC) or die("Could not select count in database.");
				$aantal = $row["aantal"];
				$pages = ceil($aantal / 10);

				$nieuwe_card_onder .= "&nbsp;<select class='form_input' style='padding: 6px; width: auto;' onchange='document.location = \"s_save.php?blz=\" + this.value + \"&search=" . $search . "#new\"'>";
				
				for($i = 0; $i < $pages; $i++)
				{
					if($blz == $i) $selected = "selected"; else $selected = "";
					
					$nieuwe_card_onder .= "<option value='" . $i . "' " . $selected . ">" . ($i + 1) . " / " . $pages . "</option>";
				
				}
				
				$nieuwe_card_onder .= "</select>&nbsp;";

				$nieuwe_card_onder .= "</td><td style='vertical-align: middle;'>";



				if($blz >= ($pages - 1))
				{
					$nieuwe_card_onder .= "<span class='button_disabled' style='width: 120px;'>Volgende ></span> ";
				}
				else
				{
					$nieuwe_card_onder .= "<a href='/s_save.php?blz=" . ($blz + 1) . "&search=" . $search . "#new' class='button' style='width: 120px;'>Volgende ></a> ";
				}
				
				$nieuwe_card_onder .= "</td></tr></table>";




				
				
				if($card_count <= 0) $nieuwe_card_boven .= "<i>Helaas geen " . $whitelabel_naam_card . " gevonden!</i><br><br>";
				
				
				$html .= "
				
				<table cellspacing='0' cellpadding='0' style='width: 100%'>
				<tr>
					<td>
					
						<h2><a href='https://" . $link2 . $row_p["naam"] . "' target='_blank'>Bekijk hier uw card</a></h2>
						
						<h1>Uw pakket: " . str_ireplace("mobicard", $whitelabel_naam, $row_pakket["titel"]) . "</h1>
						
						" . str_ireplace("mobicard", $whitelabel_naam_card, $row_pakket["beschrijving"]) . "
						
						<br><br>
						
						<a href='~pakketkiezen~' class='button' style='width: 140px;'>pakket wijzigen</a>
						<a href='~vormgevingbewerken~' class='button' style='width: 170px;'>vormgeving wijzigen</a>
						<a href='~uitloggen~' class='button_alt'>uitloggen</a>
										
						<br><br>
					
					</td>
				
					<td style='width: 135px;'>" . $pasfoto . "</td>
								
				</tr>
				</table>
				
				<a name='new'></a>
				<h1>Uw " . $whitelabel_naam_card . "s (max. ~pakket_max~)</h1>


								
				" . $nieuwe_card_boven . "
								
				<div style='display: table; width: 100%'>	
				
					" . $cards . "
				
				</div>
				
				" . $cardmelding . "
				
				<br>
								
				" . $nieuwe_card_onder . "
								
				<br>
				
				<div style='padding-top: 2px; padding-bottom: 16px;'>
				
					Legenda:
					<img src='images/icons/bekijken" . $whitelabel . ".png' width='20' height='20' align='absmiddle'> : Bekijken &nbsp;
					<img src='images/icons/bewerken" . $whitelabel . ".png' width='20' height='20' align='absmiddle'> : Bewerken &nbsp;
					<img src='images/icons/verwijderen" . $whitelabel . ".png' width='20' height='20' align='absmiddle'> : Verwijderen
					
				</div>				
				
				";
			
			}
			else
			{
				// *** Als niet superuser, doorsturen naar stappen
				if($row_c['centraalbeheer'] == 1){
					$_SESSION['ingelogd'] = 0;
					$_SESSION["ingelogd_email"] = '';
					$_SESSION["ingelogd_wachtwoord"] = '';
					$_SESSION['ingelogd_id'] = '';
					
					$html .= "
					
					Helaas heb je geen toegang tot je kaartje. Neem contact op met een collega die je kaartje beheert.
					
					<br><br>
					
					<a href='/s_edit.php' class='button'>Terug</a>				
					
					";
				}
				else{
					$html .= "
					
					Welkom " . $row_p["volledige_naam"] . ", u bent succesvol ingelogd!<br>
					Klik op de button om uw gegevens op uw " . $whitelabel_naam_card . " te bewerken.
					
					<br><br>
					
					<a href='~cardbewerken~' class='button'>Doorgaan</a> &nbsp;<a href='s_edit.php' class='button_alt'>Uitloggen</a>				
					
					";
				}
			}
			
		}
		elseif($_SESSION['ingelogd'] == -1){
			$html .= "
			<h1>Verificatie vereist</h1>
				
			Je hebt je e-mailadres nog niet bevestigt. Check je inbox om dit te doen.
			Heb je geen mail ontvangen?
			<br><br>
			<a href='s_edit.php?e=" . $_POST["email"] ."' class='button' style='width: 200px;'>inloggen</a>";

		}
		elseif($_POST['a'] == 'passwordreset'){
			$query1 = mysql_query("SELECT * FROM mobilecard_passwordreset WHERE keycode='" . $_POST['key'] . "';");
			$row1    = mysql_fetch_array($query1, MYSQL_ASSOC) or die("Could not find the key.");
			$query2 = mysql_query("UPDATE mobilecard_persons SET wachtwoord='". password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT) ."'  WHERE id=" . $row1['person_id'] . ";");
			$query3 = mysql_query("DELETE FROM mobilecard_passwordreset WHERE keycode='". $_POST['key'] ."';");
			
			$html.= "Uw wachtwoord is gereset.<br><br> <a href='/s_edit.php' class='button'>Inloggen</a>";

			//$row    = mysql_fetch_array($query, MYSQL_ASSOC) or die("Could not select count in database.");
		}
		else
		{
			// *** Toon inlogfout op scherm
			$html = "
			
			<h1>Inlog gegevens onjuist!</h1>

			De door u opgegeven e-mail en wachtwoord komen helaas niet overeen.<br>
			Ga a.u.b. terug en probeer opnieuw.
			
			<br><br>
			
			<a href='javascript: history.go(-1);' class='button'>Terug</a>			
			
			<br><br><br>
			
			Bent u uw <a href='s_edit.php?a=ww&e=" . $_POST["email"] . "'>wachtwoord vergeten?</a>
			
			";			
		
		}
	}
	if($_POST["a"] == "wachtwoord opvragen")
{
	
	$row = Array();
    $query = mysql_query("SELECT * FROM mobilecard_persons WHERE username = '" . strtolower($_POST["email"]) . "'");
    if(mysql_num_rows($query) > 0){
	    $row   = mysql_fetch_array($query, MYSQL_ASSOC);
    }
    else{
        $query2 = mysql_query("SELECT * FROM mobilecard_persons WHERE email = '" . strtolower($_POST["email"]) . "'");
        $row= mysql_fetch_array($query2, MYSQL_ASSOC);
    }
	if(!empty($row)){
		if(strstr($whitelabel_server_name, "office.online4you.nl")) { $port = ":8380"; $www = $whitelabel_wwwdev; }
		
		$resetcode = getToken(32);
		$query1 = mysql_query("INSERT INTO mobilecard_passwordreset (keycode, person_id, timestamp) VALUES ('" . $resetcode . "','" . $row["id"] . "','" . date(c) . "');" );

		$resetlink = "https://" . $whitelabel_server_name . $port . "/s_edit.php?key=" . $resetcode ."&action=reset";	
		if($query1 != 1){
			errorDie('Er is iets misgegaan met het generen van een password resetcode.');
		}
		
		$mailtext = "
		
		Beste " . trim($row["voorvoegsel"] . " " . $row["voornaam"] . " " . $row["tussenvoegsel"] . " " . $row["achternaam"]) . ",
		
		<br><br>
		
		Je hebt een verzoek ingediend op " . $whitelabel_naam . " om je wachtwoord te resetten. 
			
		<br><br>
		

		Je wachtwoord kan je hier resetten: <a href='" . $resetlink . "'>" . $resetlink .  "</a>
				
		
		<br><br>
		
		Stuur een e-mail naar <a href='mailto:" . $whitelabel_email . "'>" . $whitelabel_email . "</a> als je problemen blijft ondervinden!
		
		";	
			$mailtext = str_replace("~html~", $mailtext, $emailTemplate);
			mail($row["email"], "Uw " . $whitelabel_naam . " wachtwoord", $mailtext, $headers, "-f " . $whitelabel_email);


		$html.='
		<h1>Wachtwoord vergeten</h1>
			
		Een link voor het resetten van je wachtwoord is naar je e-mailadres verzonden!<br>
		Klik op de toegestuurde link en pas je wachtwoord aan. De link is een uur geldig.<br>
		Mocht je geen e-mail hebben ontvangen, controleer dan ook je spambox.
		<br><br>
		
		<a href=\'s_edit.php?e=' . $_POST["email"] . '\' class=\'button\' style=\'width: 200px;\'>inloggen</a>';

	}
	else
	{
		?>
		<h1>Wachtwoord vergeten</h1>
			
		Er bestaat geen <?php echo $whitelabel_naam;?> met dit e-mailadres.
		<br><br>
		<a href='s_edit.php?e=<?php echo $_POST["email"]; ?>' class='button' style='width: 200px;'>inloggen</a>
		<?php
	}

}
	// *** Tildecodes vervangen
	$html = str_replace("~pakket_max~", $row_pakket["cards_max"], $html);
	$html = str_replace("~pakket_einddatum~", date("d-m-Y", strtotime($row_c["einddatum"])), $html);

	$html = str_replace("~nieuwecard~", 		"s_save.php?a=new#new", $html);
	
	if($row_c["template"] == '1')
	{
		$html = str_replace("~vormgevingbewerken~", "s.php?e=1", $html);
	}
	else
	{
		$html = str_replace("~vormgevingbewerken~", "javascript: if(confirm(\"U heeft een op maat gemaakte vormgeving die niet of nauwelijks via deze online beheeromgeving gewijzigd kan worden. De opties in het hieropvolgend scherm hebben daarom geen of nauwelijks effect op de vormgeving.\\n\\nNeem contact met ons op als u wijzigingen wenst zodat wij deze op maat kunnen uitvoeren.\\n\\nWeet u zeker dat u door wilt gaan?\") == true) { document.location = \"s.php?e=1\"; }", $html);
	}
	
	$html = str_replace("~cardbewerken~", 		"s.php?e=2", $html);
	$html = str_replace("~pakketkiezen~", 		"s.php?e=4", $html);
	$html = str_replace("~uitloggen~", 		"/s_edit.php", $html);
	
	
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">  
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title>Uw <? echo $whitelabel_naam_card; ?></title>
	
	<link rel="shortcut icon" type="image/png" href="/images/icons/favicon<? echo $whitelabel; ?>.png">
	<?php 
	
		if($whitelabel == "_bizzerd") echo "<link href='/webfonts/MyFontsWebfontsKit.css' rel='stylesheet' type='text/css'>"; 
		
	?>
	
	<link href="/styles/layout_new.css" rel="stylesheet" type="text/css">
	<!--link href="/styles/styles.css" rel="stylesheet" type="text/css"-->
	
	<link href="/styles/layout_stappen.css" rel="stylesheet" type="text/css">
	<?php if($whitelabel != "") echo "<link href=\"/styles/layout" . $whitelabel . ".css\" rel=\"stylesheet\" type=\"text/css\">"; ?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/styles/GDPRModal_CSS.css">


    <script src="/javascript/global.js" type="text/javascript"></script>
	
</head>
<body>


<?php

	include("includes/header_s" . $whitelabel . ".php");
	
?>

<div>
<?php
    if(!checkGDPR() && !isset($_GET['cancel'])){
        showGDPRdialog();
    }
	echo $html;

	if($www == $whitelabel_wwwdev)	
	{
		$link_customsession = "http://" . str_replace($www . ".", "cards.", $whitelabel_server_name) . ":8380/resetcustomsession.php"; // http://cards.mobicard.nl.office.online4you.nl:8380/index.php?p=template&c=template
	}
	else
	{
		$link_customsession = "https://cards." . $whitelabel_domein . "/resetcustomsession.php";	
	}
		
?>

<iframe style='border: 0px; width: 10px; height: 10px; background-color: #FFFFFF;' frameborder='0' src='<? echo $link_customsession; ?>' scrolling='no'></iframe>
		
</div>

<?php

	include("includes/footer_s" . $whitelabel . ".php");
	
?>

<!-- Div popup -->
<a href='javascript: hidePopupDiv();' id='page_screen'></a>

<div name='popupDiv' id='popupDiv' style='display:none;'>
<div style='padding: 5px;'>

	<a href='javascript: hidePopupDiv();' class='x_button' style='float: right; margin-top: 3px; margin-right: 3px;'></a>

	<iframe id='divpopup' name='divpopup' marginwidth='0' marginheight='0' height='280' width='453' border='0' frameborder='0' style='margin: 5px;'></iframe>


</div>
</div>
<!-- /Div popup -->



</body>
</html>
<?php		
		
	// *** Databaseconnectie afsluiten
	include("./admin/databasedeconnectie.php");

?>