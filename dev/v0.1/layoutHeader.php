<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        layoutHeader.php
// Dir:         /layoutHeader.php
// Desc:        Provide templating for the Header UI
//-----------------------------------------------------------------------------
*/
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    
    <!--this php variable is set in each page-->
    <title><?php echo $pageTitle; ?></title>
    
    <!--latest compiled and minified bootstrap-->
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' /> 
    
    <!--Biblian CSS-->
    <link rel='stylesheet' type='text/css' href='stylesheet.css' media='screen'/>
    
    <link href='https://fonts.googleapis.com/css?family=Ubuntu&display=swap' rel='stylesheet'>    
    
    
</head>    
<body>
    <!--generic container-->
    <div class='container'>
        <?php
        // show the page header
        // There's a horizontal rule established in CSS with the rule "border-bottom: 1px solid #eee;"
        // the closing </div> tag is in readTemplate.php
        echo "<div class='page-header'>
            <h1>{$pageTitle}</h1>";
        
        ?>
        <!--</div>, </body> , and </html> intentionally left out--they're in the layoutFooter.php file-->
