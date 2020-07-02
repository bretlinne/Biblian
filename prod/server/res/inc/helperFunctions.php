<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        helperFunctions.php - REDO USING BiblianDB_S
// Dir:         /createBook.php
// Desc:        Construct UI for new book data entry.
//              Construct the INSERT command from the fields in the page and 
//              the POST. 
//              Give UI feedback on success or failure of the INSERT
//---------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------
// Function:    debug_to_console()        
//
// Desc:        DEBUGGING FUNCTION.
//              Print passed parameter to browser console.  
// Invocations: many places, not crucial to operation of Application
//              --index.php
//              --createBook.php
//              --books.php
//---------------------------------------------------------------------------*/
//$GLOBALS['debug'] = true;

function debug_to_console($data) {
    
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('[Debug]: " . $output . "' );</script>";
    
}

function sanitizeString($s) {
    // remove all non alpha-numeric characters
    $s = preg_replace("/[^A-Za-z0-9 ]/", '', $s);
    // trim excess spaces
    $s = preg_replace("/  +/", ' ', $s);
    return $s;
}
?>