<?
require_once dirname(dirname(__FILE__)).'/classes/analyze.php';
$a = new traffix_analyze;

$sql = array('select * from traffix_banned_ips');
$banned = $a->select($sql); # change to suspicious SQL after panel is made
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Traffix - Control Panel - IP Bans</title>
    <meta charset="utf-8">
    <script src="js/functions.js"></script>    
    <link rel="stylesheet" href="css/traffix.css">
</head>

<body>
    <div id="content">

	<div id="details"></div>

	<?include 'nav.html'?>

        <div class="info_box">
            <span class="info_box_header">Ip Bans</span>
            <span class="info small_cell">IP Address</span>
	    <span class="info_small_cell">Action</span>
	    <br><br>
            <?
	    foreach( $banned as $ip ) {
                echo "\n<span class='info small_cell'>$ip[ip]</span>\n";
                echo "<span class='info small_cell'><a href='ban.php?type=unip&ip=$ip[ip]'>Remove Block</a></span><br><br>\n";
            }
            ?>
        </div>

    </div>
</body>

</html>
