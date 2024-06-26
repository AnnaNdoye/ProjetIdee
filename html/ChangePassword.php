<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../static/css/style1.css">
    <title>Changer le mot de passe</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='Accueil.html'">
            <img src="../static/img/icon.png">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../database/change_password.php" method="POST">
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
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>
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
