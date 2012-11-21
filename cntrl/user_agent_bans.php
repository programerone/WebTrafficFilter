<?
require_once dirname(dirname(__FILE__)).'/classes/analyze.php';
$a = new traffix_analyze;

$sql = array('select * from traffix_banned_user_agents');
$banned = $a->select($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Traffix - Control Panel - User Agent Bans</title>
    <meta charset="utf-8">
    <script src="js/functions.js"></script>    
    <link rel="stylesheet" href="css/traffix.css">
</head>

<body>
    <div id="content">

	<div id="details"></div>

	<?include 'nav.html'?>

        <div class="info_box">
            <span class="info_box_header">User Agent Bans</span>
            <span class="info xlarge_cell">User Agent</span>
	    <span class="info small_cell">Action</span>
	    <br><br>
            <?
	    foreach( $banned as $user_agent ) {
                echo "\n<span class='info xlarge_cell'>$user_agent[user_agent]</span>\n";
                echo "<span class='info small_cell'><a href='ban.php?type=unua&ua=$user_agent[user_agent]'>Remove Block</a></span><br><br>\n";
            }
            ?>
        </div>

    </div>
</body>

</html>
