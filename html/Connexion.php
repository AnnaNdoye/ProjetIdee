<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../static/css/style1.css">
    <title>Connexion</title>
    <style>
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='Accueil.html'">
            <img src="../static/img/icon.png">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
        <div class="connect_entete">
            <a href="inscription.php">
                <i class="fas fa-user"></i>
                <strong>S'inscrire</strong>
            </a>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../database/connexion.php" method="POST">
            <div class="entete">
                <span>Vous avez déjà un compte ? </span>
                <header>Connectez-vous</header>
            </div>
            <?php
            session_start();
            if (isset($_SESSION['error_message'])) {
                echo '<p class="error">' . $_SESSION['error_message'] . '</p>';
                unset($_SESSION['error_message']);
            }
            session_destroy();
            ?>
            <div class="formulaire">
                <input type="email" name="email" class="input" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="formulaire">
                <input type="password" name="mot_de_passe" class="input" placeholder="Mot de passe" id="mot_de_passe" required>
                <i class="fas fa-eye eye" id="togglePassword"></i>
            </div>

            <div class="formulaire">
                <a href="PasswordOublie.php" class="mdp_oublie">Mot de passe oublié ?</a>
            </div>
            
            <div class="formulaire">
                <input type="submit" class="submit" value="Se connecter">
            </div>
        </form>
    </div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">©Orange/Juin2024</h4>
    </div>
    <script src="../static/js/script1.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#mot_de_passe');

        togglePassword.addEventListener('click', function (e) 
        {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
