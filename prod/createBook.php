<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN  
// File:        createBook.php - REDO USING BiblianDB_S
// Dir:         /createBook.php
// Desc:        Construct UI for new book data entry.
//              Construct the INSERT command from the fields in the page and 
//              the POST. 
//              Give UI feedback on success or failure of the INSERT
// TODO:        --Author
//              --check that if a blank is POSTed for Subject, the system does
//                NOT try to create a row in Subjects table for a blank entry.
//              --when a custom value is entered for Subject, make sure the form
//                works differently and POSTs the subject_name INSTEAD of 
//                subject_id.  Right now, createBookSubject is being called 
//                and receives the Name of the subject to be inserted, which fails.
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
include_once 'layoutHeader.php';

$lastSubjectID = null;

// NAV Button to INDEX.PHP
echo "<div class='right-button-margin'>";
    echo "<a href='index.php' class='btn btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-book'></span> Biblian Home";
    echo "</a>";
echo "</div>";
?>

<?php


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
    ?>

    <script>
        function toggleField(hideObj,showObj){
            hideObj.disabled=true;        
            hideObj.style.display='none';
            showObj.disabled=false;   
            showObj.style.display='inline';
            showObj.focus();
        }

        /* COMMENTED OUT JQUERY
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
<!--
    <script>
        const tagContainer = document.querySelector('.linne-tag-container')
        function createTag(label){
            const div = document.createElement('div');
            div.setAttribute('class', 'linne-tag');
            const btn = document.createElement('button');
            
            const span = document.createElement('span');
            span.innerHTML = label;
            const closeBtn = document.createElement('i');
            closeBtn.setAttribute('class', 'close');
            closeBtn.innerHTML = 'close';
            
            div.appendChild(span);
            div.appendChild(closeBtn);
            return div;
        }
        tagContainer.prepend(createTag('javascript'));
    </script>
<!--
<div class='linne-container'>
        <div class='linne-tag-container'>
            <div class='linne-tag'>
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">Foo&times;</span>
                </button>
            </div>
        </div>  
    </div>
-->



        <?php
        //read book subjects from DB
        $stmt = $subject->readAll();
        ?>
    

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

<!--include the base64 Javascript helper functions-->
<script src='./res/inc/base64.js'></script>
<!--<script src='/bib/res/inc/base64.js'></script>-->

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


<!------------------------------------------------>
<!-- Javascript for the Tag Container           -->
<!------------------------------------------------>
<script>
    // this is JS reference to the tag-container we're working with
    const tagContainer = document.querySelector('.linne-tag-container');

    // create a new input
    const tagInput = document.querySelector('.linne-tag-container input');
    
    // create an alias for the datalist
    const subjectDatalistInput = document.querySelector('#subject-input');
    
    // this array is so that the backend or API calls have some place in memory to 
    // put stored tags from the DB.
    var tags = [];
    
    // tagsToSubmit is to collect the tags from the TagContainer and pass it to the DB. 
    // The best way to do this might be to treat the tag data as structured JSON and make this an object
    // which gets passed to the server as a string of base64 data
    var tagsToSubmit = {};
    var encodedTags = null;
    
    // This variable may be unnecessary - DELETE later if so
    var subjectTagCount = 0;
    
    // the tagJSON passed in is the value in the middle of <span></span> elems
    // like 'Javascript', 'foo', 'Computer History'
    function createTag(tagJSON){
        const div = document.createElement('div');
        div.setAttribute('class', 'tag');
        const span = document.createElement('span');
        span.innerHTML = tagJSON.name;
        // the <i></i> elems are being used as the close button for some reason
        const closeBtn = document.createElement('i');
        closeBtn.setAttribute('class', 'material-icons');
        closeBtn.setAttribute('data-item', tagJSON.name);
        
        // this adds the ID of the subject to the tag's metadata
        // if the subject is new (not in DB) then the value is 'new' and is parsed out server-side
        closeBtn.setAttribute('data-id', tagJSON.id);
        
        closeBtn.innerHTML = 'close';

        div.appendChild(span);
        div.appendChild(closeBtn);
        return div;
    }
    
    function resetTags(){
        document.querySelectorAll('.tag').forEach(function(tag){
            tag.parentElement.removeChild(tag);
        })
    }

    function addTags() {
        resetTags();
        // loop through the tags object and for each item, create a tag in the container and prepend it to the front of the container
        tags.slice().reverse().forEach(function(tag) {
            const input = createTag(tag);
            tagContainer.prepend(input);
        })
    }

    // this looks for when User is finished entering data in the tag-container
    /*
    tagInput.addEventListener('keyup', function(e){
        // first check if the values entered are not empty strings
        if(tagInput.value != ''){
            // only if there's value data, turn into a tag
            if(e.key === 'Enter'){
                tags.push(tagInput.value);
                addTags();
                tagInput.value='';
                tagsToSubmit = gatherTagsForSubmit();
                console.log('tagData: ', tagsToSubmit);
                encodedTags = base64_encode(tagsToSubmit);
                console.log('base64 tagData: ', encodedTags);
                
                // Call appendToPost() function and add the subject tags in Base64 coding
                appendToPost(encodedTags);
            }
        }
    })
    */
    
    // --------------------------------------
    // Scan for ENTER in Tag Container entry
    // --------------------------------------
    subjectDatalistInput.addEventListener('keyup', function(e){
        if(subjectDatalistInput.value != ''){
            if(e.key === 'Enter'){
                
                //WORKING PUSH TO TAGS
                //tags.push(subjectDatalistInput.value);
                
                // What was I planning here? I need to detect whether a tag is coming from the DB or from custom entry.
                var sel = document.getElementById('subject-input');
                var newSubs = {};
                var found = false;
                console.log('sel.value: ', sel.value);
                
                // Loop through the list of subjects and see if the entered subject is one of them
                // if so, add it to the tags object with its ID.  If not add it to tags object with a 'new' value
                for (var i=0; i < datalistTestData.options.length; i++){
                    if(datalistTestData.options[i].value == sel.value){
                        //console.log('subject data value: ', datalistTestData.options[i].getAttribute('data-value'));
                        
                        // push the value in the datalist input into the tags object
                        existingSubjectData = {name: datalistTestData.options[i].getAttribute('value'), id: datalistTestData.options[i].getAttribute('data-value')}
                        if (_.findWhere(tags, existingSubjectData) == null) {
                            tags.push(existingSubjectData);
                        }
                        
                        //console.log('existingSubjectData:', existingSubjectData);
                        found = true;
                    }
                }
                
                // add new subjects to a separate array object to encode and push to the Subject->Create()
                if(found == false){
                    newSubjectData = {name: sel.value, id: 'new'};
                    if (_.findWhere(tags, newSubjectData) == null){
                        tags.push(newSubjectData);
                    }
                    //console.log('newSubjectData: ', newSubjectData);
                }

                addTags();
                subjectDatalistInput.value = '';
                tagsToSubmit = gatherTagsForSubmit();
                encodedTags = btoa(JSON.stringify(tagsToSubmit));
                
                //encodedTags = base64_encode(tagsToSubmit);
                //console.log('tagsToSubmit: ', encodedTags);
                appendToPost(encodedTags);    
            }
        }    
    })
    
    // -------------------------------
    // Remove Tags from Tag Container
    // -------------------------------
    // this looks for clicks on the close X's for the tags and eliminates them from the tag-container
    document.addEventListener('click', function(e){
        if(e.target.tagName === 'I'){
            const value = e.target.getAttribute('data-item');

            
            // The tag now has meta-data associated with it for 'data-id' and
            // its set to either a SubjectID or 'new'
            // The indexOf () function returns -1 if the value is never found.
            // the problem is that this is no longer just an array of values, but a Javascript Object and this has to be 
            // converted to a string to use JSON.parse() and then using the array may function to get the correct index.
            var item = JSON.parse(JSON.stringify(tags));
            var index = item.map(function(d) { return d['name']; }).indexOf(value);
            //const index = tags.name.indexOf(value) + 1;
            
            tags = [... tags.slice(0, index), ... tags.slice(index + 1)];
            addTags();
            
            //subjectTagCount = subjectTagCount - 1;
        }
    })
    
    // -------------------------------
    // Gather Tags For Submit
    // -------------------------------
    function gatherTagsForSubmit(){
        var tagsToSubmit = [];
        var i = 0;  // accumulator
        
        document.querySelectorAll('.tag').forEach(function(tag){
            //str = tag.textContent;
            //str = str.slice(0, -5);
            name = tag.getElementsByTagName('i')[0].getAttribute('data-item');
            id = tag.getElementsByTagName('i')[0].getAttribute('data-id');
            tagData = {name: name, id: id};
            //console.log('tag Name: ', name, '; tag ID: ', id);
            
            tagsToSubmit.push(tagData);
            console.log('tagsToSubmit: ', tagsToSubmit);
            i += 1;
        })  
        subjectTagCount = i;
        
        return tagsToSubmit;
    }
    
    // --------------------------------------
    // Add the tags to the POST
    // --------------------------------------
    function appendToPost(tagsToSubmit){
        const form = document.querySelector('#mainForm');
        const tagInput = document.createElement('input');
        const tagCount = document.createElement('input');
        tagInput.setAttribute('name', 'subject_name');
        tagInput.setAttribute('type', 'hidden');
        tagInput.setAttribute('value', tagsToSubmit);
        tagCount.setAttribute('name', 'subject_count');
        tagCount.setAttribute('type', 'hidden');
        tagCount.setAttribute('value', subjectTagCount);
        form.appendChild(tagInput);
        form.appendChild(tagCount);
    }

    function attachReadingToPost(){
        var readingValue = document.querySelector('#reading-box').checked;
        if(readingValue){
            readingValue = 1;
        }else{
            readingValue = 0;
        }
        const form = document.querySelector('#mainForm');
        const readingInputToAttach = document.createElement('input');
        
        readingInputToAttach.setAttribute('name', 'Reading');
        readingInputToAttach.setAttribute('type', 'hidden');
        readingInputToAttach.setAttribute('value', readingValue);
        
        form.appendChild(readingInputToAttach);
    }
</script>