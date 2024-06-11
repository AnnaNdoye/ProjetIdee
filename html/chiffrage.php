<?php
// Mot de passe à chiffrer
$motDePasse = "adminpasse";

// Chiffrage du mot de passe
$motDePasseChiffre = password_hash($motDePasse, PASSWORD_DEFAULT);

// Affichage du mot de passe chiffré
echo "Mot de passe chiffré : " . $motDePasseChiffre;
?>
