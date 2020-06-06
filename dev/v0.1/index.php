<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        index.php
// Dir:         /index.php
// Desc:        Basic read of the library as it stands, and nav to other pages.
//-----------------------------------------------------------------------------
*/

//-----------------------------------------------------------------------------
// INCLUDES & DEFINITIONS
//-----------------------------------------------------------------------------
// page given in URL parameter, default page is one
$page = isset($_GET['page']) ? $_GET['page'] : 1;
 
// set number of records per page
$recordsPerPage = 5;
 
// calculate for the query LIMIT clause
$fromRecordNum = ($recordsPerPage * $page) - $recordsPerPage;

// core.php holds Pagination variables
include_once __DIR__.'/res/inc/core.php';
// include database and object files
require_once __DIR__.'/res/inc/database.php';
include_once 'objects/subjects.php';
include_once 'objects/books.php';
include_once 'res/inc/helperFunctions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// set page header
$pageTitle = "Your Biblian Library";
include_once "layoutHeader.php";

// Instantiate DB Class
$database = new Database();
$db = $database->getConn();

// Instantiate object classes
$book = new Book($db);
$subject = new Subject($db);

//Query Books
$stmt = $book->readAll($fromRecordNum, $recordsPerPage);

//specify page where we paginate
$pageURL = 'index.php?';

// This line is required for readTemplate.php to work right.  
// Otherwise it won't display any entries from the DB
$totalRows = $book->countAll();

include_once 'paging.php';

// Display the Biblian Library
include_once 'readTemplate.php';

// set page footer
include_once "layoutFooter.php";
?>
