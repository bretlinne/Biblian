<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN  
// File:        readingList.php
// Dir          /server/readingList.php
// Desc:        Generate the HTML from server for a reading list page 
//              Handle the logic server side for DB conns needed for this
// INCLUDES:    ./res/inc/core.php';
//              ./res/inc/database.php'
//              ./objects/books.php
//              ./objects/subjects.php
//              ./res/inc/helperFunctions.php 
// TODO:        search for 'CHANGE'
// OPTIMIZE     Search for 'OPTIMIZE'
//-----------------------------------------------------------------------------
*/

include_once './res/inc/core.php';
include_once './res/inc/database.php';
include_once './objects/books.php';
include_once './objects/subjects.php';
include_once './res/inc/helperFunctions.php';
$path = $_SERVER['SERVER_NAME'];
debug_to_console('path: '. $path);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConn();

$book = new Book($db);
$subject = new Subject($db);

$pageURL = 'readingList.php?';

// ------------------------------------------------------------
// PAGING SETUP
// ------------------------------------------------------------
// page given in URL parameter, default page is one
$page = isset($_GET['page']) ? $_GET['page'] : 1;
 
// set number of records per page
$recordsPerPage = 5;
 
// calculate for the query LIMIT clause
$fromRecordNum = ($recordsPerPage * $page) - $recordsPerPage;

// This line is required for readTemplate.php to work right.  
// Otherwise it won't display any entries from the DB
// implement $book->countReadingList()
$totalRows = $book->countReadingList();

$pageTitle = 'My Reading List';
$view = 'readingList';  //$view values: 'create', 'read', 'readingList', 'update'

$stylesheet = '../client/readingList.css';

include_once './layoutHeaderReadingList.php';
include_once './paging.php';

// ----------------------------------------------------------------------------
// DATABASE ACCESS
// ----------------------------------------------------------------------------
$stmt = $book->getBooksOnList($fromRecordNum, $recordsPerPage);
$booksNotOnList = $book->getBooksNotOnList();
$allBooks = $book->readTitles();
$respType = null;
$resp = null;

if ($_POST){
    if ($_POST['mode'] === 'addBook') {
        $book->ID        = $_POST['ID'];
        debug_to_console('ID in POST: ' . $book->ID);
        //UPDATE READING - SET TO TRUE
        if($book->updateReading($book->ID, 1)){
            $respType = 'success';
            $resp = 'Added to List';   
            $stmt = $book->getBooksOnList($fromRecordNum, $recordsPerPage);
        }else{
            $respType = 'failure';
            $resp = "Add Failed!";  
        }
        debug_to_console('add called');
    }

    if ($_POST['mode'] === 'removeBooks'){
        // CAST THE bookIds to a PHP array (a JS array apparently isn't the same)
        // $bookIds        = (array)$_POST['bookIds'];
        $bookIds        = $_POST['bookIds'];
        $titles         = $_POST['titles'];
        
        // if on success of delete, reload all books from DB to refresh the UI
        if($book->updateReadingMultiple($bookIds, $titles, 0)){
            $resp = 'Removed book from List';
            $respType = 'success';
        }else{
            $resp = 'Removal Failed!';
            $respType = 'failure';
        }    
        $stmt = $book->getBooksOnList($fromRecordNum, $recordsPerPage);
        debug_to_console('remove called');
    }

    if ($_POST['mode'] === 'updateRating'){
        $rating = $_POST['modalRatingValue'];
        $bookId = $_POST['bookId'];

        if($book->updateRating($bookId, $rating)){
            $respType = 'success';
            $resp = 'Rating Updated';
            $stmt = $book->getBooksOnList($fromRecordNum, $recordsPerPage);
        }
        else{
            $respType = 'failure';
            $resp = 'Rating Update Failed!';
        }
        debug_to_console('update rating called');
    }
    
    // --------------------------------------------------------
    // CALL PAGE RELOAD AFTER DB CALL
    // --------------------------------------------------------
    if($resp){
        $params = "msg=". urlencode($resp)."&type=". urlencode($respType);
        header("Location:./res/inc/clearReload.php?$params");
    }
}


// ------------------------------------------------------------
// CREATE NAV BUTTONS
// ------------------------------------------------------------

// NAV TO HOME
//-----------------------------
echo "<div class='nav'>";
    // HOME BUTTON
    // ----------------------------
    echo "<a href='index.php' class='btn btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-book'></span> Biblian Home";
    echo "</a>";

    // ADD TO LIST BUTTON
    //-----------------------------
    // should pop up a modal to take the input.  Modal should have a drop-down for existing books
    // ...could also function as a create (advanced implementation)
    echo "<button class='btn btn-primary' id='btnOpenAddToListModal'>";    
        echo "<span class='glyphicon glyphicon-plus'></span>";
    echo " Add Book to List</button>";

    // REMOVE BUTTON
    // ----------------------------
    echo "<form id='removeForm' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>";
        echo "<button class='btn btn-primary' id='btnRemoveFromList'>";    
            echo "<span class='glyphicon glyphicon-minus'></span>";
        echo " Remove Book from List</button>";
    echo "</form>";

    // NAV TO CREATE A BOOK
    //-----------------------------
    // PROB SHOULD BE A SIDEBAR THING

echo "</div>"; // END NAV


// ----------------------------------------------------------------------------
//
// MODALS 
//
// ----------------------------------------------------------------------------
echo "<div class='modal' id='addToListModal'>";
    echo "<div class='modalContent'>";
    echo "<span class='close' id='closeAddToListModel'>&times;</span>";

    // --------------------------------
    // MODAL CONTENT - ADD BOOK TO LIST
    // --------------------------------
    echo "<div class='modalHeader'>Add Book to List";
    echo "</div>";

        echo "<div class='modalBody'>";
            // start form
            // -------------
            echo "<form id='modalForm' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>";
                echo "<div id='modalBookSelect'>";
                    echo "<b>Choose Book:</b>";
                echo "</div>";

                echo "<input class='form-control' id='book-input' list='booksInLibrary' name='booksInLibrary'/>";
                
                // LOAD DATA LIST WITH THE BOOK TITLES
                echo "<datalist id='booksInLibrary'>";

                while($row_book = $booksNotOnList->fetch(PDO::FETCH_ASSOC)){
                    extract($row_book);
                    if(strpos($Title,"'")){
                        $Title = str_replace("'", "&#39;", $Title);
                    }
                    
                    echo "<option id-value='{$ID}' value='{$Title}'></option>";
                }
                echo "</datalist>";
                
                // MODAL BUTTONS
                echo "<div class='modalBtnContainer'>";
                    echo "<button class='modalCancel' id='modalAddBookCancel'>";
                        echo "<span class='glyphicon glyphicon-ban-circle'></span>";
                    echo " Cancel</button>";
                    echo "<button class='modalAccept' id='modalAddBookAccept' type='submit'>";
                        echo "<span class='glyphicon glyphicon-ok'></span>";
                    echo " Add Book</button>";
                echo "</div>";
            echo "</form>"; // END FORM
        echo "</div>"; // END MODAL BODY
    echo "</div>";  // END MODAL CONTENT
echo "</div>";  // END MODAL


// --------------------------------
// MODAL CONTENT - STAR RATING
// --------------------------------
echo "<div class='modal' id='updateRatingModal' bookId='' active=false>";
    echo "<div class='modalContent'>";
    echo "<span class='close' id='closeRatingModel'>&times;</span>";

    echo "<div class='modalHeader'>Update Rating";
    echo "</div>";

        echo "<div class='modalBody'>";
            echo "<form id='modalStarForm' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>";
                echo "<div id='modalStarInput'>";
                    echo "<b>How did you like this book?</b>";
                echo "</div>";

                // ------------
                // STARS
                // ------------
                echo "<div id='modalStarContainer'>";
                    for ($i=0; $i < 5; $i++) { 
                        echo "<div class='modalStarEmpty'></div>";
                    }
                echo "</div>";
                
                // MODAL BUTTONS
                echo "<div class='modalBtnContainer'>";
                    echo "<button class='modalCancel' id='modalRatingCancel'>";
                        echo "<span class='glyphicon glyphicon-ban-circle'></span>";
                    echo " Cancel</button>";
                    echo "<button class='modalAccept' id='modalRatingAccept' type='submit'>";
                        echo "<span class='glyphicon glyphicon-ok'></span>";
                    echo " Add Book</button>";
                echo "</div>";
            echo "</form>"; // END FORM
        echo "</div>"; // END MODAL BODY
    echo "</div>";  // END MODAL CONTENT
echo "</div>";  // END MODAL

// ------------------------------------------------------------
// SUCCESS / FAIL ALERT NOTIFICATION
// ------------------------------------------------------------
echo "<div id='notification' class='alert'></div>";

echo "</div>"; // END PAGE-HEADER

//---------------------------------------------------------------------------------------------
//
// BOOK LISTING
//
//---------------------------------------------------------------------------------------------
if($totalRows > 0){
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        echo "<div class='bookContainer'>";
        
        // ------------------------------------------------------------------------------
        // START selectBoxAndAllOthers - contains Select Box, Title, author, rating
        // ------------------------------------------------------------------------------
        echo "<div class='selectBoxAndAllOthers'>";

        //-----------------------------
        // SELECT BOX
        //-----------------------------
        echo "<div class='selectBox'>";
            // echo "<input class='bookCheckBox' book-id=".$ID." type='checkbox'></input>";
            echo "<input class='bookCheckBox' book-id=".$ID." book-title=\"", $Title,"\" type='checkbox'></input>";
        echo "</div>";

        echo "<div class='titleAndStatusContainer'>";
            echo "<div class='titleStarsAuthSubs'>"; 
                //-----------------------------
                // TITLE
                //-----------------------------
                echo "<div class='titleContainer'>";
                    echo "<h2>{$Title}</h2>";
                echo "</div>";  // END titleContainer div
                echo "<div class='starsAuthorsSubs'>";
                    //-----------------------------
                    // STARS
                    //-----------------------------
                    echo "<div class='starsAuthors'>";
                        echo "<div class='stars'>";
                        
                            echo "<span class='starsModalLink'>";
                            // RATING
                            //-----------------------------
                            // Just display stars for the rating given for the book
                            if($Rating){
                                $starCount = 0;
                                for ($i = 0; $i < 5; $i++){
                                    // place half star - only if Rating == 0.5
                                    if ($Rating < 1 && $starCount < 1){
                                        echo "<div class='starImgHalf'></div>";
                                        $starCount++;
                                    }
                                    // place whole star
                                    else if($starCount < floor($Rating)){
                                        echo "<div class='starImg'></div>";
                                        $starCount++;
                                    }
                                    else if (($Rating - $starCount) > 0){
                                        echo "<div class='starImgHalf'></div>";
                                        $starCount++;
                                    }
                                    // place empty star
                                    else{
                                        echo "<div class='starImgEmpty'></div>";
                                    }
                                }
                            }
                            else{
                                echo "<div class='starsModalLinkStandin'>Not Yet Rated</div>";
                            }
                            echo "</span>";  // END starsModalLink div
                        echo "</div>"; // END stars
                        //-----------------------------
                        // AUTHORS & SUBJECTS
                        //-----------------------------
                        echo "<div class='authors'>";
                            // AUTHOR(s)
                            echo "<div><p><i>Authors go here</i></p></div>";
                        echo "</div>"; // END authors
                        
                        echo "<div class='subjects'>{$SubjectName}";
                        echo "</div>"; // END subjects
                    echo "</div>"; // END starsAuthors
                    //-----------------------------
                    // PROGRESS CONTAINER
                    // ----------------------------
                    echo "<div class='progress-container'>";
                        echo "<div class='progress-label'>Reading Progress";
                        echo "</div>";
                        
                        // echo "<input class='progress-input' type='range' min=0 max={$PageCount} step=1 value={$Progress} />";
                        // echo "<div class='progress-bar-label' data-value={$Progress}></div>";
                        echo "<progress class='progressBar' max={$PageCount} value={$Progress}></progress>";
                        echo "<div class='progress-pageCount-container'>";
                            echo "<div class='pageCount-zero-label'>0</div>";
                            echo "<div class='pageCount-label' value={$PageCount}>{$PageCount}</div>";
                        echo "</div>";

                        echo "<div class='progress-outputContainer'>";
                            // OUTPUT FIELD
                            // --------------------------------
                            echo "<input type='number' class='progress-output' onkeyup='updateProgress(this)' min=0 max={$PageCount} value={$Progress}></input>"; //onfocusout='updateProgress(this)'

                            echo "<button class='btn progress-plus1' onclick='incrementProgress(this, 1)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 1";
                            echo "</button>";
                            
                            echo "<button class='btn progress-plus5' onclick='incrementProgress(this, 5)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 5";
                            echo "</button>";

                            echo "<button class='btn progress-plus10' onclick='incrementProgress(this, 10)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 10";
                            echo "</button>";
                            
                            // CHEVRON VERSION - DO LATER IN UI IMPROVEMENT
                            // CHANGE
                            // echo "<button class='btn-chevron'>";
                            //     echo "<span class='glyphicon glyphicon-plus'></span> 10";
                            // echo "</button>";

                            echo "<div class='dateStartedLabel' value='{$DateStarted}'>Started: {$DateStarted}</div>";
                        echo "</div>";  // END progress-outputContainer
                    echo "</div>"; // END progress-container
                    // -----------------------------------------------------
                    // ALTERNATIVE PROGRESS-CONTAINER FOR WHEN NO PAGECOUNT
                    // -----------------------------------------------------
                    echo "<input class='progress-standin' type='number' min=1 
                        placeholder='Add a page count to track progress.'>";
                    echo "</input>";

                    // BUTTON
                    // ------------------------
                    echo "<button onclick='updatePageCount(this)' class='progress-standin-btn'>";
                    echo "<span class='glyphicon glyphicon-cloud-upload'></span> Update</button>";

                    // -----------------------------------------------------
                    // ALTERNATIVE PROGRESS-CONTAINER FOR WHEN FINISHED
                    // -----------------------------------------------------
                    echo "<div class='progress-container-finished'>";
                        echo "<div class='progress-label'>Reading Progress</div>";

                        echo "<progress class='progressBar' max={$PageCount} value={$PageCount}></progress>";
                        echo "<div class='progress-pageCount-container'>";
                            echo "<div class='pageCount-zero-label'>0</div>";
                            echo "<div class='pageCount-label' value={$PageCount}>{$PageCount}</div>";
                        echo "</div>";
    
                        echo "<div class='progress-outputContainer'>";
                            // OUTPUT FIELD
                            // --------------------------------
                            echo "<input type='number' class='progress-output' onkeyup='updateProgress(this)' min=0 max={$PageCount} value={$Progress}></input>"; //onfocusout='updateProgress(this)'

                            echo "<button class='btn progress-plus1' onclick='incrementProgress(this, 1)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 1";
                            echo "</button>";
                            
                            echo "<button class='btn progress-plus5' onclick='incrementProgress(this, 5)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 5";
                            echo "</button>";

                            echo "<button class='btn progress-plus10' onclick='incrementProgress(this, 10)'>";
                                echo "<span class='glyphicon glyphicon-plus'></span> 10";
                            echo "</button>";
                            
                            // CHEVRON VERSION - DO LATER IN UI IMPROVEMENT
                            // CHANGE
                            // echo "<button class='btn-chevron'>";
                            //     echo "<span class='glyphicon glyphicon-plus'></span> 10";
                            // echo "</button>";

                            echo "<div class='dateStartedLabel' value='{$DateStarted}'>Started: {$DateStarted}</div>";
                        echo "</div>";  // END progress-outputContainer
                
                    echo "</div>"; // END progress-container-finished

                echo "</div>"; // END starsAuthorsSubs
            echo "</div>";  // END titleStarsAuthSubs
            
            // ------------------------------------------------------------------------------
            // START STATUS & UPDATE CONTAINER - contains "Finished" / "Not Read" / "Reading" Graphics
            // ------------------------------------------------------------------------------
            echo "<div class='statAndUpdateContainer'>";
                // ---------------------------
                // UI LINK to UPDATE PAGE
                // ---------------------------
                echo "<div class='navToUpdatePageContainer'>";
                    echo "<button class='navToUpdatePage' book-id=".$ID." onclick='navToUpdate(this);'><span class='glyphicon glyphicon-pencil' style='font-size: 24px; padding-bottom: 10px;'></span>Update";
                    echo "</button>";
                echo "</div>"; // END navToUpdatePageContainer


                // ---------------------------
                // FINISHED BUTTON
                // ---------------------------
                // THe logic for the status of books on reading list is simpler.  If a book's on the list and 
                // NOT Finished, it's status == Reading.  If its Finished, status == Finished
                echo "<div class='statContainer'>";
                    // IF FINISHED
                    if ($DateFinished){ 
                        echo "<button class='statusFin' statusValue='finished' onclick='statusContainerAction(this)'>";
                    }
                    // IF NOT FINISHED
                    else{
                        echo "<button class='statusReading' statusValue='reading' onclick='statusContainerAction(this)'>";    
                    }
                    
                    echo "</button>";
                    
                    echo "<div class='dateFinishedLabel'>{$DateFinished}";
                    echo "</div>";
                    
                echo "</div>"; // END statusContainer div
            echo "</div>";  // END statusAndUpdateContainer div
        
        echo "</div>";  // END titleAndStatusContainer div
        echo "</div>";  // END selectBoxAndAllOthers
        // ------------------------------------------------------------------------------
        // START ADVANCED CONTAINER - contains All other data for book
        // ------------------------------------------------------------------------------
        echo "<div class='advContainer advanced-toggle' onclick='slideToggle(this, 200)'><b>Book Details  <span id='details-icon' class='glyphicon glyphicon-chevron-down'></span></b>";
                echo "<div class='advanced' style='display:none'>";
                    echo "<div class='dateStarted' value={$DateStarted}>Date Started: {$DateStarted}</div>";
                    echo "<div>Date Acquired: {$DateAcquired}</div>";
                    echo "<div>Date Finished: {$DateFinished}</div>";
                    echo "<div>Progress: {$Progress}</div>";
                    echo "<div>ListPrice: {$ListPrice}</div>";
                    echo "<div>ISBN: {$ISBN}</div>";
                    echo "<div>Comments: {$Comments}</div>";
                echo "</div>";
            echo "<div class='endOfBookSpan'></div>";
        echo "</div>";  // END advContainer div
        
        echo "</div>";  // END bookContainer div
        

        //echo "<div class='starSlider'></div";
        //echo "<div class='slidecontainer'>";
        //    echo "<input type='range' min='0' max='5' value='0' class='starSlider' id='myRange'>";
        //    echo "<p>Value: <span id='demo'></span></p>";
        //echo "</div>";    
        
            
        //echo "<div class='ratingSlider'>";
        //    echo "<input type='range min=0 max=5 value=0 class='starSlider' step='0.5'/>";
        //echo "</div>";
  
  
        // REMOVE BUTTON
        //-----------------------------
       /*
        echo "<form method='POST' id='deleteForm' name='deleteForm' action=''>";
            echo "<button id='deleteSelectedBtn' onclick='JavaScript:return deleteSelected();'>";
                echo "<span class='glyphicon glyphicon-minus'></span>";
            echo "</button>";
        echo "</form>";
        */
    } // ORIG END WHILE
} 

//-----------------------------------------------------------------------------
// set page footer
include_once "layoutFooter.php"; 
?>
<script src='../client/readingList.js'></script>
<script src='../client/res/inc/collapsible.js'></script>