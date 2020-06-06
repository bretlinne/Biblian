<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        createBook.php - ORIGINAL FILE
// Dir:         /createBook.php
// Desc:        Construct UI for new book data entry.
//              Construct the INSERT command from the fields in the page and 
//              the POST. 
//              Give UI feedback on success or failure of the INSERT
//-----------------------------------------------------------------------------
*/
include_once 'res/inc/database.php';
include_once 'objects/books.php';
include_once 'objects/subjects.php';
//include ('SQLFunctions.php');

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
include_once 'layoutHeader.php';

// button to index.php
echo "<div class='right-button-margin'>";
echo "<a href='index.php' class='btn btn-default pull-right'>Read Products</a>";
echo "</div>";
?>

<?php
function debug_to_console($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);

        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }

//this 'if' checks if a POST exists in the browswer (if an data's been sent)
if($_POST){
    $book->Title        = $_POST['Title'];
    $book->ISBN         = $_POST['ISBN'];
    $book->PageCount    = $_POST['PageCount'];
    $book->Comments     = $_POST['Comments'];
    $book->ListPrice    = $_POST['ListPrice'];
    $book->DateAcquired = $_POST['DateAcquired'];
    $book->DateStarted  = $_POST['DateStarted'];
    $book->DateFinished = $_POST['DateFinished'];
    $book->Progress     = $_POST['Progress'];
    $book->Rating       = $_POST['Rating'];
    
    //give user feedback that successful INSERT took place
    if ($book->create()){
        echo "<div class='alert alert-success'>Book successfully added to Biblian</div>";   
    }
    //if fails, tell user of his utter failure
    else{
        echo "<div class='alert alert-danger'>You have failed me Starscream!</div>";   
    }
}
?>


    <!------------------------------>
    <!--HTML for creating the book-->
    <!------------------------------>

    
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

      //DEBUG LINE - DELETE
      //echo "<script>alert({$book->DateAcquired})</script>";
    ?>

    <script>
        function toggleField(hideObj,showObj){
            hideObj.disabled=true;        
            hideObj.style.display='none';
            showObj.disabled=false;   
            showObj.style.display='inline';
            showObj.focus();
        }

        /*
        $(document).ready(function() {
        var date = new Date();

        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();

        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;

        var today = year + "-" + month + "-" + day;       
        $("#dateAcquired").attr("value", today);
    });
    */

    </script>
    <!--------------------------------------------------------------------------------->
            <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method='post'>
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
                            <td>Subject</td>
                            <td>
                            <!--Dropdown menu populated by Subject Table, but also has an option-->    
                            <!--to type in a custom value, which will be added to the subject table-->
                            <select class='form-control' name='subject_id'
                                    onchange="if(this.options[this.selectedIndex].value=='customOption'){
                                        toggleField(this,this.nextElementSibling);
                                        this.selectedIndex='0';}">
                            <option></option>
                            <option value="customOption">[type a custom value]</option>
                            <!--insert PHP here to make subject dropdown work-->
                            <?php
                                //read book subjects from DB
                                $stmt = $subject->read();
                            
                                //put them in dropdown menu
                                
                                while($row_subject = $stmt->fetch(PDO::FETCH_ASSOC)){
                                    extract($row_subject);
                                    echo "<option value='{$id}'>{$name}</option>";
                                }
                                echo '</select>';
                            ?>
                            <!--Alternate input field for custom subject value-->
                            <input class='form-control' name="subject_id" style="display:none;" disabled="disabled" 
                                onblur="if(this.value==''){toggleField(this,this.previousElementSibling);}">
                            </td>
                        </tr>
                    </thead>
<!-- ADVANCED      -->
<!------------------->                    
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
                                <input type='number' class='form-control' name='Rating' placeholder='(between 0.0 and 5.0 Stars)' min='0.0' max='5.0' step='.1' value='' />
                            </td>
                        </tr>
                        <!-- PAGE COUNT    -->
                        <!------------------->
                        <tr>
                            <td>Page Count</td>
                            <td><input type='number' name='PageCount' value='222' /></td>
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
                            <td><input type='number' name='ListPrice' placeholder=10.99 value=1.49 min='0.0' step='0.01' /></td>
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
                            <td><input type='text' name='ISBN' maxlength='13' size='13' value='0123456789abc' /></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr id='button-row'>
                            <td id='button-cell' colspan='2' width='100%'>
                                <button type='submit' class='linne-btn linne-btn-submit'>Create New Book</button>
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
  
<script>
    //toggle the advanced section
    $("#advanced-toggle").click(function() {
          $("#advanced").closest("tbody").toggle('fast');
    });           
    
    //deal with toggling the Currently Reading section
    $("#reading-box").change(function() {
    if($(this).prop('checked')) {
        $('tr.start-reading').nextUntil('tr.stop-reading').toggle('fast');
    } else {
        $('tr.start-reading').nextUntil('tr.stop-reading').toggle('fast');
    }
});
    
</script>
<!------------------------------------------------>
<!--END FIGURING OUT COLLAPSIBLE AREA           -->
<!------------------------------------------------>

<?php

// footer
include_once "layoutFooter.php";
?>