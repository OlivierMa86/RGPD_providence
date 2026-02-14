<?php
include("includes/config.php");

$error = "";
if (isset($_POST['login'])) {
    if (login($_POST['pseudo'], $_POST['motdepasse'], $pdo)) {
        if ($_SESSION['user']['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Identifiants invalides";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Connexion - Plateforme RGPD</title>
</head>

<body>
    <div class="login-container">
        <h2>Plateforme RGPD<br>École & Collège La Providence - La Salle</h2>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="pseudo" placeholder="Pseudo" required>
            <input type="password" name="motdepasse" placeholder="Mot de passe" required>
            <button type="submit" name="login">Connexion</button>
        </form>
        <a href="register.php">Créer un compte</a>
    </div>
</body>

</html>