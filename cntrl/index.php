<?
require_once dirname(dirname(__FILE__)).'/classes/analyze.php';
$a = new analyze;

$suspicious = $a->select(array('select * from traffix_request_log where analyzed=0')); # change to suspicious SQL after panel is made
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Traffix - Control Panel</title>
    <meta charset="utf-8">
    <script src="js/functions.js"></script>    
    <link rel="stylesheet" href="css/traffix.css">
</head>

<body>
    <div id="content">

        <div id="nav_bar">
            <div class="nav_option">Home</div>
            <div class="nav_option">Options</div>
            <div class="nav_option">Blocked IPs</div>
            <div class="nav_option">[+/-] Users</div>
        </div>

        <div class="info_box">
            <span class="info_box_header">Suspicious Traffic</span>
            <span class="info small_cell">Initial Request</span>
            <span class="info small_cell">IP Address</span>
            <span class="info small_cell">Reverse DNS</span>
            <span class="info large_cell">User Agent</span>
            <?
            foreach( $suspicious as $request ) {
                echo "\n<span class='info small_cell'>$request[time_stamp]</span>\n";
                echo "<span class='info small_cell'>$request[ip]</span>\n";
                echo "<span class='info small_cell'>$request[rDNS]</span>\n";
                echo "<span class='info small_cell'>$request[user_agent]</span>\n";
            }
            ?>
        </div>

    </div>
</body>

</html>