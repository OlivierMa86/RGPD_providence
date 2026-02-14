<?php
include("includes/config.php");

$new_password = "Oliv2001!";
$hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE pseudo = 'ADMIN'");
    $stmt->execute([$hash]);

    echo "<h2>Récupération de compte</h2>";
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;'>✅ Le mot de passe de l'utilisateur <b>ADMIN</b> a été réinitialisé avec succès.</p>";
        echo "<p>Vous pouvez maintenant vous connecter avec : <br>Pseudo: <b>ADMIN</b> <br>Pass: <b>Oliv2001!</b></p>";
        echo "<p style='color:red;'>⚠️ <b>IMPORTANT :</b> Veuillez supprimer ce fichier (fix_login.php) de votre serveur dès que possible pour des raisons de sécurité.</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ L'utilisateur 'ADMIN' n'a pas été trouvé (ou le mot de passe était déjà identique).</p>";
        echo "<p>Vérifiez que vous avez bien importé la table 'utilisateurs'.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>