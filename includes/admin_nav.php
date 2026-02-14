<?php
// Barre de navigation secondaire pour l'administration
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-subnav" style="background: #edf2f7; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px;">
    <div class="container"
        style="margin: 0 auto; padding: 10px 30px; display: flex; gap: 25px; align-items: center; max-width: 1200px; box-shadow: none; background: transparent; border: none; border-radius: 0;">
        <span
            style="font-weight: 800; color: #4a5568; font-size: 0.85em; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 10px;">Modules
            Admin :</span>

        <a href="rgpd_dashboard.php"
            style="text-decoration: none; color: <?php echo $currentPage == 'rgpd_dashboard.php' ? '#0059b2' : '#718096'; ?>; font-weight: <?php echo $currentPage == 'rgpd_dashboard.php' ? '800' : '600'; ?>; font-size: 14px; display: flex; align-items: center; gap: 5px;">
            🛡️ <span
                style="<?php echo $currentPage == 'rgpd_dashboard.php' ? 'border-bottom: 2px solid #0059b2;' : ''; ?>">RGPD</span>
        </a>

        <a href="ppms_dashboard.php"
            style="text-decoration: none; color: <?php echo $currentPage == 'ppms_dashboard.php' ? '#ed8936' : '#718096'; ?>; font-weight: <?php echo $currentPage == 'ppms_dashboard.php' ? '800' : '600'; ?>; font-size: 14px; display: flex; align-items: center; gap: 5px;">
            🚨 <span
                style="<?php echo $currentPage == 'ppms_dashboard.php' ? 'border-bottom: 2px solid #ed8936;' : ''; ?>">PPMS</span>
        </a>

        <a href="users.php"
            style="text-decoration: none; color: <?php echo $currentPage == 'users.php' ? '#38b2ac' : '#718096'; ?>; font-weight: <?php echo $currentPage == 'users.php' ? '800' : '600'; ?>; font-size: 14px; display: flex; align-items: center; gap: 5px;">
            👥 <span
                style="<?php echo $currentPage == 'users.php' ? 'border-bottom: 2px solid #38b2ac;' : ''; ?>">Utilisateurs</span>
        </a>

        <div style="height: 20px; width: 1px; background: #cbd5e0; margin: 0 5px;"></div>

        <a href="../ppms_view.php" target="_blank"
            style="text-decoration: none; color: #718096; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 5px;">
            👁️ Aperçu Public PPMS
        </a>

        <a href="index.php"
            style="text-decoration: none; color: #4a5568; font-weight: 600; font-size: 14px; margin-left: auto; display: flex; align-items: center; gap: 5px; opacity: 0.8;">
            🏠 Accueil Admin
        </a>
    </div>
</div>
<style>
    .admin-subnav a:hover {
        opacity: 0.8;
    }
</style>