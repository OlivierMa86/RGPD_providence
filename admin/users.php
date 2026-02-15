<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$message = "";
$error = "";

// Création manuelle d'un utilisateur
if (isset($_POST['create_user'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $nom = trim($_POST['nom']);
    $fonction = $_POST['fonction'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (empty($pseudo) || empty($email) || empty($nom) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $checkStmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE pseudo=? OR email=?");
        $checkStmt->execute([$pseudo, $email]);
        if ($checkStmt->fetch()) {
            $error = "Ce pseudo ou email existe déjà.";
        } else {
            $hashedPwd = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO utilisateurs (pseudo, mot_de_passe, email, nom, fonction, role) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$pseudo, $hashedPwd, $email, $nom, $fonction, $role]);
            $message = "Utilisateur créé avec succès.";
        }
    }
}

// Suppression d'un utilisateur
if (isset($_GET['delete'])) {
    $idToDelete = (int) $_GET['delete'];
    if ($idToDelete != $currentUser['id_utilisateur']) {
        $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur=?")->execute([$idToDelete]);
        $message = "Utilisateur supprimé.";
    } else {
        $error = "Vous ne pouvez pas supprimer votre propre compte.";
    }
}

// Modification du rôle
if (isset($_POST['change_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = $_POST['new_role'];
    $pdo->prepare("UPDATE utilisateurs SET role=? WHERE id_utilisateur=?")->execute([$newRole, $userId]);
    $message = "Rôle modifié avec succès.";
}

// Réinitialisation mot de passe
if (isset($_POST['reset_password'])) {
    $userId = (int) $_POST['user_id'];
    $newPassword = bin2hex(random_bytes(4));
    $hashedPwd = password_hash($newPassword, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE id_utilisateur=?")->execute([$hashedPwd, $userId]);
    $message = "Mot de passe réinitialisé : $newPassword (à communiquer à l'utilisateur)";
}

$users = $pdo->query("SELECT * FROM utilisateurs ORDER BY id_utilisateur DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Gestion des Utilisateurs</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>
    <div class="container">
        <h2>👥 Gestion des Utilisateurs</h2>

        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div><?php endif; ?>

        <div class="section">
            <h3>➕ Créer un utilisateur</h3>
            <form method="post" class="form-inline">
                <input type="text" name="pseudo" placeholder="Pseudo" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="nom" placeholder="Nom complet" required>
                <select name="fonction">
                    <option value="Enseignant(e)">Enseignant(e)</option>
                    <option value="CPE">CPE</option>
                    <option value="Direction">Direction</option>
                    <option value="Chef d’établissement">Chef d’établissement</option>
                    <option value="Secrétaire">Secrétaire</option>
                    <option value="AESH">AESH</option>
                    <option value="Vie scolaire">Vie scolaire</option>
                </select>
                <select name="role">
                    <option value="utilisateur">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit" name="create_user">Créer</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pseudo</th>
                    <th>Nom</th>
                    <th>Fonction</th>
                    <th>Rôle</th>
                    <th>Bilan</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id_utilisateur']; ?></td>
                        <td><?php echo htmlspecialchars($u['pseudo']); ?></td>
                        <td><?php echo htmlspecialchars($u['nom']); ?></td>
                        <td><?php echo htmlspecialchars($u['fonction']); ?></td>
                        <td>
                            <form method="post" style="display:inline; white-space:nowrap;">
                                <input type="hidden" name="user_id" value="<?php echo $u['id_utilisateur']; ?>">
                                <select name="new_role" onchange="this.form.submit()"
                                    style="padding:3px 6px; border-radius:4px; border:1px solid #cbd5e0; font-size:13px; cursor:pointer;">
                                    <option value="utilisateur" <?php echo $u['role'] == 'utilisateur' ? 'selected' : ''; ?>>
                                        Utilisateur</option>
                                    <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin
                                    </option>
                                </select>
                                <input type="hidden" name="change_role" value="1">
                            </form>
                        </td>
                        <td>
                            <a href="edit_submission.php?user_id=<?php echo $u['id_utilisateur']; ?>"
                                title="Voir le questionnaire">👁️ Voir</a>
                            <a href="../generate_bilan_pdf.php?user_id=<?php echo $u['id_utilisateur']; ?>" target="_blank"
                                title="Télécharger le bilan PDF complet" style="margin-left:8px; text-decoration:none;">📥
                                PDF</a>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $u['id_utilisateur']; ?>">
                                <button type="submit" name="reset_password" title="Réinitialiser MDP"
                                    style="background:none; border:none; cursor:pointer; font-size:18px; padding:5px;">🔑</button>
                            </form>
                            <?php if ($u['id_utilisateur'] != $currentUser['id_utilisateur']): ?>
                                <a href="users.php?delete=<?php echo $u['id_utilisateur']; ?>" title="Supprimer"
                                    style="text-decoration:none; font-size:18px; padding:5px; margin-left:10px;"
                                    onclick="return confirm('Supprimer ?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>