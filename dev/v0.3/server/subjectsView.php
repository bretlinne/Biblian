<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        subjectsView.php
// Dir:         /subjectsView.php
// Desc:        Basic CRUD functions for dealing with the subjects table
// INCLUDES:    ./res/inc/core.php
//              ./res/inc/database.php
//              ./objects/subjects.php
//              ./objects/books.php
//              ./res/inc/helperFunctions.php
// Which functions on here are AJAX?
// --update subject name
// --delete
// --add new
// I don't think I have any standard form-submit actions on this page
//---------------------------------------------------------------------------*/

//-----------------------------------------------------------------------------
// INCLUDES & DEFINITIONS
//-----------------------------------------------------------------------------
// page given in URL parameter, default page is one
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// core.php holds Pagination variables
include_once './res/inc/helperFunctions.php';
include_once './res/inc/core.php';
// include database and object files
include_once './res/inc/database.php';
include_once './objects/subjects.php';
include_once './objects/books.php';

// set number of records per page
$recordsPerPage = 50;
 
// calculate for the query LIMIT clause
$fromRecordNum = ($recordsPerPage * $page) - $recordsPerPage;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// set page header
$pageTitle = "Biblian Subjects";
$view = 'subject';         //$view values: 'create', 'read', 'readingList', 'update', 'subject'

// set page stylesheet
$stylesheet = '../client/subjectsView.css';

include_once "./layoutHeader.php";

// Instantiate DB Class
$database = new Database();
$db = $database->getConn();

// Instantiate object classes
$subject = new Subject($db);

//Query Books
$stmt = $subject->readAllPaging($fromRecordNum, $recordsPerPage);
// $stmt = $subject->readAll();


//specify page where we paginate
$pageURL = 'subjectsView.php?';

// This line is required for readTemplate.php to work right.  
// Otherwise it won't display any entries from the DB
$totalRows = $subject->countAll();

include_once 'paging.php';

// --------------------------------
// CREATE NAV BUTTON
// --------------------------------
echo "<div class='nav'>";
    echo "<a href='./index.php' class='btn btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-book'></span> Biblian Home";
    echo "</a>";
echo "</div>"; // END NAV


echo "</div>"; // END Page Header

// ------------------------------------------------------------
// SUCCESS / FAIL ALERT NOTIFICATION
// ------------------------------------------------------------
echo "<div id='notification' class='alert'></div>";

// ------------------------------------------------------------
// SEARCH & CREATE 
// ------------------------------------------------------------
echo "<div id='containerSearchAndCreate'>";
    // CREATE NEW SUBJECT INPUT
    // ----------------------------------------
    echo "<div id='containerCreate'>";
        echo "<div id='labelSubject'>Add New Subject?</div>";
        echo "<input id='inputSubject' type='text' class='form-control' name='Name' maxlength='32' autofocus/>";
        echo "<button class='btn btn-primary' onclick='createSubject(this)' id='buttonSubjectCreate'><span class='glyphicon glyphicon-plus'></span></button>";
    echo "</div>";

    // // SEARCH 
    // // ----------------------------------------
    // echo "<div id='searchContainer'>";
    //     echo "<form id='formSearch' role='search' action='search.php'>";
    //         $searchValue=isset($searchTerm) ? "value='{$searchTerm}'" : "";
    //         echo "<input id='inputSearch' type='text' placeholder='Search Subject name...' name='s' id='searchTerm' required {$searchValue} />";
    //         echo "<button class='btn btn-primary' id='search-btn' type='submit'><i class='glyphicon glyphicon-search'></i></button>";
    //     echo "</form>";
    // echo "</div>";

echo "</div>"; // END containerSearchAndCreate

// ------------------------------------------------------------
// READ PORTION
// ------------------------------------------------------------
echo "<div id='editPrompt'>Click in field to edit subject name...</div>";
echo "<div id='headReadContainer' row-count='$totalRows'>";
    //Display Library Contents if they exist
    $count = 0;
    $doOnce = false;
    $subjectList = '';
    // ----------------------------------------
    // START LEFT CONTAINER
    // ----------------------------------------
    echo "<div id='readContainerLeft'>";
    // this if just checks to see if there are any subject entries for the
    // case of an empty subjects table.
    if($totalRows > 0){
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            
            if($count < ceil($totalRows / 2)){
                
                echo "<div class='headContainerSubject'>";
                    echo "<input type='text' class='containerSubject' oninput='updateSubject(this)' onfocus='focusOnInput(this)' onfocusout='loseFocusInput(this)' master-name={$SubjectName} data-id={$SubjectID} value={$SubjectName}></input>";
                    echo "<button class='btn btn-primary buttonDelete' onclick='deleteSubject(this)'><span class='glyphicon glyphicon-trash'></span></button>";
                echo "</div>"; // END HEAD CONTAINER SUBJECT
                
                $subjectList .= $SubjectName . ",";
                if(($count + 1) == ceil($totalRows / 2)){
                    echo "</div>";  // END readContainerLeft div    
                }
            }else{
                if(! $doOnce){
                    // ----------------------------------------
                    // START RIGHT CONTAINER
                    // ----------------------------------------
                    echo "<div id='readContainerRight'>";
                    $doOnce = true;
                }
                echo "<div class='headContainerSubject'>";
                    echo "<input type='text' class='containerSubject' oninput='updateSubject(this)' onfocus='focusOnInput(this)' onfocusout='loseFocusInput(this)' master-name={$SubjectName} data-id={$SubjectID} value={$SubjectName}></input>";
                    echo "<button class='btn btn-primary buttonDelete' onclick='deleteSubject(this)'><span class='glyphicon glyphicon-trash'></span></button>";
                echo "</div>"; // END HEAD CONTAINER SUBJECT   
                $subjectList .= $SubjectName . ",";
            }
            $count++;
        }
        $subjectList = rtrim($subjectList, ',');
        echo "</div>";  // END readContainerRight div
    }
echo "</div>";  // END headReadContainer div
echo "<div id='subjectList' data-value='$subjectList' style='display:none'></div>";

// set page footer
include_once "layoutFooter.php";
?>
<script src='../client/res/inc/fade.js'></script>
<script src='../client/subjectsView.js'></script>
