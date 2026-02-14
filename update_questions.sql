-- Suppression des anciennes questions de test
DELETE FROM questions;

-- Insertion des nouvelles questions conformes au milieu scolaire
INSERT INTO questions (fonction_cible, question_txt, type_reponse, options_reponse) VALUES
(NULL, 'Utilisez-vous une session nominative avec mot de passe pour accéder à votre poste de travail ?', 'choix', '["Oui","Non"]'),
(NULL, 'Verrouillez-vous systématiquement votre session (Win+L) lorsque vous quittez votre poste ?', 'choix', '["Toujours","Parfois","Jamais"]'),
(NULL, 'Où stockez-vous principalement vos documents de travail contenant des données élèves ?', 'choix', '["ENT / Drive établissement","Clé USB non chiffrée","Ordinateur personnel","Disque dur externe"]'),
('Enseignant(e)', 'Utilisez-vous des applications tierces (hors ENT) pour noter ou évaluer les élèves (Padlet, Kahoot, etc.) ?', 'choix', '["Oui","Non"]'),
('Enseignant(e)', 'Demandez-vous l''accord du référent RGPD avant d''utiliser un nouvel outil numérique avec les élèves ?', 'choix', '["Toujours","Parfois","Jamais"]'),
('Enseignant(e)', 'Comment partagez-vous des documents avec vos élèves ?', 'choix', '["ENT / Pronote","Email personnel","Clé USB"]'),
('CPE', 'Comment sont sécurisés les dossiers papier contenant des sanctions disciplinaires ?', 'choix', '["Armoire fermée à clé","Bureau ouvert","Salle d''archives sécurisée"]'),
('CPE', 'Transmettez-vous des informations médicales ou sociales par email non chiffré ?', 'choix', '["Jamais","Rarement","Parfois"]'),
('Vie scolaire', 'Les listes d''élèves avec numéros de téléphone sont-elles visibles des élèves ou du public ?', 'choix', '["Non","Parfois"]'),
('Secrétaire', 'Le registre des inscriptions contient-il des données de santé (APV, PAI) ?', 'choix', '["Oui","Non"]'),
('Secrétaire', 'Comment détruisez-vous les documents contenant des données personnelles (RIB, identité) ?', 'choix', '["Broyeur de documents","Poubelle classique","Archivage"]'),
('Secrétaire', 'Le public peut-il voir vos écrans lors de l''accueil à l''accueil ?', 'choix', '["Non","Uniquement partiellement","Oui"]'),
('AESH', 'Prenez-vous des notes sur vos élèves sur votre téléphone personnel ?', 'choix', '["Non","Oui"]'),
('AESH', 'Avec qui partagez-vous les informations sur le suivi de l''élève ?', 'choix', '["Uniquement l''enseignant et le CPE","Plusieurs collègues","Personne"]'),
('Direction', 'Avez-vous réalisé une cartographie de l''ensemble des traitements de données de l''établissement ?', 'choix', '["Oui","En cours","Non"]'),
('Direction', 'Le registre des traitements est-il régulièrement mis à jour ?', 'choix', '["Oui","À revoir"]');
