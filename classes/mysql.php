<?php

require_once dirname( dirname( __FILE__ ) ).'/traffix_config.php';

class mysql
{

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
    
    self::connect();

    if( $test ) {
      $this->test = $test;
      self::errors('EXCEPTION');
    }
  }

  /**
  * Close the connection.
  *
  */    
  public function __destruct() {

    $this->PDO = null;
  }


  /**
  * Holds commonly used queries to help make more readable database calls.
  *
  * @param string $query_name  The name of the query
  *
  * @return string  Query
  */
  public function named_queries( $query_name ) {

    try {
      // example
      $queries['get employee address by name'] = 'SELECT address FROM employees_table WHERE first_name=:first_name AND last_name=:last_name LIMIT 1';

      if( isset( $queries[$query_name] ) )
        return $queries[$query_name];

      else
        return $query_name;

    } catch( Exception $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e );

      return $query_name;
    }

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
      $host   = MYSQL_HOST;
      $db     = MYSQL_DB;
      $user   = MYSQL_USER;
      $pass   = MYSQL_PASS;
    }
        
    try {
      $this->PDO = new PDO( "mysql:host=$host;dbname=$db", $user, $pass );
      return true;

    }catch( PDOException $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e->getMessage() );

      return false;
    }
  }

  /**
  * Queries the database for an INSERT or UPDATE statement
  *
  * @param array  $query          An array containing the details of the INSERT or UPDATE.
  * @param string $query[0]       The SQL statement, or the named query.
  * @param array  $query[1]       Assoc Array containing the column_name => values.
  * @param mixed  $insert_id      If passed, the id to the last inserted row is returned.
  *
  * @return mixed  The row count unless the last insert id is requested, false on failure.
  */
  public function alter( array &$query, $insert_id=FALSE ) {
    
    try {
      $stmt = $this->PDO->prepare( self::named_queries( $query[0] ) );
      foreach( $query[1] as $k=>$v )
        $stmt->bindValue(":$k",$v);

      $stmt->execute();
      $query = NULL;
      if( $insert_id )
        return $this->PDO->lastInsertId();
      else
        return $stmt->rowCount();

    }catch( PDOException $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e->getMessage() );

      return false;
    }
  }

  /**
  * Queries the database for a SELECT statement
  *
  * @param array  $query          An array containing the details of the SELECT.
  * @param string $query[0]       The SQL statement, or the named query.
  * @param array  $query[1]       Assoc Array containing the column_name => values.
  * @param bool   $one            Specifies to use fetch instead of fetchALL
  *
  * @return mixed  Returns the requested type or false on an error.
  */    
  public function select( array &$query, $one=FALSE ) {

    try {

      $stmt = $this->PDO->prepare( self::named_queries( $query[0] ) );
      $stmt->execute( $query[1] );
      $query = NULL; # If the query is from a loop this prevents only the first loop's SELECT from being used.
            
      if( $one )
        return $stmt->fetch();
      else
        return $stmt->fetchAll();
               
    }catch( PDOException $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e->getMessage() );    
        return false;
    }
  }

  /**
  * Queries the database for an INSERT without needing the SQL statement
  *
  * @param string $query[table]   Table name, should be first element.
  * @param array  $query          Assoc Array containing the column_name => values.
  * @param mixed  $insert_id      If passed, the id to the last inserted row is returned.
  *
  * @return mixed  The row count unless the last insert id is requested, false on failure.
  */
  public function insert( array &$query, $insert_id=FALSE ) {

    try {
      $q = "insert into $query[table]";
      unset($query['table']);

      foreach( $query as $k=>$v ) {
        $columns .= "$k,";
        $values  .= ":$k,";
      }
      $columns = trim($columns,',');
      $values  = trim($values,',');

      $q = "$q ($columns) values ($values)";

      $stmt = $this->PDO->prepare( $q );
      $stmt->execute( $query );
      $query = NULL;

      if( $insert_id )
        return $this->PDO->lastInsertId();
      else
        return $stmt->rowCount();

    }catch( PDOException $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e->getMessage() );

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
        self::print_error( __METHOD__, $e->getMessage() );

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
        case 'EXCEPTION':   $this->PDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );   break;
        case 'WARNING':     $this->PDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );     break;
        default:            $this->PDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );      break;
      }
      return true;

    }catch( PDOException $e ) {

      if( $this->test )
        self::print_error( __METHOD__, $e->getMessage() );

      return false;
    }
  }

  /**
  * Prints error message
  *
  * @param string $method  Function name
  * @param string $error   Error message
  *
  */
  private function print_error( $method, $error ) {
    try {
      echo "[ERROR] $method: $error\n";
    } catch( Exception $e ) { die("Unable to print error: $e"); }
  }

}
?>
