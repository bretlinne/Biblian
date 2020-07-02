<?php
    // simple file that serves as a refresh point for 
    // a hacky way to implement the POST/REDIRECT/GET pattern
    // need to refactor code later to have a better MVC architecture
    
    $path = $_SERVER['HTTP_REFERER'];
    $url = explode('?', $path);

    $url2 = $_SERVER['HTTP_REFERER'];
    
    $firstParam = false;

    if($_GET['msg']){
        $firstParam = true;
        $respBounce = $_GET['msg'];    
//        $path .= '?bouncedMsg=' . $respBounce;
        $url2 = null;
        $url2 = $url[0];
        $url2 .= '?bouncedMsg=' . $respBounce;
    }

    if($_GET['type']){
        $typeBounce = $_GET['type'];    
//        $path .= '&bouncedType=' . $typeBounce;
        $url2 .= '&bouncedType=' . $typeBounce;
    }

    if($_GET['id']){
        $id = $_GET['id'];
        if($firstParam === false){
            $url2 .= '?id=' . $id;    
        }else{
            $url2 .= '&id=' . $id;    
        }
    }
//header("Location:./res/inc/clearReload.php\?msg=$resp");
    header("Location:$url2");
    
?>
