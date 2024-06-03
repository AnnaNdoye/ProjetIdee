<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../static/css/style1.css">
    <title>Mot de passe oublié</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='accueil.html'">
            <img src="../static/img/icon.png">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
        <div class="connect_entete">
            <i class="fas fa-user"></i>
            <a href="Connexion.php"><strong>Se connecter</strong></a>
            <a href="Inscription.php"><strong>S'inscrire</strong></a>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../database/password_oublie.php" method="POST">
            <div class="entete">
                <span>Vous avez oublié votre mot de passe? </span>
                <header>Veuillez renseigner ces informations</header>
            </div>
            <?php
            session_start();
            if (isset($_SESSION['error_message'])) {
                echo '<span style="color: red;">' . $_SESSION['error_message'] . '</span>';
                unset($_SESSION['error_message']);
            }
            session_destroy();
            ?>
            <div class="formulaire">
                <input type="text" class="input" placeholder="Prénom" name="prenom" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="formulaire">
                <input type="text" class="input" placeholder="Nom" name="nom" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="formulaire">
                <input type="email" class="input" placeholder="Email" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="formulaire">
                <input type="submit" class="submit" value="Soumettre">
            </div>
        </form>
    </div>
    <div class="espace"></div>
    <div class="footer">
    <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>
    <script src="../static/js/script1.js"></script>
</body>
</html>
