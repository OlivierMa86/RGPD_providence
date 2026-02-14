<?php $root = file_exists("dashboard.php") ? "" : "../"; ?>
<header class="site-header">
    <div class="header-content">
        <h1>Plateforme Providence</h1>
        <nav>
            <?php if ($currentUser['role'] == 'admin'): ?>
                <a href="<?php echo $root; ?>admin/index.php">🏠 Tableau de bord</a>
                <a href="<?php echo $root; ?>admin/rgpd_dashboard.php">🛡️ RGPD</a>
                <a href="<?php echo $root; ?>admin/ppms_dashboard.php">🚨 Sécurité & PPMS</a>
            <?php else: ?>
                <a href="<?php echo $root; ?>dashboard.php">🏠 Tableau de bord</a>
                <a href="<?php echo $root; ?>rgpd_view.php">🛡️ RGPD</a>
                <a href="<?php echo $root; ?>ppms_view.php">🚨 Sécurité & PPMS</a>
            <?php endif; ?>
            <a href="<?php echo $root; ?>change_password.php">🔐 Profil</a>
            <a href="<?php echo $root; ?>logout.php">🚪 Déconnexion</a>
        </nav>
    </div>
</header>