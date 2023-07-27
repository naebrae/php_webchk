<?php

function check($host, $port, $vhost, $path)
{
    $fp = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$fp)
    {
        echo "Could not connect to $host<br>\n";
        echo "$errstr ($errno)<br>\n";
    }
    else
    {
        $header = "GET $path HTTP/1.1\r\n";
        $header .= "Host: $vhost\r\n";
        $header .= "Connection: close\r\n\r\n";
        fputs($fp, $header);

        $header = "";
        $inheader = true;
        $chunked = false;

        while (!feof($fp) && $inheader)
        {
            $inline = fgets($fp, 1024);
            $header .= $inline."<br>";
            if (trim($inline) == "") { $inheader = false; }

            if (stripos($inline, 'Content-Length:') !== false)
            {
                $tmpstr = explode(": ", $inline);
                $blockcount = intval($tmpstr[1], 10);
                $chunked = false;
            }

            if (stripos($inline, 'Transfer-Encoding:') !== false)
            {
                $blockcount = -1;
                $chunked = true;
            }
        }

        if ($blockcount == -1)
        {
            $inline = fgets($fp, 1024);
            $blockcount = intval($inline, 16);
        }

        if ($blockcount == 0)
        {
            print $header;
        }

        $body = "";
        $bytesread = 1;

        while (!feof($fp) && ($blockcount > 0) && ($bytesread > 0))
        {
            $buffer = "";
            $readbytes = $blockcount;

            while ($bytesread < $blockcount)
            {
                $readbytes = $blockcount - $bytesread;
                $buffer .= fread($fp, $readbytes);
                $bytesread = strlen($buffer);
            }

            $body .= $buffer;

            if ($chunked)
            {
                $inline = fgets($fp, 1024);
                $inline = fgets($fp, 1024);
                $blockcount = intval($inline, 16);
                $bytesread = 1;
            }
        }

        fclose($fp);

        $doc = new DOMDocument();
        $doc->loadHTML($body);
        $links = $doc->getElementsByTagName('link');
        foreach ($links as $link) {
            if (strpos($link->attributes->getNamedItem('href')->nodeValue, '//') === false) {
                $link->attributes->getNamedItem('href')->nodeValue = "//".$vhost.$path.$link->attributes->getNamedItem('href')->nodeValue;
            }
        }
        $imgs = $doc->getElementsByTagName('img');
        foreach ($imgs as $img) {
            if (strpos($img->attributes->getNamedItem('src')->nodeValue, '//') === false) {
                $img->attributes->getNamedItem('src')->nodeValue = "//".$vhost.$path.$img->attributes->getNamedItem('src')->nodeValue;
            }
        }
        $body = $doc->saveHTML();

        print $body;
    }
}

if (isset($_GET['host'])) { $host = $_GET['host']; } else { $host = ""; }
if (isset($_GET['url'])) { $url = $_GET['url']; } else { $url = ""; }
$url = base64_decode($url);

if (strtolower(parse_url($url, PHP_URL_SCHEME)) == "https")
{
    $host = "ssl://".$host; $port = 443;
}
else
{
    $port = 80;
}

if (parse_url($url, PHP_URL_PORT) != "")
{
    $port = parse_url($url, PHP_URL_PORT);
}

$vhost = parse_url($url, PHP_URL_HOST);
if (parse_url($url, PHP_URL_PORT) != "")
{
    $vhost .= ":".parse_url($url, PHP_URL_PORT);
}

$path = parse_url($url, PHP_URL_PATH);
if ($path == "") { $path = "/"; }
if (parse_url($url, PHP_URL_QUERY) != "")
{
    $path .= "?".parse_url($url, PHP_URL_QUERY);
}

if ($host !== "")
{
    check($host, $port, $vhost, $path);
}
else
{
    print "<br>No host specified!<br>\n";
}

?>
