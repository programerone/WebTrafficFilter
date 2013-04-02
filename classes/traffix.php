<?php
require_once dirname(__FILE__).'/mysql.php';

class traffix extends mysql
{

  /**
  * @var int   Max wait time in hours until the request headers are recorded again for traffic monitoring
  */
  private $max_wait = 24;

  /**
  * Get all the request information
  *
  * @ var mysql_errors_on bool  Pass true to turn on error reporting.
  *
  */    
  public function __construct( $mysql_errors_on=null, $ip_lock=null ) {
    
    parent::__construct( $mysql_errors_on, $ip_lock );

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

    $this->max_session_length = ($this->request_time - 3600 * $this->max_wait);
                
    # Flags: Deny traffic missing these request headers when set to true, when the deny function is called.
  	if( !self::good_bot() ) {
      $this->deny['Accept']             = false;
      $this->deny['Accept-Encoding']    = false;
      $this->deny['Cache-Control']      = false;
      $this->deny['Connection']         = false;
      $this->deny['Host']               = false;
      $this->deny['Accept-Language']    = false;
      $this->deny['User-Agent']         = false;
    }
  }

  /**
  * Notes in the database that the css file was downloaded and the time it was accessed.
  *
  * @var string  The file path to the css file.
  *
  * @return none
  */
  public function monitor_css_file( $css_file_path ) {

    try {
      $query = array(
        'table'         => 'traffix_css_file_hits',
        'ip'            => $this->ip,
        'time_stamp'    => time() );
      parent::insert( $query );

     header('Content-Type: text/css');
      echo file_get_contents( $css_file_path );

    } catch( Exception $e ) {
      return 0;
    }
  }

  /**
  * Notes in the database that the image file was downloaded and the time it was accessed.
  *
  * @var string  The file path to the image.
  * @var string  The type of the image. ( jpeg, png, or bmp )
  *
  * @return none
  */
  public function monitor_image_file( $image_file_path, $type ) {

    try {

      $query = array(
        'table'       => 'traffix_img_file_hits',
        'ip'          => $this->ip,
        'time_stamp'  => time() );
      parent::insert( $query );

      $headers['png'] = 'image/png';
      $headers['jpg'] = 'image/jpeg';
      $headers['bmp'] = 'image/bmp';

      if( !isset($headers[$type]) )
        return 0;

      header('Content-Type: '.$headers[$type]);
      echo file_get_contents( $image_file_path );

    } catch( Exception $e ) {
      return 0;
    }
  }

  /**
  * Notes in the database that the js file was downloaded and the time it was accessed.
  *
  * @var string  The file path to the js file.
  *
  * @return none
  */
  public function monitor_js_file( $js_file_path ) {

    try {
      $query = array(
        'table'         => 'traffix_js_file_hits',
        'ip'            => $this->ip,
        'time_stamp'    => time() );
      parent::insert( $query );

      header('Content-Type: text/javascript; charset=utf-8');
      echo file_get_contents( $js_file_path );

    } catch( Exception $e ) {
      return 0;
    }
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

      $max_session_length = ($this->request_time - 3600 * $this->max_wait);

      if( !$this->good_bot() ) {
        $count[] = 'select id from traffix_request_log where ip=:ip and request_time>:max_session order by id desc limit 1';
        $count[] = array(
          'ip'            => $this->ip,
          'max_session'   => $this->max_session_length 
        );
        list($last_request)   = parent::select($count,1);

        if( !$last_request ) {
          // log the request details
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
        } else {
          // update page request speed and page access count
          $update[] = 'update traffix_request_log set page_count=page_count+1, total_page_access_time=('.$this->request_time.' - request_time), pages_per_minute=(page_count/total_page_access_time)*60 where id=:id';
          $update[] = array('id'=>$last_request);
          parent::alter( $update );
        }
        return true;
      }

    }catch( Exception $e ) {
      return false;
    }
  }

  /**
  * Gets the speed at which this user is accessing pages on the site
  *
  * @return int
  */
  public function pages_per_minute()
  {
    $select[] = 'select pages_per_minute from traffix_request_log where ip=:ip and request_time>:max_session order by id desc limit 1';
    $select[] = array(
      'ip'            => $this->ip,
      'max_session'   => $this->max_session_length
    );
    list($pages_per_minute) = parent::select($select,1);

    return $pages_per_minute;
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

    if( $value !== null ) {
      if( $this->rHeaders[$header] == $value )
         return 1;
      else
         return 0;

    }elseif( isset( $this->rHeaders[$header] ) )
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
    
    if( preg_match( '#\.googlebot\.com$#', $this->rDNS ) ||
      preg_match( '#\.search\.msn\.com$#', $this->rDNS ) ||
      preg_match( '#\.search\.live\.com$#', $this->rDNS ) ||
      preg_match( '#\.crawl\.yahoo\.com$#', $this->rDNS ) ||
      preg_match( '#\.google\.com$#', $this->rDNS ) ) {
        return true;
    }
            
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
        self::banned();

    if( $referer && !self::assert_referer( $referer ) ) 
      self::banned();

    if( self::blacklisted() )
      self::banned();

    return false;
  }

  /**
  * Checks for known characteristics of the browser in the User Agent
  *
  * @return bool  True if matches expected pattern
  */
  public function check_browser_pattern() {

    try {

      $headers = array_keys( $this->rHeaders );
      if( preg_match("/ Firefox/i", $this->user_agent) != false &&
          ( $headers[0] != 'Host' ||
            $headers[1] != 'User-Agent' ||
            $headers[2] != 'Accept' ) ) {
        return FALSE;

      }elseif( preg_match("/ Chrome/i", $this->user_agent) != false &&
          ( $headers[0] != 'Host' ||
            $headers[1] != 'Connection' ||
            ( $headers[2] != 'Cache-Control' && $headers[2] != 'Accept' ) ) ) {
        return FALSE;
      
      }elseif( preg_match("/ MSIE/", $this->user_agent) != false && $headers[0] != 'Accept' ) {
        return FALSE;
      }

      return TRUE;

    } catch( Exception $e ) {
      die(__METHOD__);
    }
  }


  /**
  * Checks User-Agent header against a blacklist
  *
  * @return bool  Returns true if blacklisted
  */
  private function blacklisted() {
    # !! This can also be done with htaccess, which would be more efficient, but if you don't have access this is an option.

  	if( CHECK_BANNED_USER_AGENTS ) {
	    $sql[] = 'select count(1) from traffix_banned_user_agents where user_agent=:user_agent';
	    $sql[] = array('user_agent'=>$this->user_agent);
	    list($banned) = parent::select($sql,true);
	    if( $banned )
    		self::banned();
	  }

	  if( CHECK_BANNED_IPS ) {
	    $sql[] = 'select count(1) from traffix_banned_ips where ip=:ip';
	    $sql[] = array( 'ip' => $this->ip );
	    list($banned) = parent::select($sql,true);
	    if( $banned )
	        self::banned();
	  }
    return false;
  }

  /**
  * Sends the user to a banned page
  *
  * @return none
  */
  private function banned() {
	  die( header( 'Location: '.BANNED_PAGE ) );
  }
}
?>
