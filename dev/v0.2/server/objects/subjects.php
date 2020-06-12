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
// TODO:        --search for 'CHANGE'
//              --update function of subject entry in createBook() so that if a - DONE
//                duplicate subject is entered, it will auto-populate the form
//                with the correct value, instead of trying to do an INSERT op-
//                eration and then failing.  This would be FAR better UX.
                1) if user types entry, if it exists in the DB, it just         - DONE
                    populates the list and makes a matching tag entry           
                2) if an existing tag in the DB is sent and entered from the    - DONE
                   tag container, it doesn't kick out an error, it              
                   just does an insert into the BookSubject table.                                                                  
                3) if mixed entries are input (1 entry that doesn't exist in    - DONE
                   the DB and 1 entry that is an existing subject)              
                   then have the system make a subject insert for the new one, 
                   but don't attempt the 2nd, and do a booksubhject 
                   insert for both.
// OPTIMIZE     Search for 'OPTIMIZE'
//-----------------------------------------------------------------------------
*/
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
    /*=============================================================================
    
    HELPER FUNCTIONS
    
    =============================================================================*/
    // OPTIMIZE - perhaps its redundant to have a getLastInsertID() for each table
    // consider ways to optimize later.
    // The behavior of LAST_INSERT_ID in MySQL is to return the first ID of a multi
    // -row insert op.  That means that if I insert 3 rows and the first's ID is 
    // 10, then lastInsertID returns 10, not 12, which would have been the ID of 
    // the very last.  It returns the ID of the 1st of the group of inserts. I can
    // compute the IDs needed, but need to know 
    //    1) what is the 1st ID of the group & 
    //    2) what is the count of array members in the group? 
    //    *** NOTE *** this is now handled in $this->sortUserInput(); (BELOW)
    /*-----------------------------------------------------------------------------
    // Function:    getLastInsertID()        
    //
    // Params:      
    // Desc:        Gets the ID of the last INSERT Operation from DB and returns it
    // Invocations: createBook.php 
    //              --$lastSubjectID = $this->getLastInsertID();
    //---------------------------------------------------------------------------*/
    function getLastInsertID(){
        $query = 'SELECT DISTINCT LAST_INSERT_ID() FROM ' . $this->table_name;  
        $stmt = $this->dbConn->prepare($query);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lastID = $row['LAST_INSERT_ID()'];
        
        return $lastID;
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    sortUserInput()        
    //
    // Params:      
    // Desc:        intended to take the input sent from the Client POST and sort
    //              whether its to be a new INSERT to the DB in Subjects table (a
    //              non-existing subject) or just an INSERT to the BookSubjects 
    //              table to attach it to the book.
    //              --automatically calls $this->create() to create any new 
    //                subjects & uses the last inserted ID along with count to
    //                add the new ids inserted to an array of IDs to be attached
    //                to the book.
    // Returns:     $idsToAttach - an array of IDs to be attached to a book through
    //              invoking $this->createBookSubject().
    //              
    //              1) Decode the passed base64 data and put into an array              DONE
    //              2) array should be elements with JSON data of                       DONE
    //                 {name: '<name>' and id: <number> or <'new'>}
    //              3) sort the values into newSubjectNames[] array and                 DONE
    //                 oldSubjectIDs[].      
    //                 newSubjectNames should be array of 'name's
    //                 oldSubjectIDs should be array of 'id's=
    //              4) call create() and pass the newSubjects array to it               DONE
    //              5) get the lastInsert ID of that set of INSERTS and a count of      DONE
    //                 how many inserts were done so IDs can be calculated from it.
    //              6) append the IDs of newly INSERTED subjects to the ID array.       DONE
    //              7) call createBookSubject() and pass the IDs of the oldSubjects     HANDLED IN createBook.php
    //                 and create the attachment of all related subjects to the 
    //                 book in question.
    // Invocations: createBook.php
    //              updateBook.php
    // TODO:        fix the areas with bad variable names.  $a, $x, $i all suck
    //---------------------------------------------------------------------------*/
    function sortUserInput(){
        //          debug_to_console($this->Name);
        
        // decode FROM base64
        // example: 
        // W3sibmFtZSI6InF1eCIsImlkIjoiMTAzIn1d -> [{"name":"qux","id":"103"}]
        $subjectString = base64_decode($this->Name);
        debug_to_console('string from base64: ' . $subjectString);
        
        // preg_replace removes the beginning and trailing brackets so that json_decode() will work
        // example:
        // [{"name":"qux","id":"103"}] -> {"name":"qux","id":"103"}
        
        $subjectString = preg_replace(array('/^\[/','/\]$/'), '',$subjectString); 
        
        $s = $subjectString;// quick alias for shorter code
        $a[0] = '';        // array target 
        $x = 0;             // array elem accumulator
        $i = 0;             // string parser
        while( $i < strlen($s))
        {
            debug_to_console('$i: '. $i . '; $s: '. $s[$i] .'; $x: ' . $x .'; $a: ' . $a[$x] );
            if ($s[$i] !== '}'){
                $a[$x] .= $s[$i]; 
            }
            else {
                // append the text for the end of the JSON pair
                $a[$x] .= '}';
                if ($i+1 < strlen($s)){
                    $i += 1;    // jump ahead two places
                    $x += 1;    // increment the target array accumulator
                    $a[$x] = '';
                }
            }            
            $i++;
        }
        debug_to_console('strlen($s): ' . strlen($s));
        /*
        -----------------OLD VERSION OF THIS PARSING LOOP-----------------------
        // manually parse the JSON and subdivide into an array
        // $i is used to parse the string, $x is used to iterate through array elems
        $s = $subjectString;// quick alias for shorter code
        $a[] = null;        // array target 
        $x = 0;             // accumulator
        for($i = 0; $i < strlen($subjectString); $i++)
        {
            if (($i + 1 <= strlen($s)) && $s[$i+1] != '}') {
                // concat to the target array in elem position[$x] the value in the string.
                $a[$x] .= $s[$i];   
            }
            else if ((true)){
                // append the text for the end of the JSON pair
                $a[$x] .= '"}';
                //if ($i+2 < strlen($s)){
                    $i+=2;      // jump ahead two places
                //}
                $x += 1;    // increment the target array accumulator
            }
            debug_to_console('$i: '. $i . '; $x: '. $x . '; ' . strlen($s));
        }
        */
        $newSubjectNames = array();
        $oldSubjectIDs = array();
        $idsToAttach = array();
        //          debug_to_console('is array empty? ' . !empty($idsToAttach));
        $x = 0;
        $y = 0;
        // loop through the array created above and decode the JSON.  At each iteration
        // assign either the name of id to one of the two above arrays.  If the ID is 'new'
        // then assign the name to the names array.  Otherwise add the ID to the ID array.
        foreach($a as $elem){
            //          debug_to_console('$elem: '. $elem);
            $elem = json_decode($elem);
            if ($elem->id == 'new'){
                $newSubjectNames[$x] = $elem->name;
                $x+=1;
            }
            else{
                $oldSubjectIDs[$y] = $elem->id;
                //          debug_to_console('old ids: ' . $oldSubjectIDs[$y]);
                $y+=1;
            }
        }
        
        // send off the subject names array to the $this->create(), then get the lastInsertID
        // and calculate how many values were inserted.  Append the IDs thus calculated to the
        // IDs array and then return that to createBook.php
        if(!empty($newSubjectNames)){
            if($this->create($newSubjectNames)){
                $lastSubjectID = $this->getLastInsertID();
                //$idsToAttach = array();
                for($i = 0; $i < $x; $i++){ 
                    array_push($idsToAttach, $lastSubjectID + $i);
                }
            }
        }
        if(!empty($oldSubjectIDs)){ 
            for($i = 0; $i < sizeof($oldSubjectIDs); $i++){
                array_push($idsToAttach, $oldSubjectIDs[$i]);
            }        
        }
        
        for($i = 0; $i < sizeof($idsToAttach); $i++){
            debug_to_console('ids to attach: ' . $idsToAttach[$i]);
        }        
        return $idsToAttach;
    }
    
    /*=============================================================================
    
    CRUD FUNCTIONS
    
    =============================================================================*/
    /*-----------------------------------------------------------------------------
    // Function:    create($sArray)        
    //
    // Params:      $sArray - 'subject array' 
    // Desc:        INSERT new entry(ies) to Subjects table of Biblian.  Does NOT 
    //              insert new ID, that's handled by MYSQL.
    // Invocations: $this->sortUserInput()
    //---------------------------------------------------------------------------*/
    // called in books.php->create() when a subject is added to the book being created
    function create($sArray){
        
        //This is now handled in $this->sortUserInput() (ABOVE)
        //$subjectString = base64_decode($this->Name);
        //$subjectArray = explode(',', $subjectString);
        
        // MULTI-ROW INSERT template: ( this is the string I need to build )
        //$query = 'INSERT INTO Subjects(SubjectName) VALUES ('Foo'), ('Bar');'
        
        $query = 'INSERT INTO Subjects(SubjectName) VALUES ("';
        
        if ($sArray){
            $subjectArray = $sArray;
        }
        foreach ($subjectArray as $elem){
            debug_to_console('foo decoded: ' . $elem);
            $elem = sanitizeString($elem);
            debug_to_console('foo scrubbed: ' . $elem);
            $query .= $elem . '"), ("';    
        }
        $query = rtrim($query, ', ("');
        $query .= ';';
          
        debug_to_console('foo string: ' . $query);
            
        // OLD QUERY FOR SINGLE INSERT
        //$query = 'INSERT INTO ' . $this->table_name . ' SET 
        //    SubjectName=:Name';
        
        $stmt = $this->dbConn->prepare($query);
        
        // Scrub out any extraneous characters
        $this->Name=htmlspecialchars(strip_tags($this->Name));
        
        // Bind params
        $stmt->bindParam(':Name', $this->Name);
        
        
        // execute
        if($stmt->execute()){
            return true;
        }
        else{
            //CHANGE LATER - this would be better to have improved UX that populates 
            $mysqlError = $stmt->errorInfo();
            // Check if duplicate
            if($mysqlError[1]==$this->mysqlErrorDuplicate){
                echo "<div class='alert alert-danger'>Duplicate Subject Entered--Select it in dropdown.</div>";   
            }
            
            //$stmt->debugDumpParams();
            return false;
        }
    }
    
    
    /*-----------------------------------------------------------------------------
    // Function:    createBookSubject($lastBookID, $subjectIDs)        
    //
    // Params:      $lastBookID - this is BookID of last inserted book to DB
    //              $subjectIDs - can be either an array or single value to be
    //                            attached to the book.
    // Desc:        INSERTS new row to BookSubject junction table, creating a link 
    //              to a subject field for the book.  Typically called immediately
    //              after $book->create() executes.
    // Invocations: createBook.php 
    //              --if ($book->create()){
    //                  $lastID = $subject->getLastInsertID();
    //                  if($subject->createBookSubject($lastID, $book->SubjectID)){
    //                    echo "<div class='alert alert-success'>Book Subject 
    //                    Record Created</div>";   
    //                  }
    //                  ...
    //---------------------------------------------------------------------------*/
    // called in books.php->create() when a subject is added to the book being created
    function createBookSubject($lastBookID, $subjectIDs){
        // First make sure the value coming in for SubjectIDs is an array.  If it isn't convert it to one.
        
        if (! is_array($subjectIDs)){
            $subjectIDs = array($subjectIDs);
        }

        /* SINGLE SUBJECT INPUT
        //construct our query (NOTE - these are NOT yet bound to their final value.  This is a template)
        $query = 'INSERT INTO BookSubjects SET 
            BookID = :lastID,
            SubjectID = :subjectID';
        */
        
        // MULTI-ROW INSERT template: ( this is the string I need to build )
        //$query = 'INSERT INTO BookSubjects(BookID, SubjectID) VALUES (131,1), (131, 2), (131, 3);'
        $query = 'INSERT INTO BookSubjects(BookID, SubjectID) VALUES (' . $lastBookID . ', ';
        
        debug_to_console($subjectIDs);
        $i = 0;
        foreach ($subjectIDs as $elem){
            $query .= $elem;
            $i+=1;
            if($i < sizeof($subjectIDs)){
                $query .= '), (' . $lastBookID . ', ';            
            }    
        }
        $query .= ');';
          
        debug_to_console('createBookSubject query: ' . $query);
        
        $final = $this->dbConn->prepare($query);
        
        $final->bindParam(':$lastBookID', $lastBookID);
        $final->bindParam(':subjectID', $subjectID);
        
        if($final->execute()){
            return true;
        }
        else
        {   
            $final->debugDumpParams();
            return false;
        }
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readAll()        
    //
    // Desc:        Reads all SubjectIDs and SubjectNames
    // Invocations: subjects.php
    //              --$stmt = $subject->readAll();
    //---------------------------------------------------------------------------*/
    //used by the drop-down menu for adding books
    function readAll(){
        $query = "SELECT SubjectID, SubjectName
                  FROM " . $this->table_name . 
                 " ORDER BY SubjectName";
        
        $stmt = $this->dbConn->prepare( $query );
        $stmt->execute();
        
        return $stmt;
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readID($name)        
    // 
    // Parameters:  takes in a passed in SubjectName
    // Desc:        Used to read Subject ID from its Name. When a user types a 
    //              custom value, a SubjectName is passed
    // Invocations: NOT USED YET
    //---------------------------------------------------------------------------*/
    function readID($name){
        $query = "SELECT SubjectID 
                  FROM " . $this->table_name .
                  " WHERE SubjectName = ? limit 0,1";
        $stmt = $this->dbConn->prepare( $query );
        $stmt->bindParam(1, $Name);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->SubjectID = $row['SubjectID'];
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readName()        
    //
    // Desc:        Used to read Subject Name from its SubjectID.  Used in the 
    //              Subject dropdown selectors, which selects and passes in ID
    // Invocations: updateBook.php
    //              --$book->readOne();
    //---------------------------------------------------------------------------*/
    function readName(){
        $query = "SELECT SubjectName
                  FROM " . $this->table_name . 
                 " WHERE SubjectID = ? limit 0,1";
        $stmt = $this->dbConn->prepare( $query );
        // CHANGE - SubjectID should probably be just 'ID'
        $stmt->bindParam(1, $this->SubjectID);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->SubjectName = $row['SubjectName'];
    }
    
    /*-----------------------------------------------------------------------------
    // Function:    readOne()        
    //
    // Desc:        SELECT SubjectID and SubjectName from the Subject table,
    //              matching passed in BookID value.  
    // Invocations: updateBook.php
    //              --$book->readOne();
    //---------------------------------------------------------------------------*/
    function readOne(){
        $query = "SELECT s.ID, s.Name FROM 
                " . $this->tableName . "
            AS b  
            INNER JOIN BookSubjects AS bs ON b.ID = bs.BookID 
            INNER JOIN Subjects AS s on s.ID = bs.SubjectID 
            WHERE ID = ? LIMIT 0,1";
        
        $final = $this->dbConn->prepare($query);
        $final->bindParam(1, $this->ID);
        $final->execute();
        
        $row = $final->fetch(PDO::FETCH_ASSOC);
        
        $this->ID = $row['ID'];
        $this->Name = $row['Name'];
    }
    /*-----------------------------------------------------------------------------
    // Function:    deleteAllBookSubjectEntries()        
    //
    // Desc:        DELETE a given book's entries in the BookSubjects junction 
    //              table, matching passed in BookID value.  
    // Invocations: 
    //---------------------------------------------------------------------------*/
    function deleteAllBookSubjectEntries($bookID){
        debug_to_console('from Top of delete All: ' . $bookID);
        if($bookID){
            $query = "DELETE FROM BookSubjects WHERE BookID = ?";
        
            $final = $this->dbConn->prepare($query);
            $final->bindParam(1, $bookID);
            
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

    // DUMMY TEST FUNCTION - DELETE LATER
    function return10(){
        return 10;
    }
// ----------------------------------------------------------------------------
}// END CLASS
//-----------------------------------------------------------------------------
    
?>

<!--include the base64 Javascript helper functions-->
<script src='../client/res/inc/base64.js'></script>
