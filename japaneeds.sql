-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 27 mai 2025 à 09:57
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `japaneeds`
--

-- --------------------------------------------------------

--
-- Structure de la table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cities`
--

INSERT INTO `cities` (`id`, `name`, `slug`) VALUES
(1, 'Kyoto', 'kyoto'),
(2, 'Osaka', 'osaka'),
(3, 'Himeji', 'himeji');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `guides`
--

CREATE TABLE `guides` (
  `id` int(11) NOT NULL,
  `speciality_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nb_places` int(11) DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `smoking` tinyint(1) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `prefernces` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `guides`
--

INSERT INTO `guides` (`id`, `speciality_id`, `user_id`, `nb_places`, `languages`, `smoking`, `description`, `prefernces`) VALUES
(1, 1, 1, 1, 'Français', 0, 'Bonjour c\'est Thomas', NULL),
(2, 3, 2, 1, 'Japonais', 0, 'Bonjour, je m\'appelle Miho, et je suis guide touristique au Japon depuis plusieurs années. Passionnée par la cuisine japonaise, l’histoire de mon pays et les échanges interculturels, je suis ravie de vous accompagner dans la découverte de ce pays fascinant.', NULL),
(3, 2, 3, 1, 'Anglais', 0, 'Hello', NULL),
(6, 2, 4, 1, 'Anglais', 1, 'Bonjour je suis Julia', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `guides_cities`
--

CREATE TABLE `guides_cities` (
  `guides_id` int(11) NOT NULL,
  `cities_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `guides_cities`
--

INSERT INTO `guides_cities` (`guides_id`, `cities_id`) VALUES
(1, 1),
(1, 3),
(2, 1),
(6, 1);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `day` date NOT NULL,
  `begin` time NOT NULL,
  `end` time NOT NULL,
  `meal` tinyint(1) NOT NULL,
  `price` int(11) NOT NULL,
  `places_dispo` int(11) NOT NULL DEFAULT 0,
  `reserv_number` varchar(255) NOT NULL,
  `status` enum('A venir','En cours','Fini','Confirmé','Vérification par la plateforme') NOT NULL DEFAULT 'A venir',
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `guide_id`, `city_id`, `day`, `begin`, `end`, `meal`, `price`, `places_dispo`, `reserv_number`, `status`, `address`) VALUES
(2, 2, 2, '2025-03-31', '11:07:45', '18:07:45', 1, 15, 0, 'RES#35babe0c', 'A venir', NULL),
(3, 3, 3, '2025-03-31', '10:07:45', '17:07:45', 0, 20, 0, 'RES#35babf3b', 'A venir', NULL),
(5, 6, 1, '2025-04-02', '16:57:00', '17:57:00', 1, 20, 0, 'RES#35bac004', 'A venir', NULL),
(9, 1, 3, '2025-04-11', '14:25:00', '14:25:00', 1, 20, 0, 'RES#35bac18d', 'Confirmé', NULL),
(14, 1, 3, '2025-04-10', '11:00:00', '20:00:00', 1, 20, 0, 'RES#9a6572f8', 'Confirmé', 'Ekimaecho, Himeji, Hyogo 670-0927, Japon'),
(15, 1, 3, '2025-03-31', '18:33:00', '20:33:00', 0, 25, 1, 'RES#b482fbcc', 'Fini', 'Japon, 〒670-0927 兵庫県姫路市駅前町188-1'),
(20, 2, 1, '2025-04-21', '16:46:00', '17:46:00', 0, 20, 0, 'RES#11d290da', 'Confirmé', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(21, 2, 1, '2025-04-21', '16:58:00', '15:00:00', 0, 20, 0, 'RES#d4e3efb9', 'Confirmé', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(22, 6, 1, '2025-04-21', '10:53:00', '12:54:00', 0, 20, 0, 'RES#98efe19d', 'Confirmé', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(23, 2, 1, '2025-04-22', '14:41:00', '16:41:00', 0, 20, 0, 'RES#a3c13124', 'Confirmé', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(24, 2, 1, '2025-04-29', '10:00:00', '17:00:00', 0, 20, 0, 'RES#60806657', 'Confirmé', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(25, 6, 1, '2025-05-31', '11:00:00', '18:00:00', 0, 20, 1, 'RES#9271b7a8', 'A venir', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(26, 1, 1, '2025-05-31', '14:00:00', '20:00:00', 1, 15, 1, 'RES#4f4ae7af', 'A venir', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon'),
(27, 2, 1, '2025-05-31', '09:00:00', '17:00:00', 1, 25, 1, 'RES#a911e27e', 'A venir', 'Kyoto Station, Rue Shiokōji, Shiokōji Nord, Arrondissement de Shimogyō, Kyoto, Préfecture de Kyoto, 600-8216, Japon');

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `commentary` longtext NOT NULL,
  `validate` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `guide_id`, `reservation_id`, `rate`, `commentary`, `validate`) VALUES
(1, 5, 2, 21, 5, 'Très bonne journée passé avec Miho', 1),
(2, 5, 2, 20, 4, 'Très bonne journée, trop courte', 1),
(6, 5, 1, 14, 2, 'Guide pas organisé', 1),
(7, 5, 2, 24, 2, 'Non', 1);

-- --------------------------------------------------------

--
-- Structure de la table `specialities`
--

CREATE TABLE `specialities` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `specialities`
--

INSERT INTO `specialities` (`id`, `name`, `slug`) VALUES
(1, 'Histoire', 'histoire'),
(2, 'Vie locale', 'vie locale'),
(3, 'Culinaire', 'culinaire');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `credits` int(11) NOT NULL DEFAULT 20,
  `picture` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `roles`, `password`, `pseudo`, `credits`, `picture`, `is_verified`) VALUES
(1, 'nt@mail.fr', '[\"ROLE_ADMIN\"]', '$2y$13$jcEp57MJeoje9mS2fuwqveZwSfaXwP0fhBhhtV2uNBEmyLqYiEBtS', 'thomas', 94, '38f97374c323073dad7aad54fe0478fd.jpeg', 0),
(2, 'ntb@mail.fr', '[]', '$2y$13$/v9ptacuQFjqLNn9ev6wsuiE83vxpOVHsfBx4pvm4QVfJ9Fq5F3/i', 'miho', 182, '', 0),
(3, 'ntc@mail.fr', '[]', '$2y$13$0OpXZEPciEd4L4BwAYye8Os9C7onnbFmXGDugnfzCcF2hxNEODdX.', 'Oliver', 0, 'ab09d4afdce1c42a3872b4eb4033cf78.jpeg', 0),
(4, 'ntd@mail.fr', '[]', '$2y$13$oBh9BKYJUMrySYJgwmu/seyPKL6eV/Mh4TjtvjhQPV2iRi9.QvzYO', 'julia', 18, '4feaa5c865c1e07ae1c667af89bf6056.jpeg', 0),
(5, 'ntab@mail.fr', '[]', '$2y$13$AXCycRlqqu/JrljBpyO9/unM7GzA7jGYmFOgdo.gOCWiUrwfr96SW', 'chiharu', 20, 'c6a50cc19e1eb1087b54295d8c1d15fa.jpeg', 1),
(6, 'ntb@mailt.fr', '[]', '$2y$13$zK9k8hUdwCNDfVgRTg5tyuU2F778oJG45m6bmKNaZPrZ1Snbg7.VG', 'thomas', 20, NULL, 1),
(7, 'nt@maail.fr', '[]', '$2y$13$nDOjFmXQpYJGWRFxPfkjMeIXsCWqlDTged44aS2WIdDmqVaS0IPPe', 'thomas', 20, NULL, 1),
(9, 'ntbb@mail.fr', '[\"ROLE_STAFF\"]', '$2y$13$2jDpXXpXL6hDH2RrtXE15Oph5yjLpGeB77ggC9pfLDKHzLnwvXzKq', 'azerty', 20, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users_reservations`
--

CREATE TABLE `users_reservations` (
  `users_id` int(11) NOT NULL,
  `reservations_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users_reservations`
--

INSERT INTO `users_reservations` (`users_id`, `reservations_id`) VALUES
(3, 22),
(4, 23),
(5, 9),
(5, 14),
(5, 20),
(5, 21),
(5, 24);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_4D7795EFA76ED395` (`user_id`),
  ADD KEY `IDX_4D7795EF3B5A08D7` (`speciality_id`);

--
-- Index pour la table `guides_cities`
--
ALTER TABLE `guides_cities`
  ADD PRIMARY KEY (`guides_id`,`cities_id`),
  ADD KEY `IDX_BAA1BBD96A8C820` (`guides_id`),
  ADD KEY `IDX_BAA1BBD9CAC75398` (`cities_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4DA239D7ED1D4B` (`guide_id`),
  ADD KEY `IDX_4DA2398BAC62AF` (`city_id`);

--
-- Index pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6970EB0FA76ED395` (`user_id`),
  ADD KEY `IDX_6970EB0FD7ED1D4B` (`guide_id`),
  ADD KEY `IDX_6970EB0FB83297E7` (`reservation_id`);

--
-- Index pour la table `specialities`
--
ALTER TABLE `specialities`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- Index pour la table `users_reservations`
--
ALTER TABLE `users_reservations`
  ADD PRIMARY KEY (`users_id`,`reservations_id`),
  ADD KEY `IDX_5309843567B3B43D` (`users_id`),
  ADD KEY `IDX_53098435D9A7F869` (`reservations_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `guides`
--
ALTER TABLE `guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `specialities`
--
ALTER TABLE `specialities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `guides`
--
ALTER TABLE `guides`
  ADD CONSTRAINT `FK_4D7795EF3B5A08D7` FOREIGN KEY (`speciality_id`) REFERENCES `specialities` (`id`),
  ADD CONSTRAINT `FK_4D7795EFA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `guides_cities`
--
ALTER TABLE `guides_cities`
  ADD CONSTRAINT `FK_BAA1BBD96A8C820` FOREIGN KEY (`guides_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BAA1BBD9CAC75398` FOREIGN KEY (`cities_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `FK_4DA2398BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  ADD CONSTRAINT `FK_4DA239D7ED1D4B` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`);

--
-- Contraintes pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `FK_6970EB0FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_6970EB0FD7ED1D4B` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
