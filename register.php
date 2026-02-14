<?php
include("includes/config.php");

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $nom = trim($_POST['nom']);
    $fonction = $_POST['fonction'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validations
    if (empty($pseudo) || empty($email) || empty($nom) || empty($fonction) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si le pseudo ou l'email existe déjà
        $checkStmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE pseudo=? OR email=?");
        $checkStmt->execute([$pseudo, $email]);
        if ($checkStmt->fetch()) {
            $error = "Ce pseudo ou cet email est déjà utilisé.";
        } else {
            // Insertion du nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, mot_de_passe, email, nom, fonction, role) VALUES (?, ?, ?, ?, ?, 'utilisateur')");
            $insertStmt->execute([$pseudo, $hashedPassword, $email, $nom, $fonction]);

            $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";

            // Optionnel : log de l'action
            $newId = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO journal_actions (id_utilisateur, action, cible, ip_origine) VALUES (?, ?, ?, ?)")
                ->execute([$newId, "Création de compte", "Utilisateur $newId", $_SERVER['REMOTE_ADDR']]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Créer un compte - Plateforme Providence</title>
</head>

<body>
    <div class="login-container">
        <h2>Créer un compte</h2>
        <p style="font-size:14px; color:#555;">École & Collège La Providence - La Salle</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <a href="index.php" class="btn">Se connecter</a>
        <?php else: ?>
            <form method="post">
                <input type="text" name="pseudo" placeholder="Pseudo" required
                    value="<?php echo isset($_POST['pseudo']) ? htmlspecialchars($_POST['pseudo']) : ''; ?>">

                <input type="email" name="email" placeholder="Adresse e-mail" required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

                <input type="text" name="nom" placeholder="Nom complet" required
                    value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">

                <label for="fonction">Fonction :</label>
                <select name="fonction" id="fonction" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Enseignant(e)">Enseignant(e)</option>
                    <option value="CPE">CPE</option>
                    <option value="Secrétaire">Secrétaire</option>
                    <option value="AESH">AESH</option>
                    <option value="Vie scolaire">Vie scolaire</option>
                    <option value="Personnel technique">Personnel technique</option>
                    <option value="Direction">Direction</option>
                </select>

                <input type="password" name="password" placeholder="Mot de passe" required>

                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>

                <button type="submit" name="register">Créer mon compte</button>
            </form>

            <a href="index.php">← Retour à la connexion</a>
        <?php endif; ?>
    </div>
</body>

</html>