<?php
require_once dirname(__FILE__).'/mysql.php';

class traffix_analyze extends traffix_mysql
{

    public function __construct() {

        parent::__construct();
    }

    /**
    *   Writes the HTACCESS file using the provided content and an appends a rule blocking all ips marked as bad.
    *
    *   @return bool    True on success
    */
    private function write_htaccess() {
        
        if( !ALLOW_HTACCESS_OVERWRITE )
            return false;
            
        try{    
            list($content) = parent::select(array('select content from traffix_htaccess order by id desc limit 1'));
        
            $banned_ips = parent::select(array('select ip from traffix_analysis where banned=1 order by id asc'));
        
            if( count($banned_ips) ) {
                $htaccess = "\norder allow,deny\n";
                foreach($banned_ips as $ip)
                        $htaccess.= "deny from $ip\n";
                $htaccess.= "allow from all\n";
            }
            file_put_contents( HTACCESS_FILE_PATH.'.htaccess', $content.$htaccess );
            return true;
        }catch( Exception $e ) {
            return false;
        }
    }

    /**
    *   Checks the request for signs that this user may not be legitimate
    *
    *   @var array      The entry for this ip in the request log table
    *
    *   @return array   Warning messages.
    */
    public function warnings( $request ) {

	$reqh = json_decode($request['request_headers'],true);

	if( !$reqh['User-Agent'] )
	    $warnings[] = "MISSING HEADER: User Agent";

	if( !$reqh['Host'] )
	    $warnings[] = "MISSING HEADER: Host";

	if( !$reqh['Accept'] )
	    $warnings[] = "MISSING HEADER: Accept";

	if( !$reqh['Accept-Language'] )
	    $warnings[] = "MISSING HEADER: Accept Language";

	if( !$reqh['Accept-Encoding'] )
	    $warnings[] = "MISSING HEADER: Accept Encoding";

	if( !$reqh['Connection'] )
	    $warnings[] = "MISSING HEADER: Connection";

	if( !$reqh['Cache-Control'] )
	    $warnings[] = "MISSING HEADER: Cache Control";

	if( IMG_DOWNLOAD_CHECK ) {
	    $sql[] = 'select count(1) from traffix_img_file_hits where ip=:ip';
            $sql[] = array( 'ip' => $request['ip'] );
            list($img_file) = parent::select($sql,true);
	    if( !$img_file )
		$warnings[] = "VERIFICATION IMG FILE NOT DOWNLOADED";
	}

	if( CSS_DOWNLOAD_CHECK ) {
	    $sql[] = 'select count(1) from traffix_css_file_hits where ip=:ip';
	    $sql[] = array( 'ip' => $request['ip'] );
	    list($css_file) = parent::select($sql,true);
	    if( !$css_file )
		$warnings[] = "CSS FILE NOT DOWNLOADED";
	}

	if( JS_DOWNLOAD_CHECK ) {
	    $sql[] = 'select count(1) from traffix_js_file_hits where ip=:ip';
            $sql[] = array( 'ip' => $request['ip'] );
            list($js_file) = parent::select($sql,true);
	    if( !$js_file ) 
                $warnings[] = "JS FILE NOT DOWNLOADED";
	}

	if( !$warnings )
		$warnings[] = "No warnings or deviations.";

	return $warnings;		
    }
    
}
?>
