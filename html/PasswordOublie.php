<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../static/css/style1.css">
    <link rel="stylesheet" href="../static/css/style5.css">
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
            <a href="Connexion.php">
                <i class="fas fa-user"></i>
                <strong>Se connecter</strong>
            </a>
            <a href="Inscription.php">
                <i class="fas fa-user"></i>
                <strong>S'inscrire</strong>
            </a>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../database/password_oublie.php" method="POST">
            <div class="entete">
                <span>Vous avez oublié votre mot de passe? </span>
                <header>Veuillez renseigner votre email</header>
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
                <input type="email" class="input" placeholder="Email" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="formulaire">
                <input type="submit" class="submit" value="Soumettre">
            </div>
        </form>
    </div>
    <div class="espace"></div>
    <?php
        include("barrefooter.html");
    ?>
    <script src="../static/js/script1.js"></script>
</body>
</html>
