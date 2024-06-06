<?php

if($_SERVER["HTTP_HOST"] == "localhost:8002"){
header("Location: Inscription.php");
}else{
    echo $_SERVER["HTTP_HOST"];
    //;
}

