<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        paging.php
// Dir:         /server/paging.php
// Desc:        Functions for creating pagination of data output from Biblian
// INCLUDES:    NONE
// INVOCATIONS: index.php
//              readTemplate.php (toward end of html)
//              subjectsView.php
//---------------------------------------------------------------------------*/

echo "<div class='pages-gantt'>";
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

// GANTT CHART LINK - ONLY SHOWN ON HOME PAGE
// ------------------------------------------
// $view is set on the calling php file, such as index.php or readingList.php.  Its not so much realated to the
// exact file, but the 'view' function of the calling file.  For now, index.php calls paging.php, layoutHeader.php, 
// layoutFooter.php, readTemplate.php.  All these have the FUNCTION of being the 'read' view for the web app. 
// possible $view values: 'create', 'read', 'readingList', 'update'
if ($view == 'read'){
    echo "<div class='gantt'>";
        echo "<a href='https://docs.google.com/spreadsheets/d/1Y_w0t-RUGoz8xgz2xlpvpzduak_sq-8ZgmynjVCoZGQ/edit?usp=sharing'>View Project GANTT Chart</a>";
    echo "</div>";
}

echo "</div>";
?>


