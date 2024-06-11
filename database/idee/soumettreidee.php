<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = mysqli_connect($host, $user, $password, $database);

// Vérifier la connexion
if ($connexion->connect_error) 
{
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

//Je récupère les valeur du formulaire avec POST
$titre = $_POST['titre'];
$contenu = $_POST['contenu'];
$categorie_id = $_POST['categorie_id'] ;
$visibilite = $_POST['visibilite'] === 'publique' ? 0 : 1;
$fichier = $_FILES['fichier'];
$statut = "Soumis";
// je définis le fuseau horaire
date_default_timezone_set('Africa/Dakar');
// Récupérer la date courante au format YYYY-MM-DD hh:mm:ss qui va correspondre à DateTime dans ma base de données
$dateCourante = date('Y-m-d H:i:s');


// Gérer l'upload du fichier
$target_dir = "uploads/";
$target_fichier = $target_dir . basename($fichier["name"]);//
//move_uploaded_file($fichier["tmp_name"], $target_fichier);

//$récupérer l'id de l'employé à récupérer après

$requete1 = "INSERT INTO idee (titre, contenu_idee, est_publique, date_creation, date_modification, categorie_id, statut) VALUES ('$titre', '$contenu', '$visibilite', '$dateCourante', '$dateCourante', '$categorie_id', '$statut')";
$resultat1 = mysqli_query($connexion,$requete1);

if ($requete1) 
{
    echo "Nouvelle idée créée avec succès";
} 
else 
{
    echo "Erreur: La requête a échoué. " . $requete1 . "<br>" . $connexion->error;
}

$connexion->close();
