<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = mysqli_connect($host, $user, $password, $database);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

// Récupérer les idées publiques depuis la base de données
$query = "SELECT id_idee, titre, contenu_idee, date_creation, date_modification, statut FROM idee WHERE est_publique = 1";
$result = mysqli_query($connexion, $query);

if (!$result) {
    die("Erreur lors de la requête: " . mysqli_error($connexion));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idées Publiques</title>
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/IdeePP.css">
    <style>
    </style>
</head>
<body>
<div class="header">
        <div class="logo" onclick="location.href='../accueil.html'">
            <img src="../../static/img/icon.png" alt="Logo">
            <div>
                <h1>Orange</h1>
                <h3><span class="for-ideas">for ideas</span></h3>
            </div>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Rechercher des idées publiques...">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="navigation">
            <strong>
                <a href="NouvelleIdee.php"><i class="fa fa-plus-circle"></i> Nouvelle idée</a>
            </strong>
        </div>
        <div class="connect_entete">
            <a href="../connexion.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
        <div class="profil">
            <a href="Profil.php">
                <i class="fas fa-user-circle"></i>
                <strong>Profil</strong>
            </a>
        </div>
    </div>

<div class="container">
    <h1>Idées Publiques</h1>
    <div class="ideas">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="idea">
                <h2>Titre: <?php echo $row['titre']; ?></h2>
                <p>Contenu: <?php echo $row['contenu_idee']; ?></p>
                <p>Date de création: <?php echo $row['date_creation']; ?></p>
                <p>Date de modification: <?php echo $row['date_modification']; ?></p>
                <p>Statut: <?php echo $row['statut']; ?></p>
                
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script></script>
</body>
</html>

<?php
mysqli_close($connexion);
?>
