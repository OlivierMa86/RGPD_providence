<?php $root = file_exists("dashboard.php") ? "" : "../"; ?>
<header class="site-header">
    <div class="header-content">
        <h1>Plateforme RGPD</h1>
        <nav>
            <a href="<?php echo $root; ?>dashboard.php">🏠 Tableau de bord</a>
            <a href="<?php echo $root; ?>fiches.php">📄 Mes Fiches PDF</a>
            <a href="<?php echo $root; ?>change_password.php">🔐 Profil</a>
            <?php if ($currentUser['role'] == 'admin'): ?>
                <a href="<?php echo $root; ?>admin/index.php">⚙️ Admin</a>
                <a href="<?php echo $root; ?>admin/users.php">👥 Utilisateurs</a>
                <a href="<?php echo $root; ?>admin/registre.php">📁 Registre</a>
                <a href="<?php echo $root; ?>admin/documentation.php">📚 Documentation</a>
            <?php endif; ?>
            <a href="<?php echo $root; ?>logout.php">🚪 Déconnexion</a>
        </nav>
    </div>
</header>