<?php
//     generates the XML file and sends it to the client computer
header('Content-Type: text/xml');    
include_once './res/inc/core.php';
include_once './res/inc/database.php';
//    include_once './objects/books.php';
include_once './res/inc/helperFunctions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConn();

$book = new Book($db);

//-----------------------------------------------------------------------------
// BOOK OBJECT COPY
//-----------------------------------------------------------------------------
class Book{
    //DB conn
    private $dbConn;
    private $tableName='Books';
    
    //Object Props
    public $ID;
    public $Title;
    public $ISBN;
    public $PageCount;
    public $Comments;
    public $ListPrice;
    public $Progress;
    public $Rating;
    public $DateAcquired;
    public $DateStarted;
    public $DateFinished;
    public $Reading;        // true/false value to create the reading list
    public $SubjectName;
    public $SubjectCount;   // Not a displayed value, pulled from $this->readAll()
    public $subjectIDs;     // a string of comma-separated Subject IDs used in the readOne() method
                            // used for sending the pre-existing subject IDs to the UPDATE page
    
    //Constructor
    public function __construct($db){
        $this->dbConn = $db;
    }
    
    function updateReading($id, $reading){
        if($id){
            //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
            $query = "UPDATE " . $this->tableName . " 
            SET 
                Reading=:Reading
            WHERE 
                ID=:ID";

            $final = $this->dbConn->prepare($query);

            // NOTE - we're not altering a Book ID and there's no need to assign the ID to $book->ID.
            // Utilizing the parameter passed through the browser ($id) is sufficient.
            $this->ID = $id;  
            $this->Reading = $reading;

            // Again, its not necessary to use $this->ID.  The browser parameter is fine
            $final->bindParam(':ID', $id);
            $final->bindParam(':Reading', $this->Reading);

            if($final->execute()){
            //if($final->execute($parameters)){
                return true;
            }
            $final->debugDumpParams();
        }
        return false;
    }
    
    function getUpdatedReading($id){
        $query = "SELECT Reading FROM " . $this->tableName . " 
        WHERE 
            ID=:ID";
        
        $final = $this->dbConn->prepare($query);
        
        $final->bindParam(':ID', $id);
        $final->execute();
        
        $row = $final->fetch(PDO::FETCH_ASSOC);
        $this->Reading = $row['Reading'];
        
        if(empty($row['Reading'])){
            return 0;
        } 
        
        return $row['Reading'];    
    }
}// END Book CLASS

// tell it we're generating XML content.  Whenever we generate an XML file with PHP, we need to tell the network what sort of data it is
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';    
// missing the ? at the end of this line, right before the last angle bracket can cause the XMLresponse to not be readable
// it will come in as always NULL.  Be sure to include the ? before the >.


if($_GET['mode'] === 'reading'){
    $reading = (int)$_GET['reading'];
    
    $id = ($_GET['id']);
    
    if ($book->updateReading($id, $reading)){
        $resp = $book->getUpdatedReading($id);
    }
}
else{
    $resp = 'mode not set';
}

echo '<response>';
    echo $resp;
echo '</response>'; 
?>