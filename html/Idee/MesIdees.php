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

// Récupérer l'ID de l'utilisateur connecté
$employe_id = $_SESSION['user_id'];

// Récupérer les idées de l'utilisateur depuis la base de données
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
           categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    WHERE idee.employe_id = $employe_id";
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
    <title>Mes Idées</title>
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .header {
            background-color: #333;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1, .header h3 {
            margin: 0;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .search-bar {
            flex-grow: 1;
            margin: 0 10px;
        }

        .search-bar input {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-bar button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .navigation a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #007bff;
        }

        .navigation a:hover {
            background-color: #0056b3;
        }

        .connect_entete a, .profil a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .connect_entete a:hover, .profil a:hover {
            color: #ccc;
        }

        .footer {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
            margin-top: 20px;
        }

        .idea {
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .idea:hover {
            transform: translateY(-5px);
        }

        .idea h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
        }

        .idea p {
            margin-bottom: 10px;
            color: #555;
        }

        .idea .status-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-soumis .status-circle {
            background-color: #F39C12;
        }

        .status-approuve .status-circle {
            background-color: #27AE60;
        }

        .status-rejete .status-circle {
            background-color: #E74C3C;
        }

        .status-implemente .status-circle {
            background-color: #3498DB;
        }

        .idea a {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .idea a:hover {
            background-color: #0056b3;
        }

        .idea i {
            margin-right: 5px;
        }

        .ideas {
            width: 100%;
        }

        .footer-left, .footer-right {
            margin: 0;
            padding: 5px;
        }

        .footer-left a {
            color: white;
            text-decoration: none;
        }

        .footer-left a:hover {
            color: #ccc;
        }

        .footer-right {
            margin-left: auto;
        }

        @media screen and (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                margin: 10px 0;
            }

            .navigation a {
                margin-top: 10px;
            }

            .ideas {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
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
    <h1>Mes Idées</h1>
    <div class="ideas">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="idea">
                <h2><?php echo $row['titre']; ?></h2>
                <p><?php echo $row['contenu_idee']; ?></p>
                <p><strong>Catégorie:</strong> <?php echo $row['nom_categorie']; ?></p>
                <?php if ($row['nom_fichier']) : ?>
                    <p><strong>Fichier:</strong> <a href="data:<?php echo $row['type']; ?>;base64,<?php echo base64_encode($row['contenu_fichier']); ?>" target="_blank"><?php echo $row['nom_fichier']; ?></a></p>
                <?php endif; ?>
                <p><strong>Date de création:</strong> <?php echo $row['date_creation']; ?></p>
                <p><strong>Date de modification:</strong> <?php echo $row['date_modification']; ?></p>
                <p class="status-<?php echo strtolower($row['statut']); ?>">
                    <span class="status-circle"></span>
                    <strong>Statut:</strong> <?php echo $row['statut']; ?>
                </p>
                <a href="editeridee.php?id=<?php echo $row['id_idee']; ?>"><i class="fas fa-edit"></i> Éditer</a>
                <a href="supprimeridee.php?id=<?php echo $row['id_idee']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette idée?');"><i class="fas fa-trash"></i> Supprimer</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="espace"></div>
<div class="footer">
    <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
    <h4 class="footer-right">© Orange/Juin2024</h4>
</div>
</body>
</html>

<?php
mysqli_close($connexion);
?>
