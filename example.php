<?php
require_once dirname(__FILE__).'/classes/traffix.php';
$traffix = new traffix;
$DOMAIN = $_SERVER['HTTP_HOST'];
?>
<html lang="en">
  <head>
    <title>Traffix Quick Example</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/style.php">
    <script type="text/javascript" src="js/javascript.php"></script>
  </head>
  <body>
<?php

echo "<b>Request</b>:<br>";
echo "IP Address: ".$traffix->ip;
echo "<br>Reverse DNS: ".$traffix->rDNS;
echo "<br>Made at: ".date('r',$traffix->request_time);
echo "<br>Pages Per Minute Access Speed: ".$traffix->pages_per_minute();
echo "<br><br>";


// Log the traffic, a new entry for each visitor is made daily.
$traffix->log();


// Trafic Headers.
echo "<b>Request Headers:</b><br>";
$info = $traffix->info();
foreach( $info['request_headers'] as $header=>$value )
  echo "[$header] = $value<br>";


// Assert a refer, for example if your processing a form submission
// and want to check if the form is comming from your site.
echo "<br><b>Assert Referrer:</b><br>";
if( $traffix->assert_referer($DOMAIN) ) 
  echo "<span style='color:green;font-weight:bold;'>Traffic has a referrer matching $DOMAIN.</span><br>";
else
  echo "<span style='color:red;font-weight:bold;'>Traffic refferer does not match $DOMAIN</span><br>";


// Assert a request method, for example if your form processes via POST you can check
// to make sure the request matches
echo "<br><b>Assert Request Method: POST</b><br>";
if( $traffix->assert_request_method("POST") ) 
  echo "<span style='color:green;font-weight:bold;'>Request made with POST</span><br>";
else
  echo "<span style='color:red;font-weight:bold;'>Request made with GET</span><br>";
?>
<br>
<a href="<?=$_SERVER['PATH_INFO']?>">Visit this page with a local link.</a>
<br>
<script type="text/javascript">
  did_this_page_load();
</script>

  </body>
</html>
