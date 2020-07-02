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
    
    /*-----------------------------------------------------------------------------
    // Function:    updateProgress($id, $value)      
    //
    // Params:      $id - ID of the book
    //              $value - progress value to update
    //              $started - T/F value as to whether this is the first progress added 
    //                         to the book.  If T, set DateStarted to today.
    // Desc:        special UPDATE function that sets only the Progress value to 
    //              $value for a specified Book's ID
    // Invocations: readingList.php 
    //              --if($book->updateProgress($bookIdToUpdateProgress, $progressValue)){...}
    //---------------------------------------------------------------------------*/ 
    function updateProgress($id, $value, $dateStarted){
        if($id){
            $query = "UPDATE " . $this->tableName . " 
            SET 
                Progress=:Progress";

            if(! empty($dateStarted)){
                $query .= ", DateStarted=:DateStarted ";   
            }
            
            $query .= " WHERE ID=:ID";
            
            $final = $this->dbConn->prepare($query);

            // NOTE - we're not altering a Book ID and there's no need to assign the ID to $book->ID.
            // Utilizing the parameter passed through the browser ($id) is sufficient.
            $this->ID = $id;  
            $final->bindParam(':ID', $id);
            
            $this->Progress = $value;
            $final->bindParam(':Progress', $this->Progress);
            
            if(! empty($dateStarted)){
                $this->DateStarted = $dateStarted;    
                $final->bindParam(':DateStarted', $this->DateStarted);
            }

            if($final->execute()){
            //if($final->execute($parameters)){
                return true;
            }
            $final->debugDumpParams();
        }
        return false;
    
    }
    
    function updatePageCount($id, $pageCount, $progress){
        $query = "UPDATE " . $this->tableName . " 
            SET 
                Progress=:Progress, 
                PageCount=:PageCount
            WHERE 
                ID=:ID";
        
        $final = $this->dbConn->prepare($query);
        $this->ID = $id;
        $this->PageCount = $pageCount;
        $this->Progress = $progress;
        
        $final->bindParam(':ID', $id);
        $final->bindParam(':PageCount', $pageCount);
        $final->bindParam(':Progress', $progress);
        
        if($final->execute())
        {
            return true;
        }else{    
            $final->debugDumpParams();
            return false;
        }
    }
    
    function getUpdatedProgress($id, $getDateStartedAlso){

        
        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = "SELECT Progress";
        if($getDateStartedAlso === 1){
            $query .= ", DateStarted ";
        }
        
        $query .= " FROM " . $this->tableName . " 
        WHERE 
            ID=:ID";

        $final = $this->dbConn->prepare($query);

        $final->bindParam(':ID', $id);

        $final->execute();
        $row = $final->fetch(PDO::FETCH_ASSOC);
        
        $this->Progress = $row['Progress'];
        
        $resp = $row['Progress'];
        if ($getDateStartedAlso === 1){
            $this->DateStarted = $row['DateStarted'];
            $resp .= ',' . $row['DateStarted'];
        }
        return $resp;
    }
    
    function getUpdatedPageCount($id){

        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = "SELECT PageCount FROM " . $this->tableName . " 
        WHERE 
            ID=:ID";

        $final = $this->dbConn->prepare($query);

        $final->bindParam(':ID', $id);

        $final->execute();
        $row = $final->fetch(PDO::FETCH_ASSOC);
        $this->PageCount = $row['PageCount'];
        return $row['PageCount'];
    }
    
    function updateDateFinished($id, $dateFinished){
        if($id){
            //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
            $query = "UPDATE " . $this->tableName . " 
            SET 
                DateFinished=:DateFinished
            WHERE 
                ID=:ID";
            
            $final = $this->dbConn->prepare($query);
            
            // NOTE - we're not altering a Book ID and there's no need to assign the ID to $book->ID.
            // Utilizing the parameter passed through the browser ($id) is sufficient.
            $this->ID = $id;  
            
            if(empty($dateFinished)){
                $this->DateFinished = null;    
            }else{
                $this->DateFinished = $dateFinished;
            }

            // Again, its not necessary to use $this->ID.  The browser parameter is fine
            $final->bindParam(':ID', $id);
            $final->bindParam(':DateFinished', $this->DateFinished);

            if($final->execute()){
            //if($final->execute($parameters)){
                return true;
            }
            $final->debugDumpParams();
        }
        return false;
    }
    
    // seems to not work if the return is actually null
    function getUpdatedDateFinished($id){
        $query = "SELECT DateFinished FROM " . $this->tableName . " 
        WHERE 
            ID=:ID";
        
        $final = $this->dbConn->prepare($query);
        
        $final->bindParam(':ID', $id);
        $final->execute();
        
        $row = $final->fetch(PDO::FETCH_ASSOC);
        $this->DateFinished = $row['DateFinished'];
        
        if(empty($row['DateFinished'])){
            return 'null';
        } 
        
        return $row['DateFinished'];    
        
    }
}
//-----------------------------------------------------------------------------
// tell it we're generating XML content.  Whenever we generate an XML file with PHP, we need to tell the network what sort of data it is
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';    
// missing the ? at the end of this line, right before the last angle bracket can cause the XMLresponse to not be readable
// it will come in as always NULL.  Be sure to include the ? before the >.

// ----------------------------
// PROGRESS UPDATE
// ----------------------------
if($_GET['mode'] === 'progress'){
    
    $progressValue = strtolower($_GET['progressValue']);
    $id = ($_GET['id']);
    $started = ($_GET['started']);
    
    $dateStarted = null;
    if($_GET['started'] === '1'){
        $timezone = 'America/Los_Angeles';
        date_default_timezone_set($timezone);
        $dateStarted = date("Y-m-d");    
    }

    
    if ($book->updateProgress($id, $progressValue, $dateStarted)){
        if(! empty($dateStarted)){
            $resp = $book->getUpdatedProgress($id, 1);
        }else{
            $resp = $book->getUpdatedProgress($id, 0);
        }
    }
    
    //$resp = '<progress>200</progress><dateStarted>"2020-06-01"</dateStarted>';
    
}
// ----------------------------
// PAGECOUNT UPDATE
// ----------------------------
else if($_GET['mode'] === 'pageCount'){
    $pageCountValue = strtolower($_GET['pageCountValue']);
    $progressValue = strtolower($_GET['progressValue']);
    $id = ($_GET['id']);
    
    if ($book->updatePageCount($id, $pageCountValue, $progressValue)){
        $resp = $book->getUpdatedPageCount($id);
    }
}
// ----------------------------
// FINISHED UPDATE
// ----------------------------
else if($_GET['mode'] === 'finished'){
    $dateFinished = null;
    if($_GET['finished'] === '1'){
        $timezone = 'America/Los_Angeles';
        date_default_timezone_set($timezone);
        $dateFinished = date("Y-m-d");    
    }
    
    $id = ($_GET['id']);
    
    if($book->updateDateFinished($id, $dateFinished)){
        $resp = $book->getUpdatedDateFinished($id);
    }
}
// ----------------------------
// NO MODE SET
// ----------------------------
else{
    $resp = 'mode not set';
}

echo '<response>';
    echo $resp;
echo '</response>';   
?>