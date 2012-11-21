<?
require_once dirname(dirname(__FILE__)).'/classes/analyze.php';
$a = new traffix_analyze;
$a->test = true;

switch( $_GET['type'] ) {

    case 'ip':
	$sql[] = 'select ip from traffix_request_log where id=:id';
	$sql[] = array('id'=>$_GET['id']);
	list($ip) = $a->select($sql,true);

	$sql[] = 'select count(1) from traffix_banned_ips where ip=:ip';
	$sql[] = array('ip'=>$ip);
	list($exists) = $a->select($sql,true);

	if(!$exists) {
	    $sql[] = 'insert into traffix_banned_ips (ip) values (:ip)';
	    $sql[] = array('ip'=>$ip);
	    $a->alter($sql);
	}
	$a->redir('index.php');
	break;

    case 'ua':
        $sql[] = 'select user_agent from traffix_request_log where id=:id';
        $sql[] = array('id'=>$_GET['id']);
        list($ua) = $a->select($sql,true);

        $sql[] = 'select count(1) from traffix_banned_user_agents where user_agent=:user_agent';
        $sql[] = array('user_agent'=>$ua);
        list($exists) = $a->select($sql,true);

        if(!$exists) {
            $sql[] = 'insert into traffix_banned_user_agents (user_agent) values (:user_agent)';
            $sql[] = array('user_agent'=>$ua);
            $a->alter($sql);
        }
	$a->redir('index.php');
        break;

    case 'unip':
	$sql[] = 'delete from traffix_banned_ips where ip=:ip';
	$sql[] = array('ip'=>$_GET['ip']);
	$a->alter($sql);
	$a->redir('ip_bans.php');
	break;

    case 'unua':
	$sql[] = 'delete from traffix_banned_user_agents where user_agent=:user_agent';
	$sql[] = array('user_agent'=>$_GET['ua']);
	$a->alter($sql);
	$a->redir('user_agent_bans.php');
	break;	
}
?>
