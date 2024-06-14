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
    <link rel="stylesheet" href="../../static/css/style5.css">
    <link rel="stylesheet" href="../../static/css/IdeePP.css">
    <style>
        .serach-bar form {
            display: flex;
            align-items: center;
            border-radius: 5px;
            overflow: hidden;
            margin-left: 20px;
            flex: 1;
            border: #FF6600 solid;
            outline: none;
        }

        form input{
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
            <form action="IdeePubliqeu.php" method="GET">
                <input type="text" name="search" placeholder="Rechercher des idées publiques..." value="<?php echo htmlspecialchars($search); ?>">
                <button><i class="fas fa-search"></i></button>
            </form>
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
            <div class="enveloppe">
                <div class="idea"><h2>Titre: <?php echo $row['titre']; ?></h2></div>
                <div class="idea"><p>Contenu: <?php echo $row['contenu_idee']; ?></p></div>
                <div class="idea">
                <p>Date de création: <?php echo $row['date_creation']; ?></p>
                <p>Date de modification: <?php echo $row['date_modification']; ?></p>
                <p>Statut: <?php echo $row['statut']; ?></p> <span class="status-circle"></span></strong>
            </div>
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
