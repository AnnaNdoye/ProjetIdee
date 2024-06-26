<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../static/css/style1.css">
    <title>Changer le mot de passe</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='AccueilIdee.html'">
            <img src="../../static/img/icon.png">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../../database/idee/change_password2.php" method="POST">
            <div class="entete">
                <header>Saisissez le nouveau mot de passe</header>
            </div>
            <div class="formulaire">
                <input type="password" id="mot_de_passe" class="input" placeholder="Mot De passe" name="mot_de_passe" required>
                <i class="fas fa-eye eye" id="togglePassword"></i>
            </div>
            <div class="formulaire">
                <input type="submit" class="submit" value="Changer le mot de passe">
            </div>
        </form>
    </div>
    <div class="espace"></div>
    <?php
        include("../barrefooter.html");
    ?>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#mot_de_passe');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
