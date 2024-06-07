<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connexion = mysqli_connect($host, $user, $password, $database);

if ($connexion->connect_error) 
{
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}