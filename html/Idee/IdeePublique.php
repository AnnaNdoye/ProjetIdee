<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

// Créer une connexion
$connexion = mysqli_connect($host, $user, $password, $database);

// Vérifier la connexion
if (!$connexion) {
    die("Erreur lors de la connexion: " . mysqli_connect_error());
}

$search = isset($_GET['search']) ? $_GET['search'] : ''; 

$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, employe.photo_profil, employe.prenom, employe.nom,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee) AS like_count,
    (SELECT COUNT(*) FROM LikeIdee WHERE LikeIdee.idee_id = idee.id_idee AND LikeIdee.employe_id = ?) AS user_liked
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN employe ON idee.employe_id = employe.id_employe
    WHERE idee.est_publique = 1 AND idee.titre LIKE ?";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}
$like_search = '%' . $search . '%';
$stmt->bind_param('is', $_SESSION['user_id'], $like_search);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}
$result = $stmt->get_result();

if (!$result) {
    die("Erreur lors de la requête: " . $connexion->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idées Publiques</title>
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <link rel="stylesheet" href="../../static/css/IdeePP.css">
    <link rel="stylesheet" href="../../static/css/publique.css">
    <style>
        .search-bar form {
            display: flex;
            align-items: center;
            border-radius: 5px;
            overflow: hidden;
            margin-left: 20px;
            flex: 1;
            border: #FF6600 solid;
            outline: none;
        }

        form input {
            border: none;
            padding: 10px;
            outline: none;
            color: #000;
            width: 100%;
        }

        form button {
            background: #fff;
            border: none;
            color: #FF6600;
            padding: 10px 15px;
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
            background-color: #ccc;
        }

        .connect_entete a, .profil a {
            color: #ff6600;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .connect_entete a:hover, .profil a:hover {
            color: #000;
        }

        .idea {
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 900px;
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

        .enveloppe {
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            width: 100%;
        }

        @media screen and (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                margin: 10px 0;
                width: 100%;
            }

            .navigation a {
                margin-top: 10px;
            }

            .ideas {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 20px;
            }

            .form input {
                width: 150px;
            }

            .form input:focus {
                width: 200px;
            }
        }

        .like-container {
            display: flex;
            align-items: center;
        }

        .like-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 2rem;
            transition: transform 0.2s ease;
        }

        .like-button:focus {
            outline: none;
        }

        .thumb-icon {
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .like-button:hover .thumb-icon {
            color: #888;
            transform: scale(1.1);
        }

        .liked .thumb-icon {
            color: #007BFF;
            transform: scale(1.2);
        }

        .like-count {
            margin-left: 10px;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .liked + .like-count {
            color: #007BFF;
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
        <form action="IdeePublique.php" method="GET">
            <input type="text" name="search" placeholder="Rechercher des idées publiques..." value="<?php echo htmlspecialchars($search); ?>">
            <button><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="navigation">
        <strong><a href="AccueilIdee.php">Accueil</a></strong>
    </div>
    <div class="profil">
        <a href="Profil.php">
            <i class="fas fa-user-circle"></i>
            <strong>Profil</strong>
        </a>
    </div>
</div>

<div class="menu-deroulant">
    <button><strong>Menu</strong></button>
    <ul class="sous">
        <li><a href="NouvelleIdee.php">Nouvelle Idée</a></li>
        <li><a href="MesIdees.php">Mes idées</a></li>
        <li><a href="IdeePublique.php">Idées publiques</a></li>
        <li><a href="IdeeComite.php">Idées comité</a></li>
        <li><a href="IdeePrivee.php">Idées privées</a></li>
    </ul>
</div>

<div class="enveloppe">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='idea'>";
            echo "<h2>" . htmlspecialchars($row['titre']) . "</h2>";
            echo "<p><strong>Par:</strong> " . htmlspecialchars($row['prenom']) . " " . htmlspecialchars($row['nom']) . "</p>";
            echo "<p><strong>Catégorie:</strong> " . htmlspecialchars($row['nom_categorie']) . "</p>";
            echo "<p>" . nl2br(htmlspecialchars($row['contenu_idee'])) . "</p>";
            echo "<p><strong>Date de création:</strong> " . htmlspecialchars($row['date_creation']) . "</p>";

            $statutClass = strtolower($row['statut']);
            echo "<p class='status-$statutClass'><span class='status-circle'></span>" . htmlspecialchars($row['statut']) . "</p>";
            
            echo "<div class='like-container'>";
            echo "<form action='../../database/idee/like.php' method='POST'>";
            echo "<input type='hidden' name='idee_id' value='" . $row['id_idee'] . "'>";
            echo "<button type='submit' class='like-button" . ($row['user_liked'] ? ' liked' : '') . "'>";
            echo "<i class='fas fa-thumbs-up thumb-icon'></i>";
            echo "</button>";
            echo "<span class='like-count'>" . $row['like_count'] . "</span>";
            echo "</form>";
            echo "</div>";
            
            echo "<a href='VoirIdeePublique.php?id=" . $row['id_idee'] . "'>Voir les détails</a>";
            echo "</div>";
        }
    } else {
        echo "<p>Aucune idée publique trouvée.</p>";
    }
    $stmt->close();
    $connexion->close();
    ?>
</div>
</body>
</html>
