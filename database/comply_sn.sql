-- ============================================================
-- COMPLY-SN - Script de création et de démonstration
-- Base de données : comply_sn
-- Compatible : MariaDB 10.4+ / MySQL 8+
-- Encodage : UTF-8 / utf8mb4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `comply_sn`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `comply_sn`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- Suppression des tables existantes
-- Permet de réimporter proprement le script
-- ============================================================

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `evidences`;
DROP TABLE IF EXISTS `corrective_actions`;
DROP TABLE IF EXISTS `controls`;
DROP TABLE IF EXISTS `obligations`;
DROP TABLE IF EXISTS `regulations`;
DROP TABLE IF EXISTS `domains`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Table : users
-- ============================================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','advanced','standard') NOT NULL DEFAULT 'standard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : users
-- Les mots de passe sont stockés sous forme de hash bcrypt.
-- ============================================================

INSERT INTO `users`
(`id`, `full_name`, `email`, `password`, `role`, `created_at`)
VALUES
(1, 'Administrateur COMPLY-SN',
 'admin@comply-sn.local',
 '$2y$10$eIxYsNB3.z8dH4HSJ4s2.ujLGEEBAPf1BwMpujlusfX2xxJ41r6fG',
 'admin',
 '2026-08-13 12:57:01'),

(2, 'Fatou Diop',
 'fatou@comply-sn.local',
 '$2y$10$KV3TkTyopWNcCM2u6M2IAuMlui8L0oXTjzB3saxMBUOXJ713QOi62',
 'advanced',
 '2026-08-13 12:57:01'),

(3, 'Awa Ndiaye',
 'awa@comply-sn.local',
 '$2y$10$hCBZgEcX./n9yldY8atXkuqBSC6yZeQo6fIcv89j9Bf3Uldj.ROQO',
 'standard',
 '2026-08-13 12:57:01');

-- ============================================================
-- Table : domains
-- ============================================================

CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : domains
-- ============================================================

INSERT INTO `domains`
(`id`, `name`, `description`, `created_at`)
VALUES
(1, 'Fiscalité',
 'Gestion des obligations fiscales et déclaratives de l’organisation.',
 '2026-08-13 13:24:10'),

(2, 'Comptable',
 'Suivi des obligations comptables et financières.',
 '2026-08-13 13:24:10'),

(3, 'Protection des données',
 'Protection des données personnelles et respect des règles de confidentialité.',
 '2026-08-13 13:24:10'),

(4, 'Sécurité informatique',
 'Gestion des obligations relatives à la sécurité des systèmes d’information.',
 '2026-08-13 13:24:10'),

(5, 'Bancaire',
 'Suivi des obligations liées aux activités financières et bancaires.',
 '2026-08-13 13:24:10'),

(6, 'Ressources humaines',
 'Gestion des obligations liées aux ressources humaines.',
 '2026-08-13 15:29:53');

-- ============================================================
-- Table : regulations
-- ============================================================

CREATE TABLE `regulations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `reference` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_regulation_domain` (`domain_id`),
  CONSTRAINT `fk_regulation_domain`
    FOREIGN KEY (`domain_id`)
    REFERENCES `domains` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : regulations
-- ============================================================

INSERT INTO `regulations`
(`id`, `domain_id`, `title`, `reference`, `description`, `effective_date`, `created_at`)
VALUES
(1, 1,
 'Code général des impôts',
 'CGI-SN',
 'Référentiel regroupant les principales règles et obligations fiscales applicables au Sénégal.',
 '2025-01-01',
 '2026-08-13 13:27:10'),

(2, 2,
 'Acte uniforme relatif au droit comptable et à l’information financière',
 'SYSCOHADA',
 'Référentiel comptable applicable dans l’espace OHADA.',
 '2018-01-01',
 '2026-08-13 13:27:10'),

(3, 3,
 'Loi sur la protection des données personnelles',
 'Loi n° 2008-12',
 'Cadre juridique sénégalais relatif à la protection des données à caractère personnel.',
 '2008-01-25',
 '2026-08-13 13:27:10'),

(4, 4,
 'Référentiel de sécurité informatique',
 'SEC-IT-001',
 'Référentiel interne de contrôle et de sécurité des systèmes d’information.',
 '2026-01-01',
 '2026-08-13 13:27:10'),

(5, 5,
 'Réglementation bancaire et financière',
 'BCEAO',
 'Référentiel de conformité applicable aux activités bancaires et financières.',
 '2025-01-01',
 '2026-08-13 13:27:10');

-- ============================================================
-- Table : obligations
-- ============================================================

CREATE TABLE `obligations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regulation_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `criticality` enum('Faible','Moyenne','Élevée','Critique') NOT NULL DEFAULT 'Moyenne',
  `status` enum('Conforme','Non conforme','En cours','À vérifier') NOT NULL DEFAULT 'À vérifier',
  `responsible_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_obligation_regulation` (`regulation_id`),
  KEY `fk_obligation_user` (`responsible_user_id`),
  CONSTRAINT `fk_obligation_regulation`
    FOREIGN KEY (`regulation_id`)
    REFERENCES `regulations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_obligation_user`
    FOREIGN KEY (`responsible_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : obligations
-- ============================================================

INSERT INTO `obligations`
(`id`, `regulation_id`, `title`, `description`, `frequency`,
 `due_date`, `criticality`, `status`, `responsible_user_id`, `created_at`)
VALUES

(1, 1,
 'Déclaration des obligations fiscales',
 'Préparer et transmettre les déclarations fiscales dans les délais réglementaires.',
 'Mensuelle',
 '2026-08-31',
 'Critique',
 'En cours',
 1,
 '2026-08-13 13:28:54'),

(2, 1,
 'Paiement des impôts et taxes',
 'Vérifier le calcul et le paiement des impôts et taxes dus.',
 'Mensuelle',
 '2026-08-31',
 'Élevée',
 'Conforme',
 2,
 '2026-08-13 13:28:54'),

(3, 2,
 'Tenue régulière de la comptabilité',
 'Garantir l’enregistrement correct et régulier des opérations comptables.',
 'Continue',
 '2026-12-31',
 'Élevée',
 'Conforme',
 2,
 '2026-08-13 13:28:54'),

(4, 2,
 'Conservation des pièces comptables',
 'Assurer la conservation et la disponibilité des justificatifs comptables.',
 'Continue',
 '2026-12-31',
 'Moyenne',
 'Non conforme',
 3,
 '2026-08-13 13:28:54'),

(5, 3,
 'Protection des données personnelles',
 'Mettre en place des mesures permettant de protéger les données personnelles.',
 'Continue',
 '2026-12-31',
 'Critique',
 'En cours',
 1,
 '2026-08-13 13:28:54'),

(6, 3,
 'Gestion des accès aux données',
 'Limiter l’accès aux données personnelles aux personnes autorisées.',
 'Trimestrielle',
 '2026-09-30',
 'Élevée',
 'Conforme',
 2,
 '2026-08-13 13:28:54'),

(8, 4,
 'Sauvegarde des données',
 'Effectuer régulièrement des sauvegardes et vérifier leur restauration.',
 'Hebdomadaire',
 '2026-08-16',
 'Critique',
 'En cours',
 3,
 '2026-08-13 13:28:54'),

(9, 5,
 'Surveillance des opérations',
 'Surveiller les opérations afin d’identifier les anomalies et risques de non-conformité.',
 'Continue',
 '2026-12-31',
 'Critique',
 'À vérifier',
 1,
 '2026-08-13 13:28:54'),

(10, 5,
 'Archivage des documents réglementaires',
 'Conserver les documents nécessaires au suivi de la conformité.',
 'Mensuelle',
 '2026-09-30',
 'Moyenne',
 'Conforme',
 3,
 '2026-08-13 13:28:54');

-- ============================================================
-- Table : controls
-- ============================================================

CREATE TABLE `controls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `obligation_id` int(11) NOT NULL,
  `controlled_by` int(11) NOT NULL,
  `control_date` date NOT NULL,
  `result` enum('Conforme','Non conforme','Partiellement conforme') NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_control_obligation` (`obligation_id`),
  KEY `fk_control_user` (`controlled_by`),
  CONSTRAINT `fk_control_obligation`
    FOREIGN KEY (`obligation_id`)
    REFERENCES `obligations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_control_user`
    FOREIGN KEY (`controlled_by`)
    REFERENCES `users` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : controls
-- ============================================================

INSERT INTO `controls`
(`id`, `obligation_id`, `controlled_by`, `control_date`,
 `result`, `comment`, `created_at`)
VALUES

(1, 1, 2,
 '2026-08-05',
 'Conforme',
 'Les déclarations fiscales ont été vérifiées.',
 '2026-08-13 13:29:52'),

(2, 2, 2,
 '2026-08-06',
 'Conforme',
 'Les paiements ont été contrôlés.',
 '2026-08-13 13:29:52'),

(3, 3, 2,
 '2026-08-07',
 'Conforme',
 'Les écritures comptables ont été vérifiées.',
 '2026-08-13 13:29:52'),

(4, 4, 3,
 '2026-08-08',
 'Partiellement conforme',
 'Certaines pièces comptables nécessitent une vérification.',
 '2026-08-13 13:29:52'),

(5, 5, 1,
 '2026-08-08',
 'Non conforme',
 'Des mesures supplémentaires de protection des données sont nécessaires.',
 '2026-08-13 13:29:52'),

(6, 6, 2,
 '2026-08-09',
 'Conforme',
 'Les droits d’accès ont été contrôlés.',
 '2026-08-13 13:29:52'),

(8, 8, 3,
 '2026-08-10',
 'Partiellement conforme',
 'La procédure de sauvegarde doit être améliorée.',
 '2026-08-13 13:29:52'),

(9, 9, 1,
 '2026-08-11',
 'Non conforme',
 'Une anomalie a été identifiée dans la surveillance des opérations.',
 '2026-08-13 13:29:52'),

(10, 10, 3,
 '2026-08-12',
 'Conforme',
 'Les documents réglementaires sont correctement archivés.',
 '2026-08-13 13:29:52');

-- ============================================================
-- Table : corrective_actions
-- ============================================================

CREATE TABLE `corrective_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `control_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `responsible_user_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('À faire','En cours','Terminée','En retard') NOT NULL DEFAULT 'À faire',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_action_control` (`control_id`),
  KEY `fk_action_user` (`responsible_user_id`),
  CONSTRAINT `fk_action_control`
    FOREIGN KEY (`control_id`)
    REFERENCES `controls` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_action_user`
    FOREIGN KEY (`responsible_user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : corrective_actions
-- ============================================================

INSERT INTO `corrective_actions`
(`id`, `control_id`, `title`, `description`,
 `responsible_user_id`, `due_date`, `status`, `created_at`)
VALUES

(1, 4,
 'Compléter les pièces comptables',
 'Identifier et compléter les pièces justificatives manquantes.',
 3,
 '2026-08-25',
 'En cours',
 '2026-08-13 13:32:33'),

(2, 5,
 'Renforcer la protection des données',
 'Mettre en place des mesures supplémentaires de protection des données personnelles.',
 1,
 '2026-08-30',
 'À faire',
 '2026-08-13 13:32:33'),

(3, 8,
 'Améliorer la procédure de sauvegarde',
 'Formaliser et tester régulièrement la procédure de sauvegarde.',
 3,
 '2026-08-20',
 'En cours',
 '2026-08-13 13:32:33'),

(4, 9,
 'Corriger l’anomalie de surveillance',
 'Analyser l’anomalie détectée et mettre en œuvre les mesures correctives.',
 1,
 '2026-08-22',
 'À faire',
 '2026-08-13 13:32:33');

-- ============================================================
-- Table : evidences
-- ============================================================

CREATE TABLE `evidences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `control_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_evidence_control` (`control_id`),
  KEY `fk_evidence_user` (`uploaded_by`),
  CONSTRAINT `fk_evidence_control`
    FOREIGN KEY (`control_id`)
    REFERENCES `controls` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_evidence_user`
    FOREIGN KEY (`uploaded_by`)
    REFERENCES `users` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : evidences
-- Aucun fichier issu des essais personnels n'est référencé ici.
-- ============================================================

-- Pas de fichier de démonstration afin d'éviter de référencer
-- un fichier local absent du dépôt GitHub.

-- ============================================================
-- Table : audit_logs
-- ============================================================

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de démonstration : audit_logs
-- Les logs issus des essais personnels ont été supprimés.
-- ============================================================

-- Les journaux seront générés automatiquement par l'application.

-- ============================================================
-- Fin du script
-- ============================================================

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;