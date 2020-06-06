<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        subjects.php
// Dir:         /objects/subjects.php
// Desc:        Define a Subject class with public properties corresponding to
//              the row members of the Subject table of the Biblian DB.
//              Create a method to be called elsewhere to INSERT a new Subject
//              into the DB
//              Create a method to be called elsewhere to READ the Subjects
//              from the DB
//-----------------------------------------------------------------------------
*/
class Subject{
    //DB conn
    private $dbConn;
    private $table_name='Subjects';
    
    //Object Props
    public $id;
    public $name;
    
    //Constructor
    public function __construct($db){
        $this->dbConn = $db;
    }
    
    //used by the drop-down menu for adding books
    function read(){
        $query = "SELECT id, name
                  FROM " . $this->table_name . 
                 " ORDER BY name";
        
        $stmt = $this->dbConn->prepare( $query );
        $stmt->execute();
        
        return $stmt;
    }
    
    //Used to read Category Name from its ID
    function readName(){
        $query = "SELECT name
                  FROM " . $this->table_name . 
                 " WHERE id = ? limit 0,1";
        $stmt = $this->dbConn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->name = $row['name'];
    }
}
?>






