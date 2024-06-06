<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../static/css/style1.css">
    <title>Inscription</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='Accueil.html'">
            <img src="../static/img/icon.png">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
        <div class="connect_entete">
            <a href="connexion.php">
                <strong>Se connecter</strong>
                <i class="fas fa-user"></i>
            </a>
        </div>
    </div>
    <div class="boite">
        <form class="container" action="../database/inscription.php" method="POST">
            <div class="entete">
                <span>Vous n'avez
                    pas de compte ? </span>
                <header>Inscrivez-vous</header>
            </div>
            <?php //code php pour afficher le message d'erreur
                session_start(); // Démarrez la session pour accéder aux variables de session
                if (isset($_SESSION['erreur_email'])) 
                {
                    echo '<span style="color: red;">' . $_SESSION['erreur_email'] . '</span>';
                    unset($_SESSION['erreur_email']); // Effacez la variable de session après l'avoir affichée
                    $prenom = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '';
                    $nom = isset($_SESSION['nom']) ? $_SESSION['nom'] : '';
                    $mot_de_passe = isset($_SESSION['mot_de_passe']) ? $_SESSION['mot_de_passe'] : '';
                    $sexe = isset($_SESSION['sexe']) ? $_SESSION['sexe'] : '';
                    $poste = isset($_SESSION['poste']) ? $_SESSION['poste'] : '';
                    $departement = isset($_SESSION['departement']) ? $_SESSION['departement'] : '';
                }
                else 
                {
                    $prenom = '';
                    $nom = '';
                    $sexe = '';
                    $poste = '';
                    $departement = '';
                }
                session_destroy();

            ?>

            <div class="formulaire">
                <input type="text" class="input" placeholder="Prénom" name="prenom" value="<?php echo $prenom; ?>" required>
                <i class="fas fa-user"></i>
            </div>

            <div class="formulaire">
                <input type="text" class="input" placeholder="Nom" name="nom" value="<?php echo $nom; ?>" required>
                <i class="fas fa-user"></i>
            </div>

            <div class="formulaire">
                <input type="text" class="input" placeholder="Email" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
    
            <div class="formulaire">
                <input type="password" class="input" placeholder="Mot De passe" name="mot_de_passe" id="mot_de_passe" required>
                <i class="fas fa-eye eye" id="togglePassword"></i>
            </div>

            <div class="formulaire">
                <select name="sexe" required>
                    <option value="" disabled selected>Choisissez votre sexe</option>
                    <option value="Masculin">Masculin</option>
                    <option value="Féminin">Féminin</option>
                </select>
                <i class="fas fa-venus-mars"></i>
            </div>

            <div class="formulaire">
                <input type="text" class="input" placeholder="Poste" name="poste" value="<?php echo $poste; ?>" required>
                <i class="fas fa-briefcase"></i>
            </div>

            <div class="formulaire">
                <input type="text" class="input" placeholder="Département" name="departement" value="<?php echo $departement; ?>" required>
                <i class="fas fa-briefcase"></i>
            </div>

            <div class="accept-conditions-container">
                <input type="checkbox" id="acceptConditions" required/>
                <strong for="acceptConditions" style="color: #FF6600;">
                    J'accepte <a href="ConditionUtilisation.html" style="color: #FF6600; text-decoration: none;">les conditions d'utilisation</a>
                </strong>
            </div>

            <div class="formulaire">
                <input type="submit" class="submit" value="Créer un nouveau compte">
            </div>
        </form>
    </div>
    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>
    <script src="../static/js/script1.js"></script>
    <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#mot_de_passe');

    togglePassword.addEventListener('click', function (e) 
    {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // Toggle the icon
        this.classList.toggle('fa-eye-slash');
    });
    </script>
</body>
</html>
