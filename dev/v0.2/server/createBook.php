<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN  
// File:        createBook.php - REDO USING BiblianDB_S
// Dir:         /createBook.php
// Desc:        Construct UI for new book data entry.
//              Construct the INSERT command from the fields in the page and 
//              the POST. 
//              Give UI feedback on success or failure of the INSERT
// INCLUDES:    ./res/inc/database.php';
//              ./objects/books.php
//              ./objects/subjects.php
//              ./res/inc/helperFunctions.php 
//              ../client/res/inc/base64.js
// TODO:        --Author
//              --check that if a blank is POSTed for Subject, the system does
//                NOT try to create a row in Subjects table for a blank entry.
//              --get the create table to stop adjusting width when subjects are 
//                added to tag container, or any other inputs.  Make it static width always
//              --prevent code embedding in TAGS (that is, sanitize the inputs)
// OPTIMIZE     Search for 'OPTIMIZE'
//-----------------------------------------------------------------------------
*/
include_once './res/inc/database.php';
include_once './objects/books.php';
include_once './objects/subjects.php';
include_once './res/inc/helperFunctions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

//Instantiate the database class
//Then create the DB Conn and assign an alias of it to an object called '$db'
$database = new Database();
$db = $database->getConn();

//TODO - create book.php object file for this to call
//  -needs public props, constructor, a method for creating (INSERTING), new book into the DB
$book = new Book($db);
$subject = new Subject($db);

//Set page headers
$pageTitle = 'Create New Book';

// set stylesheet
$stylesheet = '../client/createBook.css';

include_once 'layoutHeader.php';

$lastSubjectID = null;

//-----------------------------------------------------------------------------------------------------------------------
//-- POST and DB logic
//-----------------------------------------------------------------------------------------------------------------------
//this 'if' checks if a POST exists in the browswer (if an data's been sent)
if($_POST){
    $book->Title        = $_POST['Title'];
    $book->ISBN         = $_POST['ISBN'];
    $book->PageCount    = $_POST['PageCount'];
    $book->Comments     = $_POST['Comments'];
    $book->ListPrice    = $_POST['ListPrice'];
    $book->Progress     = $_POST['Progress'];
    $book->Rating       = $_POST['Rating'];
    $book->DateAcquired = $_POST['DateAcquired'];
    $book->DateStarted  = $_POST['DateStarted'];
    $book->DateFinished = $_POST['DateFinished'];
    $book->SubjectID    = $_POST['subject_id'];         //only used for INSERT of BookSubject row
    $book->Reading      = $_POST['Reading'];
    $subject->Name      = $_POST['subject_name'];       //only used for INSERT of custom subject
    
    // DUMMY DATA
    //$foo = 7;
    //$subject->createBookSubject(131, $foo);
    
    // ------------------------------------------
    // CREATE BOOK
    // ------------------------------------------
    
    if ($book->create()){
        
        $lastBookID = $book->getLastInsertID();
        $subjectsToAttach = null;
        // Check if there's a custom Subject entry(s)
        if ($_POST['subject_name']){
            // call the sortUserInput() function which finds all new subjects and creates them, and return
            // an array of IDs for all new subjects and old subjects
            $subjectsToAttach = $subject->sortUserInput();
            
            debug_to_console($subjectsToAttach);
        }
        if($subjectsToAttach !== null){
            if( $subject->createBookSubject($lastBookID, $subjectsToAttach)){
                echo "<div class='alert alert-success'>Book Subject Record Created</div>";   
            }
            else{
                echo "<div class='alert alert-danger'>Book Subject Creation Failed!</div>";       
            }
        }
        
        // UI Feedback for Book creation
        echo "<div class='alert alert-success'>Book successfully added to Biblian</div>";   
    }
    //if $book->create() fails, tell user of his utter failure
    else{
        echo "<div class='alert alert-danger'>Book Creation Failed...!</div>";   
    }
    
}
?>
    
    <!--------------->
    <!--SideBar Div-->
    <!--------------->
    <!--
    <div id='sidebar'>
        <div id='sidebarMargin'>
            <h1 id='appTitle'>Biblian</h1>
            <h2 id='pageTitle'>Add Book</h2>
        </div>
    </div>
    <div id='content'>
        <div id='contentMargin'>
-->
<!--------------------------------------------------------------------------------->
<!--either this PHP script or the commented JS works for getting today's date
and inserting it into the dateAcquired field
To use the JS, 
1) remove the 'value=php echo $today' below
2) comment out the PHP or remove it
3) un-comment the JS
-->
    <?php
      $timezone = 'America/Los_Angeles';
      date_default_timezone_set($timezone);
      $today = date("Y-m-d");
    
    //read book subjects from DB
    $stmt = $subject->readAll();
    ?>


    <!-- NAV Button to INDEX.PHP -->
    <div class='right-button-margin'>
        <a href='index.php' class='btn btn-primary pull-right'>
            <span class='glyphicon glyphicon-book'></span> Biblian Home
        </a>
    </div>


    <!---------------------------------------------------------------------------------------------------------------------->
    <!-- Generate HTML                                                                                                    -->
    <!---------------------------------------------------------------------------------------------------------------------->
    <form id='mainForm' action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
         <!--This is a dummy submit button to eliminate the default behavior of 'Enter' key automatically sending the POST-->
         <button type="submit" disabled style="display: none" aria-hidden="true"></button>

         <table class='table table-hover table-responsive table-bordered'>
            <thead>
                <!-- TITLE         -->
                <!------------------->
                <tr>
                    <td>Title</td>
                    <td><input type='text' class='form-control' name='Title' maxlength='512' autofocus required/></td>
                </tr>
                <!-- AUTHOR LAST   -->
                <!------------------->
                <tr>
                    <td>Author Last</td>
                    <td><input type='text' class='form-control' name='LastName' value='Howard' /></td>
                </tr>
                <!-- AUTHOR FIRST  -->
                <!------------------->
                <tr>
                    <td>Author First</td>
                    <td><input type='text' class='form-control' name='FirstName' value='Curly' /></td>
                </tr>
                <!-- SUBJECT       -->
                <!------------------->
                
                <tr>
                    <td>Subject(s)</td>
                    <td>
                        <input class='form-control' id='subject-input' list='datalistTestData' name='datalistTest'/>
                        <datalist id='datalistTestData'>
                        <?php
                            //read book subjects from DB
                            $stmt = $subject->readAll();

                            //put them in the datalist
                            while($row_subject = $stmt->fetch(PDO::FETCH_ASSOC)){
                                extract($row_subject);
                                echo "<option data-value='{$SubjectID}' value='{$SubjectName}'></option>";
                                //echo "<option value='{$SubjectID}'>{$SubjectName}</option>";
                            }
                        ?>
                        </datalist>
                        <div class='linne-container'>
                            <div class='linne-tag-container' >
                                <input readonly='readonly'/>
                            </div>
                        </div>
                    </td>
                </tr>
                
                
                
             </thead>
<!-- ADVANCED      -->
<!------------------->
             <!--   JS-based advanced slide function.  replace the jquery version when this page is updated to have
                    divs instead of tables
             <tr>
                <td>
                    <div class='advContainer advanced-toggle' onclick='slideToggle(this, 200)'><b>Advanced Book Data  <span id='details-icon' class='glyphicon glyphicon-chevron-down'></span></b>
                </td>
             </tr>
            -->
             <tr id='button-row'>
                <td id='button-cell' colspan='2' width='100%'>
                    <a id="advanced-toggle" class="collapsible" href="#">Advanced Book Data</a>
                </td>
             </tr>
             <tbody id='advanced' style='display:none'>
                <!-- AUTHOR MIDDLE 01 -->
                <!---------------------->
                <tr>
                    <td>Author Middle 01</td>
                    <td><input type='text' class='form-control' name='MiddleName01' value='J' /></td>
                </tr>
                <!-- AUTHOR MIDDLE 02 -->
                <!---------------------->
                <tr>
                    <td>Author Middle 02</td>
                    <td><input type='text' class='form-control' name='MiddleName02' value='' /></td>
                </tr>
                <!-- AUTHOR SUFFIX -->
                <!------------------->
                <tr>
                    <td>Author Suffix</td>
                    <td><input type='text' class='form-control' name='Suffix' placeholder='Jr., Sr., III., Esq., Ph.D, etc' value='' /></td>
                </tr>
                <!-- RATING        -->
                <!------------------->
                <tr>
                    <td>Rating</td>
                    <td>
                        <input type='number' class='form-control' name='Rating' placeholder='(between 0.0 and 5.0 Stars)' min='0.0' max='5.0' step='0.1' />

                    </td>
                </tr>
                <!-- PAGE COUNT    -->
                <!------------------->
                <tr>
                    <td>Page Count</td>
                    <td><input type='number' name='PageCount' value='' /></td>
                </tr>
                <!-- DATE ACQUIRED -->
                <!------------------->
                <tr>
                    <td>Date Acquired</td>
                    <td><input type='date' id='DateAcquired' name='DateAcquired' value='<?php echo $today?>' /></td>
                </tr>
        <!-- STARTED READING? -->
        <!---------------------->
                <tr class='start-reading'>
                    <td>Started Reading?</td>
                    <td><input id='reading-box' type='checkbox'></td>
                </tr>
                <!-- DATE STARTED  -->
                <!------------------->
                <tr class='reading-content' width='50%' style='display:none'>
                    <td class='reading-content'>Date Started</td>
                    <td class='reading-content'>
                        <input type='date' name='DateStarted' />
                    </td>
                </tr>
                <!-- DATE FINISHED -->
                <!------------------->
                <tr style='display:none'>
                    <td class='reading-content'>Date Finished</td>
                    <td class='reading-content'>
                        <input type='date' name='DateFinished' />
                    </td>
                </tr>
                <!-- PROGRESS      -->
                <!------------------->
                <tr style='display:none'>
                    <td class='reading-content'>Progress (in pages)</td>
                    <td class='reading-content'><input type='number' name='Progress' value='' /></td>
                </tr>
                <!-- LIST PRICE    -->
                <!------------------->
                <tr class='stop-reading'>
                    <td>List Price</td>
                    <td><input type='number' name='ListPrice' placeholder=10.99 min='0.0' step='0.01' /></td>
                </tr>
                <!-- COMMENTS      -->
                <!------------------->
                <tr>
                    <td>Comments</td>
                    <td><textarea class='form-control' name='Comments' placeholder='Any thoughts on it so far?'></textarea></td>
                </tr>
                <!-- ISBN          -->
                <!------------------->
                <tr>
                    <td>ISBN</td>
                    <td><input type='text' name='ISBN' maxlength='13' size='13' value='' /></td>
                </tr>
            </tbody>
            <tfoot>
                <tr id='button-row'>
                    <td id='button-cell' colspan='2' width='100%'>
                        <button type='submit' onclick='attachReadingToPost()' class='linne-btn linne-btn-submit save'>Create New Book</button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>

<!------------------------------------------------>
<!--FIGURING OUT COLLAPSIBLE AREA - DELETE LATER-->
<!------------------------------------------------>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script> 
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<!--underscore library include-->
<script src="https://underscorejs.org/underscore-min.js"></script>

<!--include the createBook.js file-->
<script src='../client/createBook.js'></script>

<!--include the base64 & tagContainer Javascript helper functions-->
<!-- <script src='../client/res/inc/base64.js'></script> THIS ONE DOESN"T SEEM TO BE NEEDED ANYMORE--> 
<script src='../client/res/inc/tagContainer.js'></script>


<!------------------------------------------------>
<!--END FIGURING OUT COLLAPSIBLE AREA           -->
<!------------------------------------------------>

<?php

// footer
include_once "layoutFooter.php";
?>