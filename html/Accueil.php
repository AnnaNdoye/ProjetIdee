<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../static/css/style1.css">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="../static/css/style4.css">
    <title>Accueil - ORANGE for Ideas</title>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="location.href='accueil.html'">
            <img src="../static/img/icon.png" alt="Logo Orange">
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
        <div class="connect_entete">
            <a href="connexion.php">
                <i class="fas fa-user"></i>
                <strong>Se connecter</strong>
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <section class="hero">
            <div class="hero-text">
                <h2>Bienvenue sur Orange for Ideas</h2>
                <p>Partagez vos idées innovantes et collaborez avec des esprits créatifs.</p>
                <a href="inscription.php" class="cta-button">Rejoignez-nous</a>
            </div>
            <div class="hero-image">
                <img src="../static/img/idee.jpg" alt="Idées innovantes">
            </div>
        </section>

        <section class="features">
            <h2>Pourquoi choisir Orange for Ideas?</h2>
            <div class="feature-cards">
                <div class="card">
                    <img src="../static/img/passion.png" alt="Communauté passionnée">
                    <h3>Communauté Passionnée</h3>
                    <p>Rejoignez une communauté d'innovateurs et de penseurs créatifs.</p>
                </div>
                <div class="card">
                    <img src="../static/img/colaboration.jpeg" alt="Collaboration">
                    <h3>Collaboration</h3>
                    <p>Travaillez ensemble pour transformer des idées en réalité.</p>
                </div>
                <div class="card">
                    <img src="../static/img/ibou.png" alt="Support et Ressources">
                    <h3>Support et Ressources</h3>
                    <p>Accédez à des ressources et à un support pour soumettre vos idées.</p>
                </div>
            </div>
        </section>


        <section class="call-to-action">
            <h2>Prêt à partager vos idées?</h2>
            <p>Inscrivez-vous dès aujourd'hui et commencez à collaborer avec une communauté passionnée.</p>
            <a href="inscription.php" class="cta-button">Inscrivez-vous</a>
        </section>
    </div>

    <?php
        include("barrefooter.html");
    ?>
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script src="../static/js/script1.js"></script>
</body>
</html>
