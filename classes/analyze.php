<?php
require_once dirname(__FILE__).'/mysql.php';

class traffix_analyze extends traffix_mysql
{

    /**
    * constant bool     Determines if the class is allowed to overwrite the .htaccess file
    */ 
    const ALLOW_HTACCESS_TO_BE_OVERWRITTEN = false;
    
    /**
    * constant string   .htaccess file path, requires ending slash
    */
    const HTACCESS_FILE_PATH = '/var/www/yoursite.com/';

    public function __construct() {

        parent::__construct();
    }

    /**
    *   Writes the HTACCESS file using the provided content and an appends a rule blocking all ips marked as bad.
    *
    *   @return bool    True on success
    */
    private function write_htaccess() {
        
        if( !ALLOW_HTACCESS_TO_BE_OVERWRITTEN )
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

    
    
}
?>