<?php
    // simple file that serves as a refresh point for 
    // a hacky way to implement the POST/REDIRECT/GET pattern
    // need to refactor code later to have a better MVC architecture
    header("Location: {$_SERVER['HTTP_REFERER']}");
?>