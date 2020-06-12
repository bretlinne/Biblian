 <?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        books.php - REDO USING BiblianDB_S
// Dir:         /objects/books.php
// Desc:        Define a Book class with public properties corresponding to the
//              row members of the book table of the Biblian DB.
//              Create a method to be called elsewhere to INSERT a new book
//              into the DB
    RULES
    -----------------------------------------
    Title:
    --only REQUIRED attribute
    --must be entered by user or INSERT will not submit to DB
    
    RATING:
    --can be NULL
    --must be positive AND between (0.0 and 5.0) inclusive
    
    COMMENTS:
    --can be NULL
    
    PageCount:
    --can be NULL
    
    PROGRESS:
    --can be NULL
    --can affect the DateFinished setting IF Progress == PageCount
        -further, most books don't have you read to the VERY end because
         of indexes, blank pages, appendices etc.  This might prompt to 
         mark the book as finished if its within 90%...
            -This just sounds like an annoying UI feature
            -BAD IDEA
    
    ISBN:
    --can be NULL
    
    LISTPRICE:
    --can be NULL
    --cannot be negative
    --can be ZERO?
        -no.  What manufacturer would have ListPrice of $0.00?
        
    DateAcquired:
    --must be set to at least when the book was entered in Biblian
    --cannot be null
    --(can be null in DB but isn't treated that way client-side)

    Date Started:
    --can be NULL
    --can be set without DateFinished?
        --YES - of course

    DateFinished:
    --can be NULL
    --can dateFinished be set without Started?
        --YES - more likely to log an accomplishment like Finished than started
    --can DateFinished without setting Progress?
        --YES - if user doesn't feel like logging pages, don't make them
    
    Reading:
    --tinyint - either 0 or 1
    --defaults to 'false' if unset
    --can be set on book Creation, updated in the update page or just added from 
      the read page by clicking on the status button toggling between 'Unread', 'Reading', 'Finished'

    TODO                                                                            Finished?
    -----------------------------------------------------------------------------------------
    --Make sure this can submit UPDATE and check if the Title is blank/NULL         [ DONE ]
        --It should NOT be able to succeed if a blank/NULL is sent                  [ DONE ]
    --Create UI feedback on what was updated to what. 
        --make a dropdown section of the notification that the UPDATE was 
          successful and display the info there.  
            EXAMPLE: "UPDATED:
                    Title:      'Foo Book'      --> 'Bar Book'
                    Rating:     2.5             --> 5.0
                    Progress:   15 pages        --> 55 pages
                    "
// OPTIMIZE     Search for 'OPTIMIZE'
//-----------------------------------------------------------------------------
*/
include_once '../res/inc/helperFunctions.php';
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
    
    /*=============================================================================
    
    HELPER FUNCTIONS
    
    =============================================================================*/
    /*-----------------------------------------------------------------------------
    // Function:    getLastInsertID()        
    //
    // Params:      
    // Desc:        Gets the ID of the last INSERT Operation from DB and returns it
    // Invocations: createBook.php 
    //              --if ($book->create()){
    //                  $lastID = $subject->getLastInsertID();
    //                  ...
    //---------------------------------------------------------------------------*/
    function getLastInsertID(){
        //$query = 'SELECT LAST_INSERT_ID()';
        $query = 'SELECT DISTINCT LAST_INSERT_ID() FROM ' . $this->tableName;
        $stmt = $this->dbConn->prepare($query);
        
        // if the execution of the main book INSERT goes fine, then perform the 2nd INSERT to 
        // the Subjects table, using the finalAutoID value
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lastID = $row['LAST_INSERT_ID()'];
        
        return $lastID;
    }
    /*-----------------------------------------------------------------------------
    // Function:    replace_with_null(&$var, $var_name)        
    //
    // Params:      takes in the address of a parameter to be modified (which 
    //              would normally be something like $Book->PageCount) and the 
    //              name of the variable being changed for debugging displays.
    // Desc:        Tests any parameter passed in as to whether its NULL or empty.
    //              NULL is okay, empty is not.  The DB needs NULL if no actual 
    //              data is meant to be sent in for a field.  This replaces all
    //              empty entries with NULL values
    // Invocations: books.php 
    //              --$this->replace_with_null($this->ISBN, $parameters[0]);
    //              --et al similar
    //---------------------------------------------------------------------------*/
    function replace_with_null(&$var, $var_name){
        //debug_to_console($var);
        if(empty($var)){
            //debug_to_console($var_name . ' is either an empty string, false, or 0. VALUE: ' . $var);
            $var = null;
            if (is_null($var)){
                //debug_to_console('-->' . $var_name . ' changed to NULL.');
            }
            else{
                //debug_to_console('Failed to modify ' . $var_name . ' field.');
            }
        }
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    replace_with_null(&$final, $id)        
    //
    // Params:      takes in the address of a "$final" which is the query being
    //              prepared for sending to the DB for either an UPDATE or INSERT.
    //              Takes in an ID for the book being modified in the case of update
    // Desc:        Tests any parameter passed in as to whether its NULL or empty.
    //              NULL is okay, empty is not.  The DB needs NULL if no actual 
    //              data is meant to be sent in for a field.  This replaces all
    //              empty entries with NULL values
    // Invocations: books.php --> create() 
    //              books.php --> update()
    //              --$this->bindAndScrub()
    //---------------------------------------------------------------------------*/
    function scrubAndBind(&$final){
               //Scrub out any extraneous characters
        $this->Title=htmlspecialchars(strip_tags($this->Title));                                                                  
        $this->ISBN=htmlspecialchars(strip_tags($this->ISBN));                                                                
        $this->PageCount=htmlspecialchars(strip_tags($this->PageCount));                                                    
        $this->ListPrice=htmlspecialchars(strip_tags($this->ListPrice));
        $this->Comments=htmlspecialchars(strip_tags($this->Comments)); 
        $this->Progress=htmlspecialchars(strip_tags($this->Progress));
        $this->Rating=htmlspecialchars(strip_tags($this->Rating));
        $this->DateAcquired=htmlspecialchars(strip_tags($this->DateAcquired));
        $this->DateStarted=htmlspecialchars(strip_tags($this->DateStarted));
        $this->DateFinished=htmlspecialchars(strip_tags($this->DateFinished));
        $this->Reading=htmlspecialchars(strip_tags($this->Reading));
    
        // NULL SCRUBBING
        // --------------    
        $parameters= [
            'ISBN', 'PageCount', 'Comments', 'ListPrice', 'Progress', 'Rating', 'DateStarted', 'DateFinished'
        ];
        $this->replace_with_null($this->ISBN, $parameters[0]);
        $this->replace_with_null($this->PageCount, $parameters[1]);
        $this->replace_with_null($this->Comments, $parameters[2]);
        $this->replace_with_null($this->ListPrice, $parameters[3]);
        $this->replace_with_null($this->Progress, $parameters[4]);
        $this->replace_with_null($this->Rating, $parameters[5]);
        $this->replace_with_null($this->DateStarted, $parameters[6]);
        $this->replace_with_null($this->DateFinished, $parameters[7]);
        
        // DATEACQUIRED SCRUBBING
        // ----------------------
        // THiS SHOULDN'T EVEN BE NEEDED HERE AS THE BOOK SHOULD ALREADY HAVE A TIMESTAMP BY DEFAULT
        // FROM CREATION.  
        // Even though the current day is auto-populated on the client side and passed in, its possible for the user
        // to eliminate it and try to pass in nothing.
        // create timestamp for dateAcquired field if its NULL
        // This is only necessary if the value of the dateAcquired is empty.  
        
        if (empty($this->DateAcquired) or is_null($this->DateAcquired)){
            debug_to_console('DateAcquired is either empty, false, 0 and CANNOT be NULL. VALUE: '. $this->DateAcquired);
            $this->DateAcquired = date('Y-m-d');  
            
            if (is_null($this->DateAcquired)){
                debug_to_console('Failed to modify DateAcquired field.');
            }
            else{
                debug_to_console('-->DateAcquired changed to todays date: ' . $this->DateAcquired);
            }
        }       
        
        //--------------------------------------------------------------------------------
        
        //bind the values to the query template
        $final->bindParam(':Title', $this->Title);
        $final->bindParam(':ISBN', $this->ISBN);
        $final->bindParam(':PageCount', $this->PageCount);
        $final->bindParam(':Comments', $this->Comments);
        $final->bindParam(':ListPrice', $this->ListPrice);
        $final->bindParam(':Progress', $this->Progress);
        $final->bindParam(':Rating', $this->Rating);
        $final->bindParam(':DateAcquired', $this->DateAcquired);
        $final->bindParam(':DateStarted', $this->DateStarted);
        $final->bindParam(':DateFinished', $this->DateFinished);
        $final->bindParam(':Reading', $this->Reading);
    }
    
    /*=============================================================================
    
    CRUD FUNCTIONS
    
    =============================================================================*/
    /*-----------------------------------------------------------------------------
    // Function:    create()        
    //
    // Desc:        INSERT a new entry to Books table of Biblian.  Does NOT insert 
    //              new ID, that's handled server-side by MYSQL.
    // Invocations: createBook.php 
    //              --$stmt = $book->readAll($fromRecordNum, $records_per_page);
    //---------------------------------------------------------------------------*/
    function create(){
    
        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = 'INSERT INTO ' . $this->tableName . ' SET 
            Title=:Title,
            ISBN=:ISBN,
            PageCount=:PageCount,
            Comments=:Comments,
            ListPrice=:ListPrice,
            Progress=:Progress,
            Rating=:Rating,
            DateAcquired=:DateAcquired,
            DateStarted=:DateStarted,
            DateFinished=:DateFinished,
            Reading=:Reading;';
            
        $final = $this->dbConn->prepare($query);
        
        debug_to_console('query: ' .$query);
        
        // this calls a function with code shared between $this->create() and $this->update()
        $this->scrubAndBind($final);
        
        if($final->execute()){
            return true;
        }
        else
        {   
            // DELETE or suppress this debug DumpParams at some point
            $final->debugDumpParams();
            return false;
        }
        
    } /* END CREATE METHOD */
      
    /*-----------------------------------------------------------------------------
    // Function:    readAll()        
    //
    // Desc:        SELECT all fields from the Books table, order by Title ASC
    // Invocations: index.php
    //              --$stmt = $book->readAll($fromRecordNum, $recordsPerPage);
    //---------------------------------------------------------------------------*/
    function readAll($fromRecordNum, $recordsPerPage){
        /*
        //ORIGINAL WORKING QUERY --WITHOUT-- GROUP_CONCAT or COUNTING
        $query = "SELECT b.ID, s.SubjectName, b.Title, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished FROM 
                " . $this->tableName . " AS b 
                LEFT OUTER JOIN BookSubjects AS bs ON b.ID = bs.BookID
                LEFT OUTER JOIN Subjects AS s ON s.SubjectID = bs.SubjectID
            ORDER BY
                Title ASC
            LIMIT
                {$fromRecordNum}, {$recordsPerPage}";
        */
        $query = "SELECT b.ID, b.Title, GROUP_CONCAT(s.SubjectName SEPARATOR ';') as SubjectName, Count(bs.BookID) SubjectCount, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished, b.Reading FROM 
                " . $this->tableName . " AS b
                LEFT OUTER JOIN BookSubjects AS bs ON b.ID = bs.BookID
                LEFT OUTER JOIN Subjects AS s ON s.SubjectID = bs.SubjectID
            GROUP BY b.ID, bs.BookID, b.Title, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished, b.Reading
            ORDER BY b.Title ASC 
            LIMIT 
                {$fromRecordNum}, {$recordsPerPage}";
        /*    
        $query = "SELECT b.ID, b.Title, GROUP_CONCAT(s.SubjectName SEPARATOR ', ') AS 'SubjectName', b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished FROM 
                " . $this->tableName . " AS b 
                LEFT OUTER JOIN BookSubjects AS bs ON bs.BookID = b.ID
                LEFT OUTER JOIN Subjects AS s ON bs.SubjectID = s.SubjectID
            GROUP BY b.ID, b.Title, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished
            ORDER BY b.Title ASC
            LIMIT 
                {$fromRecordNum}, {$recordsPerPage}";
        */
        $final = $this->dbConn->prepare($query);
        $final->execute();
    
        return $final;
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readOne() - UPDATED - Reads from Books and Subjects
    //
    // Desc:        SELECT all fields from a row of Books table, matching passed in
    //              ID value.  
    // Invocations: updateBook.php
    //              --$book->readOne();
    //---------------------------------------------------------------------------*/
    function readOne(){
        //ORIGINAL WORKING QUERY --WITHOUT-- GROUP_CONCAT or COUNTING
        /*
        $query = "SELECT b.ID, s.SubjectName, b.Title, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished, b.Reading FROM 
                " . $this->tableName . " AS b 
                LEFT OUTER JOIN BookSubjects AS bs ON b.ID = bs.BookID 
                LEFT OUTER JOIN Subjects AS s ON s.SubjectID = bs.SubjectID 
            WHERE ID = ? LIMIT 0,1";
        */
        $query = "SELECT b.ID, b.Title, 
                GROUP_CONCAT(s.SubjectName SEPARATOR ';') as SubjectName, 
                GROUP_CONCAT(s.SubjectID SEPARATOR ',') as SubjectIDs,
                Count(bs.BookID) SubjectCount, b.ISBN, b.PageCount, b.Comments, b.ListPrice, b.Progress, b.Rating, b.DateAcquired, b.DateStarted, b.DateFinished, b.Reading FROM 
                " . $this->tableName . " AS b 
                LEFT OUTER JOIN BookSubjects AS bs ON b.ID = bs.BookID 
                LEFT OUTER JOIN Subjects AS s ON s.SubjectID = bs.SubjectID 
            WHERE ID = ?";

        $final = $this->dbConn->prepare($query);
        
        $final->bindParam(1, $this->ID);
        $final->execute();
        
        $row = $final->fetch(PDO::FETCH_ASSOC);
        
        $this->Title = $row['Title'];
        $this->ISBN = $row['ISBN'];
        $this->PageCount = $row['PageCount'];
        $this->Comments = $row['Comments'];
        $this->ListPrice = $row['ListPrice'];
        $this->Progress = $row['Progress'];
        $this->Rating = $row['Rating'];
        $this->DateAcquired = $row['DateAcquired'];
        $this->DateStarted = $row['DateStarted'];
        $this->DateFinished = $row['DateFinished'];
        $this->SubjectName = $row['SubjectName'];
        $this->Reading = $row['Reading'];
        $this->SubjectIDs = $row['SubjectIDs']; 
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readOne() - OUTDATED VERSION - ONLY READS FROM Books Table
    //
    // Desc:        SELECT all fields from a row of Books table, matching passed in
    //              ID value.  
    // Invocations: updateBook.php
    //              --$book->readOne();
    //---------------------------------------------------------------------------*/
    /*
    function readOne(){
        $query = "SELECT ID, Title, ISBN, PageCount, Comments, ListPrice, Progress, Rating, DateAcquired, DateStarted, DateFinished FROM 
                " . $this->tableName . "
            WHERE ID = ? LIMIT 0,1";
        
        $final = $this->dbConn->prepare($query);
        $final->bindParam(1, $this->ID);
        $final->execute();
        
        $row = $final->fetch(PDO::FETCH_ASSOC);
        
        $this->ID = $row['ID'];
        $this->Title = $row['Title'];
        $this->ISBN = $row['ISBN'];
        $this->PageCount = $row['PageCount'];
        $this->Comments = $row['Comments'];
        $this->ListPrice = $row['ListPrice'];
        $this->Progress = $row['Progress'];
        $this->Rating = $row['Rating'];
        $this->DateAcquired = $row['DateAcquired'];
        $this->DateStarted = $row['DateStarted'];
        $this->DateFinished = $row['DateFinished'];
    }
    */
    
    /*-----------------------------------------------------------------------------
    // Function:    countAll()        
    //
    // Desc:        SELECT all ID entries (all rows) in Books table and then count
    //              them.  Returned count.
    // Invocations: index.php 
    //              --$totalRows = $book->countAll();
    //---------------------------------------------------------------------------*/ 
    function countAll(){
        //Does this need an empty string appended at the end?
        $query = "SELECT ID FROM " . "$this->tableName" . "";
        
        $stmt = $this->dbConn->prepare( $query );
        $stmt->execute();
        
        $count = $stmt->rowCount();
        
        return $count;
    } // END countAll()
    
    /*-----------------------------------------------------------------------------
    // Function:    update()        
    //
    // Desc:        send updates to the DB based on user changes in the client side
    // Invocations: updateBook.php 
    //              --if ($book->update()){
    //---------------------------------------------------------------------------*/ 
    function update($id){
        // DELETE - this debug shows that the book ID is being properly passed into the books.php and Book Class
        //debug_to_console('bookID (in CLASS): ' . $id);
        //debug_to_console('bookTitle (in CLASS): ' . $this->Title);
        
        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = "UPDATE " . $this->tableName . " 
        SET 
            Title=:Title,
            ISBN=:ISBN,
            PageCount=:PageCount,
            Comments=:Comments,
            ListPrice=:ListPrice,
            Progress=:Progress,
            Rating=:Rating,
            DateAcquired=:DateAcquired,
            DateStarted=:DateStarted,
            DateFinished=:DateFinished,
            Reading=:Reading
        WHERE 
            ID=:ID";
        
        $final = $this->dbConn->prepare($query);
        
        // NOTE - we're not altering a Book ID and there's no need to assign the ID to $book->ID.
        // Utilizing the parameter passed through the browser ($id) is sufficient.
        $this->ID=htmlspecialchars(strip_tags($id));  
        
        // this calls a function with code shared between $this->create() and $this->update()
        $this->scrubAndBind($final);
        
        // Again, its not necessary to use $this->ID.  The browser parameter is fine
        $final->bindParam(':ID', $id);
        
        
        if($final->execute()){
        //if($final->execute($parameters)){
            return true;
        }
        $final->debugDumpParams();
        return false;
    }

    /*-----------------------------------------------------------------------------
    // Function:    deleteSelectedBooks()        
    //
    // Desc:        DELETE a given book's entries in the BookSubjects junction 
    //              table, matching passed in BookID value.  
    // Invocations: 
    //---------------------------------------------------------------------------*/
    function deleteSelectedBooks($bookIds){        
        if($bookIds){
            // convert the $bookIds string into an array to iterate through
            $bookArray = explode(',', $bookIds);
            $query = "DELETE FROM Books WHERE ID in ("; 
            
            // construct the SQL query and load the array into it
            $size = sizeof($bookArray);
            for($i = 0; $i < $size; $i++ ){
                //debug_to_console('size: ' . $size . '; $i: ' . $i);
                $query .= $bookArray[$i];
                if($size - $i > 1){
                    $query .= ", ";
                }
                else if ($size - $i <= 1){
                    $query .= ");";
                }
            }
            debug_to_console($query);
        
            $final = $this->dbConn->prepare($query);
            
            if($final->execute()){
                return true;
            }
            else
            {   
                $final->debugDumpParams();
            }
        }
        return false;
    }

    function deleteThisBook($ID){
        $query = "DELETE FROM Books WHERE ID = ". $ID .";"; 
        $final = $this->dbConn->prepare($query);
        if($final->execute()){
            return true;
        }else{
            $final->debugDumpParams();
        }
        return false;
    }

    /*-----------------------------------------------------------------------------
    // Function:    countBookSubjects()        
    //
    // Desc:        SELECT SubjectID and SubjectName from the Subject table,
    //              matching passed in BookID value.  
    // Invocations: updateBook.php
    //              --$book->readOne();
    //---------------------------------------------------------------------------*/
    function return10(){
        return 10;
    }
} // END BOOK CLASS
?>