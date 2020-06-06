<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        paging.php
// Dir:         /paging.php
// Desc:        Functions for creating pagination of data output from Biblian
//---------------------------------------------------------------------------*/

echo "<ul class='pagination'>";

//Button for the 1st page
if($page > 1){
    echo "<li><a href='{$pageURL}' title='Go to first page.'>";
    echo "First";    
    echo "</a></li>";
    
} // end if($page > 1)

//      debug_to_console('page: '. $page);

// do ceiling op on the division of these
$totalPages = ceil($totalRows / $recordsPerPage);
//      debug_to_console('total pages: '. $totalPages);

// range of links to show
// WHAT's this for?
$range = 2;

//display links to "range of pages" and "current"
$initialNum = $page - $range;
//      debug_to_console('initial num: '. $initialNum);

// this value is to limit how many pagination buttons are shown b/w first and last.
// currently comes out to '4'.  This means the 'First' button is 1, there's a '2' 
// and '3', and 'Last' is number 4.  If this number comes out higher, then the 
// number of possible buttons increases.
$conditionLimitNum = ($page + $range) + 1;

//      debug_to_console('condition limit: '.$conditionLimitNum);

for($i = $initialNum; $i < $conditionLimitNum; $i++){
    //double check i is > 0 AND <= $totalPages
    if(($i > 0) && ($i <= $totalPages)){
        //current page
        if ($i == $page){
            echo "<li class='active'><a href=\"#\">$i <span class=\"sr-only\">(current)</span></a></li>";
        }
        //NOT current page
        else{
            echo "<li><a href='{$pageURL} page=$i'>$i </a></li>";
        }
    }
}

//Button for Last Page
if($page < $totalPages){
    echo "<li><a href='" .$pageURL. "page={$totalPages}' title='Last Page is {$totalPages}.'>";
    echo "Last";
    echo "</a></li>";
}

echo "</ul>";
?>


