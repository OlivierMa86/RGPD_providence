<?php
include("includes/config.php");
include("includes/auth.php");

$message = "";
$error = "";

if (isset($_POST['change'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (password_verify($old, $currentUser['mot_de_passe'])) {
        if ($new === $confirm) {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?")
                ->execute([$hashed, $currentUser['id_utilisateur']]);
            $message = "Mot de passe modifié avec succès.";
        } else {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        }
    } else {
        $error = "L'ancien mot de passe est incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Changer de mot de passe</title>
</head>

<body>
    <?php include("includes/header.php"); ?>
    <div class="container" style="max-width: 500px;">
        <h2>🔐 Changer de mot de passe</h2>

        <?php if ($message): ?>
            <div class="alert success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Ancien mot de passe</label>
            <input type="password" name="old_password" required>

            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password" required>

            <label>Confirmez le nouveau mot de passe</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="change">Modifier</button>
        </form>
    </div>
</body>

</html>