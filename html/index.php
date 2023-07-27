<?php
include('servers.php');
?>

<html>
<head>
<title>Web Check</title>

<style type="text/css">

.formdiv {
	width:1420px; height:50px;
	top:10px; left:10px;
	position:absolute;
	margin:auto;

	text-align:center;
	vertical-align:middle;
	background-color:#FFFFFF;

	box-shadow:10px 10px 5px #888888;
	border:2px solid #000000;

	-moz-border-radius-bottomleft:14px;
	-webkit-border-bottom-left-radius:14px;
	border-bottom-left-radius:14px;

	-moz-border-radius-bottomright:14px;
	-webkit-border-bottom-right-radius:14px;
	border-bottom-right-radius:14px;

	-moz-border-radius-topright:14px;
	-webkit-border-top-right-radius:14px;
	border-top-right-radius:14px;

	-moz-border-radius-topleft:14px;
	-webkit-border-top-left-radius:14px;
	border-top-left-radius:14px;
}

.iframediv {
	width: 700px; height:390px; position:absolute;
}

.hold {
	width: 1440px; height: 890px; margin-left: auto; margin-right: auto;
}
.contain {
	position:absolute; z-index:0; background:transparent;
}

white.body {
	background-color:#FFFFFF;
}
blue.body {
	background-color:#66CCFF;
}
green.body {
	background-color:#D6EB99;
}
body {
	background-color:#E0E0E0;
}

iframe {
	width:100%; height:100%; background-color:#ffffff;

	box-shadow: 10px 10px 5px #888888;
	border:2px solid #000000;

	-moz-border-radius-bottomleft:14px;
	-webkit-border-bottom-left-radius:14px;
	border-bottom-left-radius:14px;

	-moz-border-radius-bottomright:14px;
	-webkit-border-bottom-right-radius:14px;
	border-bottom-right-radius:14px;

	-moz-border-radius-topright:14px;
	-webkit-border-top-right-radius:14px;
	border-top-right-radius:14px;

	-moz-border-radius-topleft:14px;
	-webkit-border-top-left-radius:14px;
	border-top-left-radius:14px;

	margin-bottom: 20px;
}

form {
	width:90%; height:50%; background-color:#ffffff; vertical-align:middle; text-align:center; margin:auto; margin-top:10px;
	font-family: Book Antiqua; font-size: 16px; font-style: italic; font-variant: normal; font-weight: normal;
}

</style>

</head>
<body>

<?php
$defaultURL = $_POST['url'];

if ($defaultURL == "" && isset($_COOKIE["virtual_host_url"]) && $_COOKIE["virtual_host_url"] != "")
{
	$defaultURL = base64_decode($_COOKIE["virtual_host_url"]);
}

if (isset($_GET['clearcookie']))
{
	setcookie("virtual_host_url", "", time()-3600);
	$defaultURL = "";
}

if ($defaultURL == "")
{
	$defaultURL = "";
}
$defaultServers = $_POST['servers'];
if ($defaultServers == "") { $defaultServers = "P"; }
?>

<div class="hold">
<div class="contain">

<div class="formdiv">
<form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
<input type="radio" name="servers" id="SD" value="D" <?php if ($defaultServers == "D") { echo "checked"; } else { echo ""; } ?> >Dev
<input type="radio" name="servers" id="ST" value="T" <?php if ($defaultServers == "T") { echo "checked"; } else { echo ""; } ?> >Test
<input type="radio" name="servers" id="SP" value="P" <?php if ($defaultServers == "P") { echo "checked"; } else { echo ""; } ?> >Prod
&nbsp;&nbsp;&nbsp;
<label for="url">VHost URL: </label>
<input type="text" name="url" id='url' size="70" placeholder="https://www.lab.home/" value=<?php echo $defaultURL; ?> >
<input type="submit" name="sub" value="Submit">
&nbsp;
<input type="checkbox" name="keepurl" value="Y">Keep?
</form>
</div>

<?php
if(isset($_POST['sub'])) {
	$url = $_POST['url'];
	$servers = $_POST['servers'];
	$base64_url = base64_encode($url);

	if ($_POST['keepurl'] == "Y")
	{
		setcookie("virtual_host_url",$base64_url);
	}

	$count = 0;
	foreach ($serversArray[$servers] as $host)
	{
		$iframetop = (intval($count/2) * 410) + 80;
		$count += 1;
		if ($count % 2 == 0) { $iframeleft = 730; } else { $iframeleft = 10; }
		echo "<div class=\"iframediv\" style=\"top:$iframetop; left:$iframeleft;\"><iframe src=\"ifmon.php?host=$host&url=$base64_url\"></iframe></div>".PHP_EOL;
	}
}
?>
</div>
</div>
</body>
</html>
