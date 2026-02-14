<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$logs = $pdo->query("SELECT j.*, u.pseudo FROM journal_actions j JOIN utilisateurs u ON j.id_utilisateur = u.id_utilisateur ORDER BY date_action DESC LIMIT 100")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Journaux d'actions - Admin</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <?php include("../includes/admin_nav.php"); ?>

    <div class="container">
        <h2>📜 Journaux d'actions</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Cible</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['date_action']; ?></td>
                        <td><?php echo htmlspecialchars($log['pseudo']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['cible']); ?></td>
                        <td><?php echo $log['ip_origine']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="../admin/index.php">← Retour</a></p>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>