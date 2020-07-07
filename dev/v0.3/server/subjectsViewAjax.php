<?php
//     generates the XML file and sends it to the client computer
header('Content-Type: text/xml');    
include_once './res/inc/core.php';
include_once './res/inc/database.php';
//    include_once './objects/subjects.php';
include_once './res/inc/helperFunctions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConn();

$subject = new Subject($db);

//-----------------------------------------------------------------------------
// SUBJECT OBJECT COPY
//-----------------------------------------------------------------------------
class Subject{
    //DB conn
    private $dbConn;
    private $table_name='Subjects';

    // called in $this->create() to check if MYSQL error is for duplicate subject entry
    private $mysqlErrorDuplicate = 1062;
    
    //Object Props
    public $ID;
    public $Name;
    
    //Constructor
    public function __construct($db){
        $this->dbConn = $db;
    }

    /*-----------------------------------------------------------------------------
    // Function:    getLastInsertID()        
    //
    // Params:      
    // Desc:        Gets the ID of the last INSERT Operation from DB and returns it
    // Invocations: 
    //---------------------------------------------------------------------------*/
    function getLastInsertID(){
        $query = 'SELECT DISTINCT LAST_INSERT_ID() FROM Subjects';
        $stmt = $this->dbConn->prepare($query);
        
        // if the execution of the main book INSERT goes fine, then perform the 2nd INSERT to 
        // the Subjects table, using the finalAutoID value
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lastID = $row['LAST_INSERT_ID()'];
        
        return $lastID;
    }

    // RETURN 0 instead of 'false'.  It affects the XML response negatively.
    /*-----------------------------------------------------------------------------
    // Function:    countAll()        
    //
    // Desc:        SELECT all ID entries (all rows) in Subject table and then count
    //              them.  Returned count.
    // Invocations: subjectsView.php 
    //              --$totalRows = $subject->countAll();
    //---------------------------------------------------------------------------*/ 
    function updateSubject($id, $name){
        $query = "UPDATE Subjects SET SubjectName=:SubjectName WHERE SubjectID=:SubjectID";

        $stmt= $this->dbConn->prepare($query);

        $this->Name=htmlspecialchars(strip_tags($name));  
        
        $stmt->bindParam(':SubjectID', $id);
        $stmt->bindParam(':SubjectName', $name);

        if($stmt->execute()){
            return true;
        }
        $stmt->debugDumpParams();
        return 0;
    }

    /*-----------------------------------------------------------------------------
    // Function:    countAll()        
    //
    // Desc:        SELECT all ID entries (all rows) in Subject table and then count
    //              them.  Returned count.
    // Invocations: subjectsView.php 
    //              --$totalRows = $subject->countAll();
    //---------------------------------------------------------------------------*/ 
    function countAll(){
        //Does this need an empty string appended at the end?
        $query = "SELECT SubjectID FROM " . "$this->table_name" . "";
        
        $stmt = $this->dbConn->prepare( $query );
        $stmt->execute();
        
        $count = $stmt->rowCount();
        
        return $count;
    } // END countAll()

    function getUpdated($id){
        $query = "SELECT SubjectName FROM Subjects WHERE SubjectID=:SubjectID";
        $stmt = $this->dbConn->prepare( $query );
        $stmt->bindParam(':SubjectID', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->Name = $row['SubjectName'];
        return $row['SubjectName'];
    }

    function createSubject($name){
        $query = "INSERT INTO Subjects (SubjectID, SubjectName) VALUES (NULL, :SubjectName)";
        $stmt = $this->dbConn->prepare($query);
        $stmt->BindParam(':SubjectName', $name);

        if($stmt->execute()){
            return true;
        }
        $stmt->debugDumpParams();    
        return 0;
    }

    function deleteSubject($id){
        $query = "DELETE FROM Subjects WHERE SubjectID=:SubjectID";
        $stmt= $this->dbConn->prepare( $query );
        $stmt->bindParam(':SubjectID', $id);
        if($stmt->execute()){
            return true;
        }
        $stmt->debugDumpParams();    
        return 0;
    }

    function deleteBookSubjects($id){
        $query = "DELETE FROM BookSubjects WHERE SubjectID=:SubjectID";
        $stmt = $this->dbConn->prepare( $query );
        $stmt->bindParam(':SubjectID', $id);
        
        if($stmt->execute()){
            return true;
        }
        $stmt->debugDumpParams();
        return 0;
    }
    
    function getBookSubjects($id){
        $query = "SELECT * FROM BookSubjects WHERE SubjectID=:SubjectID";
        $stmt = $this->dbConn->prepare( $query );
        $stmt->bindParam(':SubjectID', $id);
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row){
            return $row['BookID'];
        }else{
            return 0;
        }
    }
}
//-----------------------------------------------------------------------------
// tell it we're generating XML content.  Whenever we generate an XML file with PHP, we need to tell the network what sort of data it is
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';    
// missing the ? at the end of this line, right before the last angle bracket can cause the XMLresponse to not be readable
// it will come in as always NULL.  Be sure to include the ? before the >.

// UPDATE MODE
// ----------------------------
if($_GET['mode'] === 'update'){
    if($subject->updateSubject($_GET['id'], $_GET['name'])){
        $resp = $subject->getUpdated($_GET['id']);
    }
}
// DELETE MODE
// ----------------------------    
else if($_GET['mode'] === 'delete'){
    // CHECK TO SEE IF ANY BOOKS ARE LINKED TO THAT DELETED SUBJECT
    if($subject->getBookSubjects($_GET['id'])){
        // IF --ANY-- BOOK ID's ARE RETURNED FOR A GET QUERY, THEN CALL DELETE, BUT RETURN 'successBoth'
        if($subject->deleteSubject($_GET['id'])){
            $resp = 'successBoth';
        }else{
            $resp = 'failure';
        }
    }else{
        // IF NO BOOK ID's ARE RETURNED FOR A GET QUERY, THEN CALL DELETE, BUT RETURN 'successSubjectOnly'
        if($subject->deleteSubject($_GET['id'])){
            $resp = 'successSubjectOnly';
        }else{
            $resp = 'failure';
        }
    }
}
// CREATE MODE
// ----------------------------
else if($_GET['mode'] === 'create'){
    // assign the name of the new subject
    $resp = $subject->createSubject($_GET['name']);
}
// MODE NOT SET
// ----------------------------
else{
    $resp = 'mode not set';
}

echo '<response>';
    echo $resp;
echo '</response>';
?>