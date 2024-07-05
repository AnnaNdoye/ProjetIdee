<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../html/Connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connexion = new mysqli($host, $user, $password, $database);

if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

if (isset($_GET['id'])) {
    $id_idee = $_GET['id'];

    // Démarrer une transaction
    $connexion->begin_transaction();

    try 
    {
        // Supprimer les likes associés aux commentaires de l'idée
        $query = "DELETE FROM LikeCommentaire WHERE commentaire_id IN (SELECT id_commentaire FROM Commentaire WHERE idee_id = ?)";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_idee);
        $stmt->execute();

        // Supprimer les commentaires associés à l'idée
        $query = "DELETE FROM Commentaire WHERE idee_id = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_idee);
        $stmt->execute();

        // Supprimer les fichiers associés à l'idée
        $query = "DELETE FROM Fichier WHERE idee_id = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_idee);
        $stmt->execute();

        // Supprimer les likes associés à l'idée
        $query = "DELETE FROM LikeIdee WHERE idee_id = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_idee);
        $stmt->execute();

        // Enfin, supprimer l'idée elle-même
        $query = "DELETE FROM Idee WHERE id_idee = ?";
        $stmt = $connexion->prepare($query);
        $stmt->bind_param("i", $id_idee);
        $stmt->execute();

        // Valider la transaction
        $connexion->commit();
        header("Location: ../../html/idee/AccueilIdee.php");
        exit();
    } 
    catch (Exception $e) 
    {
        // En cas d'erreur, annuler la transaction
        $connexion->rollback();
        die("Erreur lors de la suppression de l'idée: " . $e->getMessage());
    }
} else {
    header("Location: ../../html/idee/AccueilIdee.php");
}

$connexion->close();
