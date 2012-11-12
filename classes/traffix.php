<?php
require_once dirname(__FILE__).'/mysql.php';

class traffix extends traffix_mysql
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
    * @var int       Max wait time in hours until the request headers are recorded again for traffic monitoring
    */
    private $wait_time = 24;
    
    /**
    * Get all the request information
    *
    */    
    public function __construct() {
    
        parent::__construct();

        $this->ip            = $_SERVER['REMOTE_ADDR'];
        $this->rDNS          = gethostbyaddr( $this->ip );
        $this->request_time  = time();
        
        if( function_exists( 'apache_request_headers' ) )
            $this->rHeaders  = apache_request_headers();
            
        elseif( function_exists( 'http_get_request_headers' ) )
            $this->rHeaders  = http_get_request_headers();
        
        else
            $this->rHeaders = self::check_for_known_headers();
        
        $this->user_agent    = $_SERVER['HTTP_USER_AGENT'];
        $this->script        = $_SERVER['PHP_SELF'];
        $this->method        = $_SERVER['REQUEST_METHOD'];
                
        # Flags: Deny traffic missing these request headers when set to true, when the deny function is called.
        $this->deny['Accept']             = false;
        $this->deny['Accept-Encoding']    = false;
        $this->deny['Cache-Control']      = false;
        $this->deny['Connection']         = false;
        $this->deny['Host']               = true;
        $this->deny['Accept-Language']    = true;
        $this->deny['User-Agent']         = true;
    }

    /**
    * Gets headers from the $_SERVER variable if the header functions don't exist
    *
    * @return none
    */    
    private function check_for_known_headers() {

        $headers = array(
        'HTTP_ACCEPT'           => 'Accept',
        'HTTP_ACCEPT_CHARSET'   => 'Accept-Charset',
        'HTTP_ACCEPT_ENCODING'  => 'Accept-Encoding',
        'HTTP_ACCEPT_LANGUAGE'  => 'Accept-Language',
        'HTTP_CONNECTION'       => 'Connection',
        'HTTP_HOST'             => 'Host',
        'HTTP_REFERER'          => 'Referer',
        'HTTP_USER_AGENT'       => 'User-Agent' );
        foreach( $headers as $k=>$v )
            if( isset( $_SESSION[$k] ) )
                $this->rHeaders[$v] = $_SESSION[$k];
    }

    /**
    * Checks to see if this is a new visit. Logs ip and time of request. If this is a new visit, 
    * details of the request are logged for later analysis.
    * 
    * @return bool  True on success.
    */
    public function log() {
    
        try{
            if( !self::good_bot() ) {            
                $count[] = 'select count(1) from traffix_request_log where ip=:ip and request_time<:max_session';
                $count[] = array(
                'ip'            => $this->ip
                'max_session'   => ( $this->request_time - ( $this->max_wait * 60 * 60 ) )
                );
                list($exists)   = parent::select($count,1);

                $traffix_hits = array(
                'table'         => 'traffix_hits',
                'ip'            => $this->ip,
                'time_stamp'    => time() );
                parent::insert( $traffix_hits );

                if(!$exists[0]) {
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
                    parent::insert( $traffix_request_log );
                }
                return true;
            }
        }catch( Exception $e ) {
            return false;
        }
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
            if( $this->rHeaders[$header] == $value )
                return 1;
            else
                return 0;
                
        elseif( isset( $this->rHeaders[$header] ) )
            return 1;

        return 0;
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
        
        if( preg_match( '#\.google\.com$#', $this->rDNS ) )
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

        if( self::good_bot() )
            return false;

        foreach( $this->deny as $header=>$required )
            if( $required && !isset( $this->rHeaders[$header] ) )
                return true;

        if( $this->referer )
            self::assert_referer( $referer );

        if( self::blacklisted() )
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