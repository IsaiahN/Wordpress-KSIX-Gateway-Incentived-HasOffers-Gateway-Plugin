<?php
/*
*	File: ksix_offerlist-gateway.php
*  	Description: Gets KSIX offerlist
*/
session_start();
ini_set("display_errors", 1);

// Gateway Configurations
$ksix_api_key  			= "AFFDBOpO4sswKMo27a5b5EXoHqaFUK"; 	// Your KSIX API Key
$ksix_header			= "KSIX Offerwall | Your Subtitle Goes Here";
$offer_search_limit	 	= 300;  				// Offer Search Limit (changes Loading Time & The # of Results)
$redirect_url			= "http://ksix.com";			// URL that you wish to redirect users to on offer completion.

/* ----------------------- Do Not Make Any Changes Below This Line -------------------- */

// Get Current Date & DateTime
if(!isset($_SESSION['current_datetime']) || !isset($_SESSION['current_date'])) {
	$_SESSION['current_datetime'] 	= date("Y-m-d H:i:s");
	$_SESSION['current_date'] 	= date("Y-m-d");
}

// Get User IP address & Country Code
function getRealIpAddress()
{
	if (!empty($_SERVER['HTTP_CLIENT_IP'])){$ip=$_SERVER['HTTP_CLIENT_IP'];}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];}
	else{$ip=$_SERVER['REMOTE_ADDR'];}
	$get_country_code 		= file_get_contents('http://freegeoip.net/json/'.$ip.'');
	$country_code_json		= json_decode($get_country_code, true);
	$_SESSION['country_code'] 	= $country_code_json['country_code'];
	unset($get_country_code, $country_code_json);
	return $ip;
}

function checkOfferStats() {

	if(isset($_SESSION['current_datetime']) && strtotime($_SESSION['current_datetime']) < time()) {
		$user_stats_url 		= "http://network.ksix.com/stats/lead_report.json?api_key=".$ksix_api_key."&start_date=".$_SESSION['current_date']."&end_date=".$_SESSION['current_date']."&filter[Stat.status]=approved&filter[Stat.ip]=".getRealIpAddress();
		$user_stats_results 		= file_get_contents($user_stats_url);
		$offer_list_results_json 	= json_decode($user_stats_results, true);
		unset($user_stats_results, $user_stats_url, $ksix_api_key);
		
			// Check User Results['data']['offer']
			if (is_array($offer_list_results_json))
			{
				foreach($offer_list_results_json['data']['offer'] as $item) {
					if (strtotime($item['date_time']) > strtotime($_SESSION['current_datetime'])) 
					return '<li class="redirect_link">Thank you for your offer completion.<br> Your Destination Link: <a href="'.$redirect_url.'" target="_blank">'.$redirect_url.'</a></li>';
				}
			}
		}
		return "0";	
	}
if ($_POST['act'] == "check") {echo checkOfferStats();}
else if($_POST['act'] == "list") {
	// Echo Featured Offers
	echo '<li><h3 id="wall_header">'.$ksix_header.'</h3></li>';

	// Get IP Address
	if(isset($_POST['ip'])) {$ip = $_POST['ip'];}
	else {$ip = getRealIpAddress();}

	// Get Categories
	if((isset($_POST["categories"])) && (!empty($_POST["categories"]))) {$categories = $_POST["categories"];}

	// Retrieve Offerlist
	$offer_list_url 	 	= "http://network.ksix.com/offers/offers.json?api_key=".$ksix_api_key."&limit=$offer_search_limit";
	$offer_list_results 		= file_get_contents($offer_list_url);
	$offer_list_results_json 	= json_decode($offer_list_results, true);
	unset($offer_list_results, $offer_list_url, $ksix_api_key);


	if (is_array($offer_list_results_json['data']['offers']))
	{

		foreach($offer_list_results_json['data']['offers'] as $item) {
			if ((strpos($item['countries_short'], $_SESSION['country_code']) !== false) && (strpos($item['categories'], "Content Locking") !== false)) {  //Get GEOIP Offers
				if(!isset($categories)) {
					echo '<li class="ksix_offer"><a href="'.$item["tracking_url"].'&aff_sub='.$ip.'" title="Offer Description:'.$item['description'].'" target="_blank">'.html_entity_decode(substr($item["name"], 0, -9)).'</a></li>';
				} else {
					if (strpos($item['categories'], $categories) !== false) {
					echo '<li class="ksix_offer"><a href="'.$item["tracking_url"].'&aff_sub='.$ip.'" title="Offer Description:'.$item['description'].'" target="_blank">'.html_entity_decode(substr($item["name"], 0, -9)).'</a></li>';
					}
				}
			}
		}
		unset($offer_list_results_json);
	}
	echo '<li class="end_list ksix_offer">End Of The Offerlist</li>';
}
?>