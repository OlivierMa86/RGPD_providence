-- ==========================================================
-- Plateforme RGPD - École & Collège La Providence - La Salle
-- Base adaptée pour IONOS : dbs15302347
-- Auteur : Projet IA
-- ==========================================================

USE dbs15302347;

-- ==========================================================
-- Suppression préalable (facultatif pour réimport propre)
-- ==========================================================
DROP TABLE IF EXISTS journal_actions, documents, registre_traitements, fiches, reponses, questions, questionnaires, utilisateurs;

-- ==========================================================
-- Table : utilisateurs
-- ==========================================================
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    nom VARCHAR(100),
    fonction VARCHAR(100),
    role ENUM('utilisateur','admin') DEFAULT 'utilisateur',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME NULL,
    actif TINYINT(1) DEFAULT 1
);

-- ==========================================================
-- Table : questionnaires
-- ==========================================================
CREATE TABLE questionnaires (
    id_questionnaire INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    fonction VARCHAR(100),
    statut ENUM('en_cours','complete') DEFAULT 'en_cours',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_validation DATETIME NULL,
    score_conformite DECIMAL(5,2) DEFAULT NULL,
    observations TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- ==========================================================
-- Table : questions
-- ==========================================================
CREATE TABLE questions (
    id_question INT AUTO_INCREMENT PRIMARY KEY,
    fonction_cible VARCHAR(100),
    question_txt TEXT,
    type_reponse ENUM('texte','choix','booleen','multiple') DEFAULT 'texte',
    options_reponse TEXT NULL,
    obligatoire TINYINT(1) DEFAULT 1
);

-- ==========================================================
-- Table : reponses
-- ==========================================================
CREATE TABLE reponses (
    id_reponse INT AUTO_INCREMENT PRIMARY KEY,
    id_questionnaire INT NOT NULL,
    id_question INT NOT NULL,
    reponse_utilisateur TEXT,
    date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unq_rep (id_questionnaire, id_question),
    FOREIGN KEY (id_questionnaire) REFERENCES questionnaires(id_questionnaire) ON DELETE CASCADE,
    FOREIGN KEY (id_question) REFERENCES questions(id_question) ON DELETE CASCADE
);

-- ==========================================================
-- Table : fiches
-- ==========================================================
CREATE TABLE fiches (
    id_fiche INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    type_fiche ENUM('bilan','bonnes_pratiques','points_vigilance') DEFAULT 'bilan',
    nom_fichier_pdf VARCHAR(255),
    chemin_stockage VARCHAR(255),
    observations TEXT,
    date_generation DATETIME DEFAULT CURRENT_TIMESTAMP,
    par_admin TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- ==========================================================
-- Table : registre_traitements
-- ==========================================================
CREATE TABLE registre_traitements (
    id_traitement INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NULL,
    nom_traitement VARCHAR(255),
    finalite TEXT,
    categories_donnes TEXT,
    base_legale VARCHAR(255),
    personnes_concernees TEXT,
    responsable_traitement VARCHAR(255),
    duree_conservation VARCHAR(255),
    emplacement_stockage VARCHAR(255),
    mesures_securite TEXT,
    sous_traitants TEXT,
    transfert_hors_ue VARCHAR(255) DEFAULT 'Non',
    pia_necessaire TINYINT(1) DEFAULT 0,
    dernier_maj DATETIME DEFAULT CURRENT_TIMESTAMP,
    source_questionnaire INT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE SET NULL,
    FOREIGN KEY (source_questionnaire) REFERENCES questionnaires(id_questionnaire) ON DELETE SET NULL
);

-- ==========================================================
-- Table : documents
-- ==========================================================
CREATE TABLE documents (
    id_document INT AUTO_INCREMENT PRIMARY KEY,
    id_fiche INT NOT NULL,
    nom_document VARCHAR(255),
    type_document ENUM('PDF','Rapport','Registre') DEFAULT 'PDF',
    date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    chemin_fichier VARCHAR(255),
    FOREIGN KEY (id_fiche) REFERENCES fiches(id_fiche) ON DELETE CASCADE
);

-- ==========================================================
-- Table : journal_actions
-- ==========================================================
CREATE TABLE journal_actions (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    action VARCHAR(255),
    cible VARCHAR(255),
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_origine VARCHAR(50),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

-- ==========================================================
-- Données de test : utilisateurs
-- Mot de passe = "Oliv2001!"
-- Hash bcrypt : $2y$10$u/nhHzTniqGy8ZtnBv1jK.zv4HrMezXxMIGs.F/33VZzvzLzEdbFu
-- ==========================================================
INSERT INTO utilisateurs (pseudo, mot_de_passe, email, nom, fonction, role) VALUES
('ADMIN', '$2y$10$u/nhHzTniqGy8ZtnBv1jK.zv4HrMezXxMIGs.F/33VZzvzLzEdbFu', 'admin@laprovidence-lasalle.fr', 'Administrateur Principal', 'Chef d’établissement', 'admin'),
('marie.prof', '$2y$10$u/nhHzTniqGy8ZtnBv1jK.zv4HrMezXxMIGs.F/33VZzvzLzEdbFu', 'marie.prof@laprovidence-lasalle.fr', 'Marie Dupont', 'Enseignante', 'utilisateur'),
('pierre.cpe', '$2y$10$u/nhHzTniqGy8ZtnBv1jK.zv4HrMezXxMIGs.F/33VZzvzLzEdbFu', 'pierre.cpe@laprovidence-lasalle.fr', 'Pierre Leroy', 'CPE', 'utilisateur'),
('julie.secretariat', '$2y$10$u/nhHzTniqGy8ZtnBv1jK.zv4HrMezXxMIGs.F/33VZzvzLzEdbFu', 'julie.secretariat@laprovidence-lasalle.fr', 'Julie Martin', 'Secrétaire', 'utilisateur');

-- ==========================================================
-- Données de production : questions (IA adaptées au milieu scolaire)
-- ==========================================================
INSERT INTO questions (fonction_cible, question_txt, type_reponse, options_reponse) VALUES
-- Questions communes à tous
(NULL, 'Utilisez-vous une session nominative avec mot de passe pour accéder à votre poste de travail ?', 'choix', '["Oui","Non"]'),
(NULL, 'Verrouillez-vous systématiquement votre session (Win+L) lorsque vous quittez votre poste ?', 'choix', '["Toujours","Parfois","Jamais"]'),
(NULL, 'Où stockez-vous vos documents contenant des données élèves ?', 'choix', '["ENT / Drive établissement","Clé USB non chiffrée","Ordinateur personnel","Disque dur externe"]'),
(NULL, 'Utilisez-vous votre téléphone personnel pour des échanges pros (WhatsApp, SMS, Appels) ?', 'choix', '["Oui","Non"]'),
(NULL, 'Quels autres logiciels, sites ou applications (hors Pronote/ENT) utilisez-vous avec vos élèves ?', 'texte', NULL),
(NULL, 'Comment éliminez-vous les documents papier contenant des données nominatives ?', 'choix', '["Broyeur de documents","Déchirage manuel","Poubelle classique"]'),

-- Enseignants
('Enseignant(e)', 'Utilisez-vous Pronote pour la saisie des appréciations et la communication ?', 'choix', '["Oui","Non"]'),
('Enseignant(e)', 'Utilisez-vous des outils tiers type Padlet, Genially ou Canva ?', 'choix', '["Oui","Non"]'),
('Enseignant(e)', 'Avez-vous recueilli l''autorisation de droit à l''image pour les photos prises en classe ?', 'choix', '["Oui","Parfois","Non"]'),
('Enseignant(e)', 'Utilisez-vous des messageries (WhatsApp/Discord) pour communiquer avec vos élèves ?', 'choix', '["Oui","Non"]'),

-- CPE / Vie Scolaire
('CPE', 'Accédez-vous à des données de santé (PAI, allergies) dans le cadre de votre mission ?', 'choix', '["Oui","Non"]'),
('CPE', 'Comment sont sécurisés les dossiers papier contenant des sanctions disciplinaires ?', 'choix', '["Armoire fermée à clé","Bureau ouvert","Salle d''archives"]'),
('Vie scolaire', 'Le cahier de sortie ou de retard est-il visible du public ou des autres élèves ?', 'choix', '["Non","Partiellement","Oui"]'),

-- Secrétariat
('Secrétaire', 'Le logiciel Aplim / Charlemagne est-il utilisé pour la gestion des élèves ?', 'choix', '["Oui","Non"]'),
('Secrétaire', 'Traitez-vous des dossiers de bourses ou des données financières (RIB) ?', 'choix', '["Oui","Non"]'),
('Secrétaire', 'Manipulez-vous des dossiers MDPH (handicap) ou des bilans psychologiques ?', 'choix', '["Oui","Non"]'),

-- Direction
('Direction', 'L''établissement dispose-t-il d''un RSSI (Registre de Sécurité SI) ?', 'choix', '["Oui","En cours","Non"]'),
('Direction', 'Les contrats avec vos sous-traitants incluent-ils les clauses RGPD ?', 'choix', '["Tous","Certains","Aucun"]');

-- ==========================================================
-- Données de test : questionnaires & réponses
-- ==========================================================
INSERT INTO questionnaires (id_utilisateur, fonction, statut, score_conformite, observations)
VALUES
(2, 'Enseignante', 'complete', 82.50, 'Bonne conformité générale, attention à l’absence de consentement explicite pour les photos.'),
(3, 'CPE', 'complete', 76.00, 'Conformité moyenne, à renforcer sur la durée de conservation des dossiers.'),
(4, 'Secrétaire', 'complete', 90.00, 'Conforme, mais vérifier la sécurisation des fichiers partagés.');

INSERT INTO reponses (id_questionnaire, id_question, reponse_utilisateur)
VALUES
(1, 1, 'Oui'),
(1, 2, 'Parfois'),
(2, 3, 'Conservation dans un dossier papier dans le bureau du CPE'),
(3, 4, 'Oui');

-- ==========================================================
-- Données de test : fiches
-- ==========================================================
INSERT INTO fiches (id_utilisateur, type_fiche, nom_fichier_pdf, chemin_stockage)
VALUES
(2, 'bilan', 'bilan_marieprof.pdf', '/pdf/bilan_marieprof.pdf'),
(2, 'bonnes_pratiques', 'pratiques_marieprof.pdf', '/pdf/pratiques_marieprof.pdf'),
(3, 'bilan', 'bilan_pierrecpe.pdf', '/pdf/bilan_pierrecpe.pdf'),
(4, 'bilan', 'bilan_juliesec.pdf', '/pdf/bilan_juliesec.pdf');

-- ==========================================================
-- Données : registre des traitements
-- ==========================================================
INSERT INTO registre_traitements (nom_traitement, finalite, categories_donnes, base_legale, personnes_concernees, responsable_traitement, duree_conservation, emplacement_stockage, mesures_securite, sous_traitants, pia_necessaire)
VALUES
('Dossiers élèves', 'Suivi administratif de la scolarité', 'Identité, santé, contact parent', 'Mission d’intérêt public', 'Élèves et parents', 'Chef d’établissement', 'Durée scolarité + 1 an', 'Serveur académique', 'Accès restreint, chiffrement, sauvegardes', 'ENT académique', 1),
('Gestion du personnel', 'Gestion RH et paie des salariés', 'Données personnelles, bancaires', 'Obligation légale', 'Personnel', 'Chef d’établissement', 'Fin du contrat + 5 ans', 'Serveur OGEC', 'Accès réservé, chiffrement', 'OGEC, cabinet comptable', 0);

-- ==========================================================
-- Journal des actions
-- ==========================================================
INSERT INTO journal_actions (id_utilisateur, action, cible, ip_origine)
VALUES
(2, 'Connexion', 'Tableau de bord', '192.168.0.12'),
(3, 'Soumission questionnaire', 'Q2', '192.168.0.13'),
(4, 'Téléchargement fiche', 'F4', '192.168.0.14');
