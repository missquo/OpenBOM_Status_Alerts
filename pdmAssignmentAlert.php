<?php

include 'depthx.php';

//To test manually from web interface
//if(isset($_POST['email'])) {
 
	//Curl session to log in and get app key
	$loggingIn = curl_init();
	curl_setopt($loggingIn, CURLOPT_POST, true);
	curl_setopt($loggingIn, CURLOPT_URL, 'https://developer-api.openbom.com/login');
	curl_setopt($loggingIn, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($loggingIn, CURLOPT_POSTFIELDS, '{"username":'.$account.',
	"password":'.$entry.'}');
	curl_setopt($loggingIn, CURLOPT_HTTPHEADER, [
	  'x-openbom-appkey: '.$key,
	  'Content-Type: application/json'
	]);
	curl_setopt($loggingIn, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//Save returned data to output variable
	$output = curl_exec($loggingIn);
	//Decode string data to json format
	$json = json_decode($output, true);
	$access = $json['access_token'];
	$info = curl_getinfo($loggingIn);	
	curl_close($loggingIn);
	
	//Curl session to get Stone Catalog data from OpenBOM
	$getStoneCatalog = curl_init();
	curl_setopt($getStoneCatalog, CURLOPT_URL, $catalogURL);
	curl_setopt($getStoneCatalog, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($getStoneCatalog, CURLOPT_HTTPHEADER, [
	  'x-openbom-accesstoken: ' . $access,
	  'x-openbom-appkey: '.$key
	]);
	curl_setopt($getStoneCatalog, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//Save returned data to catalog variable
	$catalog = curl_exec($getStoneCatalog);
	//Decode string data to json format
	$stonejson = json_decode($catalog, true);
	curl_close($getStoneCatalog);
	
	$previousStone = file_get_contents('previousStone.json'); //currently unused
	$previousStonejson = json_decode($previousStone, true);  //currently unused
	
	//echo "<script>console.log('Debug Objects: " . addslashes($catalog) . "' );</script>";
	
	// Create table title row and declare rows and row count variables for each individual
	$ajl_rows = $jcs_rows = $jhr_rows = $jtm_rows = $ntw_rows = $sdl_rows = $vig_rows = '';
	$ajlCount = $jcsCount = $jhrCount = $jtmCount = $ntwCount = $sdlCount = $vigCount = 0;
	
	$findPart = "";
	$arrayOfParts = array();
	
	function searchForParts($partfromnew) {
		$none = "";
		// Check to see if part is designed in-house
		if (($partfromnew[1] === "0") || ($partfromnew[1] === "")) {
			return $none;
		}
		//CHeck to see if part is in any state of review
		if (strpos($partfromnew[10], 'REVIEW') === false) {
			return $none;
		}			
		return $partfromnew;
	}
	
	//Function to email users table of their parts that are in transition
	function emailUser ($tableRows, $count, $userName) {
		$titleRow = '<table style="border-collapse:collapse;"><tr style="background:#BFBFFF";><td style="border: 2px solid blue; padding-left:5px; padding-right:5px;"><b>PART NUMBER</b></td><td style="border: 2px solid blue; padding-left:5px; padding-right:5px;"><b>DESCRIPTION</b></td><td style="border: 2px solid blue; padding-left:5px; padding-right:5px;"><b>PDM STATE</b></td><td style="border: 2px solid blue; padding-left:5px; padding-right:5px;"><b>PDM ASSIGNMENT</b></td><td style="border: 2px solid blue; padding-left:5px; padding-right:5px;"><b>PDM COMMENTS</b></td></tr>';
		$tableRows .= '</table>';
		$message = "";
		$plural = ' is';
		if ($count > 1) {
			$plural = 's are';
		}
		$message .= '<h2 style="font-variant: small-caps;"> '. $userName .', the following item'. $plural .' in transition:</h2>';
	
		//Add table to email message
		$message .= $titleRow;
		$message .= $tableRows;
		
		// Puts PHP-required line breaks in every 70 characters
		$message = wordwrap($message,70);		
		return $message;
	}
		
	//Filter for qualifying rows
	foreach($stonejson['cells'] as $part) {
		$findPart = searchForParts($part);
		// If searchForParts function returns value, push part to $arrayOfParts
		if ($findPart !== "") {
			array_push($arrayOfParts, $findPart);
		}
	}
	
	//CSS for table rows
	$rowCss = '<td style="border: 2px solid blue; padding-left:5px; padding-right:5px;">';
	
	if (!empty($arrayOfParts)) {
		$counter = 0;
		foreach($arrayOfParts as $part) {
			//Remove unnecessary columns
			unset($part[0],$part[1],$part[4],$part[5],$part[6],$part[7],$part[8],$part[9],$part[13],$part[14],$part[15],$part[16]);
			$arrayOfParts[$counter] = array_values($part);
			echo "<script>console.log('Debug Objects 103: " . $part[3] . "' );</script>";
			$counter = $counter + 1;
		}
		
		//Build individual table rows		
		foreach($arrayOfParts as $part) {
			//If parts aren't assigned they get sent to everyone
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'AJL') !== false))) {
				$ajlCount = $ajlCount + 1;
				$ajl_rows .= '<tr>';
				foreach($part as $column) {
					$ajl_rows .= $rowCss.addslashes($column).'</td>';
				}
				$ajl_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'JCS') !== false))) {
				$jcsCount = $jcsCount + 1;
				$jcs_rows .= '<tr>';
				foreach($part as $column) {
					$jcs_rows .= $rowCss.addslashes($column).'</td>';
				}
				$jcs_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'JHR') !== false))) {
				$jhrCount = $jhrCount + 1;
				$jhr_rows .= '<tr>';
				foreach($part as $column) {
					$jhr_rows .= $rowCss.addslashes($column).'</td>';
				}
				$jhr_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'JTM') !== false))) {
				$jtmCount = $jtmCount + 1;
				$jtm_rows .= '<tr>';
				foreach($part as $column) {
					$jtm_rows .= $rowCss.addslashes($column).'</td>';
				}
				$jtm_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'NTW') !== false))) {
				$ntwCount = $ntwCount + 1;
				$ntw_rows .= '<tr>';
				foreach($part as $column) {
					$ntw_rows .= $rowCss.addslashes($column).'</td>';
				}
				$ntw_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'SDL') !== false))) {
				$sdlCount = $sdlCount + 1;
				$sdl_rows .= '<tr>';
				foreach($part as $column) {
					$sdl_rows .= $rowCss.addslashes($column).'</td>';
				}
				$sdl_rows .= '</tr>';
			}
			if (($part[3] === '') || ($part[3] === null) || ((strpos($part[3], 'VIG') !== false))) {
				$vigCount = $vigCount + 1;
				$vig_rows .= '<tr>';
				foreach($part as $column) {
					$vig_rows .= $rowCss.addslashes($column).'</td>';
				}
				$vig_rows .= '</tr>';
			}
		}
		
	}	
	
	// create email headers
	$headers = 'MIME-Version: 1.0' . "\r\n"; 
	$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
	$headers .= 'Organization: Rachel\'s PDM Clearinghouse' . "\r\n";
	$headers .= 'From: PDM Status <noreply@depotbench.com>'."\r\n".'Reply-To: noreply@depotbench.com'."\r\n" .'X-Mailer: PHP' . phpversion();
	
	//Current date for email subject line
	date_default_timezone_set('America/Chicago');
	$dow = date('l');
    $datestamp = date('m-d-Y');
	$datehour = date('l F j');
	
	//Email subject line
    $email_subject = 'PDM Items in Transition for '.$datehour;
	
	if ($ajlCount !== 0) {
		$ajlMessage = emailUser($ajl_rows, $ajlCount, 'Alberto');		
		@mail('rp@depotbench.com', $email_subject, $ajlMessage, $headers); 
	}
	
	if ($jcsCount !== 0) {
		$jcsMessage = emailUser($jcs_rows, $jcsCount, 'Justin');		
		@mail('rp@depotbench.com', $email_subject, $jcsMessage, $headers); 
	}
	
	if ($jhrCount !== 0) {
		$jhrMessage = emailUser($jhr_rows, $jhrCount, 'James');		
		@mail('rp@depotbench.com', $email_subject, $jhrMessage, $headers); 
	}	
	
	if ($jtmCount !== 0) {
		$jtmMessage = emailUser($jtm_rows, $jtmCount, 'Josh');		
		@mail('rp@depotbench.com', $email_subject, $jtmMessage, $headers); 
	}
	
	if ($ntwCount !== 0) {
		$ntwMessage = emailUser($ntw_rows, $ntwCount, 'Nathan');		
		@mail('rp@depotbench.com', $email_subject, $ntwMessage, $headers); 
	}
	
	if ($sdlCount !== 0) {
		$sdlMessage = emailUser($sdl_rows, $sdlCount, 'Scott');		
		@mail('rp@depotbench.com', $email_subject, $sdlMessage, $headers); 
	}
	
	if ($vigCount !== 0) {
		$vigMessage = emailUser($vig_rows, $vigCount, 'Veronica');		
		@mail('rp@depotbench.com', $email_subject, $vigMessage, $headers); 
	}	
	
	//Put current purchasing inquiry into file for later comparison
	file_put_contents('previousStone.json', $catalog); // not currently in use

	//Troubleshooting to Google console
	//echo "<script>console.log('Debug Objects: " . addslashes($catalog) . "' );</script>";
	
//}
?>