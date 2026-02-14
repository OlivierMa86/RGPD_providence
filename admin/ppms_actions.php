<?php
include("../includes/config.php");
include("../includes/auth.php");

if ($currentUser['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Handler for PPMS Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Upload Document PPMS
    if ($action === 'upload_doc') {
        $type_doc = $_POST['type_doc'] ?? '';
        $observations = $_POST['observations'] ?? '';

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/ppms/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $fileName = basename($_FILES['file']['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // Check if doc already exists for this type to update or insert
                $stmt = $pdo->prepare("SELECT id_doc_ppms FROM ppms_documents WHERE type_doc = ?");
                $stmt->execute([$type_doc]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $upd = $pdo->prepare("UPDATE ppms_documents SET nom_fichier = ?, chemin_fichier = ?, date_upload = NOW(), observations = ? WHERE type_doc = ?");
                    $upd->execute([$fileName, $targetPath, $observations, $type_doc]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO ppms_documents (type_doc, nom_fichier, chemin_fichier, observations) VALUES (?, ?, ?, ?)");
                    $ins->execute([$type_doc, $fileName, $targetPath, $observations]);
                }

                header("Location: ppms_dashboard.php?success=doc_uploaded");
                exit;
            }
        }
        header("Location: ppms_dashboard.php?error=upload_failed");
        exit;
    }

    // 2. Add / Program Exercise
    if ($action === 'add_exercice') {
        $type_exercice = $_POST['type_exercice'] ?? '';
        $date_prevue = $_POST['date_prevue'] ?? '';
        $date_realisee = $_POST['date_realisee'] ?? null;
        $statut = $_POST['statut'] ?? 'Programmé';
        $observations = $_POST['observations'] ?? '';

        $ins = $pdo->prepare("INSERT INTO ppms_exercices (type_exercice, date_prevue, date_realisee, statut, observations) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$type_exercice, $date_prevue, $date_realisee ?: null, $statut, $observations]);

        header("Location: ppms_dashboard.php?success=exercice_added");
        exit;
    }

    // 3. Update / Finalize Exercise
    if ($action === 'update_exercice') {
        $id = $_POST['id_exercice'] ?? '';
        $date_realisee = $_POST['date_realisee'] ?? '';
        $statut = 'Réalisé';

        $upd = $pdo->prepare("UPDATE ppms_exercices SET date_realisee = ?, statut = ? WHERE id_exercice = ?");
        $upd->execute([$date_realisee, $statut, $id]);

        header("Location: ppms_dashboard.php?success=exercice_updated");
        exit;
    }

    // 4. Upload Shared Procedure
    if ($action === 'upload_procedure') {
        $titre = $_POST['titre'] ?? 'Procédure sans titre';
        $description = $_POST['description'] ?? '';

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/ppms_procedures/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $fileName = basename($_FILES['file']['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $ins = $pdo->prepare("INSERT INTO ppms_procedures (titre, nom_fichier, chemin_fichier, description) VALUES (?, ?, ?, ?)");
                $ins->execute([$titre, $fileName, $targetPath, $description]);

                header("Location: ppms_dashboard.php?success=procedure_uploaded");
                exit;
            }
        }
        header("Location: ppms_dashboard.php?error=upload_failed");
        exit;
    }

    // 5. Delete Procedure
    if ($action === 'delete_procedure') {
        $id = $_POST['id_procedure'] ?? '';
        $stmt = $pdo->prepare("SELECT chemin_fichier FROM ppms_procedures WHERE id_procedure = ?");
        $stmt->execute([$id]);
        $proc = $stmt->fetch();

        if ($proc) {
            if (file_exists($proc['chemin_fichier']))
                unlink($proc['chemin_fichier']);
            $del = $pdo->prepare("DELETE FROM ppms_procedures WHERE id_procedure = ?");
            $del->execute([$id]);
            header("Location: ppms_dashboard.php?success=procedure_deleted");
            exit;
        }
    }

    // 6. Update Safety Sheet (Fiche)
    if ($action === 'update_fiche') {
        $id = $_POST['id'] ?? null;
        $titre = $_POST['titre'] ?? '';
        $entete = $_POST['entete'] ?? '';
        $signal_sonore = $_POST['signal_sonore'] ?? '';
        $alerte_msg = $_POST['alerte_msg'] ?? '';
        $consignes_1 = $_POST['consignes_1'] ?? '';
        $consignes_2 = $_POST['consignes_2'] ?? '';
        $divers = $_POST['divers'] ?? '';
        $last_update = date('d/m/Y');

        if ($id) {
            $upd = $pdo->prepare("UPDATE ppms_fiches SET titre=?, entete=?, signal_sonore=?, alerte_msg=?, consignes_1=?, consignes_2=?, divers=?, last_update=? WHERE id=?");
            $upd->execute([$titre, $entete, $signal_sonore, $alerte_msg, $consignes_1, $consignes_2, $divers, $last_update, $id]);
            header("Location: ppms_dashboard.php?success=fiche_updated");
            exit;
        }
    }
}
?>