-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 27 mai 2025 à 00:33
-- Version du serveur : 11.4.7-MariaDB
-- Version de PHP : 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `puev4583_portfolio_evan_2025`
--

-- --------------------------------------------------------

--
-- Structure de la table `Commentaires`
--

CREATE TABLE `Commentaires` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` date DEFAULT curdate(),
  `id_utilisateur` int(11) NOT NULL,
  `id_trace` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Fichiers_traces`
--

CREATE TABLE `Fichiers_traces` (
  `id_fichier` int(11) NOT NULL,
  `id_trace` int(11) NOT NULL,
  `fichier_url` varchar(255) NOT NULL,
  `type_fichier` enum('image','video') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Logs`
--

CREATE TABLE `Logs` (
  `id_log` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `date_log` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Traces`
--

CREATE TABLE `Traces` (
  `id_trace` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `type_trace_id` int(11) NOT NULL,
  `annee_creation_BUT` int(11) NOT NULL,
  `date_ajout` date DEFAULT curdate(),
  `apprentissage_critique` text DEFAULT NULL,
  `competence_BUT` text DEFAULT NULL,
  `argumentaire_5W` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Types_de_traces`
--

CREATE TABLE `Types_de_traces` (
  `id_type` int(11) NOT NULL,
  `nom_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateurs`
--

CREATE TABLE `Utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('concepteur','evaluateur','visiteur') NOT NULL,
  `statut_validation` tinyint(1) DEFAULT 0,
  `date_inscription` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Utilisateurs`
--

INSERT INTO `Utilisateurs` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `statut_validation`, `date_inscription`) VALUES
(1, 'Dupont', 'Claire', 'claire.dupont@example.com', '75216c44a46bfff78f692d1fe695c02a407a2136625dcc17ca6cf3141e0c4c72', 'visiteur', 0, '2025-05-21'),
(2, 'Martin', 'Jean', 'jean.martin@example.com', 'c6ba91b90d922e159893f46c387e5dc1b3dc5c101a5a4522f03b987177a24a91', 'evaluateur', 1, '2025-05-21');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Commentaires`
--
ALTER TABLE `Commentaires`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_trace` (`id_trace`);

--
-- Index pour la table `Fichiers_traces`
--
ALTER TABLE `Fichiers_traces`
  ADD PRIMARY KEY (`id_fichier`),
  ADD KEY `id_trace` (`id_trace`);

--
-- Index pour la table `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `Traces`
--
ALTER TABLE `Traces`
  ADD PRIMARY KEY (`id_trace`),
  ADD KEY `type_trace_id` (`type_trace_id`);

--
-- Index pour la table `Types_de_traces`
--
ALTER TABLE `Types_de_traces`
  ADD PRIMARY KEY (`id_type`);

--
-- Index pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Commentaires`
--
ALTER TABLE `Commentaires`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Fichiers_traces`
--
ALTER TABLE `Fichiers_traces`
  MODIFY `id_fichier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Logs`
--
ALTER TABLE `Logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Traces`
--
ALTER TABLE `Traces`
  MODIFY `id_trace` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Types_de_traces`
--
ALTER TABLE `Types_de_traces`
  MODIFY `id_type` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Commentaires`
--
ALTER TABLE `Commentaires`
  ADD CONSTRAINT `Commentaires_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `Utilisateurs` (`id_utilisateur`),
  ADD CONSTRAINT `Commentaires_ibfk_2` FOREIGN KEY (`id_trace`) REFERENCES `Traces` (`id_trace`);

--
-- Contraintes pour la table `Fichiers_traces`
--
ALTER TABLE `Fichiers_traces`
  ADD CONSTRAINT `Fichiers_traces_ibfk_1` FOREIGN KEY (`id_trace`) REFERENCES `Traces` (`id_trace`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `Logs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `Utilisateurs` (`id_utilisateur`) ON DELETE SET NULL;

--
-- Contraintes pour la table `Traces`
--
ALTER TABLE `Traces`
  ADD CONSTRAINT `Traces_ibfk_1` FOREIGN KEY (`type_trace_id`) REFERENCES `Types_de_traces` (`id_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
