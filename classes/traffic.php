<?php
require_once dirname(__FILE__).'/mysql.php';

class traffic extends mysql
{

    /**
    * @var mixed    If set this string will be required to be in the referer header.
    */
    public $referer = null;

    /**
    * @var bool      If true the deny function will check the useragent against a blacklist.
    */
    private $blacklisted_user_agent = true;
    
    /**
    * @var bool      If true the deny function will check the ip against a blacklist.
    */
    private $blacklisted_ip = false;

    /**
    * Get all the request information
    *
    */    
    public function __construct() {
    
        parent::__construct();

        $this->ip            = getenv('REMOTE_ADDR');
        $this->rDNS          = gethostbyname( $this->ip );
        $this->request_time  = time();
        $this->rHeaders      = apache_request_headers();
        $this->user_agent    = $_SERVER['HTTP_USER_AGENT'];
        $this->script        = $_SERVER['PHP_SELF'];
        $this->method        = $_SERVER['REQUEST_METHOD'];
                
        # Flags: Deny traffic missing these request headers when set to true.
        $this->deny['Host']               = true;
        $this->deny['Accept']             = false;
        $this->deny['Accept-Encoding']    = false;
        $this->deny['Accept-Language']    = true;
        $this->deny['Cache-Control']      = false;
        $this->deny['Connection']         = false;
        $this->deny['User-Agent']         = true;
    }    
   
    /**
    * Returns traffic info.
    *
    * @return array  Array of collected information.
    */    
    public function info() {
        return array(
        'user_agent'        => $this->user_agent,
        'request_headers'   => $this->rHeaders,
        'request_time'      => $this->request_time,
        'rDNS'              => $this->rDNS,
        'ip'                => $this->ip,
        'method'            => $this->method,
        'script'            => $this->script );
    }

    /**
    * Checks to see if this is a new visit. Logs ip and time of request. If this is a new visit, 
    * details of the request are logged for later analysis.
    * 
    * @return bool  True on success.
    */
    public function log() {
    
        try{
            $traffix_hits[] = 'select count(1) from traffix_hits where ip=:ip';
            $traffix_hits[] = array('ip' => $this->ip);
            list($exists)   = parent::select($traffix_hits);

            $traffix_hits[] = 'insert into traffix_hits (ip,time_stamp) values (:ip,:time_stamp)';
            $traffix_hits[] = array(
            'ip'            => $this->ip,
            'time_stamp'    => time() );
            parent::alter($traffix_hits);

            if(!$exists) {
                $traffix_request_log = array(
                'table'             => 'traffix_request_log',
                'ip'                => $this->ip,
                'rDNS'              => $this->rDNS,
                'request_time'      => time(),
                'user_agent'        => $this->user_agent,
                'script'            => $this->script,
                'request_headers'   => json_encode( $this->rHeaders ),
                'method'            => $this->method,
                'rh_host'           => $this->assert_request_header( 'Host' ),
                'rh_accept'         => $this->assert_request_header( 'Accept' ),
                'rh_accept_encoding'=> $this->assert_request_header( 'Accept-Encoding' ),
                'rh_accept_language'=> $this->assert_request_header( 'Accept-Language' ),
                'rh_cache_control'  => $this->assert_request_header( 'Cache-Control' ),
                'rh_connection'     => $this->assert_request_header( 'Connection' ),
                'rh_user_agent'     => $this->assert_request_header( 'User-Agent' ) );
                parent::insert($traffix_hits);
        }
        }catch( Exception $e ) {
            return false;
        }
    }

    /**
    * Check if request method is the expected type
    *
    * @param string $request_method  'GET', 'POST', or expected method.
    *
    * @return bool  true if matches
    */    
    public function assert_request_method( $request_method ) {
    
        if( $request_method == $this->method )
            return true;
        return false;   
    }
    
    /**
    * Check if the header exists and if it matches the value, if passed.
    *
    * @param string $header  The request header to check for.
    * @param string $value  The value to require for the header, if passed.
    *
    * @return bool  true if matches
    */ 
    public function assert_request_header( $header, $value=null ) {

        if( $value !== null )
            if( $rHeaders[$header] == $value )
                return true;
                
        elseif( isset($rHeader[$header]) )
            return true;
            
        return false;
    } 
 
    /**
    * Check if referer matches expected
    *
    * @param string $referer  The substring to look for in referer header, defaults to preset.
    *
    * @return bool  true if matches
    */  
    public function assert_referer( $referer=null ) {
    
        if( $referer !== null )
            $this->referer = $referer;
        
        if( strpos( $_SERVER['HTTP_REFERER'], $this->referer ) !== false )
            return true;

        return false;
    }
    
    /**
    * Uses reverse DNS to see if this is white-listed traffic.
    *
    * @return bool  Returns true for good bots.
    */
    public function good_bot() {
    
        if( preg_match( '#\.googlebot\.com$#', $this->rDNS ) )
            return true;
            
        if( preg_match( '#\.search\.msn\.com$#', $this->rDNS ) )
            return true;
            
        if( preg_match( '#\.search\.live\.com$#', $this->rDNS ) )
            return true;
            
        if( preg_match( '#\.crawl\.yahoo\.com$#', $this->rDNS ) )
            return true;
            
        return false;
    }

    /**
    * Check for flags to deny traffic.
    *
    * @param string $referer  Required referer can be set otherwise defaults to any preset referer.
    *
    * @return bool  Returns true to deny traffic.
    */
    public function deny( $referer=null ) {

        if( $this->good_bot() )
            return false;

        foreach( $this->deny as $header=>$required )
            if( $required && !isset($this->rHeaders[$header]) )
                return true;

        if( $this->referer )
            $this->assert_referer( $referer );

        if( $this->blacklisted() )
            return true;

        return false;
    }

    /**
    * Checks User-Agent header against a blacklist
    *
    * @return bool  Returns true if blacklisted
    */
    private function blacklisted() {
        # Get your blacklist from your db or modify the array, this is an example array (Baidu doesn't follow my robots.txt).
        # This can also be done with htaccess, which would be more efficient, but if you don't have access this is an option.

        $user_agents = array(
        'Yandex',
        'Baiduspider' );

        $ips = array(
        'ban.ned.ip.add.r' );

        if( $this->blacklisted_user_agent ) {
            foreach( $user_agents as $banned )
                if( strpos( $this->user_agent, $banned ) !== false )
                    return true;
        }

        if( $this->blacklisted_ip ) {
            foreach( $ips as $banned )
                if( preg_match( "#$banned#", $this->ip ) )
                    return true;
        }

        return false;
    }
    
}
?>