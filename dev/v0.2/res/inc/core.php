<?php 
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        core.php
// Dir:         /core.php
// Desc:        Holds various configuration variables.
//              Has pagination settings
//---------------------------------------------------------------------------*/

// Page sent into URL parameter.  Default is 'one'
// What this is doing is querying IF a page has been sent into the URL routing
// statement.  If so, it leaves the assignment as is.  If not, it assigns '1'
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Set number of records per page
// CHANGE LATER - make modifyable by USER.  They should be able to choose how 
// many are shown on each page in some settings.
$recordsPerPage = 10;

//Calculate for query LIMIT clause
$fromRecordNum = ($recordsPerPage * $page) - $recordsPerPage;
?>