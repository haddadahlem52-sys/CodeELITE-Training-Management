-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 18 avr. 2026 à 16:39
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `centredeformations`
--

-- --------------------------------------------------------

--
-- Structure de la table `alerte_absence`
--

CREATE TABLE `alerte_absence` (
  `id_alerte` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_formation` int(11) NOT NULL,
  `nb_absences` int(11) NOT NULL,
  `date_alerte` datetime DEFAULT current_timestamp(),
  `statut` enum('non_lu','lu','traite') DEFAULT 'non_lu',
  `commentaire` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE `compte` (
  `id_compte` int(11) NOT NULL,
  `statut` enum('actif','desactive') DEFAULT 'actif',
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `compte`
--

INSERT INTO `compte` (`id_compte`, `statut`, `id_user`) VALUES
(11, 'desactive', 33),
(12, 'desactive', 36),
(13, 'actif', 43),
(14, 'actif', 48);

-- --------------------------------------------------------

--
-- Structure de la table `formateur`
--

CREATE TABLE `formateur` (
  `id_formateur` int(11) NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `etat` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formateur`
--

INSERT INTO `formateur` (`id_formateur`, `specialite`, `etat`) VALUES
(42, 'informatique', 1),
(45, 'informatique', 0),
(46, 'marketing', 0);

-- --------------------------------------------------------

--
-- Structure de la table `formations`
--

CREATE TABLE `formations` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prerequis` varchar(255) DEFAULT NULL,
  `type` enum('Presentiel','enligne') NOT NULL,
  `duree` int(11) NOT NULL,
  `id_formateur` int(11) NOT NULL,
  `id_salle` int(11) DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formations`
--

INSERT INTO `formations` (`id`, `titre`, `description`, `prerequis`, `type`, `duree`, `id_formateur`, `id_salle`, `date_debut`, `date_fin`, `created_at`) VALUES
(2, 'finance', 'tous les personnes', '.', 'Presentiel', 30, 42, 13, '2026-04-01', '2026-04-30', '2026-04-04 19:14:18'),
(3, 'programmation web', 'au moins un niveau baccalauréat', 'baccalauréat', 'Presentiel', 10, 45, 15, '2026-04-01', '2026-05-02', '2026-04-06 22:54:12');

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `id_compte` int(11) DEFAULT NULL,
  `id_session` int(11) DEFAULT NULL,
  `date_inscription` date DEFAULT curdate(),
  `statut` varchar(20) DEFAULT 'en cours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id_inscription`, `id_compte`, `id_session`, `date_inscription`, `statut`) VALUES
(1, 13, 5, '2026-04-01', 'en cours'),
(3, 14, 5, '2026-04-01', 'en cours'),
(4, 14, 7, '2026-04-03', 'en cours');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `nom_titulaire` varchar(100) DEFAULT NULL,
  `numero` varchar(30) DEFAULT NULL COMMENT 'Stocker uniquement les 4 derniers chiffres ex: ****1234',
  `code_pin` char(4) DEFAULT NULL,
  `id_inscription` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id_paiement`, `cin`, `nom_titulaire`, `numero`, `code_pin`, `id_inscription`) VALUES
(1, '12345678', 'Derbali Sarah', '************4521', NULL, 1),
(4, '87654321', 'Ayed Amina', '************7893', NULL, 4),
(5, '153366265', 'ahlem haddad', '2587', '1675', 3);

-- --------------------------------------------------------

--
-- Structure de la table `participant`
--

CREATE TABLE `participant` (
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participant`
--

INSERT INTO `participant` (`id_user`) VALUES
(33),
(36),
(43),
(48);

-- --------------------------------------------------------

--
-- Structure de la table `presence`
--

CREATE TABLE `presence` (
  `id_presence` int(11) NOT NULL,
  `id_participant` int(11) DEFAULT NULL,
  `id_session` int(11) DEFAULT NULL,
  `date_presence` date DEFAULT NULL,
  `statut` enum('present','absent') DEFAULT 'absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `presence`
--

INSERT INTO `presence` (`id_presence`, `id_participant`, `id_session`, `date_presence`, `statut`) VALUES
(1, 48, 5, '2026-04-18', 'absent'),
(2, 43, 5, '2026-04-18', 'present');

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

CREATE TABLE `salle` (
  `id_salle` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL,
  `disponibilite` enum('disponible','indisponible') DEFAULT 'disponible',
  `type_salle` enum('informatique','classique','conference') DEFAULT NULL,
  `reference` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `salle`
--

INSERT INTO `salle` (`id_salle`, `nom`, `capacite`, `disponibilite`, `type_salle`, `reference`) VALUES
(13, 'SALLE A3', 50, 'indisponible', 'classique', 4030),
(15, 'SALLE A50', 100, 'disponible', 'conference', 5000);

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `id_formation` int(11) DEFAULT NULL,
  `date_session` date DEFAULT NULL,
  `id_formateur` int(11) DEFAULT NULL,
  `duree` int(11) DEFAULT NULL,
  `id_salle` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `titre`, `id_formation`, `date_session`, `id_formateur`, `duree`, `id_salle`) VALUES
(5, 'Introduction Finance', 2, '2026-04-05', 42, 15, 13),
(6, 'Bases HTML & CSS', 3, '2026-04-10', 45, 10, 15),
(7, 'JavaScript Fondamentaux', 3, '2026-04-20', 45, 10, 15);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `role` enum('admin','formateur','participant') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `tel`, `role`) VALUES
(1, 'guerfala', 'nour', 'nour@gmail.com', 'nour28993740', '28993740', 'admin'),
(33, 'fattoum', 'hajer', 'fattoum@gmail.com', 're159', '32654987', 'participant'),
(34, 'mohamed', 'amine', 'amine@gmail', '$2y$10$QZYSJIpzEQmhW7Xh6kj0EuIPUVVyfj.4lwW/qS3hSwQZuILgIU45a', '', 'formateur'),
(36, 'missaoui', 'nesrine', 'nesrine@gmail.com', '1234567', '2588026', 'participant'),
(42, 'ahmed', 'khalil', 'khalil@gmail.com', 'khalil10', '56987123', 'formateur'),
(43, 'derbali', 'sarah', 'derbali@gmail.com', 'derba123', '92654789', 'participant'),
(45, 'ben ahmed', 'yessine', 'benahmed@gmail.com', '123456', '22589630', 'formateur'),
(46, 'ben jebril', 'foued', 'foued@gmail.com', '789654', '25996357', 'formateur'),
(48, 'ayed', 'amina', 'amina@gmail.com', 'amina123', '22589741', 'participant');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alerte_absence`
--
ALTER TABLE `alerte_absence`
  ADD PRIMARY KEY (`id_alerte`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_formation` (`id_formation`);

--
-- Index pour la table `compte`
--
ALTER TABLE `compte`
  ADD PRIMARY KEY (`id_compte`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Index pour la table `formateur`
--
ALTER TABLE `formateur`
  ADD PRIMARY KEY (`id_formateur`);

--
-- Index pour la table `formations`
--
ALTER TABLE `formations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_formations_formateur` (`id_formateur`),
  ADD KEY `fk_formations_salle` (`id_salle`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `id_compte` (`id_compte`),
  ADD KEY `id_session` (`id_session`);

--
-- Index pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD KEY `id_inscription` (`id_inscription`);

--
-- Index pour la table `participant`
--
ALTER TABLE `participant`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `presence`
--
ALTER TABLE `presence`
  ADD PRIMARY KEY (`id_presence`),
  ADD KEY `fk_participant` (`id_participant`),
  ADD KEY `fk_session` (`id_session`);

--
-- Index pour la table `salle`
--
ALTER TABLE `salle`
  ADD PRIMARY KEY (`id_salle`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sessions_salle` (`id_salle`),
  ADD KEY `fk_sessions_formation` (`id_formation`),
  ADD KEY `fk_sessions_formateur` (`id_formateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alerte_absence`
--
ALTER TABLE `alerte_absence`
  MODIFY `id_alerte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `compte`
--
ALTER TABLE `compte`
  MODIFY `id_compte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `formations`
--
ALTER TABLE `formations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `presence`
--
ALTER TABLE `presence`
  MODIFY `id_presence` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `salle`
--
ALTER TABLE `salle`
  MODIFY `id_salle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `alerte_absence`
--
ALTER TABLE `alerte_absence`
  ADD CONSTRAINT `alerte_absence_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `alerte_absence_ibfk_2` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id`);

--
-- Contraintes pour la table `compte`
--
ALTER TABLE `compte`
  ADD CONSTRAINT `compte_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `formateur`
--
ALTER TABLE `formateur`
  ADD CONSTRAINT `formateur_ibfk_1` FOREIGN KEY (`id_formateur`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `formations`
--
ALTER TABLE `formations`
  ADD CONSTRAINT `fk_formations_formateur` FOREIGN KEY (`id_formateur`) REFERENCES `formateur` (`id_formateur`),
  ADD CONSTRAINT `fk_formations_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id_salle`);

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_compte`) REFERENCES `compte` (`id_compte`),
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_session`) REFERENCES `sessions` (`id`);

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `paiement_ibfk_1` FOREIGN KEY (`id_inscription`) REFERENCES `inscription` (`id_inscription`);

--
-- Contraintes pour la table `participant`
--
ALTER TABLE `participant`
  ADD CONSTRAINT `fk_participant_user` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `presence`
--
ALTER TABLE `presence`
  ADD CONSTRAINT `fk_participant` FOREIGN KEY (`id_participant`) REFERENCES `participant` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_session` FOREIGN KEY (`id_session`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_formateur` FOREIGN KEY (`id_formateur`) REFERENCES `formateur` (`id_formateur`),
  ADD CONSTRAINT `fk_sessions_formation` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sessions_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id_salle`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
