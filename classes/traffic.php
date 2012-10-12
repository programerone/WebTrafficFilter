<?
namespace traffix;

class traffic
{
	/**
	* @var string  	If set this string will be required to be in the referer header.
	*/
	public $referer = null;

	/**
	* @var bool  	If true the deny function will check the useragent against a blacklist.
	*/
	private $blacklisted_user_agent = true;
	
	/**
	* @var bool  	If true the deny function will check the ip against a blacklist.
	*/
	private $blacklisted_ip = false;
	
	public function __construct() {

		$this->ip 			= getenv('REMOTE_ADDR');
		$this->rDNS 		= gethostbyname( $this->ip );
		$this->request_time = microtime();
		$this->rHeaders		= http_get_request_headers();
		$this->user_agent	= $_SERVER['HTTP_USER_AGENT'];
		$this->script		= $_SERVER['PHP_SELF'];
		$this->method		= $_SERVER['REQUEST_METHOD'];
				
		# Flags: Deny traffic missing these request headers when set to true.
		$this->deny['Host'] 			= true;
		$this->deny['Accept'] 			= true;
		$this->deny['Accept-Encoding'] 	= true;
		$this->deny['Accept-Language'] 	= true;
		$this->deny['Cache-Control'] 	= true;
		$this->deny['Connection'] 		= true;
		$this->deny['User-Agent'] 		= true;
	}
	
	/**
	* Returns traffic info.
	*
	* @return array  Array of collected information.
	*/	
	public function info() {
		return array(
		'user_agent'		=> $this->user_agent,
		'request_headers'	=> $this->rHeaders,
		'request_time'		=> $this->request_time,
		'rDNS'				=> $this->rDNS,
		'ip'				=> $this->ip );
	}
	
	/**
	* Uses reverse DNS to see if this is white-listed traffic.
	*
	* @return bool  Returns true for good bots.
	*/
	private function good_bot() {
	
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

		if( $referer !== null )
			$this->referer = $referer;
			
		foreach( $this->deny as $header=>$required )
			if( $required && !isset($this->rHeaders[$header]) )
				return true;

		if( $this->referer )
			if( strpos( $_SERVER['HTTP_REFERER'], $this->referer ) === false )
				return true;

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

	# Add a proxy checking function, add this to the IP blacklist..
	
}






















?>