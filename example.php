<?
require_once dirname(__FILE__).'/classes/traffic.php';
$tr = new traffic;

if( $tr->deny() )
	die( header( 'Location: page_where_bots_go_to_die' ) );

$request = $tr->analyze();


?>