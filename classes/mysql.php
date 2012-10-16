<?php
class traffix_mysql
{

    /**
    * @var string   Default Hostname
    */
    private $host = 'localhost';

    /**
    * @var string   Default Database
    */
    private $db = 'mydatabase';

    /**
    * @var string   Default Username
    */
    private $user = 'username';

    /**
    * @var string   Default Password
    */
    private $pass = 'password';

    /**
    * @var obj      Default PDO Object
    */
    private $PDO;

    /**
    * @var bool     When true, errors will be output
    */
    public $test = false;

    /**
    * Connect to the database, turn on testing mode if specified.
    *
    */
    public function __construct( $test=null ) {
    
        if( $test ) {
            $this->test = $test;
            self::errors('EXCEPTION');
        }
        self::connect();
    }

    /**
    * Close the connection.
    *
    */    
    public function __destruct() {

        $this->PDO = null;
    }

    /**
    * Toggles testing mode.
    *
    * @param bool $test  True for on, false for off.
    *
    */    
    public function testing_mode( bool $test ) {
    
        $this->test = $test;
        self::errors('EXCEPTION');
    }
    
    /**
    * Connects to the database, if no hostname is sent the default login credential class variables are used.
    *
    * @param string $host   Hostname
    * @param string $db     Database Name
    * @param string $user   Username
    * @param string $pass   Password
    *
    * @return bool  True on success, false on failure.
    */
    public function connect( $host=null, $db=null, $user=null, $pass=null ) {

        if( $host === null ) {
            $host   = $this->host;
            $db     = $this->db;
            $user   = $this->user;
            $pass   = $this->pass;
        }
        
        try {
            $this->PDO = new PDO( "mysql:host=$host;dbname=$db", $user, $pass );
            return true;
        }catch( PDOException $e ) {
            if( $this->test )
                echo "[ERROR] mysql class: connect function: ".$e->getMessage()."\n";
            return false;
        }
    }

    /**
    * Queries the database for an INSERT or UPDATE statement
    *
    * @param array  $query          An array containing the details of the INSERT or UPDATE.
    * @param string $query[0]       The SQL statement.
    * @param array  $query[1]       Assoc Array containing the column_name => values.
    * @param mixed  $insert_id      If passed, the id to the last inserted row is returned.
    *
    * @return mixed  The row count unless the last insert id is requested, false on failure.
    */
    public function alter( array &$query, $insert_id=null ) {
    
        try {
            $stmt = $this->PDO->prepare( $query[0] );
            $stmt->execute( $query[1] );
            unset( $query );
            if( $insert_id )
                return $this->PDO->lastInsertId();
            else
                return $stmt->rowCount();
        }catch( PDOException $e ) {
            if( $this->test )
                echo "[ERROR] mysql class: alter function: ".$e->getMessage()."\n";
            return false;
        }
    }

    /**
    * Queries the database for a SELECT statement
    *
    * @param array  $query          An array containing the details of the SELECT.
    * @param string $query[0]       The SQL statement.
    * @param array  $query[1]       Assoc Array containing the column_name => values.
    * @param bool   $one            Specifies to use fetch instead of fetchALL
    *
    * @return mixed  Returns the requested type or false on an error.
    */    
    public function select( array &$query, $one=false ) {

        try {
            $stmt = $this->PDO->prepare( $query[0] );
            $stmt->execute( $query[1] );
            unset( $query ); # If the query is from a loop this prevents only the first loop's SELECT from being used.
            
            if( $one )
                return $stmt->fetch();
            else
                return $stmt->fetchAll();
                
        }catch( PDOException $e ) {
            if( $this->test )
                echo "[ERROR] mysql class: select function: ".$e->getMessage()."\n";
            return false;
        }
    }


    
    /**
    * Gets the ID for the last inserted row.
    *
    * @return mixed  The insert id, false on failure.
    */
    public function last_id() {
    
        try {
            return $this->PDO->lastInsertId();
        }catch( PDOException $e ) {
            if( $this->test )
                echo "[ERROR] mysql class: errors function: ".$e->getMessage()."\n";
            return false;
        }
    }
    
    /**
    * Sets the error display level
    *
    * @param string $level  options are 'WARNING', 'EXCEPTION', or 'SILENT'/null
    *
    * @return bool True on success, false on failure
    */
    public function errors( $level=null ) { 
    
        try {
            switch( $level ) {
                case 'EXCEPTION':   $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );   break;
                case 'WARNING':     $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );     break;
                default:            $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );      break;
            }
            return true;
        }catch( PDOException $e ) {
            if( $this->test )
                echo "[ERROR] mysql class: errors function: ".$e->getMessage()."\n";
            return false;
        }
    }
}
?>