<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        books.php - ORIGINAL
// Dir:         /objects/books.php
// Desc:        Define a Book class with public properties corresponding to the
//              row members of the book table of the Biblian DB.
//              Create a method to be called elsewhere to INSERT a new book
//              into the DB
//-----------------------------------------------------------------------------
*/
class Book{
    //DB conn
    private $dbConn;
    private $table_name='Books';
    
    //Object Props
    public $Title;
    public $ISBN;
    public $PageCount;
    public $Comments;
    public $ListPrice;
    public $DateAcquired;
    public $DateStarted;
    public $DateFinished;
    public $Progress;
    public $Rating;
    
    //Constructor
    public function __construct($db){
        $this->dbConn = $db;
    }
    
    function debug_to_console($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);

        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }
    
    //Create (INSERT)
    function create(){

        /*$parameters = array(
            'Title' => $this->Title,
            'ISBN' => $this->ISBN,
            'PageCount' => $this->PageCount,
            'Comments' => $this->Comments,
            'ListPrice' => $this->ListPrice,
            'DateAcquired' => $this->DateAcquired,
            'DateStarted' => $this->DateStarted,
            'DateFinished' => $this->DateFinished,
            'Progress' => $this->Progress,
            'Rating' => $this->Rating
        );
        $query = " INSERT INTO Books (Title, ISBN, PageCount, Comments, ListPrice, DateAcquired, DateStarted, DateFinished, Progress, Rating) VALUES (:Title, :ISBN, :PageCount, :Comments, :ListPrice, :DateAcquired, :DateStarted, :DateFinished, :Progress, :Rating)";
        */
        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = 'INSERT INTO ' . $this->table_name . ' SET 
            Title=:Title,
            ISBN=:ISBN,
            PageCount=:PageCount,
            Comments=:Comments,
            ListPrice=:ListPrice,
            DateAcquired=:DateAcquired,
            DateStarted=:DateStarted,
            DateFinished=:DateFinished,
            Progress=:Progress,
            Rating=:Rating';
        
        $final = $this->dbConn->prepare($query);
        
        
        //Scrub out any extraneous characters
        $this->Title=htmlspecialchars(strip_tags($this->Title));                                                                  
        $this->ISBN=htmlspecialchars(strip_tags($this->ISBN));                                                                
        $this->PageCount=htmlspecialchars(strip_tags($this->PageCount));                                                    
        $this->Comments=htmlspecialchars(strip_tags($this->Comments)); 
        $this->ListPrice=htmlspecialchars(strip_tags($this->ListPrice));
        $this->DateAcquired=htmlspecialchars(strip_tags($this->DateAcquired));
        $this->DateStarted=htmlspecialchars(strip_tags($this->DateStarted));
        $this->DateFinished=htmlspecialchars(strip_tags($this->DateFinished));
        $this->Progress=htmlspecialchars(strip_tags($this->Progress));
        $this->Rating=htmlspecialchars(strip_tags($this->Rating));
        
        //create timestamp for dateAcquired field if its NULL
        // This is only necessary if the value of the dateAcquired is null.  
        // The current day is auto-populated on the client side and passed in, but its possible for the user
        // to eliminate it and try to pass in nothing.
        if ($this->DateAcquired == null){
            $this->DateAcquired = date('Y-m-d');  
        }
        
        // Check to see if dateAcquired is null and send debug msg to console
        if ($this->DateAcquired == null){
            debug_to_console('WARNING - DateAcquired still null');
        }
        else
        {
            debug_to_console('DateAcquired NOT null: ' . $this->DateAcquired);
        }
        
        //bind the values to the query template
        $final->bindParam(':Title', $this->Title);
        $final->bindParam(':ISBN', $this->ISBN);
        $final->bindParam(':PageCount', $this->PageCount);
        $final->bindParam(':Comments', $this->Comments);
        $final->bindParam(':ListPrice', $this->ListPrice);
        $final->bindParam(':DateAcquired', $this->DateAcquired);
        $final->bindParam(':DateStarted', $this->DateStarted);
        $final->bindParam(':DateFinished', $this->DateFinished);
        $final->bindParam(':Progress', $this->Progress);
        $final->bindParam(':Rating', $this->Rating);
        
 
        if($final->execute()){
        //if($final->execute($parameters)){
            return true;
        }
        else
        {   
            $final->debugDumpParams();
            return false;
        }
    }
}
?>