<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ConnexionAdmin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "idee";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtenir le nombre total d'employés
$sql = "SELECT COUNT(*) as total_employes FROM Employe";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_employes = $row['total_employes'];

// Obtenir le nombre total de départements
$sql = "SELECT COUNT(*) as total_departements FROM Department";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_departements = $row['total_departements'];

// Obtenir le nombre total d'idées publiques
$sql = "SELECT COUNT(*) as total_idees FROM Idee WHERE est_publique = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_idees = $row['total_idees'];

// Obtenir le nombre total de commentaires
$sql = "SELECT COUNT(*) as total_commentaires FROM Commentaire";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_commentaires = $row['total_commentaires'];

// Obtenir le nombre total de likes sur les idées
$sql = "SELECT COUNT(*) as total_likes_idees FROM LikeIdee";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_likes_idees = $row['total_likes_idees'];

// Obtenir le nombre total de likes sur les commentaires
$sql = "SELECT COUNT(*) as total_likes_commentaires FROM LikeCommentaire";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_likes_commentaires = $row['total_likes_commentaires'];

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <title>Accueil Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #333;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #77aaff 3px solid;
        }
        header a {
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
        }
        header ul {
            padding: 0;
            list-style: none;
        }
        header li {
            display: inline;
            padding: 0 20px 0 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .card h3 {
            margin-top: 0;
            font-size: 24px;
        }
        .card p {
            font-size: 20px;
            color: #333;
        }
        .card-icon {
            font-size: 50px;
            color: #77aaff;
            margin-bottom: 10px;
        }
        .container h1{
            text-align: center;
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

        <div class="profil">
            <a href="../idee/Profil.php">
                <i class="fas fa-user-circle"></i>
                <strong>Profil</strong>
            </a>
        </div>

        <div class="connect_entete">
            <a href="ConnexionAdmin.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>

    <div class="menu-deroulant">
        <button><strong>Menu</strong></button>
        <ul class="sous">
            <li><a href="StatutIdee.php">Statut Idée</a></li>
            <li><a href="Categorie.php">Catégories</a></li>
            <li><a href="Departement.php">Départements</a></li>
            <li><a href="IdeePubliqueAdmin.php">Idées publiques</a></li>
        </ul>
    </div>

    <header>
        <div class="container">
            <h1>Tableau de Bord Administrateur</h1>
        </div>
    </header>
    <div class="container">
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>Total des Employés</h3>
            <p><?php echo $total_employes; ?></p>
        </div>
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-building"></i>
            </div>
            <h3>Total des Départements</h3>
            <p><?php echo $total_departements; ?></p>
        </div>
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-lightbulb"></i>
            </div>
            <h3>Total des Idées Publiques</h3>
            <p><?php echo $total_idees; ?></p>
        </div>
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3>Total des Commentaires</h3>
            <p><?php echo $total_commentaires; ?></p>
        </div>
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-thumbs-up"></i>
            </div>
            <h3>Total des Likes sur les Idées</h3>
            <p><?php echo $total_likes_idees; ?></p>
        </div>
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h3>Total des Likes sur les Commentaires</h3>
            <p><?php echo $total_likes_commentaires; ?></p>
        </div>
    </div>

    <div class="espace"></div>
    <?php
        include("../barrefooter.html");
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('.menu-deroulant button');
            const menuList = document.querySelector('.menu-deroulant ul');

            menuButton.addEventListener('click', () => {
                menuList.style.display = menuList.style.display === 'flex' ? 'none' : 'flex';
            });

            menuButton.addEventListener('mouseover', () => {
                menuList.style.display = 'flex';
            });

            menuButton.addEventListener('mouseout', () => {
                if (menuList.style.display !== 'flex') {
                    menuList.style.display = 'none';
                }
            });

            menuList.addEventListener('mouseover', () => {
                menuList.style.display = 'flex';
            });

            menuList.addEventListener('mouseout', () => {
                menuList.style.display = 'none';
            });
        });
    </script>
</body>
</html>
