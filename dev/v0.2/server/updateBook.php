<?php 
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        readTemplate.php
// Dir:         /reatTemplate.php
// Desc:        Contains template for reading and searching results of the 
//              Biblian DB.
// TODO         Find 'CHANGE'
//              --make it so delete function doesn't have to got through JS to add ID to the POST.  I just
//                need to figure out how to call a PHP method from the button click.
// OPTIMIZE     Search for 'OPTIMIZE'
// INCLUDES:    ../client/res/inc/tagContainer.js
//              ../client/updateBook.js
//---------------------------------------------------------------------------*/

//INCLUDES
include_once './objects/books.php';
include_once './objects/subjects.php';
include_once './res/inc/database.php';
include_once './res/inc/helperFunctions.php';

$ID = isset($_GET['id']) ? $_GET['id'] : die('ERROR: missing ID.');
  
//SET UP DB CONN
$database = new Database();
$db = $database->getConn();

$book = new Book($db);
$subject = new Subject($db);

// set page header
$pageTitle = 'Update Book';

// set stylesheet
$stylesheet = '../client/updateBook.css';

include_once 'layoutHeader.php';

$lastSubjectID = null;

// NAV BUTTON BACK TO INDEX.PHP
echo "<div class='right-button-margin'>";
    echo "<a href='index.php' class='btn btn-primary pull-right'>";
    echo "<span class='glyphicon glyphicon-book'></span> Biblian Home";
    echo "</a>";
echo "</div>";

//Set id of book to be retrieved
$book->ID = $ID;

// NEED TO GET SUBJECT ID
//$subject->ID = $ID;

// read details of book to update
$book->readOne();
?>


<!--'POST CODE goes here
//-----------------------------------------------------------------------------
// POST OPERATION CODING FOR UPDATE LOGIC
// this 'if' checks if a POST exists in the browswer (if an data's been sent)
// PRobably will be using PUT instead as that's for update ops 
//---------------------------------------------------------------------------->
<?php

// There a specific 'title' check here becaues this code will run if its just 'if($_POST)' and
// the delete function sends anything to the server
if($_POST['Title']){
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
    $book->SubjectID    = $_POST['subjectID'];          //only used for INSERT of BookSubject row
    $subject->Name      = $_POST['subject_name'];       //only used for INSERT of custom subject
    $book->Reading      = $_POST['Reading'];
    //TEST
    $changed            = $_POST['subject_changed'];
    // though there is a "$book->SubjectIDs" which is distinct from $book->SubjectID, its only used for
    // the UPDATE page and fetching the pre-existing subjects for a book.  Therefore its not part of $_POST

    $deleteOnly = false;
    
    // ------------------------------------------------------------------------
    // UPDATE THE BOOK
    // ------------------------------------------------------------------------
    if($book->update($book->ID)){
        // ----------------------------
        // SUBJECT SECTION OF POST
        // ----------------------------
        if($changed){
            $subjectsToAttach = null;

            // 'W10=' is Base64 for empty square brackets '[]'
            debug_to_console('subject_Name: ' . $_POST['subject_name']);
            if ($_POST['subject_name'] !== 'W10='){
                $subjectsToAttach = $subject->sortUserInput();
            }
            else{
                $deleteOnly = true;
            }
            
            // if successful delete - if no subjects, this is case of removing all subjects
            // from book without attaching any new ones.
            if ($subject->deleteAllBookSubjectEntries($book->ID)){


                // check to see if there are any subjects to attach or not.
                if($subjectsToAttach !== null && $subjectsToAttach !== []){
                    
                    // create bookSubject records
                    if ($subject->createBookSubject($book->ID, $subjectsToAttach)){
                        echo "<div class='alert alert-success'>Book Updated & Book Subject Record Updated</div>";
                    }
                }
                if($deleteOnly){
                    echo "<div class='alert alert-success'>Successfully removed attached subjects</div>";
                }
                $book->readOne();
            }
        } // END if $changed
    } // END $book->update() check
    //if fails, tell user of his utter failure
    else{
        echo "<div class='alert alert-danger'>Update Failed.  Contact the Powers That Be.</div>";   
    }
}// END IF $_POST 
if($_POST['bookId']){
    $bookIdToDelete = $_POST['bookId'];
    if($book->deleteThisBook($bookIdToDelete)){
        header('Location:./index.php');
    }
}
?>

<!---------------------------------------------------------------------------->
<!-- DELETE BUTTON                                                          -->
<!---------------------------------------------------------------------------->
<form method='POST' id='deleteForm' name='deleteForm' action=''>
    <button id='deleteBtn' book-id='<?php echo $ID ?>' onclick='JavaScript:return deleteThisBook();'>
        <span class='glyphicon glyphicon-trash'></span>
    </button>
</form>

<!---------------------------------------------------------------------------->
<!-- MAIN FORM                                                              -->
<!---------------------------------------------------------------------------->
<form id='mainForm' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id={$ID}");?>" method="post">
    <!--This is a dummy submit button to eliminate the default behavior of 'Enter' key automatically sending the POST-->
    <button type="submit" disabled style="display: none" aria-hidden="true"></button>
    <table class='table table-hover table-responsive table-bordered'>
        <!--Reading-->
        <?php
        if ($book->Reading == true){ 
            echo "<tr class='start-reading'>";
                echo "<td>Started Reading?</td>";
                echo "<td><input id='reading-box' checked=true type='checkbox'></td>";
            echo "</tr>";    
        }else{
            echo "<tr class='start-reading'>";
                echo "<td>Started Reading?</td>";
                echo "<td><input id='reading-box' type='checkbox'></td>";
            echo "</tr>";
        }
        
        ?>
        <!--Title-->
        <tr>
            <td>Title</td>
            <td><input type='text' class='form-control' name='Title' maxlength='512' required value='<?php echo $book->Title; ?>'></td>
        </tr>
        <tr>
            <td>Subject(s)</td>
            <td>
            
            <input class='form-control' id='subject-input' list='datalistTestData' name='datalistTest' data-id='<?php echo $book->SubjectIDs; ?>' data-value='<?php echo $book->SubjectName; ?>'/>
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
        
        <!-- AUTHOR LAST   -->
        <tr>
            <td>Author Last</td>
            <td><input type='text' class='form-control' name='LastName' value='STAND IN FOR AUTHOR' /></td>
        </tr>
        <!-- AUTHOR FIRST  -->
        <tr>
            <td>Author First</td>
            <td><input type='text' class='form-control' name='FirstName' value='STAND IN FOR AUTHOR' /></td>
        </tr>
        
                        <!-- AUTHOR MIDDLE 01 -->
                        <!---------------------->
                        <tr>
                            <td>Author Middle 01</td>
                            <td><input type='text' class='form-control' name='MiddleName01' value='STAND IN FOR AUTHOR' /></td>
                        </tr>
                        <!-- AUTHOR MIDDLE 02 -->
                        <!---------------------->
                        <tr>
                            <td>Author Middle 02</td>
                            <td><input type='text' class='form-control' name='MiddleName02' value='STAND IN FOR AUTHOR' /></td>
                        </tr>
                        <!-- AUTHOR SUFFIX -->
                        <!------------------->
                        <tr>
                            <td>Author Suffix</td>
                            <td><input type='text' class='form-control' name='Suffix' placeholder='Jr., Sr., III., Esq., Ph.D, etc' value='STAND IN FOR AUTHOR' /></td>
                        </tr>
        <!--Rating-->
        <tr>
            <td>Rating</td>
            <td><input type='number' name='Rating' value='<?php echo $book->Rating; ?>' class='form-control' placeholder='(between 0.0 and 5.0 Stars)' min='0.0' max='5.0' step='0.1'></td>
        </tr>
        <!--PageCount-->
        <tr>
            <td>Page Count</td>
            <td><input type='number' name='PageCount' value='<?php echo $book->PageCount; ?>' class='form-control'></td>
        </tr>
        <!--DateAcquired-->
        <tr>
            <td>Date Acquired</td>
            <td><input type='date' name='DateAcquired' value='<?php echo $book->DateAcquired; ?>' class='form-control'></td>
        </tr>
        <!--DateStarted-->
        <tr>
            <td>Date Started</td>
            <td><input type='date' name='DateStarted' value='<?php echo $book->DateStarted; ?>' class='form-control'></td>
        </tr>
        <!--DateFinished-->
        <tr>
            <td>Date Finished</td>
            <td><input type='date' name='DateFinished' value='<?php echo $book->DateFinished; ?>' class='form-control'></td>
        </tr>
        <!--Progress-->
        <tr>
            <td>Progress</td>
            <td><input type='number' name='Progress' value='<?php echo $book->Progress; ?>' class='form-control'></td>
        </tr>
        <!--ListPrice-->
        <tr>
            <td>List Price</td>
            <td><input type='number' name='ListPrice' value='<?php echo $book->ListPrice; ?>' class='form-control' placeholder=10.99 min='0.0' step='0.01'></td>
        </tr>
        <!--ISBN-->
        <tr>
            <td>ISBN</td>
            <td><input type='text' name='ISBN' maxlength='13' size='13' value='<?php echo $book->ISBN; ?>' class='form-control'></td>
        </tr>
        <!--Comments-->
        <tr>
            <td>Comments</td>
            <td><textarea class='form-control' name='Comments' placeholder='Any thoughts on it so far?'><?php echo $book->Comments; ?></textarea></td>
        </tr>
        
        <tr>
            <td></td>
            <td>
                <button type="submit" onclick='attachReadingToPost()' class="btn btn-primary">Update</button>
            </td>
        </tr>
    </table>
</form>

<?php

include_once 'layoutFooter.php';
?>

<script src="https://underscorejs.org/underscore-min.js"></script>

<!--include the base64 & tagContainer Javascript helper functions-->
<!-- <script src='../client/res/inc/base64.js'></script> THIS ONE DOESN"T SEEM TO BE NEEDED ANYMORE--> 
<script src='../client/res/inc/tagContainer.js'></script>
<!--include the updateBook.js file-->
<script src='../client/updateBook.js'></script>

