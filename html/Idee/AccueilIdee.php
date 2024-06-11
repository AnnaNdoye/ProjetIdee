<?php
session_start();

// Vérifiez si l'utilisateur est connecté, sinon redirigez vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <title>Accueil</title>
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

    <div class="menu-deroulant">
        <button><strong>Menu</strong></button>
        <ul class="sous">
            <li><a href="NouvelleIdee.php">Nouvelle Idée</a></li>
            <li><a href="MesIdees.php">Mes idées</a></li>
            <li><a href="IdeePublique.php">Idées publiques</a></li>
            <li><a href="Profil.php">Profil</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="ideas-overview">
            <h2>Idées Populaires</h2>
            <div class="ideas-list">
                <div class="idea">
                    <h3>Titre de l'idée</h3>
                    <p>Catégorie: Innovation</p>
                    <p>Résumé: Description de l'idée...</p>
                    <a href="IdeePublique.php">Voir plus</a>
                </div>
            </div>
        </div>
    </div>
    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

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