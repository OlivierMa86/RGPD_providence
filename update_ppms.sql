-- ==========================================================
-- EEVOLUTION PPMS - École & Collège La Providence
-- Création des tables pour les documents et les exercices
-- ==========================================================

-- 1. Table pour les documents PPMS obligatoires
CREATE TABLE IF NOT EXISTS ppms_documents (
    id_doc_ppms INT AUTO_INCREMENT PRIMARY KEY,
    type_doc ENUM('PPMS_Unique', 'Registre_Securite', 'DUER', 'Carnet_Maintenance') NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('A_Jour', 'A_Reviser', 'Manquant') DEFAULT 'A_Jour',
    observations TEXT
);

-- 2. Table pour le suivi des exercices de sécurité
CREATE TABLE IF NOT EXISTS ppms_exercices (
    id_exercice INT AUTO_INCREMENT PRIMARY KEY,
    type_exercice ENUM('Incendie', 'Intrusion_Attentat', 'Risques_Majeurs') NOT NULL,
    date_prevue DATE NOT NULL,
    date_realisee DATE NULL,
    statut ENUM('Programmé', 'Réalisé', 'Annulé') DEFAULT 'Programmé',
    pv_chemin VARCHAR(255) NULL, -- Chemin vers le Procès-Verbal (PDF)
    observations TEXT
);

-- 3. Journalisation de l'évolution (optionnel)
INSERT INTO journal_actions (id_utilisateur, action, cible) 
VALUES (1, 'Mise à jour structure BD', 'Ajout tables PPMS');

-- 3. Table pour les procédures d'alerte partagées (Staff)
CREATE TABLE IF NOT EXISTS ppms_procedures (
    id_procedure INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    description TEXT
);

-- 4. Table pour les fiches de sécurité interactives (Fiches 1, 2, 3)
CREATE TABLE IF NOT EXISTS ppms_fiches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('evacuation', 'confinement', 'attentat') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    entete TEXT,
    signal_sonore TEXT,
    alerte_msg TEXT,
    consignes_1 TEXT, -- Consignes Adultes ou Générales
    consignes_2 TEXT, -- Consignes Élèves ou Spécifiques
    divers TEXT,      -- Rassemblement, Matériel, Fin d'alerte
    last_update VARCHAR(50)
);

-- Insertion des données initiales fournies
INSERT INTO ppms_fiches (type, titre, entete, signal_sonore, alerte_msg, consignes_1, consignes_2, divers, last_update) VALUES 
('evacuation', 'ÉVACUATION (Incendie & Alerte à la Bombe)', 'Établissement : La Providence - La Salle, Poitiers', 'Alarme sonore (sirène)', 'Appeler le 18 (Pompiers) ou le 17 (Police)', 'Faire sortir les élèves rapidement sans précipitation.\nPrendre le registre d''appel.\nFermer fenêtres et portes (sans verrouiller).\nLaisser la porte fermée pour signaler que la salle est vidée.\nSortir en dernier après vérification.', 'Ne pas courir, ne pas revenir en arrière.\nLaisser les affaires sur place.', 'RASSEMBLEMENT : Zones définies (Place de la Liberté, Place Charles VII ou Rue Paul Bert).', '25/10/2024'),
('confinement', 'PPMS CONFINEMENT (Risques Majeurs)', 'Établissement : La Providence - La Salle, Poitiers', 'Alarme avec message vocal indiquant de se confiner', 'Ne pas saturer les lignes, attendre les consignes de la cellule de crise', 'Rester dans la classe et fermer la salle à clé.\nFermer fenêtres, volets et rideaux.\nS''éloigner des fenêtres.\nÉteindre les téléphones portables des élèves.\nCompter les élèves et signaler toute anomalie (papier sur la porte).', NULL, 'MATÉRIEL : Utiliser la caisse PPMS (scotch).\nFIN D''ALERTE : Signal sonore avec message spécifique.', '25/10/2024'),
('attentat', 'PPMS ATTENTAT-INTRUSION', 'Établissement : La Providence - La Salle, Poitiers', 'Alarme sonore en continu SANS message vocal', 'Appeler le 17 ou le 112. Utiliser le 114 (SMS) pour une alerte silencieuse.', 'Postures : S''échapper (si possible) ou se cacher/se barricader.', 'Verrouiller la porte et se barricader avec des meubles.\nÉteindre les lumières.\nSilence absolu (téléphones sur silencieux SANS vibreur).\nS''allonger au sol, loin des parois vitrées.', 'LEVÉE D''ALERTE : Uniquement par message de la direction ou forces de l''ordre.', '25/10/2024');
