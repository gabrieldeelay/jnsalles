-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 11/07/2025 às 04:18
-- Versão do servidor: 10.11.6-MariaDB-log
-- Versão do PHP: 8.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gemeos2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cart_list`
--

CREATE TABLE `cart_list` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `config` varchar(2000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_list`
--

CREATE TABLE `customer_list` (
  `id` int(11) NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `phone` text NOT NULL,
  `email` text DEFAULT NULL,
  `password` text DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cpf` text DEFAULT NULL,
  `zipcode` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `number` text DEFAULT NULL,
  `neighborhood` text DEFAULT NULL,
  `complement` text DEFAULT NULL,
  `state` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `reference_point` text DEFAULT NULL,
  `is_affiliate` tinyint(1) DEFAULT 0,
  `birth` date DEFAULT NULL,
  `instagram` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `origin` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs`
--

INSERT INTO `logs` (`id`, `origin`, `description`, `date`) VALUES
(592, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:31:55'),
(593, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:31:59'),
(594, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:32:49'),
(595, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:33:16'),
(596, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:34:02'),
(597, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:34:07'),
(598, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:34:11'),
(599, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:34:26'),
(600, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-10 12:34:29'),
(601, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-11 00:06:35'),
(602, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-12 12:26:28'),
(603, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-12 12:38:07'),
(604, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-12 12:43:38'),
(605, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-12 12:55:41'),
(606, 'ORDER', 'Pedido 399 aprovado manualmente pelo usuário Premium ', '2025-05-12 12:56:42'),
(607, 'ORDER', 'Pedido 400 aprovado manualmente pelo usuário Premium ', '2025-05-12 12:58:25'),
(608, 'ORDER', 'Pedido 401 aprovado manualmente pelo usuário Premium ', '2025-05-12 13:02:08'),
(609, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-12 13:05:37'),
(610, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-12 13:08:06'),
(611, 'ORDER', 'Pedido 402 aprovado manualmente pelo usuário Premium ', '2025-05-12 13:08:18'),
(612, 'ORDER', 'Pedido 402 aprovado manualmente pelo usuário Premium ', '2025-05-12 13:08:19'),
(613, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-12 13:29:44'),
(614, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-12 13:35:07'),
(615, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-12 13:36:46'),
(616, 'ORDER', 'Pedido manual 407 criado pelo usuário Premium ', '2025-05-12 13:49:56'),
(617, 'ORDER', 'Pedido manual 408 criado pelo usuário Premium ', '2025-05-12 13:57:46'),
(618, 'ORDER', 'Pedido 409 aprovado manualmente pelo usuário Premium ', '2025-05-12 14:24:04'),
(619, 'ORDER', 'Pedido 409 aprovado manualmente pelo usuário Premium ', '2025-05-12 14:24:21'),
(620, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-12 20:30:35'),
(621, 'ORDER', 'Pedido 412 aprovado manualmente pelo usuário Premium ', '2025-05-13 12:32:34'),
(622, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 12:33:43'),
(623, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 14:18:59'),
(624, 'ORDER', 'Pedido 413 aprovado manualmente pelo usuário Premium ', '2025-05-13 14:19:03'),
(625, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 18:59:03'),
(626, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:02:52'),
(627, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:03:27'),
(628, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:43:36'),
(629, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:43:52'),
(630, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:44:07'),
(631, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-13 19:45:58'),
(632, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-13 22:05:32'),
(633, 'ORDER', 'Pedido manual 414 criado pelo usuário Premium ', '2025-05-13 22:22:18'),
(634, 'PRODUCT', 'Produto Rifa demo encerrada adicionado pelo usuário Premium ', '2025-05-13 23:31:12'),
(635, 'PRODUCT', 'Produto Rifa demo encerrada atualizado pelo usuário Premium ', '2025-05-13 23:31:33'),
(636, 'ORDER', 'Pedido 415 aprovado manualmente pelo usuário Premium ', '2025-05-13 23:32:29'),
(637, 'PRODUCT', 'Produto Rifa demo encerrada atualizado pelo usuário Premium ', '2025-05-13 23:32:50'),
(638, 'PRODUCT', 'Produto Rifa demo encerrada atualizado pelo usuário Premium ', '2025-05-13 23:33:08'),
(639, 'PRODUCT', 'Produto Rifa demo encerrada atualizado pelo usuário Premium ', '2025-05-14 00:06:27'),
(640, 'ORDER', 'Pedido 416 aprovado manualmente pelo usuário Premium ', '2025-05-14 15:15:39'),
(641, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-14 15:17:29'),
(642, 'PRODUCT', 'Produto Site Demostração dezena adicionado pelo usuário Premium ', '2025-05-14 20:16:08'),
(643, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-14 20:16:33'),
(644, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 13:49:55'),
(645, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 13:50:12'),
(646, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-15 14:05:54'),
(647, 'ORDER', 'Pedido 423 aprovado manualmente pelo usuário Premium ', '2025-05-15 14:43:00'),
(648, 'ORDER', 'Pedido 423 aprovado manualmente pelo usuário Premium ', '2025-05-15 14:43:20'),
(649, 'ORDER', 'Pedido manual 424 criado pelo usuário Premium ', '2025-05-15 14:44:38'),
(650, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 14:44:56'),
(651, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 14:45:11'),
(652, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:33:52'),
(653, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:40:50'),
(654, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:41:01'),
(655, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:45:43'),
(656, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:46:13'),
(657, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:46:38'),
(658, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:46:54'),
(659, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 15:53:02'),
(660, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 16:04:53'),
(661, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 16:05:06'),
(662, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 16:05:25'),
(663, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 17:10:31'),
(664, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 17:11:29'),
(665, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 17:13:44'),
(666, 'PRODUCT', 'Produto Site Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 17:14:02'),
(667, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-15 17:15:52'),
(668, 'ORDER', 'Pedido 425 aprovado manualmente pelo usuário Premium ', '2025-05-15 17:33:02'),
(669, 'ORDER', 'Pedido 426 aprovado manualmente pelo usuário Premium ', '2025-05-15 18:01:31'),
(670, 'ORDER', 'Pedido 427 aprovado manualmente pelo usuário Premium ', '2025-05-15 18:04:33'),
(671, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:05:21'),
(672, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:06:43'),
(673, 'ORDER', 'Pedido 428 aprovado manualmente pelo usuário Premium ', '2025-05-15 18:07:42'),
(674, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:08:42'),
(675, 'ORDER', 'Pedido 429 aprovado manualmente pelo usuário Premium ', '2025-05-15 18:09:31'),
(676, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:09:54'),
(677, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:10:12'),
(678, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:10:22'),
(679, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:10:31'),
(680, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:10:38'),
(681, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:23:44'),
(682, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:24:49'),
(683, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:25:00'),
(684, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 18:25:20'),
(685, 'PRODUCT', 'Produto Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 18:37:03'),
(686, 'PRODUCT', 'Produto Demostração dezena atualizado pelo usuário Premium ', '2025-05-15 18:37:29'),
(687, 'PRODUCT', 'Produto teste50 adicionado pelo usuário Premium ', '2025-05-15 18:54:54'),
(688, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-15 18:55:10'),
(689, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 20:24:15'),
(690, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 20:24:22'),
(691, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 20:25:46'),
(692, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 21:13:22'),
(693, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 21:18:46'),
(694, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 22:06:51'),
(695, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 23:06:25'),
(696, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 23:34:39'),
(697, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-15 23:35:52'),
(698, 'ORDER', 'Pedido manual 430 criado pelo usuário Premium ', '2025-05-15 23:47:49'),
(699, 'ORDER', 'Pedido manual 431 criado pelo usuário Premium ', '2025-05-15 23:49:14'),
(700, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 00:20:52'),
(701, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 00:22:41'),
(702, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 00:37:27'),
(703, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 00:41:53'),
(704, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 01:11:55'),
(705, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 01:14:27'),
(706, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-16 01:14:52'),
(707, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:36:44'),
(708, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:37:45'),
(709, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:49:25'),
(710, 'ORDER', 'Pedido 432 aprovado manualmente pelo usuário Premium ', '2025-05-16 01:50:51'),
(711, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:54:45'),
(712, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:55:16'),
(713, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:56:01'),
(714, 'ORDER', 'Pedido 433 aprovado manualmente pelo usuário Premium ', '2025-05-16 01:56:52'),
(715, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 01:59:31'),
(716, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:00:47'),
(717, 'ORDER', 'Pedido 434 aprovado manualmente pelo usuário Premium ', '2025-05-16 02:01:06'),
(718, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:02:42'),
(719, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:03:21'),
(720, 'ORDER', 'Pedido 435 aprovado manualmente pelo usuário Premium ', '2025-05-16 02:03:46'),
(721, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:06:43'),
(722, 'ORDER', 'Pedido 436 aprovado manualmente pelo usuário Premium ', '2025-05-16 02:07:23'),
(723, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:17:41'),
(724, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:18:54'),
(725, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-16 02:19:18'),
(726, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 02:32:16'),
(727, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 02:46:30'),
(728, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 02:46:34'),
(729, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 02:57:21'),
(730, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 03:02:05'),
(731, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 03:02:14'),
(732, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 03:02:24'),
(733, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 03:02:33'),
(734, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 03:46:39'),
(735, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:27:43'),
(736, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:28:00'),
(737, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:28:10'),
(738, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:33:05'),
(739, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:39:28'),
(740, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:39:43'),
(741, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:40:31'),
(742, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:50:20'),
(743, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:50:28'),
(744, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:57:39'),
(745, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:57:45'),
(746, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:58:12'),
(747, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 04:58:30'),
(748, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:02:29'),
(749, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:02:36'),
(750, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:12:35'),
(751, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:12:47'),
(752, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:13:02'),
(753, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:15:36'),
(754, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:28:41'),
(755, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:30:40'),
(756, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 05:31:05'),
(757, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 15:49:07'),
(758, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 15:49:19'),
(759, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 15:49:37'),
(760, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 15:49:49'),
(761, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 15:49:59'),
(762, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário ', '2025-05-16 19:45:15'),
(763, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-16 19:45:38'),
(764, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-16 23:32:32'),
(765, 'ORDER', 'Pedido 420 deletado pelo usuário Premium ', '2025-05-16 23:32:59'),
(766, 'ORDER', 'Pedido 421 deletado pelo usuário Premium ', '2025-05-16 23:33:02'),
(767, 'ORDER', 'Pedido 422 deletado pelo usuário Premium ', '2025-05-16 23:33:05'),
(768, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-17 00:07:41'),
(769, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-17 00:07:45'),
(770, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-17 00:07:49'),
(771, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-17 00:07:53'),
(772, 'PRODUCT', 'Produto teste50 atualizado pelo usuário Premium ', '2025-05-17 00:07:59'),
(773, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 00:08:21'),
(774, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-17 13:40:09'),
(775, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 13:40:51'),
(776, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 14:41:42'),
(777, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 14:42:04'),
(778, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 14:42:20'),
(779, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 15:03:42'),
(780, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 15:20:36'),
(781, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 15:21:05'),
(782, 'ORDER', 'Pedido 437 aprovado manualmente pelo usuário Premium ', '2025-05-17 15:26:25'),
(783, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:38:04'),
(784, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:38:26'),
(785, 'ORDER', 'Pedido 438 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:39:42'),
(786, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:41:41'),
(787, 'ORDER', 'Pedido 439 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:42:22'),
(788, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:42:56'),
(789, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:43:01'),
(790, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:43:27'),
(791, 'ORDER', 'Pedido 440 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:43:43'),
(792, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:45:03'),
(793, 'ORDER', 'Pedido 441 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:45:32'),
(794, 'ORDER', 'Pedido 442 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:46:03'),
(795, 'ORDER', 'Pedido 442 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:46:22'),
(796, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:49:19'),
(797, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:50:08'),
(798, 'ORDER', 'Pedido 443 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:50:24'),
(799, 'ORDER', 'Pedido 444 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:51:36'),
(800, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:52:10'),
(801, 'ORDER', 'Pedido 445 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:52:43'),
(802, 'ORDER', 'Pedido 446 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:53:01'),
(803, 'ORDER', 'Pedido 446 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:53:17'),
(804, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:54:51'),
(805, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:55:21'),
(806, 'ORDER', 'Pedido 447 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:55:51'),
(807, 'ORDER', 'Pedido 448 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:56:20'),
(808, 'ORDER', 'Pedido 449 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:56:50'),
(809, 'ORDER', 'Pedido 450 aprovado manualmente pelo usuário Premium ', '2025-05-17 18:57:20'),
(810, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 18:59:06'),
(811, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 19:00:19'),
(812, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário ', '2025-05-17 21:42:42'),
(813, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 21:43:45'),
(814, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-17 21:44:37'),
(815, 'ORDER', 'Pedido 451 aprovado manualmente pelo usuário Premium ', '2025-05-17 21:46:41'),
(816, 'ORDER', 'Pedido 452 aprovado manualmente pelo usuário Premium ', '2025-05-17 21:47:16'),
(817, 'ORDER', 'Pedido 452 aprovado manualmente pelo usuário Premium ', '2025-05-17 21:47:22'),
(818, 'ORDER', 'Pedido 453 aprovado manualmente pelo usuário Premium ', '2025-05-17 21:47:49'),
(819, 'ORDER', 'Pedido 454 aprovado manualmente pelo usuário Premium ', '2025-05-17 21:48:25'),
(820, 'ORDER', 'Pedido 455 aprovado manualmente pelo usuário Premium ', '2025-05-19 12:14:58'),
(821, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 12:20:32'),
(822, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 12:20:53'),
(823, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 12:21:14'),
(824, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 12:21:46'),
(825, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 12:26:54'),
(826, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 12:27:12'),
(827, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 12:58:24'),
(828, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 12:58:39'),
(829, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 13:05:21'),
(830, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-19 13:05:41'),
(831, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:19:25'),
(832, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:20:13'),
(833, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:22:33'),
(834, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:23:00'),
(835, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:24:03'),
(836, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 13:27:52'),
(837, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-19 13:36:44'),
(838, 'USER', 'Usuário Premium  atualizado pelo usuário Premium ', '2025-05-19 13:37:18'),
(839, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:45:21'),
(840, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:50:02'),
(841, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:50:30'),
(842, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:50:50'),
(843, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:51:27'),
(844, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 14:51:32'),
(845, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-19 15:38:11'),
(846, 'ORDER', 'Pedido 456 aprovado manualmente pelo usuário Premium ', '2025-05-19 15:46:47'),
(847, 'ORDER', 'Pedido 457 aprovado manualmente pelo usuário Premium ', '2025-05-20 14:21:19'),
(848, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Premium ', '2025-05-20 15:41:45'),
(849, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Premium ', '2025-05-20 19:53:56'),
(850, 'USER', 'Usuário Administradorr adicionado pelo usuário ', '2025-07-09 20:44:22'),
(851, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 20:44:49'),
(852, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 20:45:03'),
(853, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 20:45:29'),
(854, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 20:46:06'),
(855, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Administradorr', '2025-07-09 20:46:21'),
(856, 'USER', 'Usuário admin (Premium  Plataforma de rifa ) criado em 20/01/2021 deletado pelo usuário Administradorr', '2025-07-09 21:24:11'),
(857, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 21:46:29'),
(858, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 21:47:21'),
(859, 'ORDER', 'Pedido 459 deletado pelo usuário Administradorr', '2025-07-09 21:48:24'),
(860, 'ORDER', 'Pedido 460 deletado pelo usuário Administradorr', '2025-07-09 21:48:25'),
(861, 'ORDER', 'Pedido 461 deletado pelo usuário Administradorr', '2025-07-09 21:48:31'),
(862, 'ORDER', 'Pedido 462 aprovado manualmente pelo usuário Administradorr', '2025-07-09 21:48:33'),
(863, 'ORDER', 'Pedido 462 deletado pelo usuário Administradorr', '2025-07-09 21:48:36'),
(864, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Administradorr', '2025-07-09 22:26:45'),
(865, 'PRODUCT', 'Produto Demostração de rifa 01 com Roleta 🚀 atualizado pelo usuário Administradorr', '2025-07-09 22:27:40'),
(866, 'PRODUCT', 'Produto CARRETA MILIONÁRIA atualizado pelo usuário Administradorr', '2025-07-09 22:28:55'),
(867, 'USER', 'Usuário Administrador atualizado pelo usuário Admin', '2025-07-10 15:05:27'),
(868, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:31:36'),
(869, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:32:06'),
(870, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:32:48'),
(871, 'ORDER', 'Pedido 478 deletado pelo usuário Admin', '2025-07-10 15:32:58'),
(872, 'ORDER', 'Pedido 477 deletado pelo usuário Admin', '2025-07-10 15:33:01'),
(873, 'ORDER', 'Pedido 476 deletado pelo usuário Admin', '2025-07-10 15:33:03'),
(874, 'ORDER', 'Pedido 474 deletado pelo usuário Admin', '2025-07-10 15:33:06'),
(875, 'ORDER', 'Pedido 473 deletado pelo usuário Admin', '2025-07-10 15:33:08'),
(876, 'ORDER', 'Pedido 472 deletado pelo usuário Admin', '2025-07-10 15:33:44'),
(877, 'ORDER', 'Pedido 471 deletado pelo usuário Admin', '2025-07-10 15:33:46'),
(878, 'ORDER', 'Pedido 470 deletado pelo usuário Admin', '2025-07-10 15:33:48'),
(879, 'ORDER', 'Pedido 469 deletado pelo usuário Admin', '2025-07-10 15:33:50'),
(880, 'ORDER', 'Pedido 468 deletado pelo usuário Admin', '2025-07-10 15:33:53'),
(881, 'ORDER', 'Pedido 467 deletado pelo usuário Admin', '2025-07-10 15:33:55'),
(882, 'ORDER', 'Pedido 466 deletado pelo usuário Admin', '2025-07-10 15:33:57'),
(883, 'ORDER', 'Pedido 465 deletado pelo usuário Admin', '2025-07-10 15:33:59'),
(884, 'ORDER', 'Pedido 464 deletado pelo usuário Admin', '2025-07-10 15:34:01'),
(885, 'ORDER', 'Pedido 463 deletado pelo usuário Admin', '2025-07-10 15:34:03'),
(886, 'PRODUCT', 'Produto teste adicionado pelo usuário Admin', '2025-07-10 15:34:49'),
(887, 'ORDER', 'Pedido 479 deletado pelo usuário Admin', '2025-07-10 15:35:00'),
(888, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:35:19'),
(889, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:36:18'),
(890, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:36:48'),
(891, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 15:40:08'),
(892, 'PRODUCT', 'Produto CAMPANHA 1 MILHÃO atualizado pelo usuário Admin', '2025-07-10 16:02:29'),
(893, 'PRODUCT', 'Produto CAMPANHA 1 MILHÃO atualizado pelo usuário Admin', '2025-07-10 16:02:29'),
(894, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 16:04:18'),
(895, 'ORDER', 'Pedido 475 deletado pelo usuário Admin', '2025-07-10 16:05:26'),
(896, 'ORDER', 'Pedido 480 aprovado manualmente pelo usuário Admin', '2025-07-10 16:05:35'),
(897, 'PRODUCT', 'Produto CAMPANHA 1 MILHÃO atualizado pelo usuário Admin', '2025-07-10 16:09:38'),
(898, 'PRODUCT', 'Produto CAMPANHA 1 MILHÃO atualizado pelo usuário Admin', '2025-07-10 16:10:53'),
(899, 'SYSTEM', 'Configurações do sistema atualizadas pelo usuário Admin', '2025-07-10 16:12:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_items`
--

CREATE TABLE `order_items` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` float(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_list`
--

CREATE TABLE `order_list` (
  `id` int(11) NOT NULL,
  `code` varchar(100) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `quantity` text DEFAULT NULL,
  `total_amount` float(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=pending2=paid3=cancelled',
  `roleta` int(11) DEFAULT 0,
  `box` int(11) DEFAULT 0,
  `roleta_aberta` int(11) DEFAULT 0,
  `box_aberta` int(11) DEFAULT 0,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `product_name` text DEFAULT NULL,
  `order_token` varchar(100) DEFAULT NULL,
  `order_numbers` longtext DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `payment_method` text DEFAULT NULL,
  `order_expiration` text DEFAULT NULL,
  `pix_code` text DEFAULT NULL,
  `pix_qrcode` text DEFAULT NULL,
  `txid` text DEFAULT NULL,
  `discount_amount` text DEFAULT NULL,
  `whatsapp_status` text DEFAULT NULL,
  `dwapi_status` text DEFAULT NULL,
  `id_mp` varchar(100) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `pixel_sell` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `page_view`
--

CREATE TABLE `page_view` (
  `id` int(11) NOT NULL,
  `product_id` varchar(11) DEFAULT NULL,
  `customer_id` varchar(11) DEFAULT NULL,
  `page` varchar(255) NOT NULL,
  `origin` tinyint(1) NOT NULL COMMENT '1=Normal2=Pixel'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_list`
--

CREATE TABLE `product_list` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `price` float(12,2) NOT NULL DEFAULT 0.00,
  `image_path` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `type_of_draw` tinyint(1) NOT NULL DEFAULT 1,
  `qty_numbers` text NOT NULL,
  `min_purchase` text NOT NULL,
  `max_purchase` text NOT NULL,
  `slug` text NOT NULL,
  `pending_numbers` text NOT NULL,
  `paid_numbers` text NOT NULL,
  `ranking_qty` text NOT NULL,
  `enable_ranking` text NOT NULL,
  `image_gallery` text DEFAULT NULL,
  `enable_progress_bar` text NOT NULL,
  `enable_progress_bar_fake` tinyint(1) DEFAULT NULL,
  `enable_progress_bar_fake_value` decimal(10,2) DEFAULT NULL,
  `draw_number` text DEFAULT NULL,
  `status_display` text NOT NULL,
  `subtitle` text DEFAULT NULL,
  `date_of_draw` varchar(255) DEFAULT NULL,
  `limit_order_remove` text DEFAULT NULL,
  `discount_qty` longtext DEFAULT NULL,
  `discount_amount` longtext DEFAULT NULL,
  `discount_roleta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `roleta_qty` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `roleta_amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `enable_discount` text DEFAULT NULL,
  `enable_double` varchar(255) NOT NULL DEFAULT '0',
  `double_ini` varchar(250) DEFAULT NULL,
  `double_fim` varchar(250) DEFAULT NULL,
  `enable_cumulative_discount` text DEFAULT NULL,
  `enable_sale` text DEFAULT NULL,
  `sale_qty` text DEFAULT NULL,
  `sale_price` float(12,2) DEFAULT 0.00,
  `ranking_message` text DEFAULT NULL,
  `enable_ranking_show` text DEFAULT NULL,
  `draw_winner` text DEFAULT NULL,
  `private_draw` text DEFAULT NULL,
  `featured_draw` text DEFAULT NULL,
  `cotas_premiadas` longtext DEFAULT NULL,
  `cotas_premiadas_premios` mediumtext DEFAULT NULL,
  `cotas_premiadas_descricao` text DEFAULT NULL,
  `limit_orders` int(11) DEFAULT 0,
  `ranking_type` int(11) NOT NULL DEFAULT 1,
  `qty_select_1` int(11) NOT NULL DEFAULT 10,
  `qty_select_2` int(11) NOT NULL DEFAULT 20,
  `qty_select_3` int(11) NOT NULL DEFAULT 50,
  `qty_select_4` int(11) NOT NULL DEFAULT 100,
  `qty_select_5` int(11) NOT NULL DEFAULT 200,
  `qty_select_6` int(11) NOT NULL DEFAULT 300,
  `status_auto_cota` tinyint(1) NOT NULL DEFAULT 0,
  `valor_base_auto` int(11) NOT NULL DEFAULT 50,
  `quantidade_numeros` int(11) NOT NULL DEFAULT 2,
  `tipo_auto_cota` longtext DEFAULT NULL,
  `up` tinyint(1) NOT NULL DEFAULT 0,
  `quantidade_auto_cota` int(11) NOT NULL DEFAULT 0,
  `quantidade_auto_cota_diario` tinyint(1) DEFAULT 0,
  `roleta` tinyint(1) NOT NULL DEFAULT 0,
  `box` tinyint(1) NOT NULL DEFAULT 0,
  `enable_upsell` int(11) NOT NULL DEFAULT 0,
  `qtd_upsell` varchar(255) DEFAULT NULL,
  `desconto_upsell` varchar(255) DEFAULT NULL,
  `status_auto_cota_roleta` int(11) DEFAULT NULL,
  `tipo_auto_cota_roleta` longtext DEFAULT NULL,
  `cotas_premiadas_roleta` longtext DEFAULT NULL,
  `cotas_premiadas_premios_roleta` longtext DEFAULT NULL,
  `cotas_premiadas_descricao_roleta` longtext DEFAULT NULL,
  `discount_box` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `box_qty` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `box_amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status_auto_cota_box` int(11) DEFAULT NULL,
  `tipo_auto_cota_box` longtext DEFAULT NULL,
  `cotas_premiadas_box` longtext DEFAULT NULL,
  `cotas_premiadas_premios_box` longtext DEFAULT NULL,
  `cotas_premiadas_descricao_box` longtext DEFAULT NULL,
  `enable_ranking_definido` tinyint(1) DEFAULT NULL,
  `ranking_ini` varchar(255) DEFAULT NULL,
  `ranking_fim` varchar(255) DEFAULT NULL,
  `cota_diaria_ini` varchar(255) DEFAULT NULL,
  `cota_diaria_fim` varchar(255) DEFAULT NULL,
  `probabilidade` int(11) DEFAULT NULL,
  `habilitar_cota_sorte` tinyint(1) DEFAULT NULL,
  `cota_sorte_ini` varchar(255) DEFAULT NULL,
  `cota_sorte_fim` varchar(255) DEFAULT NULL,
  `cota_sorte` varchar(255) DEFAULT NULL,
  `quantidade_compra_sorte` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `product_list`
--

INSERT INTO `product_list` (`id`, `name`, `description`, `price`, `image_path`, `status`, `delete_flag`, `date_created`, `date_updated`, `type_of_draw`, `qty_numbers`, `min_purchase`, `max_purchase`, `slug`, `pending_numbers`, `paid_numbers`, `ranking_qty`, `enable_ranking`, `image_gallery`, `enable_progress_bar`, `enable_progress_bar_fake`, `enable_progress_bar_fake_value`, `draw_number`, `status_display`, `subtitle`, `date_of_draw`, `limit_order_remove`, `discount_qty`, `discount_amount`, `discount_roleta`, `roleta_qty`, `roleta_amount`, `enable_discount`, `enable_double`, `double_ini`, `double_fim`, `enable_cumulative_discount`, `enable_sale`, `sale_qty`, `sale_price`, `ranking_message`, `enable_ranking_show`, `draw_winner`, `private_draw`, `featured_draw`, `cotas_premiadas`, `cotas_premiadas_premios`, `cotas_premiadas_descricao`, `limit_orders`, `ranking_type`, `qty_select_1`, `qty_select_2`, `qty_select_3`, `qty_select_4`, `qty_select_5`, `qty_select_6`, `status_auto_cota`, `valor_base_auto`, `quantidade_numeros`, `tipo_auto_cota`, `up`, `quantidade_auto_cota`, `quantidade_auto_cota_diario`, `roleta`, `box`, `enable_upsell`, `qtd_upsell`, `desconto_upsell`, `status_auto_cota_roleta`, `tipo_auto_cota_roleta`, `cotas_premiadas_roleta`, `cotas_premiadas_premios_roleta`, `cotas_premiadas_descricao_roleta`, `discount_box`, `box_qty`, `box_amount`, `status_auto_cota_box`, `tipo_auto_cota_box`, `cotas_premiadas_box`, `cotas_premiadas_premios_box`, `cotas_premiadas_descricao_box`, `enable_ranking_definido`, `ranking_ini`, `ranking_fim`, `cota_diaria_ini`, `cota_diaria_fim`, `probabilidade`, `habilitar_cota_sorte`, `cota_sorte_ini`, `cota_sorte_fim`, `cota_sorte`, `quantidade_compra_sorte`) VALUES
(6, 'CAMPANHA 1 MILHÃO', 'PREMIAÇÃO: IVECO S-WAY 480-6X2 – ANO 2023 + SR/FACCHINI SRF CAED – ANO 2013 + VW JETTA CL – ANO 2018 + 6 HONDA SAHARA 300 – ANO 2025 (SUGESTÃO DE USO DO PRÊMIO LÍQUIDO R$ 1.000.000,00)', 2.99, 'uploads/campanhas/1_JEF-150-19.jpg?v=1752177749', 1, 0, '2025-04-10 23:46:42', '2025-07-10 16:10:53', 1, '1000000', '2', '2000', 'campanha-1-milhao-2', '0', '1610', '3', '0', '[]', '0', 1, 45.00, '', '1', 'POR APENAS 2,99 NA SUA VIDA MILIONÁRIA', '', '15', '[\"100\",\"200\",\"300\"]', '[\"2.00\",\"4.00\",\"5.00\"]', NULL, '[\"1\",\"2\",\"5\",\"10\"]', '[\"2\",\"5\",\"10\",\"20\"]', '0', '0', '2025-04-10T20:47', '2025-06-30T20:47', '0', '0', '0', 0.00, '', '1', '[\"\"]', '0', '1', '123456,145263,365241,256314,2521463,542563,241265,251426', '123456:R$1000:premiada,145263:R$1000:premiada,365241:R$1000:premiada,256314:R$1000:premiada,2521463:R$1000:premiada,542563:R$1000:premiada,241265:R$1000:premiada,251426:R$1000:premiada', '', 0, 2, 10, 20, 50, 100, 200, 300, 0, 0, 2, '123456,145263,365241,256314,2521463,542563,241265,251426', 0, 0, 0, 1, 1, 0, '', '', 0, '012365,032563,025642,014785,012536,032566,025874,014756,036985', '012365,032563,025642,014785,012536,032566,025874,014756,036985', '012365:R$200:premiada,032563:R$200:premiada,025642:R$200:premiada,014785:R$500:premiada,012536:R$1000:premiada,032566:R$500:premiada,025874:R$200:premiada,014756:R$200:premiada,036985:R$200:premiada', '', NULL, '[\"1\",\"2\",\"5\",\"10\"]', '[\"2\",\"5\",\"10\",\"20\"]', 0, '147852,958412,685245,695487,365475,36254,256967,365789', '147852,958412,685245,695487,365475,36254,256967,365789', '147852:R$1000:premiada,958412:R$1000:premiada,685245:R$1000:premiada,695487:R$1000:premiada,365475:R$1000:premiada,36254:R$1000:premiada,256967:R$1000:premiada,365789:R$1000:premiada', '', 1, '2025-04-30T20:16', '2025-05-17T12:21', '2025-05-19T02:37', '2025-05-19T13:25', 49, 0, '2025-05-17T18:43', '2025-05-17T18:50', '', '-1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `referral`
--

CREATE TABLE `referral` (
  `id` int(11) NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `referral_code` varchar(100) DEFAULT NULL,
  `percentage` float(12,2) NOT NULL DEFAULT 0.00,
  `amount_paid` float(12,2) NOT NULL DEFAULT 0.00,
  `amount_pending` float(12,2) NOT NULL DEFAULT 0.00,
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `referral_transactions`
--

CREATE TABLE `referral_transactions` (
  `id` int(11) NOT NULL,
  `total_amount` float(12,2) NOT NULL DEFAULT 0.00,
  `referral_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `referral_transactions`
--

INSERT INTO `referral_transactions` (`id`, `total_amount`, `referral_id`) VALUES
(1, 60.00, 18);

-- --------------------------------------------------------

--
-- Estrutura para tabela `system_info`
--

CREATE TABLE `system_info` (
  `id` int(11) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'Site Exemplo'),
(2, 'short_name', ''),
(3, 'logo', 'uploads/logo.png?v=1752176178'),
(4, 'user_avatar', 'uploads/user_avatar.jpg'),
(5, 'cover', 'uploads/cover.png?v=1675042834'),
(6, 'phone', ''),
(7, 'mobile', '00000'),
(8, 'email', 'admin@gmail.com '),
(9, 'address', 'Endereço'),
(10, 'mercadopago', '2'),
(11, 'mercadopago_access_token', 'APP_USR-325888472139985-072111-bf236d0726f8d6ce60a581cf62f10140-1170836546'),
(12, 'gerencianet', '2'),
(13, 'gerencianet_client_id', ''),
(14, 'gerencianet_client_secret', ''),
(15, 'gerencianet_pix_key', ''),
(16, 'gateway', '1'),
(17, 'enable_cpf', '2'),
(18, 'enable_email', '2'),
(19, 'enable_address', '2'),
(20, 'favicon', 'uploads/favicon.png?v=1752176178'),
(21, 'enable_share', '1'),
(22, 'enable_groups', '1'),
(23, 'telegram_group_url', ''),
(24, 'whatsapp_group_url', 'Seulinkaqui'),
(25, 'enable_footer', '1'),
(26, 'text_footer', ''),
(27, 'enable_password', '2'),
(28, 'paggue', '2'),
(29, 'paggue_client_key', ''),
(30, 'paggue_client_secret', ''),
(31, 'enable_pixel', '2'),
(32, 'facebook_access_token', 'EAAMJRtkmTmgBO6lKrMVFgNZB4Sy7YCLvz3SLDZBVH7PwZAkjdbhQKLhNpZCMBD13XtWHgt2i3ElMXy4ZCs5S6pOc846f8qnpuGAqSUvVjn4cysaywFK9CBZCljxB4UDur3l7DjsARX3J5kyGX6xuNDhiCiNpPJDMZBWbYZBkPi3rT5N4avD4sFj2EeYUZCvl1RTpLFQZDZD'),
(33, 'facebook_pixel_id', '1163730448614072'),
(34, 'enable_hide_numbers', '1'),
(35, 'whatsapp_footer', ''),
(36, 'instagram_footer', ''),
(37, 'facebook_footer', ''),
(38, 'twitter_footer', ''),
(39, 'youtube_footer', ''),
(40, 'enable_dwapi', '2'),
(41, 'token_dwapi', ''),
(42, 'numero_dwapi', ''),
(43, 'mensagem_novo_pedido_dwapi', ''),
(44, 'mensagem_pedido_pago_dwapi', ''),
(45, 'smtp_host', 'smtp.hostinger.com'),
(46, 'smtp_port', ' 465'),
(47, 'smtp_user', 'barraodadezenapremiada@gmail.com'),
(48, 'smtp_pass', '{a*^jxW5f?RPAc^$'),
(49, 'question1', 'Como acessar minhas compras?'),
(50, 'answer1', 'Fazendo login no site e abrindo o Menu Principal, você consegue consultar suas últimas compras no menu '),
(51, 'question2', 'Como envio o comprovante?'),
(52, 'answer2', 'Caso você tenha feito o pagamento via Pix QR Code ou copiando o código, não é necessário enviar o comprovante, aguardando até 5 minutos após o pagamento, o sistema irá dar baixa automaticamente, para mais dúvidas entre em contato conosco clicando aqui.'),
(53, 'question3', 'Como é o processo do sorteio?'),
(54, 'answer3', 'O sorteio será realizado com base na extração da Loteria Federal, conforme Condições de Participação constantes no título'),
(55, 'question4', ''),
(56, 'answer4', ''),
(57, 'terms', '<b>1)</b> Lorem Ipsum is simply dummy text of the printing and typesetting industry. <br><br> <b>2)</b> Lorem Ipsum has been the industry&apos;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. <br><br> <b>3)</b> It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum. <br><br> (i) It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. <br><br> (ii) Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &apos;lorem ipsum&apos; will uncover many web sites still in their infancy.<script>   window.location.href = '),
(58, 'enable_ga4', '2'),
(59, 'google_ga4_id', '1'),
(60, 'license', ''),
(61, 'enable_two_phone', '2'),
(62, 'enable_gtm', '2'),
(63, 'google_gtm_id', ''),
(64, 'theme', '1'),
(65, 'email_order', ''),
(66, 'email_purchase', ''),
(67, 'enable_legal_age', '2'),
(68, 'enable_birth', '2'),
(69, 'enable_instagram', '2'),
(70, 'enable_multiple_order', '2'),
(71, 'dealer_active', '2'),
(72, 'dealer_deactive_site', '2'),
(73, 'dealer_split_mercadopago', '2'),
(74, 'mercadopago_tax', ''),
(75, 'gerencianet_tax', ''),
(76, 'paggue_tax', '0'),
(77, 'openpix_app_id', ''),
(78, 'openpix_tax', ''),
(79, 'pay2m_client_id', ''),
(80, 'pay2m_client_secret', ''),
(81, 'pay2m_tax', '0'),
(82, 'openpix', '2'),
(83, 'pay2m', '2'),
(85, 'pagstar', '2'),
(86, 'pagstar_client_key', 'b75fd923-9dd2-4cf4-84ad-3cee1fc2779d'),
(87, 'pagstar_client_secret', 'b75fd923-9dd2-4cf4-84ad-3cee1fc2779d'),
(88, 'openpix_webhook_url', 'https://thiagoaraujoofc.com/webhook.php?notify=openpix'),
(89, 'pagstar2', '2'),
(90, 'Pagstar_webhook_url', ''),
(91, 'ezzepay', '2'),
(92, 'ezzepay_client_id', ''),
(93, 'ezzepay_client_secret', ''),
(94, 'nextpay', '2'),
(95, 'nextpay_client_id', '1jipgid7uf5jaielfphfjihaqs'),
(96, 'nextpay_client_secret', 'hvl6rnsis9u62jfco51053hjku2mah2u3vejkivc72jcilddn7q'),
(97, 'nextpay_webhook', 'https://miguelcash.com/webhook_next.php'),
(98, 'ativopay', '2'),
(99, 'ativopay_client_id', ''),
(100, 'ativopay_client_secret', ''),
(101, 'ativopay_webhook', 'https://rifasortepremiada.com/webhook_ativopay.php'),
(102, 'pay2m_webhook_url', 'https://rifasortepremiada.com/webhook.php?notify=pay2m'),
(103, 'paggue_webhook_url', 'https://rifasortepremiada.com/webhook.php?notify=paggue'),
(104, 'ezzepay_webhook_url', 'https://rifasortepremiada.com/webhook.php?notify=ezzepay'),
(105, 'ativopay_webhook_url', 'https://rifasortepremiada.com/webhook.php?notify=ativopay'),
(106, 'bestfy', '2'),
(107, 'bestfy_client_id', ''),
(108, 'bestfy_client_secret', ''),
(109, 'bestfy_webhook', 'https://miguelcash.com/webhook_bestfy.php'),
(110, 'phpay', '2'),
(111, 'phpay_client_id', 'aed12153-7697-40a9-8118-2149ac054aae'),
(112, 'phpay_client_secret', '49b51e55-2d21-49ad-81cc-7cc86d8280e7'),
(113, 'phpay_webhook', 'https://miguelcash.com/webhook.php?notify=phpay'),
(114, 'kapay_client_id', 'aed12153-7697-40a9-8118-2149ac054aae'),
(115, 'kapay_client_secret', '4cc4e08c-a799-4748-a30e-2c9f909b12ad'),
(116, 'kapay_webhook', 'https://demo.3rifas.com/webhook.php?notify=kapay'),
(117, 'kapay', '2'),
(118, 'pixup', '2'),
(119, 'pixup_client_id', ''),
(120, 'pixup_client_secret', ''),
(121, 'pixup_tax', ''),
(122, 'gateway_password', 'minhasenha123'),
(123, 'nivuspay', '1'),
(124, 'nivuspay_api_key', 'pk_live_mcgimy92_a7489bbe85265f393c76ec69cc23a809cc7e6fb724db25722ac60b54cb077bff'),
(125, 'nivuspay_tax', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `avatar` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` text DEFAULT NULL,
  `site` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='2';

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`, `email`, `site`) VALUES
(2, 'Administrador', NULL, 'Admin', 'Admin', '21232f297a57a5a743894a0e4a801fc3', NULL, NULL, 1, '2025-07-09 20:44:22', '2025-07-10 15:05:27', 'admin@admin.com', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cart_list`
--
ALTER TABLE `cart_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `customer_list`
--
ALTER TABLE `customer_list`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `order_items`
--
ALTER TABLE `order_items`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id_2` (`order_id`,`product_id`,`quantity`,`price`);

--
-- Índices de tabela `order_list`
--
ALTER TABLE `order_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_list_index` (`product_id`,`order_numbers`(64),`code`);

--
-- Índices de tabela `page_view`
--
ALTER TABLE `page_view`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `product_list`
--
ALTER TABLE `product_list`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `referral`
--
ALTER TABLE `referral`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Índices de tabela `referral_transactions`
--
ALTER TABLE `referral_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cart_list`
--
ALTER TABLE `cart_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_list`
--
ALTER TABLE `customer_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=900;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_list`
--
ALTER TABLE `order_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=481;

--
-- AUTO_INCREMENT de tabela `page_view`
--
ALTER TABLE `page_view`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `product_list`
--
ALTER TABLE `product_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `referral`
--
ALTER TABLE `referral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `referral_transactions`
--
ALTER TABLE `referral_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cart_list`
--
ALTER TABLE `cart_list`
  ADD CONSTRAINT `customer_id_fk_cl` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_id_fk_cl` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_id_fk_oi` FOREIGN KEY (`order_id`) REFERENCES `order_list` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_id_fk_oi` FOREIGN KEY (`product_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `order_list`
--
ALTER TABLE `order_list`
  ADD CONSTRAINT `customer_id_fk_ol` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `referral`
--
ALTER TABLE `referral`
  ADD CONSTRAINT `customer_id_fk_re` FOREIGN KEY (`customer_id`) REFERENCES `customer_list` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
