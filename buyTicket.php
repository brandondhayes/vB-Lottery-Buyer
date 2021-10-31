<?php

//----------------------------------------------------------------------
// Config Variables
//----------------------------------------------------------------------
$host = "127.0.0.1";		// MySQL Settings
$user = "root";
$pass = "";
$database = "vb_lotto";   
  
$buyTicketRate = 1; 		// Rate to buy tickets in seconds.
$stopTicket = 5000; 		// Ticket id to stop processing tickets.

$userCookie = "bb_lastvisit=1361433778; bb_lastactivity=0; bb_userid=REDACTED; bb_password=REDACTED; tapatalk_redirect=false; bb_sessionhash=REDACTED; skimlinks_enabled=1";

$dblink = @mysql_connect($host,$user,$pass);
mysql_select_db($database,$dblink);


//----------------------------------------------------------------------
// Functions List
//----------------------------------------------------------------------
function grabSecurityTokenVB($cookie)
{
	$crl = curl_init();

	$breakphrase1 = "<input type=\"hidden\" name=\"securitytoken\" value=\"";
	$breakphrase2 = "\" />";

	curl_setopt ($crl, CURLOPT_URL, "http://REDACTED/vbshop.php?do=lottery&action=buyticket&lotteryid=2");
	curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt ($crl, CURLOPT_COOKIE, $cookie);
	
	$sourcecode = curl_exec($crl);
	
	$breaker = explode("$breakphrase1", $sourcecode);
	$securityCode = explode("$breakphrase2", $breaker[1]);

	curl_close($crl);
	
	return $securityCode[0];
}

function postLottoTicket($token, $ticket, $cookie)
{
	$crl = curl_init();
	
	curl_setopt($crl, CURLOPT_URL, "http://REDACTED/vbshop.php");
	curl_setopt($crl, CURLOPT_POST, TRUE);
	curl_setopt($crl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0");
	curl_setopt($crl, CURLOPT_POSTFIELDS, "numbers%5B1%5D={$ticket[0]}&numbers%5B2%5D={$ticket[1]}&numbers%5B3%5D={$ticket[2]}&numbers%5B4%5D={$ticket[3]}&numbers%5B5%5D={$ticket[4]}&numbers%5B6%5D={$ticket[5]}&numbers%5B7%5D={$ticket[6]}&s=&securitytoken={$token}&do=lottery&action=dobuyticket&lotteryid=2");
	curl_setopt($crl, CURLOPT_REFERER, "http://REDACTED/vbshop.php?do=lottery&action=buyticket&lotteryid=2");	
	curl_setopt($crl, CURLOPT_COOKIE, $cookie);
	curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, 5);
	
	$ret = curl_exec($crl);
	curl_close($crl);
	return $ret;
}

//----------------------------------------------------------------------
// main()
//----------------------------------------------------------------------

$query = mysql_query("SELECT * FROM `tickets` WHERE `used` = 0 ORDER BY `tickets`.`ticket_id` ASC LIMIT 1;");

while ($table = mysql_fetch_array($query))
{
	$ticket[0] = $table[2];
	$ticket[1] = $table[3];
	$ticket[2] = $table[4];
	$ticket[3] = $table[5];
	$ticket[4] = $table[6];
	$ticket[5] = $table[7];
	$ticket[6] = $table[8];
	
	echo "Purchasing Ticket #:" . ($table[0]+1) . " ";
	echo "<title> vB Tickets: " . ($table[0]+1) . " - {$stopTicket}</title>";
	
	echo "{$ticket[0]}-{$ticket[1]}-{$ticket[2]}-{$ticket[3]}-{$ticket[4]}-{$ticket[5]}-{$ticket[6]}<br><br>";
	
	$securityToken = grabSecurityTokenVB($userCookie);
	
	postLottoTicket($securityToken, $ticket, $userCookie);
	
	if (mysql_query("UPDATE `vb_lotto`.`tickets` SET `used` = '1' WHERE `tickets`.`ticket_id` = {$table[0]};"))
		echo "Ticket Purchased!";
	
	if ($table[0] < ($stopTicket-1)) 
		echo " Refreshing in {$buyTicketRate}... <meta http-equiv=\"refresh\" content=\"{$buyTicketRate}\"/>";
	else
		echo " All done! ";
	
}

?>
