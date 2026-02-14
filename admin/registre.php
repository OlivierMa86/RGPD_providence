<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$traitements = $pdo->query("SELECT r.*, u.nom FROM registre_traitements r 
                           LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur 
                           ORDER BY r.dernier_maj DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Registre des traitements - Admin</title>
</head>

<body>
    <?php include("../includes/header.php"); ?>

    <div class="container">
        <h2>📁 Registre des activités de traitement</h2>
        <div style="margin-bottom: 20px;">
            <a href="export_registre.php" class="btn" style="background-color: #28a745;">📥 Télécharger le registre
                (XLS/CSV)</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Traitement</th>
                    <th>Saisi par</th>
                    <th>Finalité</th>
                    <th>Responsable</th>
                    <th>Base Légale</th>
                    <th>Conservation</th>
                    <th>Maj</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($traitements as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['nom_traitement']); ?></td>
                        <td><?php echo htmlspecialchars($t['nom'] ?? 'Système'); ?></td>
                        <td><?php echo htmlspecialchars($t['finalite']); ?></td>
                        <td><?php echo htmlspecialchars($t['responsable_traitement']); ?></td>
                        <td><?php echo htmlspecialchars($t['base_legale']); ?></td>
                        <td><?php echo htmlspecialchars($t['duree_conservation']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($t['dernier_maj'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="../admin/index.php">← Retour</a></p>
    </div>

    <?php include("../includes/footer.php"); ?>
</body>

</html>