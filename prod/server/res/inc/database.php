<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        database.php
// Dir:         /res/inc/database.php
// Desc:        define a database class to be instantiated in other files and a
//              method to establish the db conn
//-----------------------------------------------------------------------------
*/
class Database{
    //set login creds to MySQL
    private $host='localhost';
    private $db_name='BiblianDB_S';
    private $username='linne';
    private $password='admin';
    public $dbConn;
    
    public function getConn(){
        $this->dbConn = null;
        
        try{
            $this->dbConn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
        }
        catch(PDOException $exception){
            echo 'Connection Error: ' . $exception->getMessage();
        }
        return $this->dbConn;
    }
}

//OLDER CODE BELOW - DELETE LATER
$databaseConnection = null;

function establishDatabaseConnection() {
	global $databaseConnection;

	$hostname = "localhost";
	$db_name = "BiblianDB";
	$username = "linne";
	$password = "admin";

	if(!$databaseConnection) {
		$databaseConnection = mysqli_connect($hostname, $username, $password, $db_name);
		if(!$databaseConnection) return null;
		if($databaseConnection->error) return null;
	}
	return $databaseConnection;
}

/* usage:

$conn = establishDatabaseConnection();
$stmt = $conn->prepare("===SQL-QUERY-HERE===");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
	
*/
?>