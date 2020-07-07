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

// instantiate the book & subject objects
$book = new Book($db);
$subject = new Subject($db);

//Set page headers
$pageTitle = 'Create New Book'; 
$view = 'create';   //$view values: 'create', 'read', 'readingList', 'update'

// set stylesheet
$stylesheet = '../client/createBook.css';

include_once 'layoutHeader.php';

$lastSubjectID = null;

$timezone = 'America/Los_Angeles';
date_default_timezone_set($timezone);
$today = date("Y-m-d");
$respType = null;
$resp = null;

// ------------------------------------------------------------
// LOOK FOR BOUNCED MSG FROM RELOAD PAGE AND DISPLAY
// ------------------------------------------------------------
if($_GET){
    $bouncedMsg = $_GET['bouncedMsg'];
    echo $bouncedMsg;
}
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
    
    // ------------------------------------------
    // CREATE BOOK
    // ------------------------------------------
    // IF THERE IS A PAGECOUNT AND NO PROGRESS, MAKE PROGRESS 0
    if(! $book->PageCount){
        $book->Progress = null;
    }else{
        if(! $book->Progress){
            $book->Progress = 0;
        }
        // IF THERE IS A PAGECOUNT AND PROGRESS
        else{
            if (! $book->DateStarted){
                $book->DateStarted = $today;
            }
        }
    }

    // debug_to_console('PageCount: ' . $_POST['PageCount']);
    // debug_to_console('Comments: ' . $_POST['Comments']);
    // debug_to_console('ListPrice: ' . $_POST['ListPrice']);
    // debug_to_console('Rating: ' . $_POST['Rating']);
    // debug_to_console('DateAcquired: ' . $_POST['DateAcquired']);
    // debug_to_console('Datestarted: ' . $_POST['Datestarted']);
    // debug_to_console('DateFinished: ' . $_POST['DateFinished']);
    // debug_to_console('subject_id: ' . $_POST['subject_id']);
    // debug_to_console('subject_name: ' . $_POST['subject_name']);
    // debug_to_console('ISBN: ' . $_POST['ISBN']);
    // debug_to_console('Reading: ' . $_POST['Reading']);
    if ($book->create()){
        
        $lastBookID = $book->getLastInsertID();
        $subjectsToAttach = null;
        // Check if there's a custom Subject entry(s)
        if ($_POST['subject_name']){
            // call the sortUserInput() function which finds all new subjects and creates them, and return
            // an array of IDs for all new subjects and old subjects
            $subjectsToAttach = $subject->sortUserInput();
            
        }
        if($subjectsToAttach !== null){
            if( $subject->createBookSubject($lastBookID, $subjectsToAttach)){
                $respType = 'success';
                $resp = 'Book Subject Record Created';
            }
            else{
                $respType = 'failure';
                $resp = 'Book Subject Creation Failed!';
            }
        }
        
        // UI Feedback for Book creation
        $respType = 'success';
        $resp = 'Book successfully added to Biblian';
    }
    //if $book->create() fails, tell user of his utter failure
    else{
        $respType = 'failure';
        $resp = 'Book Creation Failed...!';
    }

    // --------------------------------------------------------
    // CALL PAGE RELOAD AFTER DB CALL
    // --------------------------------------------------------
    if($resp){
        $params = "msg=". urlencode($resp)."&type=". urlencode($respType);
        // header("Location:./res/inc/clearReload.php?$params");
    }
} // END if $_POST check
if($_POST['Title']){
    debug_to_console($_POST);
    
}

/*    
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
*/

//read book subjects from DB
$stmt = $subject->readAll();

//<!-- NAV Button to INDEX.PHP -->
echo "<div class='right-button-margin'>";
    echo "<a href='index.php' class='btn btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-book'></span> Biblian Home";
    echo "</a>";
echo "</div>";

// ------------------------------------------------------------
// SUCCESS / FAIL ALERT NOTIFICATION
// ------------------------------------------------------------
echo "<div id='notification' class='alert'></div>";

// ------------------------------------------------------------
// TOP OF HTML FORM
// ------------------------------------------------------------
echo "<form id='mainForm' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>";
    //This is a dummy submit button to eliminate the default behavior of 'Enter' key automatically sending the POST-->    
    echo "<button type='submit' disabled style='display: none' aria-hidden='true'></button>";
    
    // --------------------------------------------------------
    // TITLE
    // --------------------------------------------------------
    echo "<div id='containerTitle'>";
        echo "<div id='labelTitle'>Title</div>";
        echo "<input id='inputTitle' type='text' class='form-control' name='Title' maxlength='512' autofocus required/>";
    echo "</div>";

    // ------------------------------------------------------------------------
    // AUTHOR
    // ------------------------------------------------------------------------
    echo "<div class='headContainerAuthor' id='authorElementId0' style='display: block'>";
        echo "<div class='headLabelAuthor'>Author</div>";
        echo "<div class='containerAuthor'>";
            echo "<div class='subcontainerAuthor'>";
                echo "<div class='subSubcontainerAuthorLast'>";
                    echo "<div class='labelAuthorLast'>Last Name</div>";
                    echo "<input class='inputAuthorLast' type='text' class='form-control' name='LastName0' value='Howard' />";
                echo "</div>";

                echo "<div class='subSubcontainerAuthorFirst'>";
                    echo "<div class='labelAuthorFirst'>First Name</div>";
                    echo "<input class='inputAuthorFirst' type='text' class='form-control' name='FirstName0' value='Moe' />";
                echo "</div>";
            echo "</div>"; // END AUTHOR SUBCONTAINER 

            echo "<p>*** INSERT TOGGLE FOR AUTHOR EXTRA HERE ***</p>";
            echo "<div class='subcontainerAuthorExtra'>";
                // AUTHOR MIDDLE 01
                // --------------------
                echo "<div class='subSubcontainerMiddle01'>";
                    echo "<div class='labelAuthorMiddle01'>Author Middle 01</div>";
                    echo "<input class='inputAuthorMiddle01' type='text' class='form-control' name='MiddleName01_0' />";
                echo "</div>";
                // AUTHOR MIDDLE 02
                // --------------------
                echo "<div class='subSubcontainerMiddle02'>";
                    echo "<div class='labelAuthorMiddle02'>Author Middle 02</div>";
                    echo "<input class='inputAuthorMiddle02' type='text' class='form-control' name='MiddleName02_0' />";
                echo "</div>";
                // AUTHOR SUFFIX 
                // --------------------
                echo "<div class='subSubcontainerSuffix'>";
                    echo "<div class='labelAuthorSuffix'>Author Suffix</div>";
                    echo "<input class='inputAuthorSuffix' type='text' class='form-control' name='Suffix0' placeholder='Jr., Sr., III., Esq., Ph.D, etc' value='' />";
                echo "</div>";
            echo "</div>";  // END AUTHOR EXTRA SUBCONTAINER
        echo "</div>"; // END AUTHOR CONTAINER    
    echo "</div>";  // END AUTHOR HEAD CONTAINER 

    // ADD AUTHOR ELEMENT
    echo "<div id='subcontainerAddAnotherAuthor'>";
        echo "<button id='btnAddAnotherAuthor' onclick='return false'><span class='glyphicon glyphicon-plus'></span>";
        echo "</button>";
    echo "</div>";

    // REMOVE AUTHOR ELEMENT
    echo "<div id='subcontainerRemoveAddedAuthor' style='display: none'>";
        echo "<button id='btnRemoveAddedAuthor' onclick='return false'><span class='glyphicon glyphicon-minus'></span>";
        echo "</button>";
    echo "</div>";

    
    // --------------------------------------------------------
    // RATING AND SUBJECT
    // --------------------------------------------------------
    echo "<div id='headContainerRatingSubject'>";
        // RATING
        // --------------------
        echo "<div id='containerRating'>";
            echo "<div id='labelRating'>Rating</div>";    
            echo "<input id='inputRating' type='number' class='form-control' name='Rating' placeholder='(between 0.0 and 5.0 Stars)' min='0.0' max='5.0' step='0.1' />";
        echo "</div>";

        echo "<div id='containerSubject'>";
            echo "<div id='labelSubject'>Subject</div>";    
            echo "<div id='subcontainerSubject'>";    
                echo "<input class='form-control' id='subject-input' list='datalistTestData' name='datalistTest'/>";
                echo "<datalist id='datalistTestData'>";
                    //read book subjects from DB
                    $stmt = $subject->readAll();

                    //put them in the datalist
                    while($row_subject = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row_subject);
                        echo "<option data-value='{$SubjectID}' value='{$SubjectName}'></option>";
                        //echo "<option value='{$SubjectID}'>{$SubjectName}</option>";
                    }
                echo "</datalist>";
                echo "<div class='linne-container'>";
                    echo "<div class='linne-tag-container' >";
                        echo "<input readonly='readonly'/>";
                    echo "</div>";
                echo "</div>";
            echo "</div>";  // END subContainer SUBJECT
        echo "</div>";  // END SUBJECT
    echo "</div>"; // END headContainerRatingSubject

    // --------------------------------------------------------
    // Advanced Create Inputs
    // --------------------------------------------------------
                               
    // --------------------------------------------------------
    // EtAl - PAGE COUNT; DATEACQUIRED; LIST PRICE; ISBN
    // --------------------------------------------------------
    echo "<button id='advancedToggle' class='advContainer advanced-toggle' onclick='return false'><b>Add Advanced Details  <span id='details-icon' class='glyphicon glyphicon-chevron-down'></span></b></button>";
    echo "<div id='headContainerEtAlAndComments' style='display:none'>";
        echo "<div id='containerEtAl'>";

            // DATE ACQUIRED
            // --------------------
            echo "<div id='containerDateAcquired'>";
                echo "<div id='labelDateAcquired'>Date Acquired</div>";
                echo "<input type='date' id='DateAcquired' name='DateAcquired' value='{$today}' /></td>";
            echo "</div>";
        
            // LIST PRICE
            // ----------------
            echo "<div class='stop-reading' id='containerListPrice'>";
                echo "<div id='labelListPrice'>List Price</div>";
                echo "<input id='inputListPrice' type='number' name='ListPrice' min=0.0 step='0.01' />";
            echo "</div>";

            // ISBN
            // ----------------
            echo "<div id='containerISBN'>";
                echo "<div id='labelISBN'>ISBN</div>";
                echo "<input id='inputISBN' type='text' name='ISBN' maxlength='13' size='13' value='' />";
            echo "</div>";

        echo "</div>"; // END headContainerEtAl

        // COMMENTS
        // ----------------
        echo "<div id='containerComments'>";
            echo "<div id='labelComments'>Comments</div>";
            echo "<textarea id='inputComments' class='form-control' name='Comments' placeholder='Any thoughts on it so far?' /></textarea>";
        echo "</div>"; // END COMMENTS
    echo "</div>"; // END headContainerEtAlAndComments

    // --------------------------------------------------------
    // STARTED READING CHECKBOX 
    // --------------------------------------------------------
    echo "<div id='containerReadingCheckBox'>";
        echo "<input id='reading-box' type='checkbox' onclick='slideToggleTargeted(headContainerStartReading, 200)' />";
        echo "<div id='labelReading'>Started Reading?</div>";
    echo "</div>";

    // ----------------------------------------
    // COLLAPSIBLE READING AND PROGRESS SECTION
    // ----------------------------------------
    echo "<div id='headContainerStartReading' style='display:none'>";
    // ------------------
    // Left column
    // ------------------      
        echo "<div id='containerReadingAndProgress'>";
            // ----------------
            // PAGE COUNT
            // ----------------
            echo "<div id='containerPageCount'>";
                echo "<div id='labelPageCount'>Page Count</div>";
                echo "<input type='number' id='PageCount' min=1 name='PageCount' value='' />";
            echo "</div>";
            
            // --------------------------------
            // PROGRESS BAR AREA
            // --------------------------------
            echo "<div id='containerProgressPlaceholder'>";
                echo "<div id='labelProgressPlaceholder'>Progress</div>";
                echo "<div type='number' id='progress-placeholder' name='Progress-placeholder'>Input PageCount first...</div>";
            echo "</div>";
            
            echo "<div id='containerProgress'>";
                echo "<div class='reading-content' id='labelProgress'>Progress</div>";    
                echo "<input class='reading-content' id='inputProgress' type='number' name='Progress' placeholder='Pages read...' min=0 value='' />";
            echo "</div>";
        
            // DATE STARTED
            // ----------------
            echo "<div class='reading-content' id='subcontainerDateStarted'>";
                echo "<div class='reading-content' id='labelDateStarted'>DateStarted</div>";
                echo "<input class='reading-content' id='inputDateStarted' type='date' name='DateStarted' />";
            echo "</div>";  // END CONTAINER READING CONTENT
            // DATE FINISHED
            // ----------------
            // echo "<div style='display:none'>";
            echo "<div id='subcontainerDateFinished'>";
                echo "<div class='reading-content' id='labelDateFinished'>DateFinished</div>";
                echo "<input class='reading-content' id='inputDateFinished' type='date' name='DateFinished' />";
            echo "</div>";
            
        echo "</div>"; // containerReadingAndProgress
    // ------------------
    // Right column
    // ------------------      
        // PROGRESS BAR
        // --------------------
        echo "<div id='containerProgressBar' style='display: none'>";
            echo "<progress id='progressBar' value='' max=''></progress>";
            echo "<div class='progress-pageCount-container'>";
                echo "<div id='pageCount-zero-label'>0</div>";
                echo "<div id='pageCount-label' value=''></div>";
            echo "</div>";
        echo "</div>";  // END containerProgressBar
    echo "</div>";  // END headContainerStartReading
        
        
    echo "<button type='submit' onclick='attachReadingToPost()' class='linne-btn linne-btn-submit save'>Create New Book</button>";
echo "</form>";

?>


<!------------------------------------------------>
<!--FIGURING OUT COLLAPSIBLE AREA - DELETE LATER-->
<!------------------------------------------------>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script> 
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<!--underscore library include-->
<script src="https://underscorejs.org/underscore-min.js"></script>

<!--include the createBook.js file-->
<script src='../client/res/inc/collapsible.js'></script>
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