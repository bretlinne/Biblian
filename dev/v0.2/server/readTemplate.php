<meta name="viewport" content="width=device-width, initial-scale=1">
<?php 
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        readTemplate.php
// Dir:         /reatTemplate.php
// Desc:        Contains template for reading and searching results of the 
//              Biblian DB.
// INCLUDES:    ./paging.php
//              ./res/inc/clearReload.php
//              ./createBook.php
//              ./updateBook.php?id=${bookId}
// TODO:        DELETE DOESN"T WORK
//              READ ONE DOESN"T WORK           DONE
//-----------------------------------------------------------------------------
*/
//-------------------------------------
// POST for server-side functions
// --JS needs to add data to POST in 
//   order for the server to 'see' it.
//-------------------------------------
if($_POST){
    $bookIds            = $_POST['bookIds'];

    // if on success of delete, reload all books from DB to refresh the UI
    if($book->deleteSelectedBooks($bookIds)){
        $stmt = $book->readAll($fromRecordNum, $recordsPerPage);
        echo "<div class='alert alert-success'>Delete from Database Successful</div>";
        header('Location:./res/inc/clearReload.php');
    }
}

// this is the last section of the page-header DIV started on the layoutHeader.php
    // this form when clicked, calls search.php

    // --------------------------------
    // SEARCH BAR - NOT WORKING
    // TODO: V0.7.5 - get search working
    // --------------------------------
    debug_to_console('V0.7.5: Get Search Bar Implmented.  Its commented outon readTemplate.php');
    // echo "<form role='search' action='search.php'>";
    //         echo "<div class='input-group col-md-3 pull-left margin-right-1em'>";
    //         $searchValue=isset($searchTerm) ? "value='{$searchTerm}'" : "";
    //         echo "<input type='text' class='form-control' placeholder='Enter Book Title...' name='s' id='searchTerm' required {$searchValue} />";
    //         echo "<div class='input-group-btn'>";
    //             echo "<button class='btn btn-primary' id='search-btn' type='submit'><i class='glyphicon glyphicon-search'></i></button>";
    //         echo "</div>";
    //     echo "</div>";
    // echo "</form>";

    // --------------------------------
    // CREATE NAV BUTTON
    // --------------------------------
    echo "<div class='nav'>";
        echo "<a href='./createBook.php' class='btn btn-primary pull-right'>";
            echo "<span class='glyphicon glyphicon-plus'></span> Add New Book";
        echo "</a>";
    
        echo "<a href='readingList.php' id='btnNavToReadingList' class='btn btn-primary pull-right'>";
            echo "<span class='glyphicon glyphicon-th-list'></span> My Reading List";
        echo "</a>";
    

        // --------------------------------
        // DELETE BUTTON
        // --------------------------------
        echo "<form method='POST' id='deleteForm' name='deleteForm' action=''>";
            echo "<button class='btn btn-primary' id='btnDeleteSelected' onclick='JavaScript:return deleteSelected();'>";
                echo "<span class='glyphicon glyphicon-trash'></span>";
            echo "</button>";
        echo "</form>";
        
    echo "</div>"; // END NAV
echo "</div>"; // END page-header


//Display Library Contents if they exist
if($totalRows > 0){
    //swapStatusState($statAssigned, $statFinColorsHover);

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
            echo "<input class='bookCheckBox' book-id=".$ID." type='checkbox'></input>";
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
                        
                        // RATING
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
                            echo "<div><p><i>Not Yet Rated</i></p></div>";
                        }
                        echo "</div>"; // END stars
                        //-----------------------------
                        // AUTHORS
                        //-----------------------------
                        echo "<div class='authors'>";
                            // AUTHOR(s)
                            echo "<div><p><i>Authors go here</i></p></div>";
                        echo "</div>"; // END authors
                    echo "</div>"; // END starsAuthors
                    //-----------------------------
                    // SUBJECT CONTAINER
                    // ----------------------------
                    echo "<div class='subjects'>{$SubjectName}";
                    echo "</div>"; // END subjects
                echo "</div>"; // END starsAuthorsSubs
            echo "</div>";  // END titleStarsAuthSubs
            
            // ------------------------------------------------------------------------------
            // START STATUS CONTAINER - contains "Finished" / "Not Read" / "Reading" Graphics
            // ------------------------------------------------------------------------------
            echo "<div class='statContainer'>";
                // ---------------------------
                // UI LINK to UPDATE PAGE
                // ---------------------------
                echo "<div class='navToUpdatePageContainer'>";
                echo "<button class='navToUpdatePage' book-id=".$ID." onclick='navToUpdate(this);'>Edit this Book";
                echo "</button></div>";
                // ---------------------------
                // READING LIST TOGGLE SWITCH
                // ---------------------------
                echo "<div class='toggleContainer'>"; 
                //echo "<div class='toggleStateImage'";
                if ($Reading){
                    //echo "state='on'></div>";
                    echo "<label class='readingListSwitch'><input type='checkbox' checked='true' onclick='readingListSwitchAction(this)'><span class='slider round'></span></label>";
                }
                else{
                    //echo "state='off'></div>";
                    echo "<label class='readingListSwitch'><input type='checkbox' onclick='readingListSwitchAction(this)'><span class='slider round'></span></label>";
                }
                echo"</div>";
                
                echo "<div id='readingListSwitchSpacer'></div>";
                // ---------------------------
                // FINISHED BUTTON
                // ---------------------------
                // THe logic for the status of books on in main library list is complicated.
                
                // IF FINISHED
                if ($DateFinished){ 
                    echo "<button class='statusFin' statusValue='finished' onclick='statusContainerAction(this)'>";
                }
                // IF NOT FINISHED
                else{

                    // AND ON READING LIST
                    if ($Reading){
                        echo "<button class='statusReading' statusValue='reading' onclick='statusContainerAction(this)'>";    
                    } else{
                        if ($Progress === 0 || $Progress === null){
                            echo "<button class='statusUnread' statusValue='unread' onclick='statusContainerAction(this)'>";        
                        } else if ($Progress > 0){
                            echo "<button class='statusStarted' statusValue='started' onclick='statusContainerAction(this)'>";
                        } else{
                            echo "<button class='statusFin' statusValue='finished' onclick='statusContainerAction(this)'>";
                        }
                    }
                }
                
            echo "</button></div>";  // END statusContainer div
        
        echo "</div>";  // END titleAndStatusContainer div
        echo "</div>";  // END selectBoxAndAllOthers
        // ------------------------------------------------------------------------------
        // START ADVANCED CONTAINER - contains All other data for book
        // ------------------------------------------------------------------------------
        echo "<div class='advContainer advanced-toggle' onclick='slideToggle(this, 200)'><b>Book Details  <span id='details-icon' class='glyphicon glyphicon-chevron-down'></span></b>";
                echo "<div class='advanced' style='display:none'>Details:";
                    echo "<div>Date Started: {$DateStarted}</div>";
                    echo "<div>Date Acquired: {$DateAcquired}</div>";
                    echo "<div>Date Finished: {$DateFinished}</div>";
                    echo "<div>Progress: {$Progress}</div>";
                    echo "<div>PageCount: {$PageCount}</div>";
                    echo "<div>ListPrice: {$ListPrice}</div>";
                    echo "<div>ISBN: {$ISBN}</div>";
                    echo "<div>Comments: {$Comments}</div>";
                echo "</div>";
            echo "<div class='endOfBookSpan'></div>";
        echo "</div>";  // END advContainer div
        
        echo "</div>";  // END bookContainer div
        

        //echo "<div class='starSlider'></div";
        //echo "<div class='slidecontainer'>";
        //    echo "<input type='range' min='0' max='5' value='0' class='slider' id='myRange'>";
        //    echo "<p>Value: <span id='demo'></span></p>";
        //echo "</div>";    
        
            
        //echo "<div class='ratingSlider'>";
        //    echo "<input type='range min=0 max=5 value=0 class='slider' step='0.5'/>";
        //echo "</div>";
        
    } // END WHILE
    
    //echo '</table>';
    
    //paging buttons
    include_once 'paging.php';
    
} // END IF($totalRows > 0)
//There are no books in Biblian Yet
else{
    echo '<div class="alert alert-info">Your Biblian is Empty.</div>';
}
?>

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script> 
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src='../client/readTemplate.js'></script>