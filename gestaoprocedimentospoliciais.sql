-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Tempo de geração: 05/06/2025 às 11:45
-- Versão do servidor: 9.3.0
-- Versão do PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestaoprocedimentospoliciais`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `Anotacoes`
--

CREATE TABLE `Anotacoes` (
  `ID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `DataCriacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `UsuarioCriadorID` int NOT NULL,
  `Anotacao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Anotacoes`
--

INSERT INTO `Anotacoes` (`ID`, `ProcedimentoID`, `DataCriacao`, `UsuarioCriadorID`, `Anotacao`) VALUES
(15, 121, '2025-01-01 19:29:21', 2, '- Agendar a oitiva dos policiais militares\r\n- Verificar quanto a apresentação da arma utilizada no confronto'),
(20, 130, '2025-01-07 11:14:41', 2, 'Acompanhar exame toxicológico pelo RAI 37078062'),
(21, 80, '2025-01-07 17:13:27', 2, 'Quebra de sigilo telemático: RG 492/2024 SIPC'),
(22, 92, '2025-01-08 14:26:35', 4, 'rfgbsdrfghsdfbasd'),
(25, 118, '2025-01-15 14:46:52', 2, '- Necessário localizar a vítima para ser ouvida (Paulo)'),
(28, 73, '2025-01-27 17:20:09', 2, 'Ouvir:\r\nJosé Anailson (Irmão da Elaine) - Pode ser encontrado nas kitnets'),
(29, 149, '2025-02-21 11:04:34', 2, '- Aguardando relatório dos agentes'),
(31, 154, '2025-03-17 16:06:53', 2, 'Aguardando:\r\n- Laudo Cadavérico\r\n- Perícia Local de Crime\r\n- Perícia Armas (PM e Vítima)'),
(32, 164, '2025-04-01 10:59:15', 2, 'Expedida ordem de missão para localização e intimação da esposa do autor, Nara'),
(33, 164, '2025-06-03 15:10:47', 2, 'teste'),
(55, 162, '2025-06-03 20:21:43', 2, 'teste');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ArmaCalibre`
--

CREATE TABLE `ArmaCalibre` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ArmaCalibre`
--

INSERT INTO `ArmaCalibre` (`ID`, `Nome`) VALUES
(3, '.22'),
(10, '.22LR'),
(9, '.32'),
(11, '.357 Magnum'),
(4, '.38'),
(6, '.380'),
(5, '12'),
(8, '38 special'),
(1, '9mm'),
(7, 'Não Identificado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ArmaEspecie`
--

CREATE TABLE `ArmaEspecie` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ArmaEspecie`
--

INSERT INTO `ArmaEspecie` (`ID`, `Nome`) VALUES
(5, 'Carabina'),
(8, 'Espingarda'),
(6, 'Fuzil'),
(7, 'Não Identificado'),
(3, 'Pistola'),
(4, 'Revólver'),
(9, 'Rifle');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ArmaMarca`
--

CREATE TABLE `ArmaMarca` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ArmaMarca`
--

INSERT INTO `ArmaMarca` (`ID`, `Nome`) VALUES
(3, 'Beretta'),
(5, 'CBC'),
(2, 'Glock'),
(4, 'Não Identificada'),
(6, 'ROSSI'),
(1, 'Taurus');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ArmaModelo`
--

CREATE TABLE `ArmaModelo` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ArmaModelo`
--

INSERT INTO `ArmaModelo` (`ID`, `Nome`) VALUES
(11, '608'),
(12, '627 TRACKER'),
(9, '7022'),
(7, '838'),
(4, 'APX'),
(2, 'G2C'),
(10, 'PT 111 G2A (G2 C)'),
(5, 'PT 58 SS'),
(1, 'PT100'),
(6, 'PT92'),
(8, 'Pump Military 3.0'),
(3, 'SEM MODELO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ArmasFogo`
--

CREATE TABLE `ArmasFogo` (
  `ID` int NOT NULL,
  `ObjetoID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `EspecieID` int NOT NULL,
  `CalibreID` int NOT NULL,
  `NumeroSerie` varchar(255) DEFAULT NULL,
  `MarcaID` int NOT NULL,
  `ModeloID` int NOT NULL,
  `ProcessoJudicialID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ArmasFogo`
--

INSERT INTO `ArmasFogo` (`ID`, `ObjetoID`, `ProcedimentoID`, `EspecieID`, `CalibreID`, `NumeroSerie`, `MarcaID`, `ModeloID`, `ProcessoJudicialID`) VALUES
(14, 42, 55, 3, 1, 'ABC418721', 1, 2, 264),
(15, 44, 121, 4, 9, '151318', 1, 3, NULL),
(16, 45, 137, 3, 6, 'KOE91387', 1, 5, NULL),
(17, 46, 55, 5, 10, 'EWI4971734', 5, 3, NULL),
(18, 47, 55, 3, 1, 'ABN318952', 1, 6, NULL),
(19, 49, 139, 4, 8, 'SUPRIMIDO', 1, 3, NULL),
(21, 51, 55, 3, 6, 'KJU77612', 1, 7, 266),
(22, 52, 140, 4, 4, 'WD1152228', 1, 3, NULL),
(23, 53, 140, 4, 8, 'ABN314731', 1, 3, NULL),
(24, 54, 154, 4, 4, '', 1, 3, NULL),
(25, 60, 142, 4, 4, '624954', 6, 3, 272),
(26, 61, 143, 4, 4, '246900', 1, 3, NULL),
(27, 62, 150, 4, 4, '224217', 1, 3, 289),
(28, 63, 156, 4, 4, 'D777780', 6, 3, NULL),
(29, 64, 115, 8, 5, 'KUL4568835', 5, 8, 243),
(30, 65, 115, 9, 10, 'EUK4557563', 5, 9, 243),
(31, 66, 115, 3, 1, 'ABM299048', 1, 10, 243),
(32, 67, 366, 4, 11, 'ACD820865', 1, 11, 671),
(33, 68, 366, 4, 11, 'ACK391767', 1, 12, 671),
(34, 69, 428, 4, 8, 'NK146938', 1, 3, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Cargos`
--

CREATE TABLE `Cargos` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Cargos`
--

INSERT INTO `Cargos` (`ID`, `Nome`) VALUES
(4, 'Administrativo'),
(3, 'Agente de Polícia'),
(1, 'Delegado'),
(2, 'Escrivão de Polícia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ComentariosDesaparecimentos`
--

CREATE TABLE `ComentariosDesaparecimentos` (
  `ID` int NOT NULL,
  `DesaparecidoID` int NOT NULL,
  `UsuarioCriadorID` int NOT NULL,
  `Comentario` text NOT NULL,
  `Arquivos` json DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ComentariosDesaparecimentos`
--

INSERT INTO `ComentariosDesaparecimentos` (`ID`, `DesaparecidoID`, `UsuarioCriadorID`, `Comentario`, `Arquivos`, `CreatedAt`) VALUES
(18, 17, 2, 'ADENDO\r\n\r\nCompareceu a esta Delegacia de Polícia, nesta segunda-feira, 30/12/2024, às 11h56min, comunicante LUCIMAR PEREIRA DA SILVA informando que recebeu informações de uma amiga VALERIA (numero de contato (64) 9604-3182), informando que viu MARIELLI acompanhada de rapaz MORENO, ALTO, em uma residência localizada na rua 072, numero 698, Bairro Popular, por volta das 02h:00min do dia 30/12/2024. Comunicante diz ainda que recebeu um áudio de MARIELLI, com a voz de um homem de fundo onde MARIELLI fala: \"EU ESTOU BEM MÃE\" e logo em seguida a voz do homem diz: \"VIU TIA ELA ESTÁ BEM\". Após isso ninguém mais conseguiu contato com MARIELLI. Registra-se.', '[]', '2025-01-02 13:45:04'),
(19, 17, 2, 'Solicitado ERB e verificado que o telefone está no Estado de SP.\r\n(64) 99606-5084\r\nhttps://servicos.pc.sc.gov.br/antena/?t=e&d=MTRoMDgmQGVyYiZALTIzLjY0NTU1JkAtNDYuNjAzMzMxJkAxOTAmQDEyMCZAMTAwMA--', '[]', '2025-01-02 17:27:18'),
(20, 22, 2, 'Intimar Heltonliz (63) 99312-6291, segundo a comunicante, ele conversou e sabe onde o desaparecido está.', '[]', '2025-01-07 17:07:51'),
(21, 22, 2, 'Foi identificado que a vítima está presa no Estados Unidos.', '[]', '2025-01-27 14:33:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compromissos`
--

CREATE TABLE `compromissos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3788d8',
  `tipo` enum('reuniao','audiencia','prazo','outro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reuniao',
  `visibilidade` enum('privado','todos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todos',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `compromissos`
--

INSERT INTO `compromissos` (`id`, `usuario_id`, `titulo`, `descricao`, `data_inicio`, `data_fim`, `cor`, `tipo`, `visibilidade`, `created_at`, `updated_at`) VALUES
(1, 2, 'teste', 'teste', '2025-06-07 03:00:00', '2025-06-08 03:00:00', '#3788d8', 'reuniao', 'todos', '2025-06-04 13:40:58', '2025-06-04 13:40:58'),
(2, 2, 'teste', 'asdf', '2025-06-02 03:00:00', '2025-06-03 03:00:00', '#3788d8', 'reuniao', 'todos', '2025-06-04 13:41:13', '2025-06-04 13:41:13'),
(3, 2, 'teste', 'teste', '2025-06-05 11:30:00', '2025-06-05 12:00:00', '#3788d8', 'reuniao', 'todos', '2025-06-04 13:42:40', '2025-06-04 13:42:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Crimes`
--

CREATE TABLE `Crimes` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Descricao` text,
  `DataCriacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Crimes`
--

INSERT INTO `Crimes` (`ID`, `Nome`, `Descricao`, `DataCriacao`) VALUES
(1, 'Homicídio', NULL, '2024-12-07 19:18:21'),
(2, 'Feminicídio', NULL, '2024-12-07 19:18:51'),
(11, 'Furto', NULL, '2024-12-08 13:45:34'),
(12, 'Porte de arma de fogo', NULL, '2024-12-13 12:17:26'),
(14, 'Morte por intervenção policial', NULL, '2024-12-13 12:39:05'),
(15, 'Disparo de arma de fogo', NULL, '2024-12-15 17:26:36'),
(16, 'Suicídio', NULL, '2024-12-15 17:32:36'),
(17, 'Morte a esclarecer', NULL, '2024-12-15 20:13:51'),
(22, 'Lesão Corporal', NULL, '2024-12-16 14:22:53'),
(23, 'Tráfico de Drogas', NULL, '2024-12-18 22:00:13'),
(26, 'Homicídio no Trânsito', NULL, '2024-12-23 23:00:50'),
(27, 'Ameaça', NULL, '2024-12-23 23:12:19'),
(28, 'Outros', NULL, '2025-01-15 13:05:23'),
(29, 'FALSO TESTEMUNHO ', NULL, '2025-04-14 19:31:50'),
(30, 'ROUBO SEGUIDO DE MORTE', NULL, '2025-04-14 19:39:17'),
(31, 'Tentativa de Homicidio ', NULL, '2025-04-22 17:48:30'),
(32, 'Tortura', NULL, '2025-04-22 18:36:22'),
(33, 'TENTATIVA DE LATROCINIO', NULL, '2025-04-23 19:21:00'),
(34, 'LATOCINIO E CORRUPÇÃO DE MENORES', NULL, '2025-04-25 18:08:37'),
(37, 'FALSA IDENTIDADE E DESOBEDIÊNCIA', NULL, '2025-04-28 17:31:24'),
(39, 'POSSE IRREGULAR DE ARMA DE FOGO', NULL, '2025-04-28 18:10:18'),
(40, 'lesao corporal seguida de morte', NULL, '2025-04-29 20:14:51'),
(41, 'Extorsão', NULL, '2025-04-29 20:25:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `CumprimentosCautelares`
--

CREATE TABLE `CumprimentosCautelares` (
  `ID` int NOT NULL,
  `SolicitacaoCautelarID` int DEFAULT NULL,
  `TipoCautelarID` int NOT NULL,
  `RAI` varchar(255) NOT NULL,
  `DescricaoRAI` text NOT NULL,
  `DataCumprimento` date NOT NULL,
  `QuantidadeCumprida` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `CumprimentosCautelares`
--

INSERT INTO `CumprimentosCautelares` (`ID`, `SolicitacaoCautelarID`, `TipoCautelarID`, `RAI`, `DescricaoRAI`, `DataCumprimento`, `QuantidadeCumprida`) VALUES
(50, 16, 1, '39776673', 'Cumprimento de Prisão e Buscas', '2025-01-14', 1),
(51, 16, 3, '39776673', 'Cumprimento de Prisão e Buscas', '2025-01-14', 1),
(53, 23, 3, '40071674', 'Prisão Erich', '2025-02-02', 1),
(54, 23, 1, '40087611', 'Buscas - Casa do Erich', '2025-02-02', 1),
(55, 23, 1, '40097722', 'Buscas - Casa da noiva do Erich', '2025-02-02', 1),
(56, 16, 1, '', '', '2025-01-14', 1),
(57, 23, 1, '40609631', 'Buscas - Ribeirão Cascalheiras/MT', '2025-02-03', 1),
(58, 27, 1, '40744215', 'Buscas - Casa Marcos', '2025-03-14', 1),
(59, 27, 1, '40745737', 'Buscas - Casa Isaias (Pitbull)', '2025-03-14', 1),
(60, 27, 1, '40744042', 'Buscas - Casa Wanderson', '2025-03-14', 1),
(61, 27, 1, '40744320', 'Cumprimento - Atalaia', '2025-03-14', 1),
(62, 29, 3, '41617098', 'CUMPRIMENTO DE PRISÃO TEMPORÁRIA', '2025-05-06', 1),
(63, 30, 1, '41617098', 'CUMPRIMENTO DE BUSCA E APREENSÃO', '2025-05-06', 1),
(64, 33, 3, '41964863', 'Prisão Lucas', '2025-05-28', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Delegacias`
--

CREATE TABLE `Delegacias` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Delegacias`
--

INSERT INTO `Delegacias` (`ID`, `Nome`) VALUES
(1, 'GIH - Grupo de Investigação de Homicídios de Rio Verde');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Desaparecidos`
--

CREATE TABLE `Desaparecidos` (
  `ID` int NOT NULL,
  `Vitima` varchar(255) NOT NULL,
  `Idade` int DEFAULT NULL,
  `DataDesaparecimento` date NOT NULL,
  `DataLocalizacao` date DEFAULT NULL,
  `RAI` varchar(50) NOT NULL,
  `Situacao` enum('Desaparecido','Encontrado') NOT NULL DEFAULT 'Desaparecido',
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UsuarioCriadorID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Desaparecidos`
--

INSERT INTO `Desaparecidos` (`ID`, `Vitima`, `Idade`, `DataDesaparecimento`, `DataLocalizacao`, `RAI`, `Situacao`, `CreatedAt`, `UsuarioCriadorID`) VALUES
(14, 'Marcos Vinícius Oliveira Cardoso', 13, '2024-12-22', '2024-12-27', '39468250', 'Encontrado', '2024-12-25 01:01:16', 2),
(15, 'IVANETE OLIVEIRA', 49, '2024-06-08', '2025-04-16', '36228627', 'Encontrado', '2024-12-27 16:59:52', 7),
(16, 'WANDERSON RODRIGUES DA MATA', 31, '2024-12-27', '2025-01-05', '39534706', 'Encontrado', '2024-12-31 12:24:35', 7),
(17, 'MARIELLI DOS SANTOS PEREIRA DA SILVA', 16, '2024-12-29', '2025-02-15', '39538277', 'Encontrado', '2024-12-31 12:26:01', 2),
(18, 'PAULO HENRIQUE PEREIRA', 47, '2024-12-29', '2024-12-31', '39548886', 'Encontrado', '2024-12-31 12:29:16', 7),
(19, 'EURÍPEDES ALVES DA SILVA ', 70, '2024-12-22', '2024-12-23', '39442600', 'Encontrado', '2024-12-31 13:45:45', 7),
(20, 'NATANAEL DA SILVA MOREIRA', 22, '2024-12-15', '2024-12-19', '39351932', 'Encontrado', '2024-12-31 13:46:44', 7),
(21, 'WAGNER RODRIGUES DE SOUZA', 23, '2024-12-01', '2024-12-01', '39274721', 'Encontrado', '2024-12-31 13:51:27', 7),
(22, 'REVENILDO FRANCISCO DE ANDRADE', 38, '2024-11-24', '2025-02-15', '39171965', 'Encontrado', '2024-12-31 13:52:42', 2),
(23, 'IVANILSON SILVA GUIMARAES', NULL, '2024-11-20', NULL, '38969512', 'Desaparecido', '2024-12-31 13:53:59', 2),
(24, 'LUCAS DA SILVA DOS SANTOS', 27, '2024-10-20', '2025-02-15', '38957822', 'Encontrado', '2024-12-31 13:54:53', 2),
(25, 'JOSÉ ALVES DA CRUZ', 59, '2024-11-06', NULL, '32737951', 'Desaparecido', '2024-12-31 13:55:45', 2),
(26, 'NEILMA DE SOUZA LIMA', 41, '2024-11-20', '2025-04-16', '38895776', 'Encontrado', '2024-12-31 13:56:26', 2),
(27, 'ROBERIO DE OLIVEIRA MIRANDA', 30, '2024-11-13', '2025-04-23', '38815147', 'Encontrado', '2024-12-31 13:58:11', 2),
(28, 'CARLOS ANTONIO ALVES DE OLIVEIRA', 43, '2009-06-16', NULL, '38797904', 'Desaparecido', '2024-12-31 13:59:29', 2),
(29, 'GUSTAVO MORAES ARANTES DOS SANTOS', NULL, '2024-11-12', NULL, '38769885', 'Desaparecido', '2024-12-31 14:00:48', 2),
(30, 'TALES DA COSTA PIRES', 27, '2024-11-02', NULL, '38739984', 'Desaparecido', '2024-12-31 14:01:49', 2),
(31, 'RAFAEL FERREIRA SILVA', 40, '2024-11-06', NULL, '38681125', 'Desaparecido', '2024-12-31 14:02:38', 2),
(32, 'DIONE ROSENBERG VIANA DINIZ', 39, '2024-09-28', NULL, '38086927', 'Desaparecido', '2024-12-31 14:03:18', 2),
(33, 'RAIMUNDA NONATA BATISTA DOS REIS', 18, '2020-01-04', NULL, '13354129', 'Desaparecido', '2024-12-31 14:05:06', 2),
(34, 'LEUDIMAR DOS SANTOS', NULL, '2024-09-06', NULL, '37720799', 'Desaparecido', '2024-12-31 14:06:06', 2),
(40, 'RENATO FRANCISCO DE OLIVEIRA', 42, '2025-01-03', '2025-01-05', '39622571', 'Encontrado', '2025-01-04 16:49:37', 7),
(41, 'JOAO DIVINO SOUSA LIMA', NULL, '2024-12-21', '2025-01-05', '39425351', 'Encontrado', '2025-01-13 18:33:46', 7),
(42, 'ALENCAR ADIERS', 50, '2025-01-17', '2025-02-15', '39839578', 'Encontrado', '2025-02-14 17:44:04', 7),
(43, 'PEDRO SATURNINO DE SOUSA NETO', 41, '2025-01-17', '2025-02-15', '39842463', 'Encontrado', '2025-02-14 17:45:47', 7),
(44, 'CARLOS MATEUS DOS SANTOS', NULL, '2025-01-17', '2025-02-15', ' 39843149', 'Encontrado', '2025-02-14 17:47:23', 7),
(45, 'PEDRO LUIZ MARTINES', 60, '2025-01-21', '2025-02-15', '39915246', 'Encontrado', '2025-02-14 17:49:11', 7),
(46, ' GILVAN JOSE SANTOS DA SILVA', 32, '2025-02-02', '2025-02-15', '40120368', 'Encontrado', '2025-02-14 17:50:41', 7),
(47, 'LUCAS DE ABREU CALDEIRA NETO', 45, '2025-02-08', '2025-02-15', ' 40173761', 'Encontrado', '2025-02-14 17:52:12', 7),
(48, 'BRUNO DOS SANTOS PEREIRA', 23, '2024-03-30', NULL, '39895722', 'Desaparecido', '2025-02-14 17:57:26', 7),
(49, 'LEANDRO PEREIRA FLORENTINO', 41, '2025-02-12', '2025-02-18', '40263269', 'Encontrado', '2025-02-14 17:59:30', 7),
(50, 'ADEILSA PAULINA DA SILVA', NULL, '2025-02-13', '2025-02-20', '40265077', 'Encontrado', '2025-02-14 18:02:31', 7),
(51, 'PAULO RICARDO MIRANDA E MIRANDA', 26, '2025-02-14', '2025-02-20', '40304651', 'Encontrado', '2025-02-19 17:23:42', 7),
(52, 'MANUEL MAGALHAES DE SOUSA', 43, '2025-02-17', '2025-02-20', '40320265', 'Encontrado', '2025-02-19 17:30:22', 7),
(53, 'LUCAS DA SILVA SANTOS', 13, '2010-09-09', '2025-02-20', '34309918', 'Encontrado', '2025-02-19 17:36:02', 7),
(54, 'CARLOS ALBERTO BARROSO LOPES', 23, '2025-02-19', '2025-02-24', '40405303', 'Encontrado', '2025-02-24 12:52:01', 7),
(55, 'BARBARA MARTINS', 19, '2025-02-21', '2025-02-24', ' 40400245', 'Encontrado', '2025-02-24 14:29:25', 7),
(56, 'Cosme Ferreira de Jesus', 53, '2025-02-21', '2025-02-24', '40398276', 'Encontrado', '2025-02-24 14:30:39', 7),
(57, 'Eleonel da Silva Santos', 25, '2025-02-17', '2025-02-24', '40332809', 'Encontrado', '2025-02-24 14:31:59', 7),
(58, ' JULIO CESAR ALVES DOS SANTOS', 23, '2025-02-28', '2025-03-06', '40542494', 'Encontrado', '2025-03-05 17:38:31', 7),
(59, 'ARLOS FRANCO DA SILVA', 43, '2025-02-24', '2025-03-06', '40558836', 'Encontrado', '2025-03-05 17:42:09', 7),
(60, 'JOSÉ PAULO DEMETRIO DOS SANTOS', 23, '2025-02-28', '2025-03-06', '40520443', 'Encontrado', '2025-03-05 17:45:35', 7),
(61, 'OZIEL REIS SILVA', 31, '2025-03-10', '2025-03-31', '40673552', 'Encontrado', '2025-03-12 12:29:48', 7),
(62, ' LEANDRO ARAÚJO AMORIM', NULL, '2025-03-08', '2025-03-12', '40665559', 'Encontrado', '2025-03-12 12:31:56', 7),
(63, 'IZAURA MARIA ALVES RAMOS GOUVEIA', 63, '2025-03-05', '2025-03-12', ' 40596945', 'Encontrado', '2025-03-12 12:52:31', 7),
(64, ' MARCIO PEREIRA DE LIMA', 39, '2024-12-27', '2025-03-31', '40577079', 'Encontrado', '2025-03-12 12:54:04', 7),
(65, 'MILTON FERREIRA', 67, '2025-03-01', NULL, '40542322', 'Desaparecido', '2025-03-12 13:02:46', 7),
(66, 'SIDNEI ANTONIO MARQUES', 54, '2025-03-19', '2025-03-20', '40820331', 'Encontrado', '2025-03-20 11:57:15', 7),
(67, 'ANA CLARA GONÇALVES PARENTE', 18, '2025-03-15', '2025-03-31', '40771550', 'Encontrado', '2025-03-20 12:16:56', 7),
(68, 'GILBERTO CRUZEIRO MARTINS JUNIOR', 34, '2025-03-13', '2025-04-16', ' 40732811', 'Encontrado', '2025-03-20 12:20:23', 7),
(69, 'LUZIA PINTO DE SOUSA', 41, '2025-03-12', '2025-03-20', '40714341', 'Encontrado', '2025-03-20 12:22:04', 7),
(70, 'SANDRA JULIANA DE OLIVEIRA', 56, '2025-03-25', '2025-03-27', '40928152', 'Encontrado', '2025-03-27 11:24:36', 7),
(71, 'ANTONIO MARCOS GOMES ALVES FILHO', 28, '2025-03-22', NULL, '40871171', 'Desaparecido', '2025-03-27 11:32:01', 7),
(72, 'JOSUÉ KAUÃ ROMUALDO SILVA DOS SANTOS', 20, '2025-03-17', '2025-04-16', '40832231', 'Encontrado', '2025-03-27 11:32:56', 7),
(73, 'LETICIA OLIVEIRA DE AMORIM', 6, '2025-03-24', '2025-03-27', ' 40913386', 'Encontrado', '2025-03-27 13:28:34', 7),
(75, 'ODELIO RIBEIRO DA SILVA', 43, '2025-03-26', '2025-03-31', '40974260', 'Encontrado', '2025-03-31 12:07:31', 7),
(76, 'FERNANDO DORNELAS BORGES', 38, '2025-03-28', '2025-03-31', '40970669', 'Encontrado', '2025-03-31 12:16:25', 7),
(77, 'ALIRIO WENCESLAU SILVA JUNIOR', 43, '2025-04-06', '2025-04-07', '41122242', 'Encontrado', '2025-04-09 12:52:42', 4),
(78, 'CACILDA BUENO CASTRO', 71, '2025-03-30', '2025-04-04', '41015848', 'Encontrado', '2025-04-09 12:54:44', 4),
(79, 'GYSELE YASMIN SILVA LIMA', NULL, '2025-04-14', '2025-04-14', '41257959', 'Encontrado', '2025-04-15 18:12:28', 4),
(80, 'JOAO JOSE PERES DIAS', NULL, '2025-04-10', '2025-04-16', '41194591', 'Encontrado', '2025-04-16 13:12:44', 7),
(81, 'EURÍPEDES GONÇALVES PINTO', 62, '2025-04-04', '2025-04-16', '41143623', 'Encontrado', '2025-04-16 13:17:52', 7),
(82, 'FELIPE FRIES NETO', 30, '2025-01-04', '2025-04-23', '41268820', 'Encontrado', '2025-04-16 14:01:32', 7),
(83, 'MANOELITO DE ANDRADE', 63, '2025-04-12', '2025-04-23', ' 41281611', 'Encontrado', '2025-04-16 14:48:33', 7),
(84, 'LILIANE RODRIGUES DE SOUZA', 36, '2025-04-13', '2025-04-23', '41346722', 'Encontrado', '2025-04-23 12:09:12', 7),
(85, 'RODRIGO RUAN PEREIRA VALVERDE SAMPAIO', 22, '2025-04-15', '2025-04-23', '41289121', 'Encontrado', '2025-04-23 12:25:25', 7),
(86, 'TAIZA DA SILVA SANTANA', 30, '2025-04-20', NULL, '41365687', 'Desaparecido', '2025-04-23 12:31:07', 7),
(87, 'RODRIGO GOMES', NULL, '2025-04-19', '2025-04-23', '41362497', 'Encontrado', '2025-04-23 12:33:11', 7),
(88, 'CARLOS ANDRE DA SILVA', 24, '2025-04-17', NULL, '41334123', 'Desaparecido', '2025-04-23 12:34:22', 7),
(91, 'VITOR DE OLIVEIRA TELES', 22, '2025-04-23', '2025-04-24', ' 41408964', 'Encontrado', '2025-04-24 11:35:58', 7),
(92, 'BÁRBARA LEÃO FERREIRA DE SOUZA SANTOS', NULL, '2025-04-21', '2025-04-24', '41363550', 'Encontrado', '2025-04-24 14:25:26', 7),
(93, 'ANTONIO RICARDO LIMA GOMES', 32, '2025-04-29', NULL, ' 41512919', 'Desaparecido', '2025-05-05 11:27:21', 7),
(94, 'SOSTENES APOLLO VILAS BOAS PAIM', 29, '2025-05-01', '2025-05-05', ' 41542429', 'Encontrado', '2025-05-05 11:29:03', 7),
(95, 'ERIBALDO PEREIRA DA SILVA', 31, '2025-05-03', '2025-05-05', '41583440', 'Encontrado', '2025-05-05 11:31:03', 7),
(96, ' MAXWELL ALVES XAVIER', 27, '2025-05-04', '2025-05-05', '41584207', 'Encontrado', '2025-05-05 11:32:24', 7),
(97, 'ALVARO INACIO NOGUEIRA GONCALVES PINHEIRO', 18, '2025-05-08', '2025-05-09', '41654452', 'Encontrado', '2025-05-09 14:46:42', 7),
(98, ' VICTOR GABRIEL GONÇALVES ROBERTO', NULL, '2025-05-06', '2025-05-09', ' 41646102', 'Encontrado', '2025-05-09 14:49:28', 7),
(99, 'LUCIANO SOUSA BORGES', 30, '2025-05-05', '2025-05-12', ' 41678641', 'Encontrado', '2025-05-12 13:16:56', 7),
(100, 'MATHEUS NOVAIS CECILIANO', 22, '2025-05-10', '2025-05-31', '41677691', 'Encontrado', '2025-05-12 13:18:50', 7),
(101, 'JAILSON CESAR RODRIGUES XAVIER', 22, '2025-05-10', '2025-05-12', '41697039', 'Encontrado', '2025-05-12 13:20:05', 7),
(102, 'MARCELA RAYANE PAIXAO DA SILVA DA MATA', 24, '2025-05-08', '2025-05-12', '41640262', 'Encontrado', '2025-05-12 13:29:15', 7),
(103, 'RAFAEL FERREIRA GONÇALVES', 32, '2025-05-11', '2025-05-13', '41717124', 'Encontrado', '2025-05-13 11:40:49', 7),
(105, 'TOGILDO PARREIRA DE SOUSA', 53, '2025-04-26', '2025-05-19', '41716228', 'Encontrado', '2025-05-14 12:06:28', 7),
(107, 'NILTON ROSA DE SOUZA', 36, '2025-05-22', '2025-05-27', '41882380', 'Encontrado', '2025-05-27 14:17:32', 4),
(108, 'RAFAEL FERREIRA GONÇALVES', 32, '2025-05-24', '2025-05-27', '41899402', 'Encontrado', '2025-05-27 14:23:30', 4),
(109, 'GABRIRLLY BATISTA DE ARAÚJO', 13, '2025-05-22', '2025-05-27', '41906012', 'Encontrado', '2025-05-27 14:33:13', 4),
(110, 'VALÉRIA DA SILVA LIMA GOMES', 32, '2025-05-23', NULL, '41924464', 'Desaparecido', '2025-05-27 14:37:38', 4),
(112, 'ANA CLAUDIA MOREIRA DA SILVA', 15, '2025-05-23', '2025-05-31', '41791171', 'Encontrado', '2025-05-27 19:43:09', 4),
(113, 'WARLAN FRANCISCO DE OLIVEIRA', NULL, '2025-05-12', '2025-05-31', '41951279', 'Encontrado', '2025-05-30 17:50:52', 4),
(114, 'MARIO SILVIO PEREIRA ALVES', NULL, '2025-05-20', '2025-05-31', '41856661', 'Encontrado', '2025-05-30 18:02:39', 7),
(115, 'NEILMA DE SOUZA LIMA', 42, '2025-05-04', NULL, '41901221', 'Desaparecido', '2025-05-30 18:07:07', 7),
(116, 'FLAVIO VAZ MALTA', 46, '2025-05-17', NULL, '41822117', 'Desaparecido', '2025-05-30 18:08:10', 7),
(118, ' IVANILDE RODRIGUES SARAIVA', 39, '2025-05-11', '2025-05-31', '41712476', 'Encontrado', '2025-05-30 18:25:51', 7);

-- --------------------------------------------------------

--
-- Estrutura para tabela `DocumentosMovimentacao`
--

CREATE TABLE `DocumentosMovimentacao` (
  `ID` int NOT NULL,
  `MovimentacaoID` int NOT NULL,
  `NomeArquivo` varchar(255) NOT NULL,
  `Caminho` varchar(255) NOT NULL,
  `DataUpload` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `DocumentosMovimentacao`
--

INSERT INTO `DocumentosMovimentacao` (`ID`, `MovimentacaoID`, `NomeArquivo`, `Caminho`, `DataUpload`) VALUES
(24, 134, 'relatorio1735070359986.pdf', '../uploads/movimentacoes/676b141477c55_relatorio1735070359986.pdf', '2024-12-24 20:05:40'),
(27, 152, 'relatorio1735075180585.pdf', '../uploads/movimentacoes/676b25cf830af_relatorio1735075180585.pdf', '2024-12-24 21:21:19'),
(28, 178, 'Ofício 354-2025-DGPC.pdf', '../uploads/movimentacoes/6777e6622ad7c_Of__cio_354-2025-DGPC.pdf', '2025-01-03 13:30:10'),
(29, 180, 'SEI_GOVERNADORIA - 69000110 - Ofício.pdf', '../uploads/movimentacoes/6778416505d01_SEI_GOVERNADORIA_-_69000110_-_Of__cio.pdf', '2025-01-03 19:58:29'),
(30, 183, 'Ofício 554-2025-DGPC.pdf', '../uploads/movimentacoes/6779402fa2edb_Of__cio_554-2025-DGPC.pdf', '2025-01-04 14:05:35'),
(31, 176, 'Relatório Psicológico.pdf', '../uploads/movimentacoes/678029ace97c8_Relat__rio_Psicol__gico.pdf', '2025-01-09 19:55:24'),
(32, 152, 'Relatório Policial - 06-2024.pdf', '../uploads/movimentacoes/67812c553bf59_Relat__rio_Policial_-_06-2024.pdf', '2025-01-10 14:19:01'),
(33, 190, 'relatorio_policial_26_2022.pdf', '../uploads/movimentacoes/678134d7871cf_relatorio_policial_26_2022.pdf', '2025-01-10 14:55:19'),
(34, 201, 'relatorio1737987568473.pdf', '../uploads/movimentacoes/6797964e5b97e_relatorio1737987568473.pdf', '2025-01-27 14:21:02'),
(35, 203, 'Movimento - manifestacao.pdf', '../uploads/movimentacoes/679922c091d08_Movimento_-_manifestacao.pdf', '2025-01-28 18:32:32'),
(36, 204, 'Ofício-991680521.pdf', '../uploads/movimentacoes/679a321474c8d_Of__cio-991680521.pdf', '2025-01-29 13:50:12'),
(37, 213, 'SEI_GOVERNADORIA - 70600918 - Ofício.pdf', '../uploads/movimentacoes/67aba1909017e_SEI_GOVERNADORIA_-_70600918_-_Of__cio.pdf', '2025-02-11 19:14:24'),
(38, 214, 'SEI_GOVERNADORIA - 70601822 - Ofício.pdf', '../uploads/movimentacoes/67aba396d9875_SEI_GOVERNADORIA_-_70601822_-_Of__cio.pdf', '2025-02-11 19:23:02'),
(39, 220, 'relatorio1739538706457.pdf', '../uploads/movimentacoes/67af454d1094a_relatorio1739538706457.pdf', '2025-02-14 13:29:49'),
(40, 228, 'relatorio1739879743265.pdf', '../uploads/movimentacoes/67b475570fdd6_relatorio1739879743265.pdf', '2025-02-18 11:56:07'),
(41, 214, 'Prontuário Médico - Valter Ferreira de Lima - HMURV.pdf', '../uploads/movimentacoes/67b4ebf3e2407_Prontu__rio_M__dico_-_Valter_Ferreira_de_Lima_-_HMURV.pdf', '2025-02-18 20:22:11');

-- --------------------------------------------------------

--
-- Estrutura para tabela `EnvolvidosCumprimentoCautelar`
--

CREATE TABLE `EnvolvidosCumprimentoCautelar` (
  `ID` int NOT NULL,
  `CumprimentoCautelarID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `EnvolvidosCumprimentoCautelar`
--

INSERT INTO `EnvolvidosCumprimentoCautelar` (`ID`, `CumprimentoCautelarID`, `Nome`) VALUES
(43, 50, 'SHARLINTHON SILVA MORAIS'),
(44, 51, 'SHARLINTHON SILVA MORAIS'),
(46, 53, 'Erich Marques de Sousa'),
(47, 54, 'Erich Marques de Sousa'),
(48, 55, 'Erich Marques de Sousa'),
(49, 56, 'SHARLINTHON SILVA MORAIS'),
(50, 57, 'Erich Marques de Sousa'),
(51, 58, 'MARCOS AURELIO CARVALHO BARROSO'),
(52, 59, 'MARCOS AURELIO CARVALHO BARROSO'),
(53, 60, 'MARCOS AURELIO CARVALHO BARROSO'),
(54, 61, 'MARCOS AURELIO CARVALHO BARROSO'),
(55, 62, 'CACILDA MARIA FERREIRA'),
(56, 63, 'CACILDA MARIA FERREIRA'),
(57, 64, 'Lucas Vieira Cabral');

-- --------------------------------------------------------

--
-- Estrutura para tabela `FavoritosUsuarios`
--

CREATE TABLE `FavoritosUsuarios` (
  `ID` int NOT NULL,
  `UsuarioID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `DataFavoritado` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `FavoritosUsuarios`
--

INSERT INTO `FavoritosUsuarios` (`ID`, `UsuarioID`, `ProcedimentoID`, `DataFavoritado`) VALUES
(10, 2, 123, '2025-01-07 10:40:53'),
(12, 4, 92, '2025-01-08 14:25:45'),
(14, 4, 136, '2025-01-15 10:38:51'),
(15, 2, 149, '2025-03-17 09:46:52'),
(17, 2, 72, '2025-05-20 10:17:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Investigados`
--

CREATE TABLE `Investigados` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `ProcedimentoID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Investigados`
--

INSERT INTO `Investigados` (`ID`, `Nome`, `ProcedimentoID`) VALUES
(346, 'SHARLINTHON SILVA MORAIS', 55),
(347, 'WARLEY FERREIRA DE SOUZA', 56),
(348, 'LUCIANO DA ROCHA CAVALCANTE', 57),
(349, 'JOSÉ CARLOS DA SILVA DE SOUZA', 58),
(350, 'IRIVELTO JUSTINO DE OLIVEIRA', 59),
(351, 'MARCIEL SAURES DOS SANTOS', 59),
(352, 'ELISANGELA MACHADO TAVARES ', 60),
(353, 'JUSCELIA FERREIRA INACIO', 60),
(354, 'JOSIMAR MACHADO ALVES', 61),
(355, 'NAILTON SILVA DOS SANTOS', 62),
(356, 'CONFRONTO POLICIAL', 63),
(357, 'Ignorado', 64),
(358, 'VANDERLEI SOUSA CRUVINEL', 65),
(359, 'VANDERLEI SOUSA CRUVINEL', 66),
(360, 'FABRICIO ROMEIRO BARRETO LEMES', 67),
(361, 'KAIQUE SANTAN SILVA ALBUQUERQUE', 67),
(362, 'RAI DA SILVA LIMA', 67),
(363, 'GUSTAVO COELHO DOS SANTOS', 68),
(364, 'MÁRIO SÉRGIO FERRARI', 69),
(365, 'CÁSSIO JOSÉ SOUZA DE OLIVEIRA', 69),
(366, 'PAULO HENRIQUE SOUZA', 69),
(367, 'LARA OLIVEIRA DA SILVA', 69),
(368, 'CHESTER BATISTA DA SILVA', 69),
(369, 'ALLAN ALVES SOUSA', 69),
(370, 'RONALDO SILVANO MOREIRA', 69),
(371, 'HENRIQUE SANTOS BRAZ', 70),
(372, 'JOSÉ WILSON DA SILVA E SILVA', 71),
(373, 'RADILSON CONCEIÇÃO DA SILVA', 71),
(374, 'VINÍCIUS DE SOUZA NASCIMENTO', 71),
(375, 'LUCAS RAFAEL MARQUES DE SOUZA', 72),
(376, 'WEFERSON BARBOSA SANTOS', 73),
(377, 'CUSTÓDIO CABRAL DA SILVA', 74),
(378, 'DAVID SILVA SANTOS', 75),
(379, 'RICARDO HENRIQUE SILVA E SILVA', 76),
(380, 'DERISVANE SOUSA SANTOS', 77),
(381, 'ANA CAROLINA VIEIRA DA SILVA', 78),
(382, 'GETULIO LUIS DA COSTA ARAUJO', 78),
(383, 'GUSTAVO FELIX DE SOUZA SANTOS', 78),
(384, 'GCM', 79),
(385, 'LUDIEYME MOREIRA DA CRUZ', 80),
(386, 'MATHEUS BORGES LAURIANO FERREIRA', 80),
(387, 'MAYRONE SILVA FERREIRA', 81),
(388, 'GUILHERME OLIVEIRA MACHADO', 81),
(389, 'LUANDERSON FERREIRA DE OLIVEIRA', 81),
(390, 'IGNORADO', 82),
(391, 'CICERO PIRES GONCALVES DE OLIVEIRA', 83),
(392, 'MARCUS VINICIUS DE ARAÚJO RIBEIRO', 84),
(393, 'MURILO SILVA VIEIRA', 85),
(394, 'DHEYSON DE SOUSA PEREIRA', 86),
(395, 'ANTHONY GABRIEL GUERRA GUERRA', 87),
(396, 'MARCOS SHIGUEO KUABATA', 88),
(397, 'FILIPH RANGEL ALVES PEREIRA', 89),
(398, 'BRUNNO ALVES MATOS', 89),
(399, 'PEDRO HENRIQUE MARTINS MORAIS', 89),
(400, 'DÉBORA BEATRIZ RODRIGUES DA SILVA', 89),
(401, 'MARCUS VINÍCIUS DE ARAÚJO RIBEIRO', 89),
(402, 'MARCOS SHIGUEO KUABATA', 90),
(403, 'DENOMAR ALVES RODRIGUES', 91),
(404, 'GILMAR ALVES DOS SANTOS', 92),
(405, 'VERONICA QUEIROZ GOMES', 93),
(406, 'GRAZIELLY VITORIA ALVES DE OLIVEIRA', 93),
(407, 'WEGLESSON PEREIRA SILVA', 94),
(408, 'INGRID CORREIA DOS SANTOS', 94),
(409, 'THIAGO FERNANDES MARQUES', 94),
(410, 'VALTEMIR CRUZ SILVA', 95),
(411, 'WELTON ALVES FERREIRA SOBRINHO', 96),
(412, 'IGNORADO', 97),
(413, 'OSEAS BORGES DA SILVA', 98),
(414, 'LEO SILVA DE SOUZA', 98),
(415, 'MATHEUS FELIPE DOS SANTOS', 99),
(416, 'RAINE LOPES GONÇALVES', 100),
(417, 'CAUAN DE JESUS CRATEUS ALVES', 101),
(418, 'JOHN FREITAS DA SILVA', 102),
(419, 'HYGOR EMRICH LEÃO', 103),
(420, 'VANESSA DE SOUZA NASCIMENTO', 104),
(421, 'IGNORADO', 105),
(422, 'BERNARDO BATISTA DE OLIVEIRA ARAÚJO', 106),
(423, 'MOACIR LEANDRO DA SILVA', 107),
(424, 'IGNORADO', 108),
(425, 'PAULO AUGUSTO DA SILVA', 109),
(426, 'OSVALDO FUGA FILHO', 110),
(427, 'GLORIVAL CRUVINEL MORAES', 111),
(428, 'JORGE DOS SANTOS FILHO', 112),
(429, 'RONEY PIRES BAILÃO', 113),
(430, 'SIDNEI PIRES BAILÃO', 113),
(431, 'EDNALDO VICENTE DA SILVA', 114),
(432, 'KAIO SILVA LIMA', 115),
(433, 'GILDO SANTOS', 116),
(434, 'CONFRONTO POLICIAL', 117),
(435, 'GILDO SANTOS', 118),
(436, 'GABRIEL FERREIRA SILVA (Rabicó)', 119),
(437, 'VIVIANE HENRIQUE DE OLIVEIRA', 120),
(438, 'CONFRONTO POLICIAL', 121),
(439, 'FRANKLIN DE SOUZA FERREIRA', 122),
(440, 'GUILHERME HENRIQUE OLIVEIRA MOREIRA', 123),
(441, 'FELIPE DA SILVA SANTOS', 123),
(442, 'CONFRONTO POLICIAL', 124),
(443, 'IGNORADO', 125),
(444, 'CLAUDIMAR DE JESUS SANTOS', 126),
(445, 'REGIS MORAES DE ALMEIDA', 127),
(446, 'ROBSON PEIXOTO DE OLIVEIRA', 127),
(447, 'CHESLEY RODRIGUES DAS CHAGAS', 127),
(448, 'MANOEL ARAUJO DA SILVA', 127),
(449, 'MARCOS FELIPE RODRIGUES SANTOS', 127),
(450, 'CAMILA MENDES DOS REIS', 127),
(451, 'RICARDO SILVA SOUZA', 127),
(452, 'WELLINGTON VIEIRA LOPES', 128),
(453, 'JOAO VITOR BARROS COSTA', 129),
(454, 'IGNORADO', 130),
(455, 'IGNORADO', 131),
(456, 'ISRAEL SAMPAIO DOS REIS', 132),
(457, 'JEAN SANTOS BRITO', 133),
(458, 'IGNORADO', 134),
(459, 'ANTÔNIO RODRIGUES EUFRÁZIO', 135),
(460, 'KAUAN IVANHEZ PINHEIRO', 136),
(461, 'EDILSON ABREU LIMA', 136),
(462, 'LUCIANO PENA DE LIMA', 137),
(463, 'ILSON PAULA DE LIMA', 138),
(464, 'ERICH MARQUES DE SOUSA', 55),
(465, 'CONFRONTO POLICIAL', 139),
(466, 'POLICIAIS MILITARES', 140),
(467, 'IGNORADO', 141),
(468, 'ALAIR PEREIRA DA CUNHA', 142),
(469, 'KETTLYN TAYLINE QUEIROZ MARTINS', 142),
(470, 'IGNORADO', 143),
(471, 'RAINALDO DOS SANTOS DE OLIVEIRA', 144),
(472, 'IGNORADO', 145),
(473, 'IGNORADO', 146),
(474, 'IGNORADO', 147),
(475, 'RAIMUNDO GABRIEL DA SILVA PINTO', 148),
(476, 'IGNORADO', 149),
(477, 'ADRIAN OLIVEIRA DA SILVA', 150),
(478, 'CASSILDA MARIA FERREIRA', 151),
(479, 'DIEGO DE OLIVEIRA SANTOS', 152),
(480, 'IGNORADO', 153),
(481, 'CONFRONTO POLICIAL', 154),
(482, 'IGNORADO', 155),
(483, 'KETLEN GEOVANA DOS SANTOS MARQUES', 156),
(484, 'CACILDA MARIA FERREIRA', 157),
(485, 'DOUGLAS SILVA PAIVA', 158),
(486, 'KAIKE MOURA FARIA', 159),
(487, 'KAIKE MOURA FARIA', 160),
(488, 'IGNORADO', 161),
(489, 'LUCAS VIEIRA CABRAL', 162),
(490, 'MARCOS ANDRÉ PRACIANO CARNEIRO', 163),
(491, 'JAIME BARBOSA JUNIOR', 164),
(492, 'AILTON COUTO PEREIRA', 165),
(493, 'RENATO RIBEIRO DE ARAÚJO', 166),
(494, 'IGNORADO', 167),
(495, 'WANDERSON ISA DE SOUSA', 168),
(496, 'WESLEY CASTRO DOS SANTOS', 168),
(497, 'IGNORADO', 169),
(498, 'IGNORADO', 170),
(499, 'GABRIEL HENRIQUE RODRIGUES BARROS', 171),
(500, 'IGNORADO', 172),
(501, 'SIRLENE ROSA DE ASSIS', 173),
(502, 'JULIANO GONÇALVES DA SILVA', 174),
(503, 'JUNIOR', 175),
(504, 'RIQUELME SOUSA ROSA', 176),
(505, 'SAMUEL DA SILVA E JOSÉ ORLANDO DA SILVA ALVES', 177),
(506, 'IGNORADO', 178),
(507, 'MICHAEL DOUGLAS OLIVEIRA DOS SANTOS', 179),
(508, 'TAMARA DOS SANTOS PAIXÃO', 180),
(509, 'DHAYGARO MATHEUS SOUZA SANTANA', 181),
(510, 'IRAMAR CABRAL DE FREITAS', 182),
(511, 'FLAVIO PEREIRA MAIA', 183),
(512, 'EVERTON SILVA DO NASCIMENTO', 184),
(513, 'LEANDRO ALVES VIEIRA', 185),
(514, 'IGNORADO', 186),
(515, 'JUNIOR KENED LAURENÇO SILVA', 187),
(516, 'JOSÉ ANTÔNIO BATISTA', 188),
(517, 'KERLY ADRIANO PEREIRA DA SILVA', 189),
(518, 'MÁRCIO ROBERTO RAMOS SANTOS', 189),
(519, 'CRISTIANO ALVES SILVA', 190),
(520, 'LORRAINE DE JESUS FLORES', 191),
(521, 'GABRIEL FELPE DA SILVA MELO', 191),
(522, 'MARCEL TEIXEIRA DA SILVA', 192),
(523, 'LUCAS TEIXEIRA DA SILVAO', 192),
(524, 'ALISSON ALMEIDA DOS SANTOS', 193),
(525, 'DANIEL SILVA MOTA', 194),
(526, 'JOÃO PAULO DA SILVA MIRANDA', 195),
(527, 'VICTOR PAULINO SANTANA (TENTATIVA)', 196),
(528, 'CRISTIANO QUEIROZ DA SILVA (PORTE DE ARMA)', 196),
(529, 'FRANCISCO DA SILVA FERNANDES (POSSE DE ARMA)', 196),
(530, 'LEONARDO OLIVEIRA DOS SANTOS', 197),
(531, 'HARNOLDO GOMES DA COSTA', 197),
(532, 'IGNORADO', 198),
(533, 'CHRISTIAN PROFIRO DA CRUZ (MENOR)', 199),
(534, 'EVERSON GUILHERME PEREIRA RIBEIRO', 199),
(535, 'MAIKON MACHADO OLIVEIRA', 199),
(536, 'JOCENILDO SOUSA DO NASCIMENTO', 200),
(537, 'EDILSON ABREU LIMA', 200),
(538, 'JERFESON ERIVALDO DA SILVA NASCIMENTO', 201),
(539, 'CALIL FAUSTINO DA SILVA', 202),
(540, 'MAYKON DOUGLAS VIEIRA ARAÚJO', 202),
(541, 'JOSUÉ BARBOSA', 203),
(542, 'BAYRON KAUAN OLIVEIRA BARBOSA', 203),
(543, 'WESLEY RIBEIRO MARTINS', 204),
(544, 'JHONATAN MOURA AZEVEDO', 205),
(545, 'LEONARDO DE JESUS FERREIRA', 206),
(546, 'PEDRO HENRIQUE VIEIRA DA SILVA', 207),
(547, 'WELITON ARAÚJO SILVA', 208),
(548, 'JOAS PEREIRA NASCIMENTO', 208),
(549, 'MARCOS ISAAC CASTRO DOS SANTOS', 209),
(550, 'MARCOS FELIPE DOS SANTOS', 210),
(551, 'PAULO HENRIQUE LEITE AMORIM', 211),
(552, 'ANDRE LUIZ ANTUNES FERREIRA', 212),
(553, 'DANILO DANTAS CASTELO BRANCO', 213),
(554, 'CICERO FRANCISCO EUGENIO', 214),
(555, 'FERNANDA PAULA PEREIRA DA CRUZ', 214),
(556, 'ROBERTO DOS SANTOS SILVA', 215),
(557, 'CIDIVALDO MOREIRA DE JESUS', 216),
(560, 'REVIS MARTINS DA SILVA', 218),
(561, 'CONFRONTO POLICIAL', 219),
(562, 'LEANDRO SOUZA NASCIMENTO', 220),
(563, 'FRANCISCO ALVES DOS SANTOS', 221),
(564, 'CICERO NEVES DA SILVA', 221),
(565, 'DENES DUNGA LOPES DA SILVA', 221),
(566, 'NAJAN DOS SANTOS BATISTA', 222),
(567, 'FRANCISCO DAS CHAGAS LULA COSTA', 223),
(568, 'ANTONIO CARLOS LEAL RODRIGUES', 224),
(569, 'ORIVALDO MARITNS SILVA FILHO', 224),
(570, 'NELSON PEREIRA DE OLIVEIRA', 225),
(571, 'EZIO ALENCAR DA SILVA', 226),
(572, 'FELIPE AUGUSTO LIMA DE OLIVEIRA', 227),
(573, 'FRANCISCO ELTON DO NASCIMENTO', 228),
(574, 'IGNORADO', 229),
(575, 'CONFRONTO POLICIAL', 230),
(576, 'LUCAS CRUZ DA SILVA', 231),
(577, 'ALTIERES BRUNO SILVA', 232),
(578, 'EVANDIR JUNIO OLIVEIRA SILVA', 233),
(579, 'GUILHERME MARTINS', 234),
(580, 'IGNORADO', 235),
(581, 'EDIMILSON FERNANDES DOS SANTOS', 236),
(582, 'ANTONIO NERES DA SILVA', 237),
(583, 'IGNORADO', 238),
(584, 'ANTONIO FERNANDES DE SOUSA', 239),
(585, 'CAIO FLAVIO SCHREINER ANTUNES FILHO', 240),
(586, 'WELITON ARAUJO SILVA', 240),
(587, 'IGNORADO', 241),
(588, 'ROBSON ROSA REZENDE', 242),
(589, 'DELIOMAR DA SILVA PEREIRA', 243),
(590, 'GRENDO WASHINGTON DE JESUS', 243),
(591, 'JOSE CARLOS VIEIRA DA SILVA', 244),
(592, 'LEONARDO PEREIRA SAMPAIO', 245),
(593, 'GABRIEL FERREIRA SILVA', 246),
(594, 'GESIEL SILVA BORGES', 247),
(595, 'JADIELSON GUEDES DOS SANTOS', 248),
(596, 'JARDIEL GUEDES DOS SANTOS', 248),
(597, 'WISLEY SERAFIM DOS SANTOS', 249),
(598, 'JOYCE ALVES MACHADO', 250),
(599, 'ANTONIO LINHARES DA SILVA', 251),
(600, 'BRUNO MANOEL CARVALHO DA SILVA', 252),
(601, 'RODRIGO VIEIRA MENDONÇA', 252),
(602, 'EDNEY CORDEIRO SOARES', 253),
(603, 'RENATO DIAS CANDIDO', 254),
(604, 'BRUNO SILVA BORGES', 255),
(605, 'LORRAN MENDES SILVA', 256),
(606, 'JOSIEL ROBERTO FERREIRA OLIVEIRA', 257),
(607, 'LUISLEI FERREIRDA DA COSTA', 258),
(608, 'EDIVALDO FERREIRA LIMA', 259),
(609, 'JOAO PEDRO BONINO DOS SANTOS', 260),
(610, 'MAYLON ANDRE FERNANDES (MENOR)', 260),
(611, 'CONFRONTO POLICIAL', 261),
(612, 'VALDECI DE SOUZA SANTOS', 262),
(613, 'EDIVALDO SOUSA FELIX', 263),
(614, 'UIS FERNANDO SOUSA DIAS (MENOR)', 263),
(615, 'RODRIGO LAABS', 264),
(616, 'JOSIEL  DOS SANTOS DIAS', 265),
(617, 'SILVANILSON DIAS SOARES', 266),
(618, 'IGNORADO', 267),
(619, 'MAYCON PEREIRA DA SILVA', 268),
(620, 'WARLEY DA SILVA GOMES (MENOR)', 268),
(621, 'CONFRONTO POLICIAL (MOISES, HILLYARD E EMILIO)', 269),
(622, 'CARLOS EDUARDO MARTINS BERNARDES', 270),
(623, 'ISMAEL PEREIRA LIMA', 270),
(624, 'RAQUEL PEREIRA LIMA', 270),
(625, 'CARLOS AUGUSTO DOS SANTOS PINTO JUNIOT', 271),
(626, 'PAULO HENRIQUE DA CRUZ SOUSA', 271),
(627, 'JARDEL KNOP DE ALMEIDA', 272),
(628, 'IGNORADO', 273),
(629, 'IGNORADO', 274),
(630, 'EVERALDO BATISTA DE OLIVEIRA', 275),
(631, 'NATIA MARIA RIBEIRO DA SILVA', 276),
(632, 'VITOR RODRIGUES DUARTE', 276),
(633, 'DANILO', 276),
(634, 'VINICIUS', 276),
(635, 'ALTAY ARANTES ALVES DA CONCEIÇÃO (MENOR)', 276),
(636, 'IGNORADO', 277),
(637, 'DIONE ROUDENBERGUE VIANA DINIZ', 278),
(638, 'RAFAEL RODRIGUES NOGUEIRA', 279),
(639, 'FELIPE FERREIRA DOS SANTOS', 280),
(640, 'LUIZ CARLOS FERREIRA', 281),
(641, 'SIRO FERREIRA DA SILVA', 282),
(642, 'GLEICE LIMA DA SILVA', 282),
(643, 'DENNYS WILKER PEREIRA ARAUJO', 282),
(644, 'LUCELIA PIRES DE LIMA', 282),
(645, 'RIBAMAR SOUSA ROSA (MENOR)', 282),
(646, 'NATANAEL BORGES SOUSA', 283),
(647, 'EDIMAR PEREIRA DA SILVA JUNIOR', 283),
(648, 'UANDERSON SOARES DA SILVA', 284),
(649, 'DIEGO RODRIGUES SILVA', 284),
(650, 'NUBIA CHRISTINE ARANTES PRADO', 285),
(651, 'JOAO VITOR BARBOSA DA SILVA MENEZES', 286),
(652, 'PAULA CRISTINA ALVES PAGLIARINI', 286),
(653, 'ANDRÉ DA SILVA ARAUJO', 286),
(654, 'GILDO SANTOS', 287),
(655, 'ANDERSON RIBEIRO SILVA', 288),
(656, 'LUCAS CAMARGO RODRIGUES ROCHA', 289),
(657, 'EDUARDO JOSÉ CABRAL', 289),
(658, 'GABRIEL, OLIVEIRA SILVA (MENOR)', 290),
(659, 'ANTONIO JOSE DOS SANTOS FILHO (MENOR)', 290),
(660, 'LEOSMAR ALVES DE JESUS FILHO (MENOR)', 290),
(661, 'MIKAEL VITOR SILVA REIS (MENOR)', 290),
(662, 'DANILO MIGUEL DOS ANJOS', 291),
(663, 'SIDINEY RODRIGUES DA CUNHA SILVA', 291),
(664, 'VALTER JUNIOR DE SOUSA', 291),
(665, 'MARIA DO SOCORRO DELFINO DE LIMA', 292),
(667, 'PABLO HENRIQUE RIBEIRO DA SILVA', 294),
(668, 'RIBAMAR SOUSA ROSA (MENOR)', 294),
(669, 'IGNORADO', 295),
(670, 'MAURUZAN FIRMIANO DA SILVA', 296),
(671, 'AURELIANO MAIA DE OLIVEIRA', 297),
(672, 'DIEGO HENRIQUE ALVES PIRES', 297),
(673, 'MARCIO MAIA OLIVEIRA', 297),
(674, 'WAGNER ADRIANO CABRAL', 298),
(675, 'ALDAIR SILVA DE SOUZA', 299),
(676, 'CONFRONTO POLICIAL', 300),
(677, 'DIEGO DA SILVA', 301),
(678, 'JOSUE BRITO BRAGA', 302),
(679, 'DANYEL LUCAS SANTOS AMORIM', 303),
(680, 'JEHANRIER JUNIOR BIOLCHI', 304),
(681, 'HELIO MARTINS GOMES', 305),
(682, 'DARLAN MAGALHAES VILELA', 306),
(683, 'GERALDO LOPES DA SILVA', 307),
(684, 'SEBASTIÃO ANTONIO DA COSTA', 307),
(685, 'UEBERTON MENDES SENA', 307),
(686, 'ELIENE GOMES DE JESUS', 308),
(687, 'FRANCISCO EUDES DO NASCIMENTO', 309),
(688, 'MARCOS ANTÔNIO DE MATOS', 310),
(689, 'ANDRE LUIZ GONÇALVES', 311),
(690, 'DOUGLAS SANTOS SOUZA', 311),
(691, 'JOSE FABIO DOS SANTOS', 312),
(692, 'TIAGO DA SILVA SANTOS', 313),
(693, 'IGNORADO', 314),
(694, 'GENIVALDO RODRIGUES DOS SANTOS', 315),
(695, 'DANILO SOUZA XAVIER', 316),
(696, 'SAULO DIOLINO DA SILVA NETO', 317),
(697, 'FRANCISCO ASSIS JERONIMO', 318),
(698, 'MILTON BERNARDES DA SILVA NETO', 319),
(699, 'GILMARIO GASPAR PIRES', 320),
(700, 'LASARO BATISTA SILVEIRA', 321),
(701, 'EVANDRO MENDES DE SOUSA', 322),
(702, 'CLAUDIA RODRIGUES VIEIRA', 323),
(703, 'JOÃO MARTINS FILHO', 324),
(704, 'WELLYSSON ARAÚJO DE SOUZA', 325),
(705, 'ALEX NUNES DA SILVA', 326),
(706, 'ANA PAULA GUEDES DE SOUSA BORGES', 327),
(707, 'ANDERSON RAMON SOUSA BORGES', 327),
(708, 'JOSE JOSIMAR GUILHERME DA SILVA', 327),
(709, 'IVON KELLYSON FERREIRA DE FREITAS', 328),
(710, 'CONFRONTO POLICIAL', 329),
(711, 'CONFRONTO POLICIAL', 330),
(712, 'MATEUS TEIXEIRA SILVA', 331),
(713, 'VITOR HUGO CAMPOS LIMA', 332),
(714, 'JOHNY LIMA MARQUES DE OLIVEIRA', 333),
(715, 'PAULO VIEIRA XAVIER', 334),
(716, 'ESDRAS QUEIROS MENDES', 335),
(717, 'LAZARO HENRIQUE MIRANDA DE OLIVEIRA', 336),
(718, 'LAERCIO BARBOSA PEREIRA', 337),
(719, 'EVERTON SILVA DO NASCIMENTO', 338),
(720, 'WALKLEY GARCIA GOMES', 339),
(721, 'DANILO PEREIRA DE FREITAS', 340),
(722, 'PAULO CESAR DE MORAES', 341),
(723, 'DIVINO DOS REIS VITOR FRANCO', 342),
(724, 'JOAO EMANUEL MOREIRA DINIZ', 343),
(725, 'JOAO GABRIEL BARROS MEIRELES', 344),
(726, 'VANESSA DE SOUZA NASCIMENTO', 345),
(727, 'JONATHAN ROSA PEREIRA', 346),
(728, 'ELISMAR GOUVEIA FERNANDES (MENOR)', 347),
(729, 'PEDRO GABRIEL MACHADO DA SILVA (MENOR)', 347),
(730, 'SYNARA RODRIGUES ALVES', 347),
(731, 'NATHAN DUTRA MORAES', 348),
(732, 'PAULO VITOR SANTANA SOUSA (PORTE ILEGAL DE ARMA DE  DE FOGO DE USO PERMITIDO) ALTURES PAULO DE MELO (POSSE IRREGULAR DE ARMA DE FOGO)', 349),
(733, 'MANOEL EURIPEDES DA SILVA FILHO (CRIME INTELECUAL DE DISPARO DE ARMA DE FOGO)', 349),
(734, 'MAYKON DOUGLAS SANTANA SOUSA (DISPARO DE ARMA DE FOGO)', 349),
(735, 'PAULO VITOR SANTANA SOUSA (PORTE ILEGAL DE ARMA DE  DE FOGO DE USO PERMITIDO)', 349),
(736, 'KAMILLA MACEDO DO NASCIMENTO (MENOR)', 350),
(737, 'MATEUS EDUARDO SOBRINHO ANDRADE', 350),
(738, 'ELCIO JESUS VIEIRA', 351),
(739, 'VALDECI BARBOSA FILHO', 351),
(740, 'GLADEMIR MIRANDA DE SOUZA', 352),
(741, 'WILTON PROTO ARANTES', 353),
(742, 'AGNALDO FERREIRA DE JESUS', 354),
(743, 'ORNILO PALMEIRA JORGE', 355),
(744, 'EPITACIO LEMES DE FREITAS', 356),
(745, 'ALEXANDRE ROMEIRO', 357),
(746, 'EBER DA SILVEIRA SOUSA', 358),
(747, 'LEONARDO PATRICK DE JESUS', 359),
(748, 'JEAN CARVALHO OLIVEIRA(VULGO BOLACHA)', 360),
(749, 'JOSE AUGUSTO PEREIRA CAMPOS(VULGO ZE BIBIU)', 360),
(750, 'KAIO RODRIGUES DE LIMA', 361),
(751, 'RENATO DE SOUZA', 361),
(752, 'ROGERIO OLIVEIRA MUNIZ', 361),
(753, 'ROGERIO TELES BORGES', 361),
(754, 'FRANCISCO  DAS CHAGAS DE ASSIS', 362),
(755, 'JUCINALDO FREIRE DA SILVA', 363),
(756, 'DIVINO LUIZ DA COSTA', 364),
(757, 'JUNIO BORGES LENZA', 365),
(758, 'MAURICIO FERREIRA BARROS MAXIMO', 366),
(759, 'GABRIEL RODRIGUES DA SILVA', 367),
(760, 'CLODOALDO RAMOS SOARES', 368),
(761, 'LEANDRO BORGES SILVA', 369),
(762, 'EDUARDO DE SOUZA LIMA', 370),
(763, 'FRANCISCO ALEXANDRE TEIXEIRA', 371),
(764, 'MARCOS ALMEIDA BARROS', 372),
(765, 'PRESLIE RAMOS TAVARES', 373),
(766, 'LEANDRO BORGES SILVA', 374),
(767, 'RENATO DOS SANTOS NEVES', 375),
(768, 'DANILO PEREIRA DE FREITAS', 376),
(769, 'FÁBIO MENDES DE OLIVEIRA', 377),
(770, 'VINICIUS DE OLIVEIRA DA SILVA', 378),
(771, 'EZEQUIEL FARIA SILVA', 379),
(772, 'RAFAEL MARTINS MENDONÇA', 380),
(773, 'JOAO PEDRO ALVES TEIXEIRA', 381),
(774, 'ISMAEL PEREIRA DE JESUS', 382),
(775, 'MARCIANO FRANCO DA SILVA', 382),
(776, 'PAULO CESAR DA SILVA TELES', 382),
(777, 'EVERALDO HERMINIO DOS SANTOS', 383),
(778, 'GILMAR ALVES DOS SANTOS', 383),
(779, 'EVERTON SILVA DO NASCIMENTO', 384),
(780, 'LUANA(MENOR)', 385),
(781, 'PAULO VITOR XAVIER', 385),
(782, 'MILTON GONDIM DA SILVA', 386),
(783, 'SILVANILSON DIAS SOARES', 387),
(784, 'EDUARDO JOSÉ CABRAL', 388),
(785, 'LUCAS CAMARGO RODRIGUES ROCHA', 388),
(786, 'WESLEY FERREIRA DA SILVA', 389),
(787, 'IGNORADO', 390),
(788, 'IGNORADO', 391),
(789, 'IGNORADO', 392),
(790, 'ANTONIO J. BARBAOSA DE LIMA', 393),
(791, 'EDVAN CICERO DOS SANTOS', 393),
(792, 'HELLRY JHEYSON DE SOUZA SILVA', 393),
(793, 'IGNORADO', 394),
(794, 'IGNORADO', 395),
(795, 'RENE GOMES DOS SANTOS', 396),
(796, 'IGNORADO', 397),
(797, 'IGNORADO', 398),
(798, 'JOAO VICTOR LOPES PRECHEDES FERREIRA', 399),
(799, 'CARLOS EDUARDO VELASCO URIEPERO', 400),
(800, 'PEDRO HENRIQUE CABRAL COELHO', 401),
(801, 'TIAGO CABRAL COELHO', 401),
(802, 'IGNORADO', 402),
(803, 'IGNORADO', 403),
(804, 'IGNORADO', 404),
(805, 'IGNORADO', 405),
(806, 'IGNORADO', 406),
(807, 'IGNORADO', 407),
(808, 'IGNORADO', 408),
(809, 'IGNORADO', 409),
(810, 'IGNORADO', 410),
(811, 'CONFRONTO POLICIAL', 411),
(812, 'IGNORADO', 412),
(813, 'IGNORADO', 413),
(814, 'IGNORADO', 414),
(815, 'IGNORADO', 415),
(816, 'IGNORADO', 416),
(817, 'SEBASTIÃO INÁCIO DE JESUS', 417),
(818, 'FABIO SIPRIANO DA SILVA', 418),
(819, 'IDAEL SILVA DE SOUSA', 418),
(820, 'IGNORADO', 419),
(821, 'SEBASTIAO CARLOS NUNES MENO', 420),
(822, 'DIVINA PERES CRUVINEL', 421),
(823, 'IOLANDA PERES DE SOUSA', 421),
(824, 'JOAO KENEDI PEREIRA LIMA', 421),
(825, 'MARCOS ANTONIO PERES CRUVINEL', 421),
(826, 'VANILSA LOURENÇA LINA DE QUEIROZ CRUVINEL', 421),
(827, 'ALESSANDRO FERREIRA DA SILVA', 422),
(828, 'JENIFER ASSIS DE MELO', 422),
(829, 'FRANCINELDO DIAMANTINA GOMES', 423),
(830, 'DHIEK ARAÚJO DA SILVA', 424),
(831, 'WELLINGTON ARAÚJO DE SOUSA', 424),
(832, 'IGNORADO', 425),
(833, 'DIEGO DA SILVA', 426),
(834, 'TONIEL MATHEUS DE LIMA ARAUJO', 426),
(835, 'IGNORADO', 427),
(836, 'Jorsimar Dias dos Reis', 293),
(837, 'CONFRONTO POLICIAL', 428),
(839, 'TESTE', 430),
(840, 'JOHNNY MAIKON SOUZA ROSA', 431),
(841, 'IGNORADO', 432),
(842, 'IGNORADO', 433),
(843, 'IGNORADO', 434),
(844, 'IGNORADO', 435),
(845, 'IGNORADO', 436),
(846, 'IGNORADO', 437),
(847, 'BRENDON KLEYZER SILVA BATISTA', 438),
(848, 'FRANCISCO DE ASSIS ALVES JUNIOR', 438),
(849, 'IGNORADO', 439),
(850, 'IGNORADO', 440),
(851, 'MARCIO FERREIRA DE SOUSA', 441),
(852, 'ANTONIO DA SILVA ROCHA', 442),
(853, 'CARLOS MANOEL SILVA OLIVEIRA', 443);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ItensSolicitacaoCautelar`
--

CREATE TABLE `ItensSolicitacaoCautelar` (
  `ID` int NOT NULL,
  `SolicitacaoCautelarID` int NOT NULL,
  `TipoCautelarID` int NOT NULL,
  `QuantidadeSolicitada` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ItensSolicitacaoCautelar`
--

INSERT INTO `ItensSolicitacaoCautelar` (`ID`, `SolicitacaoCautelarID`, `TipoCautelarID`, `QuantidadeSolicitada`) VALUES
(37, 17, 3, 1),
(40, 20, 6, 1),
(42, 22, 1, 1),
(43, 22, 3, 1),
(46, 16, 1, 2),
(47, 16, 3, 1),
(48, 23, 1, 3),
(49, 23, 3, 1),
(51, 24, 5, 1),
(52, 25, 1, 4),
(53, 26, 4, 1),
(54, 26, 6, 1),
(56, 28, 6, 1),
(57, 27, 1, 4),
(59, 30, 1, 1),
(60, 29, 3, 1),
(61, 31, 3, 1),
(62, 31, 1, 1),
(63, 32, 3, 1),
(64, 33, 3, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `LocaisArmazenagem`
--

CREATE TABLE `LocaisArmazenagem` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `LocaisArmazenagem`
--

INSERT INTO `LocaisArmazenagem` (`ID`, `Nome`) VALUES
(1, 'Armário Fernando'),
(2, 'Armário Rafaela'),
(3, 'Pátio da 8ª DRP'),
(4, 'Outros'),
(5, 'Encaminhado para perícia'),
(6, 'Depósito Judicial'),
(7, 'Não se aplica');

-- --------------------------------------------------------

--
-- Estrutura para tabela `MeiosEmpregados`
--

CREATE TABLE `MeiosEmpregados` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `MeiosEmpregados`
--

INSERT INTO `MeiosEmpregados` (`ID`, `Nome`) VALUES
(10, 'Arma de Fogo'),
(11, 'Arma Branca'),
(12, 'Enforcamento'),
(13, 'Esganadura'),
(14, 'Estrangulamento'),
(15, 'Objeto Contundente'),
(16, 'Incêndio'),
(20, 'Não identificado'),
(21, 'Veículo'),
(22, 'Veneno'),
(23, 'Fogo'),
(24, 'Ingestão de Medicamentos'),
(25, 'TORTURA'),
(26, 'Homicidio Tortura'),
(27, 'Homicidio e Tortura'),
(28, 'Espancamento'),
(29, 'Agressões Físicas'),
(30, 'OBJETO CORTOCONTUNDENTE');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Movimentacoes`
--

CREATE TABLE `Movimentacoes` (
  `ID` int NOT NULL,
  `TipoID` int NOT NULL,
  `Assunto` varchar(255) NOT NULL,
  `Situacao` enum('Em andamento','Prazo Vencido','Finalizado') NOT NULL,
  `Detalhes` text,
  `DataCriacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `DataVencimento` datetime NOT NULL,
  `DataConclusao` date DEFAULT NULL,
  `ProcedimentoID` int NOT NULL,
  `UsuarioID` int NOT NULL,
  `ResponsavelID` int NOT NULL,
  `DataRequisicao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Movimentacoes`
--

INSERT INTO `Movimentacoes` (`ID`, `TipoID`, `Assunto`, `Situacao`, `Detalhes`, `DataCriacao`, `DataVencimento`, `DataConclusao`, `ProcedimentoID`, `UsuarioID`, `ResponsavelID`, `DataRequisicao`) VALUES
(73, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 14:21:17', '2024-12-13 00:00:00', '2024-12-13', 56, 2, 4, NULL),
(74, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 14:29:39', '2024-12-05 00:00:00', '2024-12-05', 57, 2, 4, NULL),
(75, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 14:33:57', '2024-11-29 00:00:00', '2024-11-29', 58, 2, 4, NULL),
(78, 6, 'Perícias nas armas apreendidas', 'Em andamento', '', '2024-12-23 16:37:49', '2025-06-30 00:00:00', NULL, 60, 2, 2, NULL),
(79, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 16:41:14', '2024-10-24 00:00:00', '2024-10-24', 61, 2, 2, NULL),
(80, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 16:42:11', '2024-10-30 00:00:00', '2024-10-30', 61, 2, 4, NULL),
(81, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:01:23', '2024-10-24 00:00:00', '2024-10-24', 62, 2, 2, NULL),
(82, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 17:03:02', '2024-10-31 00:00:00', '2024-10-31', 62, 2, 4, NULL),
(83, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:08:50', '2024-10-20 00:00:00', '2024-10-20', 63, 2, 2, NULL),
(84, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 17:12:01', '2025-03-31 00:00:00', '2025-03-31', 63, 2, 2, NULL),
(85, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 17:35:01', '2024-10-17 00:00:00', '2024-10-17', 65, 2, 2, NULL),
(86, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:38:12', '2024-10-08 00:00:00', '2024-10-08', 66, 2, 2, NULL),
(87, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 17:38:52', '2024-10-29 00:00:00', '2024-10-29', 66, 2, 2, NULL),
(88, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:43:24', '2024-10-05 00:00:00', '2024-10-05', 67, 2, 2, NULL),
(89, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 17:44:21', '2024-11-26 00:00:00', '2024-11-26', 67, 2, 2, NULL),
(90, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:47:15', '2024-10-04 00:00:00', '2024-10-04', 68, 2, 2, NULL),
(91, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-23 17:53:44', '2024-10-03 00:00:00', '2024-10-03', 69, 2, 2, NULL),
(92, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 20:02:18', '2024-12-17 00:00:00', '2024-12-17', 70, 2, 2, NULL),
(93, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-23 20:09:21', '2024-10-03 00:00:00', '2024-10-03', 71, 2, 4, NULL),
(94, 4, 'Relatório de Imagens das Câmeras de Segurança', 'Finalizado', '', '2024-12-24 09:05:09', '2025-01-31 00:00:00', '2025-01-27', 73, 2, 2, NULL),
(95, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:08:43', '2024-11-14 00:00:00', '2024-11-14', 74, 2, 4, NULL),
(96, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:11:51', '2024-09-10 00:00:00', '2024-09-10', 75, 2, 4, NULL),
(97, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 09:14:01', '2024-08-19 00:00:00', '2024-08-19', 76, 2, 2, NULL),
(98, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:14:41', '2024-08-28 00:00:00', '2024-08-28', 76, 2, 4, NULL),
(99, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:18:11', '2024-08-28 00:00:00', '2024-08-28', 77, 2, 4, NULL),
(100, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:20:56', '2024-09-13 00:00:00', '2024-09-13', 78, 2, 2, NULL),
(101, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 09:26:47', '2024-11-18 00:00:00', '2024-11-18', 79, 2, 4, NULL),
(102, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 10:10:36', '2024-07-29 00:00:00', '2024-07-29', 80, 2, 2, NULL),
(103, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:11:24', '2024-09-23 00:00:00', '2024-09-23', 80, 2, 2, NULL),
(104, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:21:56', '2024-10-16 00:00:00', '2024-10-16', 83, 2, 2, NULL),
(105, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 10:27:00', '2024-06-11 00:00:00', '2024-06-11', 85, 2, 2, NULL),
(106, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:27:38', '2024-07-09 00:00:00', '2024-07-09', 85, 2, 2, NULL),
(107, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:31:51', '2024-06-19 00:00:00', '2024-06-19', 86, 2, 2, NULL),
(108, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:35:24', '2024-06-21 00:00:00', '2024-06-21', 87, 2, 4, NULL),
(109, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:37:45', '2024-06-21 00:00:00', '2024-06-21', 88, 2, 4, NULL),
(110, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:43:47', '2024-07-12 00:00:00', '2024-07-12', 89, 2, 2, NULL),
(111, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 10:52:19', '2024-04-27 00:00:00', '2024-04-27', 90, 2, 2, NULL),
(112, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:52:59', '2024-06-14 00:00:00', '2024-06-14', 90, 2, 2, NULL),
(113, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 10:55:28', '2024-05-06 00:00:00', '2024-05-06', 91, 2, 4, NULL),
(114, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 11:01:17', '2024-04-26 00:00:00', '2024-04-26', 92, 2, 2, NULL),
(115, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 11:01:46', '2024-05-03 00:00:00', '2024-05-03', 92, 4, 4, NULL),
(116, 1, 'Cota ministerial', 'Finalizado', '- oitiva da testemunha DIEGO ALVES JUNIOR\r\n- JUNTADA DE EXAME NECROSCÓPICO E PAPILONECROSCÓPICO\r\n- JUNTADA DE LAUDO DE LOCAL E CADAVÉRICO.', '2024-12-24 11:02:51', '2025-01-31 00:00:00', '2025-05-22', 92, 4, 4, '2024-05-08'),
(128, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 16:36:53', '2024-04-30 00:00:00', '2024-04-30', 93, 2, 2, NULL),
(129, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 16:41:43', '2024-04-30 00:00:00', '2024-04-30', 94, 2, 4, NULL),
(130, 1, 'Possui Cota Ministerial - Verificar e ajustar esse registro', 'Em andamento', '', '2024-12-24 16:42:58', '2025-02-24 00:00:00', NULL, 94, 2, 4, NULL),
(131, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 16:52:23', '2024-06-06 00:00:00', '2024-06-06', 95, 2, 4, NULL),
(132, 1, 'Possui Cota Ministerial - Verificar e ajustar esse registro', 'Em andamento', '', '2024-12-24 16:53:23', '2025-02-24 00:00:00', NULL, 95, 2, 4, NULL),
(133, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 16:57:38', '2024-04-22 00:00:00', '2024-04-22', 96, 2, 2, NULL),
(134, 1, 'Solicita Imagens das câmeras de monitoramento', 'Finalizado', '', '2024-12-24 17:03:40', '2024-08-26 00:00:00', '2024-07-02', 96, 2, 2, '2024-07-01'),
(138, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 17:14:12', '2024-03-04 00:00:00', '2024-03-04', 97, 2, 2, NULL),
(139, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 17:15:24', '2024-05-14 00:00:00', '2024-05-14', 97, 2, 2, NULL),
(141, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 17:44:31', '2024-04-09 00:00:00', '2024-04-09', 98, 2, 2, NULL),
(142, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 17:45:21', '2024-04-17 00:00:00', '2024-04-17', 98, 2, 2, NULL),
(143, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 17:56:18', '2024-06-21 00:00:00', '2024-06-21', 99, 2, 4, NULL),
(144, 1, 'Possui Cota Ministerial - Verificar e ajustar esse registro', 'Em andamento', '', '2024-12-24 17:56:53', '2025-02-24 00:00:00', NULL, 99, 2, 4, NULL),
(145, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 18:00:17', '2024-03-01 00:00:00', '2024-03-01', 100, 2, 2, NULL),
(146, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:00:56', '2024-04-30 00:00:00', '2024-04-30', 100, 2, 4, NULL),
(147, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 18:03:24', '2024-03-09 00:00:00', '2024-03-09', 101, 2, 2, NULL),
(148, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:04:10', '2024-03-18 00:00:00', '2024-03-18', 101, 2, 2, NULL),
(149, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:09:54', '2024-04-30 00:00:00', '2024-04-30', 102, 2, 4, NULL),
(150, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:14:44', '2024-05-15 00:00:00', '2024-05-15', 103, 2, 4, NULL),
(151, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:18:05', '2024-04-30 00:00:00', '2024-04-30', 104, 2, 2, NULL),
(152, 1, 'Solicita realização de exame de lesão corporal complementar na vítima', 'Finalizado', 'Vítima não foi localizada para realização do exame', '2024-12-24 18:21:19', '2025-01-31 00:00:00', '2025-01-10', 104, 2, 2, '2024-08-09'),
(153, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 18:25:01', '2024-02-21 00:00:00', '2024-02-21', 105, 2, 2, NULL),
(154, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:26:02', '2024-05-14 00:00:00', '2024-05-14', 105, 2, 4, NULL),
(155, 1, 'Possui Cota Ministerial - Verificar e ajustar esse registro', 'Finalizado', 'PROCESSO ARQUIVADO', '2024-12-24 18:26:52', '2025-02-24 00:00:00', '2025-01-30', 105, 4, 4, '2025-01-24'),
(156, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:31:29', '2024-03-04 00:00:00', '2024-03-04', 106, 2, 4, NULL),
(157, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 18:34:44', '2024-02-28 00:00:00', '2024-02-28', 107, 2, 2, NULL),
(160, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 22:33:38', '2024-02-01 00:00:00', '2024-02-01', 109, 2, 2, NULL),
(161, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 22:34:35', '2024-03-06 00:00:00', '2024-03-06', 109, 2, 2, NULL),
(162, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-24 22:38:17', '2024-01-27 00:00:00', '2024-01-27', 110, 2, 2, NULL),
(163, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 22:39:12', '2024-02-29 00:00:00', '2024-02-29', 110, 2, 4, NULL),
(164, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 22:52:54', '2024-03-13 00:00:00', '2024-03-13', 111, 2, 4, NULL),
(165, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 22:55:56', '2024-01-30 00:00:00', '2024-01-30', 112, 2, 2, NULL),
(166, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-24 22:59:13', '2024-02-26 00:00:00', '2024-02-26', 113, 2, 2, NULL),
(167, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-26 08:45:52', '2024-12-25 00:00:00', '2024-12-25', 114, 2, 2, NULL),
(168, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-26 09:26:44', '2024-12-25 00:00:00', '2024-12-25', 115, 2, 2, NULL),
(172, 6, 'Solicitado exame de lesão corporal do autor', 'Finalizado', '', '2024-12-27 15:29:55', '2025-01-17 00:00:00', '2025-01-15', 118, 2, 2, NULL),
(173, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-30 15:02:27', '2024-12-28 00:00:00', '2024-12-28', 119, 2, 2, NULL),
(174, 5, 'Remessa de IP', 'Finalizado', '', '2024-12-30 16:33:36', '2024-12-10 00:00:00', '2024-12-10', 69, 2, 2, NULL),
(175, 3, 'Oitiva Ana Júlia', 'Finalizado', '', '2024-12-31 09:11:09', '2024-12-30 00:00:00', '2024-12-30', 119, 2, 2, NULL),
(176, 6, 'Aguardando laudo de escuta especializada do menor', 'Finalizado', '', '2024-12-31 09:52:23', '2025-01-31 00:00:00', '2025-01-09', 120, 2, 2, NULL),
(177, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2024-12-31 11:45:49', '2024-12-18 00:00:00', '2024-12-18', 121, 2, 2, NULL),
(178, 3, 'Responder ofício do exército', 'Finalizado', '', '2025-01-02 18:28:00', '2025-01-03 00:00:00', '2025-01-03', 115, 2, 2, NULL),
(179, 3, 'Pedido de dilação de prazo para conclusão do IP', 'Em andamento', '', '2025-01-03 14:34:02', '2025-01-31 00:00:00', NULL, 123, 2, 2, NULL),
(180, 9, 'Encaminha objetos para o depósito judicial', 'Finalizado', '', '2025-01-03 16:58:29', '2025-01-03 00:00:00', '2025-01-03', 127, 2, 2, NULL),
(181, 5, 'Remessa de IP', 'Finalizado', '', '2025-01-03 17:01:37', '2024-05-29 00:00:00', '2024-05-29', 128, 2, 2, NULL),
(182, 5, 'Remessa de IP', 'Finalizado', '', '2025-01-03 17:02:44', '2024-06-05 00:00:00', '2024-06-05', 127, 2, 2, NULL),
(183, 9, 'Solicita informações de dados cadastrais para provedor de internet', 'Finalizado', 'Rede Conecta Telecom', '2025-01-04 11:05:35', '2025-02-07 00:00:00', '2025-02-11', 123, 2, 2, NULL),
(184, 6, 'Solicitação de Perícia na Faca Apreendida', 'Finalizado', 'Perícia já está concluída, necessário coletar a faca no núcleo para liberação do laudo.', '2025-01-04 14:19:11', '2025-01-06 00:00:00', '2025-01-07', 118, 2, 2, NULL),
(185, 5, 'Remessa de IP', 'Finalizado', '', '2025-01-07 09:00:07', '2025-01-09 00:00:00', '2025-01-09', 129, 2, 2, NULL),
(186, 9, 'Solicita prontuário médico', 'Finalizado', '', '2025-01-07 09:17:52', '2025-01-17 00:00:00', '2025-01-09', 129, 2, 2, NULL),
(188, 1, 'Cota ministerial', 'Em andamento', 'Oitiva de testemunhas', '2025-01-08 16:41:25', '2024-12-18 00:00:00', NULL, 131, 4, 8, '2024-12-13'),
(189, 5, 'Remessa de IP', 'Finalizado', '', '2025-01-10 11:26:22', '2022-09-27 00:00:00', '2022-09-27', 133, 2, 2, NULL),
(190, 1, 'Cota Ministerial', 'Finalizado', '', '2025-01-10 11:55:19', '2025-01-10 00:00:00', '2025-01-10', 133, 2, 2, '2022-11-03'),
(191, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-01-14 10:36:41', '2025-01-11 00:00:00', '2025-01-11', 134, 4, 4, NULL),
(192, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-01-15 10:38:22', '2025-01-13 00:00:00', '2025-01-13', 136, 4, 4, NULL),
(193, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-01-15 16:01:49', '2025-01-14 00:00:00', '2025-01-14', 137, 4, 4, NULL),
(194, 5, 'Remessa de IP', 'Em andamento', '', '2025-01-15 17:44:41', '2025-01-23 00:00:00', NULL, 136, 4, 4, NULL),
(195, 6, 'Solicitar conclusão do Laudo Cadavérico', 'Finalizado', '', '2025-01-16 18:21:47', '2025-01-31 00:00:00', '2025-01-27', 119, 2, 2, NULL),
(196, 6, 'Encaminhar telefones Ferrari para extração', 'Finalizado', '', '2025-01-18 18:25:57', '2025-01-22 00:00:00', '2025-01-22', 69, 2, 2, NULL),
(197, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-01-21 10:22:15', '2025-01-19 00:00:00', '2025-01-19', 138, 2, 2, NULL),
(198, 3, 'Oitiva Otávio (Relacionado RAI)', 'Finalizado', '', '2025-01-23 09:57:43', '2025-02-28 00:00:00', '2025-05-29', 138, 2, 2, NULL),
(199, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-01-23 10:59:08', '2025-01-17 00:00:00', '2025-01-17', 140, 4, 4, NULL),
(200, 5, 'Remessa de IP', 'Finalizado', 'encaminhado ao pj com autoria definida', '2025-01-23 13:32:33', '2025-01-23 00:00:00', '2025-01-23', 136, 4, 4, NULL),
(201, 1, 'Solicita localização e oitiva de testemunha', 'Finalizado', '', '2025-01-27 11:21:02', '2025-02-15 00:00:00', '2025-01-29', 133, 2, 2, '2025-01-15'),
(202, 1, 'Cota ministerial', 'Finalizado', 'OITIVA DE VIZINHOS DOS ENVOLVIDOS, JUNTADA DE IMAGENS, JUNTADA DE LAUDO.', '2025-01-28 15:23:18', '2025-02-28 00:00:00', NULL, 136, 4, 4, '2025-01-28'),
(203, 1, 'Cota ministerial', 'Em andamento', 'OITIVA DA VITIMA, LAUDO DE LESÃO DA VITIMA, INTERROGATORIO DOS AUTORES', '2025-01-28 15:30:55', '2025-06-10 00:00:00', NULL, 129, 2, 2, '2025-01-17'),
(204, 9, 'Solicita prontuário médico', 'Finalizado', '', '2025-01-29 10:50:12', '2025-02-12 00:00:00', '2025-01-30', 129, 2, 2, NULL),
(205, 1, 'Cota ministerial', 'Em andamento', '', '2025-01-30 09:24:01', '2024-12-15 00:00:00', NULL, 70, 4, 4, '2024-12-05'),
(206, 6, 'Aguardando conclusão perícia de local de crime', 'Em andamento', '', '2025-01-30 10:36:23', '2025-06-10 00:00:00', NULL, 115, 2, 2, NULL),
(207, 1, 'Cota ministerial', 'Em andamento', 'LAUDOS DE LESÃO CORPORAL DAS VÍTIMAS', '2025-01-30 15:56:49', '2025-06-10 00:00:00', NULL, 81, 2, 2, '2024-11-17'),
(208, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-02-05 10:28:26', '2025-01-29 00:00:00', '2025-01-29', 141, 4, 4, NULL),
(209, 2, 'Prisão Erich Marques Sousa', 'Finalizado', '', '2025-02-06 11:26:55', '2025-03-03 00:00:00', '2025-02-19', 55, 2, 2, NULL),
(210, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-06 12:01:58', '2025-02-06 00:00:00', '2025-02-06', 115, 2, 2, NULL),
(211, 4, 'Localização e Intimação de Autores', 'Em andamento', 'A localização, qualificação e interrogatório dos supostos autores Zé Emanuel, Gabriel e \"Pedrinho\", os quais foram mencionados no evento n. 1, pg. 21', '2025-02-06 14:23:42', '2025-06-20 00:00:00', NULL, 129, 2, 2, NULL),
(212, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-02-11 09:43:51', '2025-02-09 00:00:00', '2025-02-09', 143, 4, 4, NULL),
(213, 9, 'Solicitação de Prontuário Médico para o HERSO', 'Finalizado', '', '2025-02-11 16:14:24', '2025-02-28 00:00:00', '2025-02-18', 115, 2, 2, NULL),
(214, 9, 'Solicitação de Prontuário Médico para o HMU', 'Finalizado', '', '2025-02-11 16:23:02', '2025-02-28 00:00:00', '2025-02-18', 115, 2, 2, NULL),
(215, 6, 'Laudo de Local de Homicídio', 'Finalizado', '', '2025-02-12 08:29:26', '2025-02-28 00:00:00', '2025-02-26', 119, 2, 2, NULL),
(216, 9, 'Solicitação de Laudo de Lesão Corporal Indireto', 'Em andamento', '', '2025-02-12 08:36:30', '2025-06-10 00:00:00', NULL, 81, 2, 2, NULL),
(217, 3, 'Concluso para Relatório Final', 'Finalizado', '', '2025-02-14 09:15:00', '2025-02-28 00:00:00', '2025-03-17', 114, 2, 2, NULL),
(218, 9, 'Solicitação de Laudo de Lesão Corporal Indireto', 'Em andamento', '', '2025-02-14 09:26:50', '2025-06-10 00:00:00', NULL, 129, 2, 2, NULL),
(219, 4, 'Relatório de Análise de Telefone Celular', 'Finalizado', '', '2025-02-14 09:40:03', '2025-02-21 00:00:00', '2025-05-29', 138, 2, 2, NULL),
(220, 1, 'Solicita informações sobre conclusão de IP', 'Finalizado', '', '2025-02-14 10:29:49', '2025-02-18 00:00:00', '2025-02-14', 55, 2, 2, '2025-02-13'),
(221, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-14 10:32:38', '2025-02-15 00:00:00', '2025-02-14', 142, 4, 4, NULL),
(222, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-14 10:33:55', '2025-02-15 00:00:00', '2025-02-14', 68, 4, 4, NULL),
(223, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-14 10:52:40', '2024-11-11 00:00:00', '2025-02-14', 64, 4, 4, NULL),
(224, 1, 'Cota ministerial', 'Finalizado', 'ANALISE DOS TELEFONES APREENDIDOS E INTERROGATÓRIO COMPLEMENTAR EDILSON - CPP', '2025-02-14 15:48:38', '2025-03-07 00:00:00', '2025-02-27', 136, 4, 4, '2025-02-12'),
(225, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-02-17 11:32:44', '2025-02-12 00:00:00', '2025-02-12', 145, 4, 4, NULL),
(226, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-02-17 11:44:03', '2025-02-13 00:00:00', '2025-02-13', 146, 4, 4, NULL),
(227, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-18 08:54:17', '2021-12-14 00:00:00', '2021-12-14', 147, 2, 2, NULL),
(228, 1, 'Requisição MP', 'Finalizado', '1. Qualificação e oitiva da equipe do Corpo de Bombeiros que socorreu a vítima;\r\n2. Qualificação e oitiva dos médicos que prestaram atendimento na UPA;\r\n3. Oitiva de VALDEMAR DOMINGO SILVA, mencionado no Registro de Atendimento Integrado;\r\n4. Oitiva dos vizinhos da vítima;\r\n5. Localização, qualificação e oitiva de RAYANE e FLÁVIO, mencionados na página 68 dos autos.', '2025-02-18 08:56:07', '2025-05-01 00:00:00', '2025-05-22', 147, 4, 2, '2022-12-12'),
(229, 9, 'Solicitação de Prontuário Médico para o HUGO', 'Finalizado', '', '2025-02-18 17:20:46', '2025-03-04 00:00:00', '2025-05-30', 115, 2, 2, NULL),
(230, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-02-21 09:16:27', '2025-02-19 00:00:00', '2025-02-19', 148, 4, 4, NULL),
(232, 5, 'Remessa de IP', 'Finalizado', '', '2025-02-28 15:09:41', '2025-03-01 00:00:00', '2025-02-28', 148, 4, 4, NULL),
(233, 5, 'Remessa de IP', 'Finalizado', 'ENCAMINHADO AO PJ EM 10/03/2025.', '2025-03-10 10:37:56', '2025-03-10 00:00:00', '2025-03-10', 150, 4, 4, NULL),
(234, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-03-12 11:14:37', '2025-03-11 00:00:00', '2025-03-11', 154, 2, 2, NULL),
(235, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-03-12 11:40:40', '2025-03-08 00:00:00', '2025-03-08', 155, 4, 4, NULL),
(236, 6, 'Aguardando Laudo Cadavérico', 'Finalizado', '', '2025-03-14 10:57:03', '2025-03-31 00:00:00', '2025-04-01', 154, 2, 2, NULL),
(237, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-03-17 09:35:15', '2024-11-13 00:00:00', '2024-11-13', 156, 2, 2, NULL),
(238, 5, 'Remessa de IP', 'Finalizado', '', '2025-03-17 10:14:05', '2025-03-17 00:00:00', '2025-03-17', 114, 2, 2, NULL),
(239, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-03-17 16:55:31', '2025-03-12 00:00:00', '2025-03-12', 161, 4, 4, NULL),
(241, 9, 'Identificação e intimação do médico que realizou o atendimento', 'Finalizado', '202400007005393', '2025-03-18 10:29:38', '2025-03-28 00:00:00', '2025-05-30', 147, 2, 2, NULL),
(243, 5, 'Remessa de IP', 'Finalizado', '', '2025-03-18 11:44:44', '2025-02-14 00:00:00', '2025-03-18', 137, 4, 4, NULL),
(244, 1, 'OITIVA DA VITIMA E LAUDO DE LESÃO INDIRETO', 'Em andamento', 'OITIVA DA VITIMA E LAUDO DE LESÃO INDIRETO', '2025-03-19 11:56:46', '2025-06-10 00:00:00', NULL, 115, 2, 2, '2025-02-25'),
(245, 5, 'Remessa de IP', 'Finalizado', '', '2025-03-31 15:04:11', '2025-03-31 00:00:00', '2025-03-31', 163, 2, 2, NULL),
(247, 5, 'Remessa de IP', 'Finalizado', 'ENCAMINHADO AO PJ', '2025-04-01 11:09:31', '2025-02-21 00:00:00', '2025-04-01', 140, 4, 4, NULL),
(248, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-08 10:27:23', '2025-04-04 00:00:00', '2025-04-04', 165, 4, 4, NULL),
(249, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-08 10:30:05', '2025-04-05 00:00:00', '2025-04-05', 166, 4, 4, NULL),
(250, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-08 10:32:30', '2025-04-06 00:00:00', '2025-04-06', 167, 4, 4, NULL),
(251, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-10 15:09:11', '2025-04-09 00:00:00', '2025-04-09', 168, 4, 4, NULL),
(252, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-11 10:19:24', '2025-04-11 00:00:00', '2025-04-11', 169, 4, 4, NULL),
(253, 5, 'Remessa de IP', 'Finalizado', 'REMESSA DE IP', '2025-04-11 11:26:18', '2025-04-11 00:00:00', '2025-04-11', 165, 4, 4, NULL),
(254, 5, 'Remessa de IP', 'Finalizado', 'REMESSA DE APF CONCLUIDO COM AUTORIA', '2025-04-15 10:48:59', '2025-04-14 00:00:00', '2025-04-14', 166, 4, 4, NULL),
(255, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-15 16:03:45', '2025-04-10 00:00:00', '2025-04-10', 186, 4, 4, NULL),
(256, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-04-15 17:40:39', '2025-04-04 00:00:00', '2025-04-04', 198, 4, 4, NULL),
(257, 5, 'Remessa de IP', 'Finalizado', '', '2025-04-28 09:38:00', '2025-05-26 00:00:00', '2025-05-21', 366, 2, 2, NULL),
(258, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-05-15 10:12:01', '2025-05-13 00:00:00', '2025-05-13', 428, 2, 2, NULL),
(261, 5, 'Remessa de IP', 'Finalizado', '', '2025-05-16 09:08:48', '2025-05-16 00:00:00', '2025-05-16', 430, 4, 4, NULL),
(262, 1, 'SOLICITAÇÃO DE LAUDO DE LESÃO INDIRETO DA VÍTIMA.', 'Em andamento', 'SOLICITAÇÃO DE LAUDO DE LESÃO INDIRETO DA VÍTIMA.', '2025-05-21 15:15:58', '2025-06-09 00:00:00', NULL, 148, 4, 4, '2025-03-09'),
(263, 1, 'SOLICITAÇÃO DE LAUDO DE LESÃO INDIRETO DA VÍTIMA.', 'Em andamento', 'PRONTUÁRIO JUNTADO ÁS FOLHAS 116 E 117.\r\nLAUDO DE LESÃO INDIRETO SOLICITADO VIA SEI (202500007036357) EM 06/05/2025.', '2025-05-21 15:32:07', '2025-04-03 00:00:00', NULL, 431, 4, 4, '2025-03-19'),
(264, 1, 'SOLICITAÇÃO LAUDO DE EXAME DE DNA.', 'Em andamento', 'LAUDO DE EXAME DE DNA DE MATERIAL GENÉTICO RETIRADO DA BARRA DE AÇO ENCONTRADA NO LOCAL DO CRIME.\r\nSOLICITADO VIA SEI (202500007041144) EM 21/05/2025.', '2025-05-21 15:43:27', '2025-05-10 00:00:00', NULL, 432, 4, 4, '2025-04-10'),
(265, 1, 'SOLICITAÇÃO DE LAUDO DE LESÃO INDIRETO DA VÍTIMA.', 'Em andamento', 'SOLICITAÇÃO DE PRONTUÁRIOS MÉDICOS EM 21/05/2025\r\nHMU - OF. 992511296\r\nHERSO - OF. 992511468\r\nUNIMED - OF. 992511475', '2025-05-21 16:47:58', '2025-04-11 00:00:00', NULL, 150, 4, 4, '2025-04-01'),
(266, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 09:39:31', '2025-05-15 00:00:00', '2025-05-15', 63, 4, 2, NULL),
(267, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 09:45:12', '2025-05-15 00:00:00', '2025-05-15', 433, 4, 4, NULL),
(268, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 10:16:43', '2025-05-13 00:00:00', '2025-05-13', 140, 4, 4, NULL),
(269, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:11:57', '2025-05-07 00:00:00', '2025-05-07', 147, 4, 4, NULL),
(270, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:16:53', '2025-05-07 00:00:00', '2025-05-07', 434, 4, 4, NULL),
(271, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:18:44', '2025-04-28 00:00:00', '2025-05-22', 137, 4, 4, NULL),
(272, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:23:08', '2025-04-28 00:00:00', '2025-05-22', 435, 4, 4, NULL),
(273, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:27:01', '2025-04-28 00:00:00', '2025-05-22', 436, 4, 4, NULL),
(274, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:30:50', '2025-04-09 00:00:00', '2025-05-22', 437, 4, 4, NULL),
(275, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:47:30', '2025-04-08 00:00:00', '2025-05-22', 92, 4, 4, NULL),
(276, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 11:54:38', '2025-04-08 00:00:00', '2025-05-22', 438, 4, 4, NULL),
(277, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 17:04:37', '2025-04-09 00:00:00', '2025-05-22', 439, 4, 4, NULL),
(278, 3, 'PROCESSO ARQUIVADO', 'Finalizado', 'PROCESSO ARQUIVADO', '2025-05-22 17:08:07', '2025-03-12 00:00:00', '2025-05-22', 229, 4, 4, NULL),
(279, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-05-23 09:14:55', '2025-05-22 00:00:00', '2025-05-22', 440, 4, 4, NULL),
(280, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-05-23 09:44:54', '2025-05-22 00:00:00', '2025-05-22', 441, 4, 4, NULL),
(281, 8, 'Acompanhamento em Local de Crime', 'Finalizado', NULL, '2025-05-26 10:51:17', '2025-05-23 00:00:00', '2025-05-23', 442, 4, 4, NULL),
(282, 5, 'Remessa de IP', 'Finalizado', '', '2025-05-26 15:37:56', '2025-05-30 00:00:00', '2025-05-30', 442, 2, 2, NULL),
(283, 5, 'Remessa de IP', 'Finalizado', 'REMESSA DE IP AO PJ', '2025-05-27 15:20:52', '2025-06-04 00:00:00', '2025-05-27', 157, 4, 4, NULL),
(284, 5, 'Remessa de IP', 'Em andamento', '', '2025-05-28 16:57:43', '2025-06-26 00:00:00', NULL, 162, 2, 2, NULL),
(285, 5, 'Remessa de IP', 'Finalizado', '', '2025-05-30 11:37:39', '2025-05-30 00:00:00', '2025-05-30', 441, 4, 4, NULL),
(286, 5, 'Remessa de IP', 'Finalizado', '', '2025-05-30 11:55:41', '2025-05-30 00:00:00', '2025-05-30', 164, 2, 2, NULL),
(288, 3, 'teste', 'Finalizado', 'teste', '2025-06-02 15:21:03', '2025-06-02 00:00:00', '2025-06-02', 115, 2, 2, NULL),
(289, 3, 'Concluso para Relatório Final', 'Em andamento', '', '2025-06-02 16:42:21', '2025-06-25 00:00:00', NULL, 119, 2, 2, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `MovimentacoesObjeto`
--

CREATE TABLE `MovimentacoesObjeto` (
  `ID` int NOT NULL,
  `ObjetoID` int NOT NULL,
  `TipoMovimentacaoID` int NOT NULL,
  `DataMovimentacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Observacao` text COLLATE utf8mb4_unicode_ci,
  `UsuarioID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `MovimentacoesObjeto`
--

INSERT INTO `MovimentacoesObjeto` (`ID`, `ObjetoID`, `TipoMovimentacaoID`, `DataMovimentacao`, `Observacao`, `UsuarioID`) VALUES
(38, 69, 2, '2025-06-03 11:43:00', 'Para realização de exames periciais', 2),
(39, 69, 4, '2025-06-03 11:49:00', '', 2),
(40, 71, 5, '2025-06-03 11:51:00', '', 2),
(41, 56, 2, '2025-06-03 11:56:00', 'Foi fazer exame e já volta.', 2),
(43, 71, 3, '2025-06-03 12:07:00', 'Retorno da Perícia - Lacre anterior: asdfsdf. Novo lacre: 123456', 2),
(44, 28, 2, '2025-06-03 14:09:00', '', 2),
(45, 73, 1, '2025-06-03 14:31:18', 'Movimentação Automática', 2),
(46, 73, 5, '2025-06-03 14:32:00', 'teste', 2),
(47, 73, 8, '2025-06-03 14:32:00', 'asdf', 2),
(48, 74, 1, '2025-06-03 15:04:07', 'Entrada inicial no sistema', 2),
(49, 74, 2, '2025-06-03 18:41:00', '', 2),
(50, 74, 7, '2025-06-03 20:20:00', '', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Objetos`
--

CREATE TABLE `Objetos` (
  `ID` int NOT NULL,
  `TipoObjetoID` int NOT NULL,
  `Descricao` text,
  `Quantidade` int NOT NULL,
  `SituacaoID` int DEFAULT NULL,
  `DataApreensao` date NOT NULL,
  `LacreAtual` varchar(255) DEFAULT NULL,
  `LocalArmazenagemID` int DEFAULT NULL,
  `ProcedimentoID` int NOT NULL,
  `UsuarioID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Objetos`
--

INSERT INTO `Objetos` (`ID`, `TipoObjetoID`, `Descricao`, `Quantidade`, `SituacaoID`, `DataApreensao`, `LacreAtual`, `LocalArmazenagemID`, `ProcedimentoID`, `UsuarioID`) VALUES
(28, 7, '2 estojos deflagrados 9mm', 2, 1, '2024-12-31', '0005479', 5, 119, 2),
(30, 2, 'Porção de Maconha, apreendida com Matheus Borges', 1, 1, '2025-01-03', '538681', 1, 80, 2),
(31, 2, 'Porções de cocaína - Restante da perícia', 1, 1, '2025-01-03', '538669', 1, 63, 2),
(32, 8, '01 pacote contendo cachimbo, chave e aparentemente roupas (Pacote Lacrado)', 1, 1, '2025-01-03', '158703', 1, 122, 2),
(33, 6, '01 FACA', 1, 1, '2025-01-03', '158707', 1, 123, 2),
(34, 8, '02 balanças de precisão', 2, 1, '2025-01-03', '', 1, 124, 2),
(35, 6, '01 FACA', 1, 1, '2025-01-03', '230188', 1, 72, 2),
(36, 8, 'PACOTE COM TEOR NÃO IDENTIFICADO', 1, 1, '2022-12-01', '000216662', 1, 125, 2),
(37, 6, '01 FACA', 1, 1, '2024-08-14', '000230450', 1, 78, 2),
(38, 6, '01 faca', 1, 1, '2025-01-03', '000196056', 1, 126, 2),
(39, 1, 'Celular destruído - Fragmento.', 1, 1, '2025-01-03', '000093650', 6, 127, 2),
(42, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: 9mm, Marca Taurus, Número de Série: ABC418721, Modelo: G2C (ARMA ÓRION)', 1, 1, '2025-01-14', '0005466', 2, 55, 2),
(43, 1, 'Aparelho celular da marca Apple, modelo Iphone 12, Número de Série DX3H2LAY0DXW, IMEI 350110422725104, IMEI2 350110422634942, contendo um chip da operadora VIVO com o número (62) 99635-0666 (CELULAR SHARLINGTHON)', 1, 1, '2025-01-14', '', 1, 55, 2),
(44, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .32, Marca Taurus, Número de Série: 151318, Modelo: SEM MODELO', 1, 1, '2024-12-18', '000195571', 2, 121, 4),
(45, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: .380, Marca Taurus, Número de Série: KOE91387, Modelo: PT 58 SS', 1, 1, '2025-01-14', '000175359', 2, 137, 4),
(46, 4, 'Arma de Fogo, Tipo: Carabina, Calibre: .22LR, Marca CBC, Número de Série: EWI4971734, Modelo: SEM MODELO (ARMA SHARLINGTHON)', 1, 1, '2025-01-16', '', 7, 55, 2),
(47, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: 9mm, Marca Taurus, Número de Série: ABN318952, Modelo: PT92 (ARMA SHARLINGTHON)', 1, 1, '2025-01-16', '', 7, 55, 2),
(48, 1, '03 Telefones Celulares de Luanderson Ferreira de Oliveira', 3, 2, '2024-10-21', '', 7, 81, 2),
(49, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: 38 special, Marca Taurus, Número de Série: SUPRIMIDO, Modelo: SEM MODELO', 1, 1, '2025-01-13', '000175678', 2, 139, 2),
(50, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: 9mm, Marca Taurus, Número de Série: ABE555780, Modelo: G2C (ARMA ERICH)', 1, 1, '2025-02-02', '', 1, 55, 2),
(51, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: .380, Marca Taurus, Número de Série: KJU77612, Modelo: 838 (ARMA ERICH)', 1, 1, '2025-02-02', '', 2, 55, 2),
(52, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca Taurus, Número de Série: WD1152228, Modelo: SEM MODELO', 1, 1, '2025-01-17', '000175411', 2, 140, 4),
(53, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: 38 special, Marca Taurus, Número de Série: ABN314731, Modelo: SEM MODELO', 1, 1, '2025-01-17', '000175359', 2, 140, 4),
(54, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca Taurus, Número de Série: Não identificado, Modelo: SEM MODELO', 1, 1, '2025-03-11', '000176993', 2, 154, 2),
(55, 7, 'Elemento de Munição Extraído do Corpo da Vítima', 1, 1, '2025-04-01', '0000580143', 2, 63, 2),
(56, 6, 'Uma faca de cozinha com cabo de madeira', 1, 1, '2025-04-01', '', 5, 162, 2),
(57, 6, '1 canivete prateado', 1, 1, '2025-04-01', '000118505', 1, 164, 2),
(58, 1, '01 telefone celular, cor prata, adesivo do Stitch.', 1, 2, '2025-04-01', '0', 7, 164, 2),
(59, 1, 'Um telefone celular Samsung Cor preto', 1, 9, '2025-04-03', '0009553', 6, 63, 2),
(60, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca ROSSI, Número de Série: 624954, Modelo: SEM MODELO', 1, 1, '2025-02-07', '00178393', 2, 142, 4),
(61, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca Taurus, Número de Série: 246900, Modelo: SEM MODELO', 1, 1, '2025-02-09', '000178395', 2, 143, 4),
(62, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca Taurus, Número de Série: 224217, Modelo: SEM MODELO', 1, 1, '2025-03-01', '000178370', 2, 150, 4),
(63, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .38, Marca ROSSI, Número de Série: D777780, Modelo: SEM MODELO', 1, 1, '2024-11-13', '00202372', 2, 156, 4),
(64, 4, 'Arma de Fogo, Tipo: Espingarda, Calibre: 12, Marca CBC, Número de Série: KUL4568835, Modelo: Pump Military 3.0', 1, 1, '2024-12-27', '000345831', 2, 115, 4),
(65, 4, 'Arma de Fogo, Tipo: Rifle, Calibre: .22LR, Marca CBC, Número de Série: EUK4557563, Modelo: 7022', 1, 1, '2024-12-27', '000345831', 2, 115, 4),
(66, 4, 'Arma de Fogo, Tipo: Pistola, Calibre: 9mm, Marca Taurus, Número de Série: ABM299048, Modelo: PT 111 G2A (G2 C)', 1, 1, '2024-12-27', '000345831', 2, 115, 4),
(67, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .357 Magnum, Marca Taurus, Número de Série: ACD820865, Modelo: 608', 1, 1, '2025-04-28', '000345887', 2, 366, 4),
(68, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: .357 Magnum, Marca Taurus, Número de Série: ACK391767, Modelo: 627 TRACKER', 1, 1, '2025-04-28', '000345887', 2, 366, 4),
(69, 4, 'Arma de Fogo, Tipo: Revólver, Calibre: 38 special, Marca Taurus, Número de Série: NK146938, Modelo: SEM MODELO', 1, 1, '2025-05-15', '0001982279', 6, 428, 4),
(70, 2, 'teste', 1, 1, '2025-06-02', '1234', 7, 123, 2),
(71, 6, 'teste', 1, NULL, '2025-06-02', '123456', 7, 162, 2),
(73, 1, 'teste', 1, NULL, '2025-06-03', '', NULL, 162, 2),
(74, 6, 'teste teste', 1, NULL, '1989-06-25', '123456', NULL, 162, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Oficios`
--

CREATE TABLE `Oficios` (
  `ID` int NOT NULL,
  `NumeroOficio` varchar(255) NOT NULL,
  `Assunto` text NOT NULL,
  `Destino` varchar(255) NOT NULL,
  `SEI` varchar(255) DEFAULT NULL,
  `DataOficio` date NOT NULL,
  `ProcedimentoID` int DEFAULT NULL,
  `ResponsavelID` int DEFAULT NULL,
  `MovimentacaoID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Oficios`
--

INSERT INTO `Oficios` (`ID`, `NumeroOficio`, `Assunto`, `Destino`, `SEI`, `DataOficio`, `ProcedimentoID`, `ResponsavelID`, `MovimentacaoID`) VALUES
(13, '513/2025/DGPC', 'Encaminha objetos para o depósito judicial', 'Depósito Judicial', '202400007005393', '2025-01-03', 127, 2, 180),
(14, '554/2025/DGPC', 'Solicita informações de dados cadastrais para provedor de internet', 'PROVEDOR REDE CONECTA TELECOM', '202400007005393', '2025-01-04', 123, 2, 183),
(15, '991522294', 'Solicita prontuário médico', 'HMU', '', '2025-01-07', 129, 2, 186),
(16, '991680521', 'Solicita prontuário médico', 'Hospital Estadual de Santa Helena de Goiás - HERSO', '', '2025-01-29', 129, 2, 204),
(17, '12196/2025/DGPC', 'Solicitação de Prontuário Médico para o HERSO', 'Hospital Estadual de Santa Helena de Goiás - HERSO', '202400007005393', '2025-02-11', 115, 2, 213),
(18, '12205/2025/DGPC', 'Solicitação de Prontuário Médico para o HMU', 'Hospital Municipal Universitário de Rio Verde - HMURV', '202400007005393', '2025-02-11', 115, 2, 214),
(19, '10974/2025/DGPC', 'Solicitação de Laudo de Lesão Corporal Indireto', 'Instituto de Medicina Legal', '202500007010948', '2025-02-07', 81, 2, 216),
(20, '8043/2025/DGPC', 'Solicitação de Laudo de Lesão Corporal Indireto', 'Instituto de Medicina Legal', '202500007008213', '2025-01-30', 129, 2, 218),
(21, '14574/2025/DGPC', 'Solicitação de Prontuário Médico para o HUGO', 'HUGO', '202500007014348', '2025-02-18', 115, 2, 229),
(23, '22294/2025/DGPC', 'Identificação e intimação do médico que realizou o atendimento', 'UPA', '202400007005393', '2025-03-18', 147, 2, 241);

-- --------------------------------------------------------

--
-- Estrutura para tabela `OrigensProcedimentos`
--

CREATE TABLE `OrigensProcedimentos` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `OrigensProcedimentos`
--

INSERT INTO `OrigensProcedimentos` (`ID`, `Nome`) VALUES
(1, 'APF'),
(2, 'Despacho'),
(3, 'Portaria');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Policiais`
--

CREATE TABLE `Policiais` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cargo` enum('Delegado','Escrivão','Agente') NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `funcional` varchar(20) NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `anexo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Policiais`
--

INSERT INTO `Policiais` (`id`, `nome`, `cargo`, `cpf`, `funcional`, `telefone`, `anexo`) VALUES
(1, 'Fernando Michel de Freitas', 'Escrivão', '01892736152', '13510', '64999225006', '678b9e77b9ac7-Funcional - Fernando Michel de Freitas.pdf'),
(2, 'Matheus Valarini Taraszkiewicz', 'Agente', '10234364939', '13255', '47989012107', NULL),
(3, 'José Alves da Silva Júnior', 'Escrivão', '83652167104', '9908', '64981210875', NULL),
(4, 'Leopoldo Peixoto Rosa', 'Agente', '00889705151', '11042', '62999796442', NULL),
(5, 'Raphael José Lima Hass Gonçalves', 'Agente', '00082603111', '10891', '64992388683', NULL),
(6, 'Rafaela Dallagnol Secorun', 'Escrivão', '00000000000', '9756', '62993205072', NULL),
(7, 'Adelson Candeo Júnior', 'Delegado', '01926156960', '9866', '62984790537', '678b9fcfcd0e0-Funcional - Adelson Candeo Júnior.pdf');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Procedimentos`
--

CREATE TABLE `Procedimentos` (
  `ID` int NOT NULL,
  `SituacaoID` int NOT NULL,
  `TipoID` int NOT NULL,
  `OrigemID` int NOT NULL,
  `NumeroProcedimento` varchar(50) NOT NULL,
  `DataFato` date DEFAULT NULL,
  `DataInstauracao` date DEFAULT NULL,
  `MotivoAparente` text,
  `EscrivaoID` int NOT NULL,
  `DelegadoID` int NOT NULL,
  `DelegaciaID` int NOT NULL,
  `DataCriacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `DataAtualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MeioEmpregadoID` int DEFAULT NULL,
  `Dependente` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Informa se o crime investigado já é apurado em outro procedimento.',
  `Favorito` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Procedimentos`
--

INSERT INTO `Procedimentos` (`ID`, `SituacaoID`, `TipoID`, `OrigemID`, `NumeroProcedimento`, `DataFato`, `DataInstauracao`, `MotivoAparente`, `EscrivaoID`, `DelegadoID`, `DelegaciaID`, `DataCriacao`, `DataAtualizacao`, `MeioEmpregadoID`, `Dependente`, `Favorito`) VALUES
(55, 1, 1, 3, '2406194410', '2024-12-12', '2024-12-12', 'INQUÉRITO PARA CONTINUIDADE DAS INVESTIGAÇÕES PARA IDENTIFICAR OUTROS ENVOLVIDOS NO HOMICÍDIO.', 2, 5, 1, '2024-12-23 14:13:46', '2024-12-24 08:43:49', NULL, 1, 0),
(56, 4, 1, 1, '2406188778', '2024-12-04', '2024-12-04', 'DOIS USUÁRIOS DE DROGAS DISCUTIRAM POR CAUSA DE UM CELULAR', 4, 5, 1, '2024-12-23 14:20:40', '2024-12-23 14:21:17', NULL, 0, 0),
(57, 4, 1, 1, '2406183068', '2024-11-29', '2024-11-29', '', 4, 5, 1, '2024-12-23 14:29:02', '2024-12-23 14:29:39', NULL, 0, 0),
(58, 4, 1, 1, '2406176342', '2024-11-23', '2024-11-23', '', 4, 5, 1, '2024-12-23 14:33:12', '2024-12-23 14:33:57', NULL, 0, 0),
(59, 1, 1, 3, '2406169440', '2024-11-09', '2024-11-09', '', 4, 5, 1, '2024-12-23 14:39:25', '2024-12-30 16:35:15', NULL, 0, 0),
(60, 1, 1, 1, '2406150645', '2024-10-24', '2024-10-24', 'DURANTE AS DILIGÊNCIAS PARA LOCALIZAR O AUTOR DO HOMICÍDIO DE UM CAMINHONEIRO, FORAM ENCONTRADAS AS ARMAS NA POSSE DAS INVESTIGADAS.', 2, 5, 1, '2024-12-23 15:59:51', '2025-01-04 08:45:46', NULL, 0, 1),
(61, 4, 1, 1, '2406150652', '2024-10-24', '2024-10-24', 'APÓS UMA DISCUSSÃO NO TRÂNSITO, O AUTOR FOI ATÉ SEU VEÍCULO E PEGOU UMA ARMA DE FOGO E DISPAROU CONTRA A VÍTIMA AINDA DENTRO DO CAMINHÃO.', 4, 5, 1, '2024-12-23 16:41:14', '2024-12-23 16:42:11', NULL, 0, 0),
(62, 4, 1, 1, '2406150293', '2024-10-24', '2024-10-24', 'O AUTOR POSSUI ESQUIZOFRENIA E ALUCINOU QUE O PAI QUERIA CAPÁ-LO, POR ESSA RAZÃO ELE DECIDIU MATAR O PAI A FACADAS ENQUANTO DORMIA.', 4, 5, 1, '2024-12-23 17:01:23', '2024-12-23 17:03:02', NULL, 0, 0),
(63, 4, 1, 3, '2406147071', '2024-10-20', '2024-10-20', 'CPE FOI REALIZAR ABORDAGEM DE UM GOL BRANCO NA GO 174 E O MOTORISTA DO GOL DESEMBARCOU E DISPAROU CONTRA OS POLICIAIS MILITARES, OCORRENDO REVIDE A INJUSTA AGRESSÃO, LEVANDO O MOTORISTA DO GOL A ÓBITO.', 2, 5, 1, '2024-12-23 17:08:50', '2025-03-31 17:29:34', NULL, 0, 0),
(64, 5, 1, 3, '2406139071', '2024-10-08', '2024-10-08', 'VITIMA FOI PRESA NO DIA 07/10/2024 APÓS UM SURTO PSICÓTICO E MORREU NO DIA SEGUINTE NA CPP', 4, 5, 1, '2024-12-23 17:20:39', '2025-02-14 10:52:40', NULL, 0, 0),
(65, 4, 1, 1, '2406136894', '2024-10-09', '2024-10-09', 'VITIMA E AUTOR ERAM CUNHADOS E POSSUÍAM UM DESENTENDIMENTO HÁ MAIS DE 10 ANOS', 2, 5, 1, '2024-12-23 17:34:13', '2024-12-23 17:35:01', NULL, 0, 0),
(66, 4, 1, 3, '2406135984', '2024-10-08', '2024-10-08', 'VITIMA E AUTOR ERAM CUNHADOS E POSSUIAM UM DESENTENDIMENTO HÁ MAIS DE 10 ANOS', 4, 5, 1, '2024-12-23 17:38:12', '2024-12-23 17:38:52', NULL, 0, 0),
(67, 4, 1, 3, '2406134994', '2024-10-05', '2024-10-05', 'VTIMA BRIGOU COM TRÊS HOMENS EM UM BAR', 4, 5, 1, '2024-12-23 17:43:24', '2025-05-05 15:43:20', NULL, 0, 0),
(68, 4, 1, 1, '2406132428', '2024-10-04', '2024-10-04', 'VITIMA FOI ESFAQUEADA PELO ENTEADO QUE DEFENDIA A MÃE DE AGRESSÕES', 4, 5, 1, '2024-12-23 17:47:15', '2025-02-14 10:33:55', NULL, 0, 0),
(69, 4, 1, 1, '2406132416', '2024-10-03', '2024-10-03', 'VITIMA ERA ADVOGADO E FOI MORTO COM CINCO TIROS NA PORTA DE SEU ESCRITÓRIO', 2, 5, 1, '2024-12-23 17:53:44', '2024-12-30 16:33:37', NULL, 0, 0),
(70, 4, 1, 1, '2406118627', '2024-09-21', '2024-09-21', 'AUTOR COLIDIU SEU CARRO COM UM VEÍCULO ESTACIONADO, NO QUAL O A VÍTIMA ESTAVA ESCORADA, CAIU NO CHÃO E FOI ATROPELADA PELO VEÍCULO DO AUTOR', 4, 5, 1, '2024-12-23 20:01:24', '2024-12-23 20:02:18', NULL, 0, 0),
(71, 4, 1, 1, '2406106468', '2024-09-08', '2024-09-08', 'VÍTIMA E AUTORES ESTAVAM BEBENDO E USANDO DROGAS JUNTOS QUANDO INICIARAM UMA DISCUSSÃO POR MOTIVO BANAL', 4, 5, 1, '2024-12-23 20:07:10', '2025-05-05 15:35:04', NULL, 0, 0),
(72, 1, 1, 3, '2406101126', '2024-09-04', '2024-09-04', 'A EX MULHER DO AUTOR E ATUAL NAMORADA DA VÍTIMA CHAMOU A VÍTIMA ATÉ SUA CASA ONDE SOFREU UMA EMBOSCADA POR PARTE DO AUTOR', 2, 5, 1, '2024-12-23 20:13:14', '2024-12-23 20:13:14', NULL, 0, 0),
(73, 1, 1, 1, '240691707', '2024-08-25', '2024-08-25', 'DURANTE UMA BRIGA POR CAUSA DO VOLUME DO SOM E DAS MÚSICAS TOCADAS, A VÍTIMA DESFERIU UM GOLPE COM UM TRONCO DE ÁRVORE NA CABEÇA DO AUTOR, O QUAL REVIDOU DESFERINDO DIVERSAS FACADAS NA VÍTIMA.', 2, 5, 1, '2024-12-24 09:04:17', '2024-12-24 09:04:17', NULL, 0, 0),
(74, 4, 1, 3, '240689321', '2024-07-28', '2024-07-28', 'AUTOR ATIROU NOS CACHORROS QUE BRIGAVAM', 4, 5, 1, '2024-12-24 09:07:55', '2024-12-24 09:08:43', NULL, 0, 0),
(75, 4, 1, 1, '240686456', '2024-08-19', '2024-08-19', 'VITIMA E AUTOR DISCUTIRAM PORQUE A VÍTIMA ESTARIA MEXENDO COM A MULHER DO AUTOR', 4, 5, 1, '2024-12-24 09:11:09', '2024-12-24 09:11:51', NULL, 0, 0),
(76, 4, 1, 1, '240686331', '2024-08-19', '2024-08-19', 'VITIMA E AUTOR DISCUTIRAM POR UMA COMPRA DE DROGAS', 4, 5, 1, '2024-12-24 09:14:01', '2024-12-24 09:14:41', NULL, 0, 0),
(77, 4, 1, 1, '240685190', '2024-08-18', '2024-08-18', 'VITIMA E AUTOR TRABALHAVAM JUNTOS NA MESMA EMPRESA E RESIDIAM NO MESMO ALOJAMENTO', 4, 5, 1, '2024-12-24 09:17:39', '2024-12-24 09:18:11', NULL, 0, 0),
(78, 4, 1, 3, '240677687', '2024-08-01', '2024-08-01', 'VITIMA ENCONTRADA EM ESTADO AVANÇADO DE DECOMPOSIÇÃO EM LAGOA DO BAUZINHO', 2, 5, 1, '2024-12-24 09:20:20', '2024-12-24 09:20:56', NULL, 0, 0),
(79, 4, 1, 3, '240676006', '2024-07-28', '2024-07-28', 'A VÍTIMA AMEAÇOU SUA COMPANHEIRA E DEPOIS OS AGENTES QUE DISPARARAM EM SEU DESFAVOR', 4, 5, 1, '2024-12-24 09:25:07', '2024-12-24 09:26:47', NULL, 0, 0),
(80, 4, 1, 3, '240666660', '2024-07-29', '2024-07-29', 'CADÁVER ENCONTRADO EM LOTEAMENTO EM CONSTRUÇÃO NO BAIRRO MARANATA, COM SINAIS DE VIOLÊNCIA E LESÕES PROVOCADAS POR ARMA DE FOGO. - GRUPO FAMÍLIAS ENLUTADAS', 2, 5, 1, '2024-12-24 10:10:36', '2024-12-24 10:11:24', NULL, 0, 0),
(81, 1, 1, 1, '240664851', '2024-07-28', '2024-07-28', 'TROCA DE TIROS EM UMA FESTA', 2, 5, 1, '2024-12-24 10:14:45', '2024-12-24 10:14:45', NULL, 0, 0),
(82, 2, 1, 3, '343/2023', '2023-11-28', '2023-11-28', 'CASO QUIRINÓPOLIS', 2, 5, 1, '2024-12-24 10:19:04', '2024-12-24 11:51:59', NULL, 0, 0),
(83, 4, 1, 3, '240635811', '2024-06-22', '2024-06-22', 'A VÍTIMA AGREDIU O AUTOR COM TAPAS NO ROSTO, DEPOIS DISSO O AUTOR SAIU DO LOCAL E VOLTOU COM UMA FACA E ESFAQUEOU A VÍTIMA.', 2, 5, 1, '2024-12-24 10:21:20', '2024-12-24 10:21:56', NULL, 0, 0),
(84, 1, 1, 1, '240629388', '2024-06-18', '2024-06-18', 'DROGA ENCONTRADA NA CASA DO AUTOR DURANTE CUMPRIMENTO DE BUSCAS', 4, 5, 1, '2024-12-24 10:25:13', '2024-12-24 10:25:13', NULL, 0, 0),
(85, 4, 1, 1, '240623848', '2024-06-11', '2024-06-11', 'AUTOR TINHA UMA RIXA COM O VIZINHO DA VÍTIMA', 4, 5, 1, '2024-12-24 10:27:00', '2024-12-24 10:27:38', NULL, 0, 0),
(86, 4, 1, 1, '240622869', '2024-06-09', '2024-06-09', 'VITIMA E AUTOR TRABALHAVAM JUNTOS NA OBRA DE DO NOVO HMU, TIVERAM UMA DISCUSSÃO, A VÍTIMA ESTAVA ARMADA, O AUTOR TOMOU A ARMA DA VÍTIMA E DESFERIU VÁRIOS DISPAROS QUE ATINGIRAM DUAS MULHERES QUE ESTAVAM SENTADAS NA PORTA DE CASA.', 2, 5, 1, '2024-12-24 10:31:04', '2024-12-24 10:31:04', NULL, 0, 0),
(87, 4, 1, 3, '240615367', '2024-05-22', '2024-05-22', 'VITIMA ERA USUARIA DE DROGAS E TINHA DIVIDAS', 4, 5, 1, '2024-12-24 10:34:52', '2024-12-24 10:35:24', NULL, 0, 0),
(88, 4, 1, 1, '16/2024', '2024-05-21', '2024-05-21', 'DROGA APREENDIDA DURANTE CUMPRIMENTO DE BUSCA E APREENSÃO - DROGA ENCAMINHADA EM 03/06/2024 PARA INCINERAÇÃO', 4, 5, 1, '2024-12-24 10:37:12', '2024-12-24 10:37:45', NULL, 0, 0),
(89, 4, 1, 3, '15/2024', '2024-04-23', '2024-04-23', 'VÍTIMA TEVE UM DESENTENDIMENTO ANTIGO COM OS AUTORES POR CAUSA DE UMA DÍVIDA', 2, 5, 1, '2024-12-24 10:43:15', '2024-12-24 10:43:47', NULL, 0, 0),
(90, 4, 1, 3, '14/2024', '2024-04-27', '2024-04-27', 'VITIMA ERA DONA DE UM BAR E PROSTÍBULO', 2, 5, 1, '2024-12-24 10:52:19', '2024-12-24 10:52:59', NULL, 0, 0),
(91, 4, 1, 1, '180/2024', '2024-04-27', '2024-04-27', 'VITIMA E AUTOR SE DESENTENDERAM POR CAUSA DE UMA DIVIDIA DE ALUGUEL', 4, 5, 1, '2024-12-24 10:54:47', '2024-12-24 10:55:28', NULL, 0, 0),
(92, 4, 1, 1, '13/2024', '2024-04-26', '2024-04-26', 'VÍTIMA E AUTOR SÃO USUÁRIOS DE DROGAS E SE DESENTENDERAM NA PRAÇA', 4, 5, 1, '2024-12-24 11:01:17', '2024-12-24 11:01:46', NULL, 0, 0),
(93, 4, 1, 3, '551/2024', '2024-04-22', '2024-04-22', 'VÍTIMA E AUTORAS SE DESENTENDERAM NO FORRÓ DO BAIXINHO, OCASIÃO EM QUE A AUTORA TAMBÉM ATINGIU CULPOSAMENTE UMA MULHER QUE ESTAVA PRÓXIMA COM UMA FACADA NAS COSTAS.', 2, 5, 1, '2024-12-24 16:36:07', '2024-12-24 16:36:53', NULL, 0, 0),
(94, 4, 1, 1, '548/2024', '2024-04-21', '2024-04-21', 'VITIMA ACUSOU O AUTOR THIAGO DE ABUSAR SEXUALMENTE DE UM CRIANÇA. THIAGO E OS PAIS DA CRIANÇA AGREDIRAM A VÍTIMA', 4, 5, 1, '2024-12-24 16:40:57', '2024-12-24 16:41:43', NULL, 0, 0),
(95, 4, 1, 3, '12/2024', '2024-04-19', '2024-04-19', 'VITIMA E AUTOR TRABALHAVAM JUNTOS E DISCUTIRAM NO ONIBUS DE TRANSPORTE DA EMPRESA', 4, 5, 1, '2024-12-24 16:51:16', '2024-12-24 16:52:23', NULL, 0, 0),
(96, 4, 1, 1, '517/2024', '2024-04-13', '2024-04-13', 'VÍTIMA E AUTOR TINHAM UM DESENTENDIMENTO DEVIDO A UMA DISPUTA POR PONTOS DE VENDAS DE CHOCOLATES PELA CIDADE', 2, 5, 1, '2024-12-24 16:56:41', '2024-12-24 16:57:38', NULL, 0, 0),
(97, 4, 1, 3, '11/2024', '2024-03-04', '2024-03-04', 'BARRACÕES FREQUENTADOS POR MORADORES DE RUA PEGOU FOGO E UM CORPO FOI ENCONTRADO CARBONIZADO.', 2, 5, 1, '2024-12-24 17:14:12', '2024-12-24 22:43:50', NULL, 0, 0),
(98, 4, 1, 1, '10/2024', '2024-04-09', '2024-04-09', 'USUÁRIOS DE DROGAS QUE TINHAM UM DESENTENDIMENTO ANTERIOR', 2, 5, 1, '2024-12-24 17:44:31', '2024-12-24 17:45:21', NULL, 0, 0),
(99, 4, 1, 3, '09/2024', '2024-02-09', '2024-02-09', 'EX COMPANHEIRO ATIROU CONTRA O NAMORADO DA TESTEMUNHA', 4, 5, 1, '2024-12-24 17:55:32', '2024-12-24 17:56:18', NULL, 0, 0),
(100, 4, 1, 3, '08/2024', '2024-03-01', '2024-03-01', 'O AUTOR ESFAQUEOU O IRMÃO E DEPOIS SE MATOU', 4, 5, 1, '2024-12-24 18:00:17', '2024-12-24 18:00:56', NULL, 0, 0),
(101, 4, 1, 1, '345/2024', '2024-03-09', '2024-03-09', 'VÍTIMA DEVIA R$30,00 PARA O AUTOR, O QUE GEROU O DESENTENDIMENTO', 2, 5, 1, '2024-12-24 18:03:24', '2024-12-24 18:04:10', NULL, 0, 0),
(102, 4, 1, 3, '07/2024', '2024-03-05', '2024-03-05', 'BRIGA DE IRMÃOS, AUTOR DAS FACADAS SE SUICIDOU', 4, 5, 1, '2024-12-24 18:09:21', '2024-12-24 18:09:54', NULL, 0, 0),
(103, 4, 1, 1, '301/2024', '2024-03-03', '2024-03-03', 'ACIDENTE DE TRÂNSITO', 4, 5, 1, '2024-12-24 18:13:57', '2024-12-24 18:14:44', NULL, 0, 0),
(104, 4, 1, 3, '06/2024', '2024-02-27', '2024-02-27', 'VÍTIMA ALEGA QUE AO NEGAR DINHEIRO PARA A AUTORA, FOI ATACADA COM UMA FACA.', 2, 5, 1, '2024-12-24 18:16:43', '2024-12-24 18:18:05', NULL, 0, 0),
(105, 4, 1, 3, '05/2024', '2024-02-21', '2024-02-21', 'VÍTIMA COM HISTÓRICO DE DEPRESSÃO', 4, 5, 1, '2024-12-24 18:25:01', '2024-12-24 18:26:02', NULL, 0, 0),
(106, 4, 1, 1, '01/2024', '2024-02-25', '2024-02-25', 'AUTOR ESFAQUEOU O ATUAL NAMORADO DE SUA EX NAMORADA', 4, 5, 1, '2024-12-24 18:30:44', '2024-12-24 18:31:29', NULL, 0, 0),
(107, 4, 1, 1, '4/2024', '2024-02-19', '2024-02-19', 'AUTOR ALEGA DESENTENDIMENTO COM A PESSOA DE VULGO PINTADINHO E QUE ACREDITA QUE A VÍTIMA ESTAVA INDO MATÁ-LO A MANDO DELE', 2, 5, 1, '2024-12-24 18:33:52', '2024-12-24 18:34:44', NULL, 0, 0),
(108, 3, 1, 3, '42/2023', '2022-03-05', '2022-03-05', 'ENCAMINHADO PELO 1º DP', 2, 5, 1, '2024-12-24 22:31:44', '2024-12-24 22:31:44', NULL, 0, 0),
(109, 4, 1, 3, '03/2024', '2024-02-01', '2024-02-01', 'VITIMA USUÁRIA DE DROGA E GAROTA DE PROGRAMA COM MUITAS INIMIZADES', 2, 5, 1, '2024-12-24 22:33:38', '2024-12-24 22:34:35', NULL, 0, 0),
(110, 4, 1, 3, '02/2024', '2024-01-27', '2024-01-27', 'VITIMA E AUTOR ESTAVAM EM PROCESSO DE SEPARAÇÃO', 4, 5, 1, '2024-12-24 22:38:17', '2024-12-24 22:38:17', NULL, 0, 0),
(111, 4, 1, 1, '151/2024', '2024-01-31', '2024-01-31', 'VITIMA E AUTOR SE ENVOLVERAM EM UM ACIDENTE DE TRANSITO E SE DESENTENDERAM, SENDO QUE O AUTOR TOMOU A ARMA DA VÍTIMA (BOMBEIRO) E EFETUOU UM DISPARO CONTRA A VÍTIMA', 4, 5, 1, '2024-12-24 22:51:49', '2024-12-24 22:52:54', NULL, 0, 0),
(112, 4, 1, 1, '107/2024', '2024-01-21', '2024-01-21', 'BRIGA DE BAR, AUTOR CHEGOU APARENTEMENTE SOB EFEITO DE DROGAS E ESFAQUEOU A VITIMA', 2, 5, 1, '2024-12-24 22:55:09', '2024-12-24 22:55:56', NULL, 0, 0),
(113, 4, 1, 3, '01/2024', '2023-11-19', '2024-01-10', 'VITIMA TERIA FURTADO DROGAS DOS AUTORES', 2, 5, 1, '2024-12-24 22:58:20', '2024-12-24 22:59:13', NULL, 0, 0),
(114, 4, 1, 1, '2406204076', '2024-12-25', '2024-12-26', 'O AUTOR TRANSITAVA DE CARRO QUANDO O PNEU DO VEÍCULO FUROU EM FRENTE A UM SOBRADO ONDE A VÍTIMA SE ENCONTRAVA. A VÍTIMA AVISOU AO AUTOR SOBRE O PNEU FURADO, MAS A INTERAÇÃO RESULTOU EM UMA DISCUSSÃO ENTRE AMBOS. DURANTE A DISCUSSÃO, A VÍTIMA ARREMESSOU UMA LATA DE CERVEJA EM DIREÇÃO AO VEÍCULO DO AUTOR.\r\n\r\nNA SEQUÊNCIA, A VÍTIMA DESCEU DO SOBRADO E FOI EM DIREÇÃO AO AUTOR, INICIANDO UMA AGRESSÃO FÍSICA AO DESFERIR SOCOS CONTRA ELE. DURANTE O CONFRONTO, O AUTOR REAGIU UTILIZANDO UM CANIVETE, DESFERINDO UM GOLPE QUE CAUSOU UM FERIMENTO FATAL NA VÍTIMA, LEVANDO-A A ÓBITO.', 2, 5, 1, '2024-12-26 08:45:52', '2025-03-17 10:14:05', NULL, 0, 0),
(115, 4, 1, 1, '2406203926', '2024-12-25', '2024-12-25', 'DURANTE UMA CONFRATERNIZAÇÃO DE NATAL, JÁ NA MADRUGADA, DOIS IRMÃOS, DIEGO (O MAIS VELHO) E KAIO (O MAIS NOVO), COMEÇARAM A DISCUTIR. DIEGO QUERIA ENCERRAR A NOITE PARA DORMIR, ENQUANTO KAIO DESEJAVA CONTINUAR JOGANDO TRUCO COM SEUS AMIGOS. IRRITADO, DIEGO MANDOU OS AMIGOS DE KAIO EMBORA.\r\n\r\nKAIO NÃO ACEITOU BEM A ATITUDE DO IRMÃO E, TOMADO PELA RAIVA, PEGOU UMA ARMA DE FOGO QUE PERTENCIA AO PAI DELES. EM UM MOMENTO DE DESCONTROLE, KAIO DISPAROU CONTRA UMA PORTA, TENTANDO ATINGIR DIEGO, QUE ESTAVA DO OUTRO LADO. CONTUDO, OS TIROS ACABARAM ACERTANDO O PRÓPRIO PAI, QUE FOI FERIDO NO LUGAR DO IRMÃO.', 2, 5, 1, '2024-12-26 09:26:44', '2025-02-06 12:01:58', NULL, 0, 0),
(116, 11, 2, 2, '2410204069', '2024-12-25', '2024-12-26', 'BRIGA - SEM MAIORES INFORMAÇÕES', 2, 5, 1, '2024-12-26 17:48:52', '2024-12-27 14:28:28', NULL, 0, 0),
(117, 11, 2, 2, '2410200118', '2024-12-18', '2024-12-18', 'CONFRONTO POLICIAL', 2, 5, 1, '2024-12-27 10:29:21', '2024-12-31 11:46:31', NULL, 0, 0),
(118, 1, 1, 3, '2406205363', '2024-12-25', '2024-12-27', 'BRIGA ENTRE DOIS USUÁRIOS DE DROGAS. A VÍTIMA É PAULO AUGUSTO DA SILVA, PRESO PELA MORTE DA JÉSSICA MARTINS DE SOUSA.', 2, 5, 1, '2024-12-27 15:29:01', '2025-01-04 08:45:42', NULL, 0, 1),
(119, 1, 1, 3, '2406206695', '2024-12-28', '2024-12-30', 'UM HOMEM ENTROU NUMA CASA DE NARGUILÉ E DISPAROU CONTRA A VÍTIMA, FUGINDO DO LOCAL A PÉ E USANDO CAPACETE.', 2, 5, 1, '2024-12-30 15:02:27', '2024-12-30 15:02:27', NULL, 0, 0),
(120, 10, 2, 2, '2410206921', '2024-12-26', '2024-12-31', 'ADOLESCENTE DE 13 ANOS FUGIU DE CASA EM JATAÍ E VEIO PARA RIO VERDE DE BICICLETA, AFIRMANDO QUE SOFREU AGRESSÕES E AMEAÇAS COM UMA FACA POR PARTE DE SUA MÃE.', 2, 5, 1, '2024-12-31 09:51:20', '2025-01-27 17:51:59', NULL, 0, 0),
(121, 1, 1, 3, '2406206952', '2024-12-18', '2024-12-31', 'DURANTE TENTATIVA DE ABORDAGEM REALIZADA PELA EQUIPE DA POLÍCIA MILITAR (CPE 90) A UM INDIVÍDUO COM MANDADO DE PRISÃO EM ABERTO, ESTE, AO AVISTAR A VIATURA, EMPREENDEU FUGA, TRANSPONDO DIVERSAS PROPRIEDADES RESIDENCIAIS. DURANTE A PERSEGUIÇÃO, O INDIVÍDUO ADENTROU UMA RESIDÊNCIA LOCALIZADA NA RUA JA 08, QUADRA 08, LOTE 14, BAIRRO MAURÍCIO ARANTES, E EFETUOU DISPAROS DE ARMA DE FOGO CONTRA OS POLICIAIS.\r\n\r\nDIANTE DA INJUSTA AGRESSÃO E VISANDO CESSAR A AMEAÇA IMINENTE À INTEGRIDADE FÍSICA DOS AGENTES DE SEGURANÇA, FOI NECESSÁRIO O USO PROPORCIONAL DA FORÇA, COM REVIDE À AGRESSÃO. O INDIVÍDUO FOI ALVEJADO E IMEDIATAMENTE SOCORRIDO AO HOSPITAL MUNICIPAL UNIVERSITÁRIO DE RIO VERDE, ONDE VEIO A ÓBITO APÓS ATENDIMENTO MÉDICO.', 2, 5, 1, '2024-12-31 11:45:49', '2024-12-31 11:45:49', NULL, 0, 0),
(122, 1, 1, 3, '04/2023', '2023-01-18', '2023-01-18', 'APARENTEMENTE O AUTOR TINHA UM DESENTENDIMENTO COM A VÍTIMA, SITUAÇÃO EM QUE  O AUTOR ATRAIU A VÍTIMA PARA FORA DE SUA RESIDÊNCIA E A ALVEJOU COM DISPAROS DE ARMA DE ARMA DE FOGO', 2, 5, 1, '2025-01-03 14:19:08', '2025-01-03 14:19:08', NULL, 0, 0),
(123, 1, 1, 3, '16/2023', '2023-03-20', '2023-03-20', 'VITIMAS SÃO UM CASAL HOMOSSEXUAL QUE CONVIDARAM OS AUTORES PARA IREM ATE SUA CASA, NO LOCAL DERAM A CHAVE PARA UM DELES, SENDO QUE ELES RETORNARAM DIA SEGUINTE E ESFAQUEARAM AS VITIMAS ENQUANTO DORMIAM', 2, 5, 1, '2025-01-03 14:25:59', '2025-01-03 14:25:59', NULL, 0, 0),
(124, 1, 1, 3, '09/2023', '2023-01-27', '2023-01-27', 'CONFRONTO POLICIAL', 2, 5, 1, '2025-01-03 14:57:45', '2025-01-03 14:57:45', NULL, 0, 0),
(125, 1, 1, 3, '47/2022', '2022-12-01', '2022-12-01', 'FOI ENCONTRADO O CORPO DA VÍTIMA, AINDA NÃO IDENTIFICADA, EM ESTADO AVANÇADO DE DECOMPOSIÇÃO E PARCIALMENTE QUEIMADO,AOS FUNDOS DO LOTEAMENTO  ALPES VERDES.', 2, 5, 1, '2025-01-03 15:03:50', '2025-01-03 15:03:50', NULL, 0, 0),
(126, 1, 1, 3, '17/2023', '2023-03-21', '2023-03-21', 'VITIMA FOI ESFAQUEADO PELAS COSTAS ENQUANTO ANDAVA PELA RUA. AMBOS SAO USUÁRIOS DE DROGAS, POSSIVELMENTE BRIGA POR DROGAS', 2, 5, 1, '2025-01-03 15:44:40', '2025-01-03 15:44:40', NULL, 0, 0),
(127, 4, 1, 3, '24/2022', '2022-05-24', '2022-05-24', 'CONFRONTO POLICIAL FORJADO', 2, 5, 1, '2025-01-03 16:45:14', '2025-01-03 17:02:45', NULL, 0, 0),
(128, 4, 1, 3, '38/2022', '2022-08-30', '2022-08-30', 'A VÍTIMA ESTAVA EM CASA QUANDO UM INDIVÍDUO CHEGOU AO LOCAL E EFETUOU UM DISPARO DE ARMA DE  FOGO EM SUA CABEÇA E EM SEGUIDA EVADIU-SE DO LOCAL , VEIO A ÓBITO DIA 10/09/2022', 2, 5, 1, '2025-01-03 17:00:54', '2025-01-03 17:01:37', NULL, 0, 0),
(129, 4, 1, 1, '2406207007', '2024-12-30', '2025-01-07', 'BRIGA ENTRE USUÁRIOS DE DROGAS.', 2, 5, 1, '2025-01-07 08:58:11', '2025-01-09 16:30:30', NULL, 0, 0),
(130, 8, 2, 2, '241068284', '2024-07-26', '2024-07-31', 'IRMÃO RELATA QUE A VÍTIMA FOI ENVENENADA.', 2, 5, 1, '2025-01-07 11:13:40', '2025-01-07 11:13:40', NULL, 0, 0),
(131, 7, 1, 3, '116/2013', '2013-09-09', '2013-09-13', '', 8, 5, 1, '2025-01-08 16:36:36', '2025-01-08 16:41:39', NULL, 0, 0),
(132, 10, 2, 2, '2510213839', '2025-01-08', '2025-01-09', 'FUNCIONÁRIO (ISRAEL) E PATRÃO (GILVAN) DE UMA BORRACHARIA DISCUTIRAM DEVIDO A QUALIDADE DO TRABALHO DAQUELE, SENDO QUE ISRAEL FERIU GILVAN COM GOLPES DE ARMA BRANCA.', 4, 5, 1, '2025-01-09 09:15:11', '2025-01-14 10:22:21', NULL, 0, 0),
(133, 4, 1, 3, '26/2022', '2022-06-05', '2022-06-05', 'VITIMA TEVE SEU PESCOÇO FRATURADO DURANTE RELAÇÃO SEXUAL COM O INVESTIGADO', 2, 5, 1, '2025-01-10 11:25:04', '2025-01-10 11:26:22', NULL, 0, 0),
(134, 8, 2, 2, '2510217806', '2025-01-11', '2025-01-14', 'VITIMA IDOSA DE 75 ANOS TIROU A PROPRIA VIDA COM UMA UNICA FACADA NO PESCOÇO EM SUA CHÁCARA.', 4, 5, 1, '2025-01-14 10:36:41', '2025-01-14 10:36:41', NULL, 0, 0),
(135, 11, 2, 2, '2510218537', '2025-01-14', '2025-01-14', 'APREENSÃO DE UMA ARMA DE FOGO NA CIDADE DE MOZARLÂNDIA-GO REFERENTE AO IP 2406194410.', 4, 5, 1, '2025-01-15 10:05:29', '2025-01-15 10:05:29', NULL, 0, 0),
(136, 4, 1, 1, '2506218521', '2025-01-13', '2025-01-14', 'OS AUTORES SE ENVOLVERAM EM UMA DISCUSSÃO COM A VÍTIMA POR CAUSA DE DROGAS, A ESFAQUEARAM PERTO DE SUA CASA E ESTÁ FUGIU EM SUA MOTO.', 4, 5, 1, '2025-01-15 10:38:22', '2025-01-23 13:32:33', NULL, 0, 0),
(137, 4, 1, 3, '2506218426', '2025-01-14', '2025-01-14', 'A VITIMA FOI ABORDADA POR UMA VIATURA DO CPE E EMPREENDEU FUGA, TENDE EFETUADO DISPAROS CONTRA OS POLICIAIS E SIDO ALVEJADO, VINDO À ÓBITO NO LOCAL - CONFRONTO POLICIAL.', 4, 5, 1, '2025-01-15 16:01:49', '2025-03-18 11:44:44', NULL, 0, 0),
(138, 1, 1, 3, '2506223755', '2025-01-19', '2025-01-21', 'DESENTENDIMENTO.', 2, 5, 1, '2025-01-21 10:22:15', '2025-01-21 10:22:15', NULL, 0, 0),
(139, 1, 1, 3, '2506224306', '2025-01-20', '2025-01-21', 'INDIVÍDUO COMETEU UM HOMICÍDIO NO DIA ANTERIOR NO BAIRRO PROMISSÃO FAZENDO USO DE UMA ARMA DE FOGO E UMA BICICLETA.', 2, 5, 1, '2025-01-21 16:19:01', '2025-01-21 16:19:01', NULL, 0, 0),
(140, 4, 1, 3, '2506224265', '2025-01-17', '2025-01-21', 'MORTE POR INTERVENÇÃO POLICIAL', 4, 5, 1, '2025-01-23 10:59:08', '2025-04-01 11:09:31', NULL, 0, 0),
(141, 10, 2, 2, '2510238145', '2025-01-29', '2025-02-04', 'SUICIDIO', 4, 5, 1, '2025-02-05 10:28:26', '2025-02-05 10:28:26', NULL, 0, 0),
(142, 4, 1, 1, '2506242470', '2025-02-07', '2025-02-07', 'DOIS CAMINHONEIROS FUCNIONÁRIOS DA COMIGO SE ENVOLVERAM EM UMA PEQUENA COLISÃO ENTRE OS VÉICULOS O QUE RESULTOU EM UM DESENTENDIMENTO E O NO DISPARO DE ARMA DE FOGO EM DESFAVOR DA VÍTIMA.', 4, 5, 1, '2025-02-10 16:52:55', '2025-02-14 10:32:38', NULL, 0, 0),
(143, 1, 1, 3, '2506245412', '2025-02-09', '2025-02-11', 'LUCAS AGREDIU A ESPOSA E AMEAÇOU A FILHA COM UMA ARMA DE FOGO E SAIU DE CASA EM UMA CAMINHONTE AMAROK, FOI PERSEGUIDO POR POLICIAIS MILITARES DO CPE E MORREU APÓS CONFORNTO NO MOTEL JOIA.', 4, 5, 1, '2025-02-11 09:43:51', '2025-02-11 09:43:51', NULL, 0, 0),
(144, 8, 2, 2, '2510243165', '2025-02-09', '2025-02-09', 'DOIS USUÁRIOS DE DROGAS BRIGARAM NA RODOVIÁRIA E O AUTOR DEFERIU UM GULPE COM UMA BARRA DE FERRO NA CABEÇA DA VÍTIMA QUE FOI INTERNADA NO HERSO.', 4, 5, 1, '2025-02-11 11:18:42', '2025-02-11 11:18:42', NULL, 0, 0),
(145, 10, 2, 2, '2510251525', '2025-02-12', '2025-02-17', 'SUICIDIO', 4, 5, 1, '2025-02-17 11:32:44', '2025-02-17 15:33:23', NULL, 0, 0),
(146, 10, 2, 2, '2510251544', '2025-02-13', '2025-02-17', 'MORTE NATURAL', 4, 5, 1, '2025-02-17 11:44:03', '2025-02-17 15:15:20', NULL, 0, 0),
(147, 4, 1, 3, '41/2021', '2021-09-28', '2021-09-28', 'CONFRONTO POLICIAL', 2, 5, 1, '2025-02-18 08:53:26', '2025-02-18 08:54:17', NULL, 0, 0),
(148, 4, 1, 1, '2506255837', '2025-02-19', '2025-02-20', 'DOIS VIZINHOS DISUTIRAM EM UMA KITNET E O AUTOR ESFAQUEOU A VÍTIMA NO ROSTO E PESCOÇO.', 4, 5, 1, '2025-02-21 09:16:27', '2025-02-28 15:09:41', NULL, 0, 0),
(149, 3, 1, 3, '2506256123', '2025-02-04', '2025-02-21', 'RECEBEMOS NO GIH - GRUPO DE INVESTIGAÇÃO DE HOMICÍDIOS, UMA MÍDIA, NA QUAL SE PODE CONSTATAR, COM NITIDEZ, UMA PESSOA (APARENTEMENTE DO SEXO MASCULINO), PEDINDO SOCORRO, CONCOMITANTE AO SOM DE PELO MENOS TRÊS BARULHOS SEMELHANTES À DISPAROS DE ARMA DE FOGO. DIANTE DESTA INFORMAÇÃO,CUMPRIMOS DIVERSAS DILIGÊNCIAS E COLETAMOS ELEMENTOS DE INFORMAÇÃO SUFICIENTES PARA EMBASAR A INSTAURAÇÃO DE UM INQUÉRITO POLICIAL PARA APURAR A AUTORIA A MATERIALIDADE DESTE SUPOSTO CRIME.', 2, 5, 1, '2025-02-21 11:04:11', '2025-02-21 11:04:11', NULL, 0, 0),
(150, 4, 1, 1, '2506264294', '2025-03-01', '2025-03-01', '.', 4, 5, 1, '2025-03-06 11:37:59', '2025-03-10 10:37:56', NULL, 0, 0),
(151, 11, 2, 2, '2510253954', '2025-02-17', '2025-02-19', 'AMANTE ATEOU FOGO AO CORPO DA VITIMA.', 4, 5, 1, '2025-03-06 17:23:45', '2025-03-17 11:52:32', NULL, 0, 0),
(152, 8, 2, 2, '2510265314', '2025-03-04', '2025-03-07', 'BRIGA EM FAMILIA', 4, 5, 1, '2025-03-07 09:26:26', '2025-03-07 09:26:26', NULL, 0, 0),
(153, 10, 2, 2, '2510268072', '2025-02-27', '2025-03-07', 'SUICIDIO', 4, 5, 1, '2025-03-07 10:00:18', '2025-03-07 10:00:18', NULL, 0, 0),
(154, 1, 1, 3, '2506273275', '2025-03-11', '2025-03-12', 'CONFRONTO POLICIAL', 2, 5, 1, '2025-03-12 11:14:37', '2025-03-12 11:14:37', NULL, 0, 0),
(155, 10, 2, 2, '2510273314', '2025-03-08', '2025-03-12', 'SUICIDIO', 4, 5, 1, '2025-03-12 11:40:40', '2025-03-12 11:40:40', NULL, 0, 0),
(156, 3, 1, 3, '2506277565', '2024-11-13', '2025-03-17', 'NO DIA 13 DE NOVEMBRO DE 2024, POR VOLTA DAS 22H45, NA RUA PIETRO UBALDI, SETOR PAUZANES, RIO VERDE/GO, A POLÍCIA ATENDEU A UMA OCORRÊNCIA DE DISPARO DE ARMA DE FOGO QUE RESULTOU NA MORTE DE HÉLIO SANTANA DE ARAÚJO. SEGUNDO RELATOS DE SUA ESPOSA, KETLEN GEOVANA DOS SANTOS MARQUES, AMBOS ESTAVAM ASSISTINDO A UM FILME QUANDO HÉLIO PEGOU UM REVÓLVER CALIBRE .38, MARCA ROSSI, E, EM TOM DE BRINCADEIRA, APONTOU PARA A PRÓPRIA CABEÇA E, EM SEGUIDA, PARA O TÓRAX. AO TENTAR IMPEDIR A AÇÃO, KETLEN ACABOU PROVOCANDO UM DISPARO ACIDENTAL QUE ATINGIU SEU MARIDO. SUA AVÓ, GEISA DOS SANTOS MARQUES, CONFIRMOU A VERSÃO DOS FATOS. A PERÍCIA FOI REALIZADA NO LOCAL E A POLÍCIA CIVIL APREENDEU A ARMA, MUNIÇÕES E UM CELULAR PERTENCENTE À ESPOSA DA VÍTIMA PARA INVESTIGAÇÃO.', 2, 5, 1, '2025-03-17 09:35:15', '2025-03-17 09:35:15', NULL, 0, 0),
(157, 4, 1, 3, '2506277985', '2025-02-17', '2025-03-17', 'MULHER ATEOU FOGO AO AMANTE.', 4, 5, 1, '2025-03-17 11:56:55', '2025-05-27 15:20:52', NULL, 0, 0),
(158, 8, 2, 2, '2510277915', '2025-03-10', '2025-03-17', 'A VÍTIMA INGERIU REMÉDIOS CONTROLADOS, FOI HOSPITALIZADA E EVOLUIU PARA ÓBITO NA UTI. O PAI DA VÍTIMA ACREDITA QUE HOUVE NEGLIGENCIA POR PARTE DO MARIDO DA VÍTIMA, QUE SUPOSTAMENTE LEVOU MAIS DE 12 HORAS PARA PRESTAR SOCORRO À VÍTIMA.', 2, 5, 1, '2025-03-17 14:22:20', '2025-03-17 14:22:20', NULL, 0, 0),
(159, 11, 2, 2, '2510267739', '2025-02-23', '2025-03-06', 'BRIGA EM FESTA.', 4, 5, 1, '2025-03-17 15:16:22', '2025-03-17 15:16:22', NULL, 0, 0),
(160, 1, 1, 3, '2506278297', '2025-02-23', '2025-03-17', 'BRIGA EM FESTA.', 4, 5, 1, '2025-03-17 15:17:29', '2025-03-17 15:17:29', NULL, 0, 0),
(161, 10, 2, 2, '2510278463', '2025-03-12', '2025-03-17', 'SUICIDIO.', 4, 5, 1, '2025-03-17 16:55:31', '2025-03-17 16:55:31', NULL, 0, 0),
(162, 1, 1, 3, '2506284994', '2025-03-22', '2025-03-24', 'NETO AGREDIU A AVÓ E O MARIDO DELA, MOMENTO EM QUE OUTROS FAMILIARES INTERVIERAM E FORAM ESFAQUEADOS PELO AUTOR.', 2, 5, 1, '2025-03-24 11:39:42', '2025-03-24 11:39:42', NULL, 0, 0),
(163, 4, 1, 3, '39/2019', '2019-09-05', '2019-09-09', '', 2, 5, 1, '2025-03-31 15:02:26', '2025-03-31 15:04:11', NULL, 0, 0),
(164, 4, 1, 1, '2506292564', '2025-03-31', '2025-03-31', '', 2, 5, 1, '2025-04-01 09:53:28', '2025-05-30 11:55:41', NULL, 0, 0),
(165, 4, 1, 1, '2506297116', '2025-04-04', '2025-04-04', 'BRIGA ENTRE TRAFICANTES DE DROGA DO BAIRRO POPULAR', 4, 5, 1, '2025-04-08 10:27:23', '2025-04-11 11:26:18', NULL, 0, 0),
(166, 4, 1, 1, '2506297741', '2025-04-05', '2025-04-05', 'BRIGA ENTRE VIZINHOS DE UM CONJUNTO DE KITNETS', 4, 5, 1, '2025-04-08 10:30:05', '2025-04-15 10:48:59', NULL, 0, 0),
(167, 10, 2, 2, '2510298626', '2025-04-06', '2025-04-07', 'SUICIDIO - VITIMA DEPRESSIVA', 4, 5, 1, '2025-04-08 10:32:30', '2025-04-08 10:32:30', NULL, 0, 0),
(168, 1, 1, 1, '2506302244', '2025-04-09', '2025-04-10', 'BRIGA ENTRE DOIS DESAFETOS EM UMA BEBIDA GELADA', 4, 5, 1, '2025-04-10 15:09:11', '2025-04-10 15:09:11', NULL, 0, 0),
(169, 8, 2, 2, '2510303297', '2025-04-11', '2025-04-11', 'VITIMA COM HISTÓRICO DE SURTO PSICÓTICO, APARENTE AVC E BRONCOASPIRAÇÃO - HISTÓRICO DE VIOLÊNCIA DOMÉSTICA - POSSIBILIDADE DE SUICÍDIO.', 2, 5, 1, '2025-04-11 10:19:24', '2025-04-11 10:19:24', NULL, 0, 0),
(170, 4, 1, 3, '02/2023', '2023-01-02', '2023-01-02', '', 2, 5, 1, '2025-04-14 14:55:31', '2025-04-14 14:55:31', NULL, 0, 0),
(171, 4, 1, 1, '37/2023', '2023-01-09', '2023-01-09', 'A VÍTIMA ESTAVA EM UMA FESTA NOTURNA, MOMENTO EM QUE SE DESENTENDEU COM O AUTOR E EM SEGUIDA FOI VÍTIMA DE DISPARO DE ARMA DE FOGO', 4, 5, 1, '2025-04-14 15:42:20', '2025-04-14 15:42:20', NULL, 0, 0),
(172, 1, 1, 1, '05/2023', '2023-01-23', '2023-08-15', 'O AUTOR VEIO A ATINGIR A VITIMA DURANTE EM UM CONFRONTO POLICIAL', 4, 5, 1, '2025-04-14 15:55:44', '2025-04-14 15:55:44', NULL, 0, 0),
(173, 1, 1, 3, '07/2023', '2023-01-25', '2023-07-18', 'SEGUNDO INFORMAÇÕES UMA MULHER HAVIA JOGADO ÁLCOOL  NA VÍTIMA E EM SEGUIDA COLOCADO FOGO', 2, 5, 1, '2025-04-14 16:04:12', '2025-04-14 16:04:12', NULL, 0, 0),
(174, 1, 1, 1, '122/2023', '2023-01-30', '2023-02-07', 'O AUTOR ESTAVA EM SUA RESIDÊNCIA QUANDO OUVIU UMA DISCUSSÃO NA RESIDEÊNCIA DE SEU PAI COM SEU IRMÃO; NESTE MOMENTO O AUTOR SAIU EM POSSE DE UMA FACA  E AO CHEGAR NA RESIDÊNCIA DE SEU GENITOR, DESFERIU VÁRIAS FACADAS EM DESFAVOR DA VÍTIMA', 2, 5, 1, '2025-04-14 16:12:31', '2025-04-14 16:12:31', NULL, 0, 0),
(175, 1, 1, 1, '172/2023', '2023-02-09', '2023-03-08', 'VITIMA ESTAVA DORMINDO NA OBRA Q TRABALHAVA E O AUTOR CHEGOU E DESFERIU UMA MARRETADA NA SUA CABEÇA', 2, 5, 1, '2025-04-14 16:19:12', '2025-04-14 16:19:12', NULL, 0, 0),
(176, 1, 1, 1, '174/2023', '2023-02-10', '2023-02-17', 'FALSO TESTEMUNHO/FÓRUM DE RIO VERDE-GO', 2, 5, 1, '2025-04-14 16:32:18', '2025-04-14 16:32:18', NULL, 0, 0),
(177, 7, 1, 3, '13/2023', '2023-02-28', '2023-03-17', 'LATROCINIO', 4, 5, 1, '2025-04-14 16:39:54', '2025-04-14 16:39:54', NULL, 0, 0),
(178, 1, 1, 1, '225/2023', '2023-02-23', '2023-07-05', 'ACIDENTE DE TRÂNSITO', 4, 5, 1, '2025-04-15 15:03:32', '2025-04-15 15:03:32', NULL, 0, 0),
(179, 1, 1, 1, '290/2023', '2023-03-08', '2023-05-04', 'SEM MOTIVO APARENTE', 4, 5, 1, '2025-04-15 15:09:52', '2025-04-15 15:09:52', NULL, 0, 0),
(180, 1, 1, 3, '15/2023', '2023-03-18', '2023-07-03', 'LESÃO CORPORAL E MAUS TRATOS', 4, 5, 1, '2025-04-15 15:18:22', '2025-04-15 15:18:22', NULL, 0, 0),
(181, 4, 1, 3, '18/2023', '2023-03-27', '2024-02-07', 'TENTATIVA DE HOMICÍDIO', 4, 5, 1, '2025-04-15 15:26:00', '2025-04-15 15:26:00', NULL, 0, 0),
(182, 7, 1, 1, '356/2023', '2023-03-27', '2023-06-27', 'ACIDENTE DE TRÂNSITO/HOMICÍDIO', 4, 5, 1, '2025-04-15 15:36:37', '2025-04-15 15:36:37', NULL, 0, 0),
(183, 1, 1, 3, '19/2023', '2023-04-14', '2023-10-19', 'TENTATIVA DE HOMICIDIO E ROUBO', 2, 5, 1, '2025-04-15 15:42:32', '2025-04-15 15:42:32', NULL, 0, 0),
(184, 1, 1, 3, '20/2023', '2023-04-14', '2023-06-16', 'MORTE POR INTERVENÇÃO POLICIAL(CONFRONTO POLICIAL)', 4, 5, 1, '2025-04-15 15:48:29', '2025-04-15 15:48:29', NULL, 0, 0),
(185, 7, 1, 1, '446/2023', '2023-04-14', '2023-04-20', 'DESENTENDIMENTO COMERCIAL - VENDA DE VEÍCULO', 4, 5, 1, '2025-04-15 15:57:49', '2025-04-15 15:57:49', NULL, 0, 0),
(186, 10, 2, 2, '2510307342', '2025-04-10', '2025-04-15', 'SUICIDIO', 4, 5, 1, '2025-04-15 16:03:45', '2025-04-15 16:03:45', NULL, 0, 0),
(187, 1, 1, 3, '22/2023', '2023-04-24', '2023-05-09', 'TRÁFICO DE DROGAS', 4, 5, 1, '2025-04-15 16:05:20', '2025-04-15 16:05:20', NULL, 0, 0),
(188, 1, 1, 1, '600/2023', '2023-05-16', '2023-05-24', 'O AUTOR TINHA UM RELACIONAMENTO COM A VÍTIMA, E APÓS SENTIR CIÚMES DESTA COM UM TERCEIRO, HOUVE UMA DISCUSSÃO A QUAL OCASIONOU O CRIME EM QUESTÃO', 2, 5, 1, '2025-04-15 16:12:22', '2025-04-15 16:12:22', NULL, 0, 0),
(189, 4, 1, 1, '620/2023', '2023-05-22', '2023-05-31', 'TENTATIVA DE HOMICÍDIO E POSSE DE ARMA DE FOGO', 4, 5, 1, '2025-04-15 16:18:14', '2025-04-15 16:18:14', NULL, 0, 0),
(190, 7, 1, 3, '29/2023', '2023-07-09', '2023-08-09', 'PAI MATOU A FILHA DE 03 ANOS DEVIDO NAO ACEITAR O FIM DO RELACIONAMENTO COM MAE DA CRIANÇA', 4, 5, 1, '2025-04-15 16:28:00', '2025-04-15 16:28:00', NULL, 0, 0),
(191, 1, 1, 3, '30/2023', '2023-07-10', '2023-10-19', 'A VÍTIMA ESTAVA NA PORTA DA CASA DE SUA CUNHADA QUANDO FOI ATACADO PELA AUTORA', 4, 5, 1, '2025-04-15 16:34:24', '2025-04-15 16:34:24', NULL, 0, 0),
(192, 1, 1, 3, '31/2023', '2023-07-31', '2023-08-25', 'VITIMA ESTAVA EM UM BAR QUANDO CHEGOU DOIS AUTORES EM UMA MOTOCICLETA E ATIRARAM NA VITIMA(FACÇÃO)', 2, 5, 1, '2025-04-15 16:43:15', '2025-04-15 16:43:15', NULL, 0, 0),
(193, 4, 1, 1, '1032/2023', '2023-08-21', '2023-08-28', 'RIXA DE BAIRRO/VITIMA ESTAVA SENDO AMEAÇADA PELO AUTOR DEVIDO RIXA ENTRE GRUPO RIVAIS', 2, 5, 1, '2025-04-15 16:52:17', '2025-04-15 16:52:17', NULL, 0, 0),
(194, 4, 1, 1, '1094/2023', '2023-08-30', '2023-09-06', 'RIXA/VITIMA FOI ALVEJADA LOGO APOS ENTRAR NO CARRO E LEVOU UM TIRO DE RASPAO NA CABEÇA', 4, 5, 1, '2025-04-15 16:57:40', '2025-04-15 16:57:40', NULL, 0, 0),
(195, 4, 1, 3, '35/2023', '2023-09-04', '2023-01-23', 'VITIMA E AUTOR BRIGARAM NA PORTA DE UMA BEBIDA GELADA', 4, 5, 1, '2025-04-15 17:04:24', '2025-04-15 17:04:24', NULL, 0, 0),
(196, 4, 1, 1, '1177/2023', '2023-09-17', '2023-09-26', 'VITIMA ESTAVA EM UM BAR QUANDO O AUTOR PASSOU NA PORTA E TIVERAM UMA DISCUSSÃO, O AUTOR FOI EM CASA, BUSCOU A ARMA DE FOGO E VOLTOU AO BAR E ATIROU NA VITIMA', 2, 5, 1, '2025-04-15 17:21:42', '2025-04-15 17:21:42', NULL, 0, 0),
(197, 4, 1, 1, '1239/2023', '2023-09-29', '2023-10-06', 'TENTATIVA DE HOMICÍDIO/PASSIVO', 4, 5, 1, '2025-04-15 17:27:58', '2025-04-15 17:27:58', NULL, 0, 0),
(198, 10, 2, 2, '2510307536', '2025-04-04', '2025-04-15', 'MORTE NATURAL', 4, 5, 1, '2025-04-15 17:40:39', '2025-04-15 17:40:39', NULL, 0, 0),
(199, 4, 1, 3, '38/2023', '2023-09-30', '2024-01-10', 'VITIMAS FORAM ALVEJADAS POR TRES AUTORES QUE CHEGARAM EM UM VEICULO E TEM RIXA COM O GRUPO RIVAL NO BAIRRO', 2, 5, 1, '2025-04-15 17:40:59', '2025-04-15 17:40:59', NULL, 0, 0),
(200, 4, 1, 1, '1240/2023', '2023-10-01', '2023-10-10', 'BRIGA DE BAR/VÍTIMA FOI ALVEJADA NO AMBRO DURANTE UMA DISCUSSAO E ESCONDEU A ARMA NA CASA DO AMIGO QUE FOI PRESO POR TRAFICO', 2, 5, 1, '2025-04-15 17:56:59', '2025-04-15 17:56:59', NULL, 0, 0),
(201, 4, 1, 1, '1248/2023', '2023-10-02', '2023-10-11', 'SOB EFEITO DE DROGAS E MOVIDO PELA INTENÇÃO DE ROUBAR,O AUTOR INVADIU A RESIDENCIA DA VITIMA ACREDITANDO QUE O IMOVEL ESTIVESSE VAZIO.NO ENTANTO,AO ENTRAR,DEPAROU-SE INESPERADAMENTE COM A VITIMIA AINDA DENTRO DE CASA', 4, 5, 1, '2025-04-15 18:10:18', '2025-04-15 18:10:18', NULL, 0, 0),
(202, 4, 1, 1, '1289/2023', '2023-10-10', '2023-10-18', 'VÍTIMA E AUTORES SE DESENTENDERAM NA PORTA DA BOATE ANTONIETA', 4, 5, 1, '2025-04-16 14:22:00', '2025-04-16 14:22:00', NULL, 0, 0),
(203, 4, 1, 1, '1330/2023', '2023-10-19', '2023-10-27', 'VITIMAS E AUTORES DISCUTIRAM POR CAUSA DA VENDA DE UMA VACA', 4, 5, 1, '2025-04-16 14:29:21', '2025-04-16 14:29:21', NULL, 0, 0),
(204, 4, 1, 1, '1356/2023', '2023-10-23', '2023-11-01', 'OS IRMÃOS SE DESENTENDERAM POR CAUSA DE DINHEIRO E PROBLEMAS FAMILIARES', 4, 5, 1, '2025-04-16 14:35:12', '2025-04-16 14:35:12', NULL, 0, 0),
(205, 4, 1, 3, '1357/2023', '2023-10-23', '2023-11-01', 'A VÍTIMA DENUNCIOU O AUTOR E ESTE SE VINGOU', 4, 5, 1, '2025-04-16 14:42:39', '2025-04-16 14:42:39', NULL, 0, 0),
(206, 4, 1, 3, '41/2023', '2023-10-23', '2023-12-06', 'A VÍTIMA FOI ALVEJADA POR VÁRIOS DISPAROS DE ARMA DE FOGO NO INTERIOR DO SEU VEÍCULO', 2, 5, 1, '2025-04-16 14:49:06', '2025-04-16 14:49:06', NULL, 0, 0),
(207, 4, 1, 3, '43/2023', '2023-11-01', '2023-12-11', 'VITIMA TEVE UM CASO AMOROSO COM A COMPANHEIRO DO AUTOR QUE ATIROU COM ESPINGARDA EM SUA DIREÇÃO QND CHEGAVA EM CASA', 2, 5, 1, '2025-04-16 14:54:14', '2025-04-16 14:54:14', NULL, 0, 0),
(208, 7, 1, 1, '1413/2023', '2023-11-05', '2023-11-14', 'OS AUTORES DESEJAVAM MATAR SEU DESAFETO, EDILSON LIMA DOS SANTOS FILHO, MAS ACERTARAM AS VÍTIMAS', 4, 5, 1, '2025-04-16 15:02:59', '2025-04-16 15:02:59', NULL, 0, 0),
(209, 4, 1, 1, '1442/2023', '2023-11-10', '2023-11-20', 'CIUMES DO AUTOR EM RELAÇÃO A VITIMA/O EX MARIDO DA NAMORADA DA VÍTIMA O ALVEJOU NA PORTA DA CASA DESTE', 4, 5, 1, '2025-04-16 15:07:21', '2025-04-16 15:07:21', NULL, 0, 0),
(210, 4, 1, 1, '1493/2023', '2023-11-20', '2023-11-27', 'VITIMA E AUTORES SAO VIZINHOS E SE DESENTENDERAM DURANTE UMA DISCUSSAO', 2, 5, 1, '2025-04-16 15:13:45', '2025-04-16 15:13:45', NULL, 0, 0),
(211, 4, 1, 1, '1518/2023', '2023-11-25', '2023-12-04', 'BRIGA POR CAUSA DE SOM ALTO', 2, 5, 1, '2025-04-16 15:20:12', '2025-04-16 15:20:12', NULL, 0, 0),
(212, 4, 1, 1, '1635/2023', '2023-11-18', '2023-12-22', 'A VITIMA DEVIA DINHEIRO AO AUTOR', 4, 5, 1, '2025-04-16 15:25:03', '2025-04-16 15:25:03', NULL, 0, 0),
(213, 4, 1, 3, '46/2023', '2023-11-23', '2023-11-05', 'VITIMA FOI GOLPEADA TRES VEZ PELO AUTOR DURANTE UMA DISCUSSÃO POR PERTECEREM A GRUPOS RIVAIS', 4, 5, 1, '2025-04-16 15:31:09', '2025-04-16 15:31:09', NULL, 0, 0),
(214, 4, 1, 1, '19/2020', '2020-03-01', '2020-03-01', 'TENTATIVA DE HOMICÍDIO', 4, 5, 1, '2025-04-16 15:39:34', '2025-05-05 10:26:47', NULL, 0, 0),
(215, 4, 1, 3, '01/2020', '2020-09-01', '2020-02-13', 'NÃO IDENTIFICADO O MOTIVO APARENTE', 4, 5, 1, '2025-04-16 15:46:13', '2025-04-16 15:46:13', NULL, 0, 0),
(216, 4, 1, 1, '46/2020', '2020-01-10', '2020-10-08', 'NÃO IDENTIFICADO O MOTIVO APARENTE', 4, 5, 1, '2025-04-16 15:50:34', '2025-04-16 15:50:34', NULL, 0, 0),
(218, 1, 1, 1, '04/2020', '2020-01-21', '2020-04-30', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 16:01:31', '2025-04-16 16:01:31', NULL, 0, 0),
(219, 7, 1, 1, '06/2020', '2020-01-30', '2023-09-01', 'MORTE POR INTERVENÇÃO POLICIAL', 2, 5, 1, '2025-04-16 16:07:15', '2025-04-16 16:07:15', NULL, 0, 0),
(220, 4, 1, 1, '08/2020', '2020-02-02', '2020-05-30', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 16:19:16', '2025-04-16 16:19:16', NULL, 0, 0),
(221, 4, 1, 1, '11/2020', '2020-02-22', '2020-05-15', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 16:28:28', '2025-04-16 16:28:28', NULL, 0, 0),
(222, 1, 1, 1, '174/2020', '2020-02-16', '2020-02-21', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 16:33:05', '2025-04-16 16:33:05', NULL, 0, 0),
(223, 1, 1, 1, '245/2020', '2020-01-03', '2020-06-30', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 16:37:36', '2025-04-16 16:37:36', NULL, 0, 0),
(224, 4, 1, 1, '312/2020', '2020-03-14', '2020-03-23', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 16:43:42', '2025-04-16 16:43:42', NULL, 0, 0),
(225, 1, 1, 1, '318/2020', '2020-03-16', '2021-04-16', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 16:47:07', '2025-04-16 16:47:07', NULL, 0, 0),
(226, 1, 1, 1, '337/2020', '2020-03-22', '2020-03-31', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 16:51:20', '2025-04-16 16:51:20', NULL, 0, 0),
(227, 7, 1, 1, '350/2020', '2020-03-23', '2020-04-02', 'MOTIVO APARENTE NÃO IDENTIFICADO/TENTATIVA DE HOMICÍDIO', 4, 5, 1, '2025-04-16 16:56:26', '2025-04-16 16:56:26', NULL, 0, 0),
(228, 4, 1, 1, '369/2020', '2020-03-28', '2020-04-07', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 16:59:17', '2025-04-16 16:59:17', NULL, 0, 0),
(229, 6, 1, 1, '16/2020', '2020-04-24', '2020-04-24', 'HOMICIDIO E TENTATIVA DE HOMICIDIO', 4, 5, 1, '2025-04-16 17:04:58', '2025-05-05 10:28:36', NULL, 0, 0),
(230, 4, 1, 3, '17/2020', '2020-04-19', '2021-08-17', 'MORTE POR INTERVENÇÃO POLICIAL/CONFRONTO POLICIAL', 2, 5, 1, '2025-04-16 17:08:15', '2025-04-16 17:08:15', NULL, 0, 0),
(231, 1, 1, 1, '530/2020', '2020-04-05', '2020-05-13', 'MOTIVO APARENTE NÃO IDENTIFICADO/TENTATIVA DE HOMICIDIO', 4, 5, 1, '2025-04-16 17:13:02', '2025-04-16 17:13:02', NULL, 0, 0),
(232, 1, 1, 1, '520/2020', '2020-05-05', '2020-05-05', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 17:16:30', '2025-05-05 10:28:57', NULL, 0, 0),
(233, 7, 1, 1, '18/2020', '2020-05-11', '2020-05-21', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 17:19:17', '2025-04-16 17:19:17', NULL, 0, 0),
(234, 4, 1, 1, '20/2020', '2020-05-23', '2023-10-13', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 17:24:16', '2025-04-16 17:24:16', NULL, 0, 0),
(235, 1, 1, 1, '21/2020', '2020-05-31', '2020-05-31', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 17:27:00', '2025-05-05 10:29:19', NULL, 0, 0),
(236, 1, 1, 1, '654/2020', '2020-05-31', '2020-06-09', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 17:33:09', '2025-04-16 17:33:09', NULL, 0, 0),
(237, 4, 1, 1, '678/2020', '2020-06-06', '2020-06-15', 'MOTIVO APARENTE NÃO IDENTIFICADO', 8, 5, 1, '2025-04-16 17:44:25', '2025-04-16 17:44:25', NULL, 0, 0),
(238, 7, 1, 1, '22/2020', '2020-06-13', '2020-06-13', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-16 17:49:45', '2025-05-05 10:29:44', NULL, 0, 0),
(239, 4, 1, 1, '743/2020', '2020-06-21', '2020-06-29', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-16 17:53:43', '2025-04-16 17:53:43', NULL, 0, 0),
(240, 4, 1, 1, '752/2020', '2020-06-22', '2020-07-01', 'HOMICIDIO', 2, 5, 1, '2025-04-22 14:42:58', '2025-04-22 14:42:58', NULL, 0, 0),
(241, 6, 1, 1, '25/2020', '2020-07-09', '2025-02-03', 'TENTATIVA DE HOMICIDIO/MOTIVO NÃO IDENTIFICADO', 2, 5, 1, '2025-04-22 14:48:53', '2025-04-22 14:48:53', NULL, 0, 0),
(242, 4, 1, 1, '816/2020', '2020-07-13', '2020-07-20', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-22 14:56:24', '2025-04-22 14:56:24', NULL, 0, 0),
(243, 4, 1, 1, '868/2020', '2020-07-26', '2020-08-04', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-22 15:00:06', '2025-04-22 15:00:06', NULL, 0, 0),
(244, 4, 1, 1, '27/2020', '2020-07-26', '2020-08-12', 'HOMICÍDIO/MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-22 15:03:09', '2025-04-22 15:03:09', NULL, 0, 0),
(245, 4, 1, 1, '904/2020', '2020-08-03', '2020-08-12', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 15:09:08', '2025-04-22 15:09:08', NULL, 0, 0),
(246, 4, 1, 1, '30/2020', '2020-08-18', '2020-11-13', '', 4, 5, 1, '2025-04-22 15:12:20', '2025-04-22 15:12:20', NULL, 0, 0),
(247, 4, 1, 1, '956/2020', '2020-08-23', '2020-09-07', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA PROVOCAR O CRIME', 4, 5, 1, '2025-04-22 15:15:58', '2025-04-22 15:15:58', NULL, 0, 0),
(248, 4, 1, 1, '31/2020', '2020-08-23', '2020-08-26', 'HOMICIDIO/ENCAMINHADO A DEPAI/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 15:24:05', '2025-04-22 15:24:05', NULL, 0, 0),
(249, 4, 1, 1, '992/2020', '2020-08-31', '2020-09-09', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 15:32:49', '2025-04-22 15:32:49', NULL, 0, 0),
(250, 4, 1, 1, '33/2020', '2020-08-31', '2020-09-10', 'MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 15:37:20', '2025-04-22 15:37:20', NULL, 0, 0),
(251, 4, 1, 1, '1011/2020', '2020-09-15', '2021-02-25', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 15:52:58', '2025-04-22 15:52:58', NULL, 0, 0),
(252, 4, 1, 1, '1035/2020', '2020-11-09', '2020-09-19', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:01:30', '2025-04-22 16:01:30', NULL, 0, 0),
(253, 4, 1, 1, '1041/2020', '2020-09-12', '2020-09-10', 'POSSE DE ARMA DE FOGO', 2, 5, 1, '2025-04-22 16:06:58', '2025-04-22 16:06:58', NULL, 0, 0),
(254, 4, 1, 1, '34/2020', '2020-09-16', '2021-08-31', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:13:27', '2025-04-22 16:13:27', NULL, 0, 0),
(255, 4, 1, 3, '35/2020', '2020-09-18', '2023-07-01', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:17:58', '2025-04-22 16:17:58', NULL, 0, 0),
(256, 4, 1, 1, '1065/2020', '2020-09-20', '2020-09-30', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 16:22:04', '2025-04-22 16:22:04', NULL, 0, 0),
(257, 4, 1, 1, '1125/2020', '2020-10-05', '2020-10-15', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:25:28', '2025-04-22 16:25:28', NULL, 0, 0),
(258, 4, 1, 1, '1148/2020', '2020-10-09', '2020-10-19', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:27:40', '2025-04-22 16:27:40', NULL, 0, 0),
(259, 4, 1, 1, '1180/2020', '2020-10-16', '2020-10-26', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:32:02', '2025-04-22 16:32:02', NULL, 0, 0),
(260, 4, 1, 1, '1200/2020', '2020-10-18', '2020-10-28', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 16:35:13', '2025-04-22 16:35:13', NULL, 0, 0),
(261, 7, 1, 3, '37/2020', '2020-10-21', '2021-11-19', 'CONFRONTO POLICIAL', 2, 5, 1, '2025-04-22 16:39:38', '2025-04-22 16:39:56', NULL, 0, 0),
(262, 4, 1, 1, '1261/2020', '2020-11-03', '2021-03-11', '', 4, 5, 1, '2025-04-22 16:42:18', '2025-04-22 16:42:18', NULL, 0, 0),
(263, 4, 1, 1, '1280/2020', '2020-11-05', '2020-11-13', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:45:22', '2025-04-22 16:45:22', NULL, 0, 0),
(264, 4, 1, 1, '1308/2020', '2020-11-12', '2021-07-07', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME/REMETIDO DIA 19/11/2020', 4, 5, 1, '2025-04-22 16:48:31', '2025-04-22 16:48:31', NULL, 0, 0),
(265, 6, 1, 3, '40/2020', '2020-10-11', '2023-08-23', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 16:52:04', '2025-04-22 16:52:04', NULL, 0, 0),
(266, 4, 1, 1, '   	 41/2020', '2020-11-08', '2021-09-14', '', 4, 5, 1, '2025-04-22 16:55:24', '2025-04-22 16:55:24', NULL, 0, 0),
(267, 6, 1, 3, '43/2020', '2020-11-17', '2023-08-25', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:00:32', '2025-04-22 17:00:32', NULL, 0, 0),
(268, 4, 1, 1, '1325/2020', '2020-11-16', '2020-11-25', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:03:52', '2025-04-22 17:03:52', NULL, 0, 0),
(269, 4, 1, 3, '44/2020', '2020-11-19', '0023-07-04', 'MORTE POR INTERVEÇÃO POLICIAL', 2, 5, 1, '2025-04-22 17:07:03', '2025-04-22 17:07:03', NULL, 0, 0),
(270, 4, 1, 1, '1343/2020', '2020-11-22', '2020-11-22', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:11:04', '2025-05-05 10:10:18', NULL, 0, 0),
(271, 4, 1, 1, '1371/2020', '2020-11-29', '2020-12-07', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:15:21', '2025-04-22 17:15:21', NULL, 0, 0),
(272, 7, 1, 3, '45/2020', '2020-11-30', '2020-12-21', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:18:09', '2025-04-22 17:18:09', NULL, 0, 0),
(273, 4, 1, 3, '47/2020', '2020-11-29', '2021-02-06', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-22 17:29:04', '2025-04-22 17:29:04', NULL, 0, 0),
(274, 4, 1, 1, '49/2020', '2020-12-19', '2021-02-09', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:37:55', '2025-04-22 17:37:55', NULL, 0, 0),
(275, 4, 1, 1, '1449/2020', '2020-12-19', '2020-12-28', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-22 17:45:46', '2025-04-22 17:45:46', NULL, 0, 0),
(276, 4, 1, 1, '27/2021', '2021-01-01', '2021-01-13', 'HOMICIDIO/DISCUSSAO FAMILIAR', 2, 5, 1, '2025-04-23 14:35:53', '2025-04-23 14:35:53', NULL, 0, 0),
(277, 4, 1, 3, '04/2021', '2021-01-04', '2021-03-15', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DESENTENDIMENTO', 4, 5, 1, '2025-04-23 14:41:58', '2025-04-23 14:41:58', NULL, 0, 0),
(278, 4, 1, 1, '53/2021', '2021-01-13', '2021-01-19', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-23 14:46:26', '2025-04-23 14:46:26', NULL, 0, 0),
(279, 4, 1, 1, '82/2021', '2021-01-15', '2021-01-22', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-23 14:50:04', '2025-04-23 14:50:04', NULL, 0, 0),
(280, 4, 1, 1, '106/2021', '2021-01-21', '2021-01-28', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-23 14:53:21', '2025-04-23 14:53:21', NULL, 0, 0),
(281, 4, 1, 1, '150/2021', '2021-01-28', '2021-01-28', 'MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 2, 5, 1, '2025-04-23 14:59:16', '2025-05-05 09:53:25', NULL, 0, 0),
(282, 4, 1, 3, '07/2021', '2021-02-02', '2021-03-30', 'DISCUSSAO POR CAUSA DA COMPRA DE UM APARELHO CELULAR/VÍTIMA FOI ALVEJADA NO INTERIOR DE SUA RESIDENCIA, POR DISPAROS DE ARMA DE FOGO, SENDO QUE TRES PESSOAS DESCERAM DE UM CARRO, FIAT/UNO, DOIS HOMENS E UMA MULHER, E EFETURAM O DISPAROS DE VITIMA', 2, 5, 1, '2025-04-23 15:05:14', '2025-04-23 15:05:14', NULL, 0, 0),
(283, 4, 1, 3, '08/2021', '2021-02-08', '2021-12-15', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-23 15:09:02', '2025-04-23 15:09:02', NULL, 0, 0),
(284, 4, 1, 1, '191/2021', '2021-02-06', '2021-02-16', 'HOMICIDIO/BRIGA POR CAUSA DE DROGAS', 2, 5, 1, '2025-04-23 15:13:27', '2025-04-23 15:13:27', NULL, 0, 0),
(285, 4, 1, 3, '09/2021', '2021-02-08', '2021-09-30', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DESENTENDIMENTO', 4, 5, 1, '2025-04-23 15:18:22', '2025-04-23 15:18:22', NULL, 0, 0),
(286, 4, 1, 3, '10/2021', '2021-02-08', '2021-03-18', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME', 4, 5, 1, '2025-04-23 15:22:53', '2025-04-23 15:22:53', NULL, 0, 0),
(287, 4, 1, 1, '211/2021', '2021-02-12', '2021-02-18', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE IDENTIFICADO COMO BRIGA DE VIZINHOS', 4, 5, 1, '2025-04-23 15:27:29', '2025-04-23 15:27:29', NULL, 0, 0),
(288, 4, 1, 1, '222/2021', '2021-02-13', '2021-02-22', 'MOTIVO APARENTE BRIGA FAMILIAR', 2, 5, 1, '2025-04-23 15:32:11', '2025-04-23 15:32:11', NULL, 0, 0),
(289, 4, 1, 1, '270', '2021-02-26', '2021-03-05', 'IDENTIFICOU-SE A EXISTÊNCIA DE DESAFETOS NO AMBIENTE  INTERPESSOAIS ENTRE OS COLABORADORES', 4, 5, 1, '2025-04-23 15:46:00', '2025-04-23 15:46:00', NULL, 0, 0),
(290, 4, 1, 3, '11/2021', '2021-03-07', '2021-03-07', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-23 15:50:44', '2025-05-05 09:58:39', NULL, 0, 0),
(291, 4, 1, 3, '12/2021', '2021-03-04', '2021-06-03', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-23 15:55:11', '2025-04-23 15:55:11', NULL, 0, 0),
(292, 4, 1, 1, '324/2021', '2021-03-12', '2021-03-19', 'NÃO IDENTIFICADO', 4, 5, 1, '2025-04-23 15:58:05', '2025-04-23 15:58:05', NULL, 0, 0),
(293, 4, 1, 3, '14/2021', '2021-03-23', '2021-10-09', 'MOTIVO APARENTE NÃO IDENTIFICADO', 4, 5, 1, '2025-04-23 16:09:28', '2025-04-23 16:09:28', NULL, 0, 0),
(294, 4, 1, 3, '15/2021', '2021-03-29', '2021-04-07', '', 4, 5, 1, '2025-04-23 16:13:32', '2025-04-23 16:13:32', NULL, 0, 0),
(295, 4, 1, 3, '16/2021', '2021-04-06', '2021-04-14', '', 2, 5, 1, '2025-04-23 16:22:46', '2025-04-23 16:22:46', NULL, 0, 0),
(296, 4, 1, 1, '451/2021', '2021-04-14', '2021-04-23', '', 2, 5, 1, '2025-04-23 16:27:57', '2025-04-23 16:27:57', NULL, 0, 0),
(297, 4, 1, 1, '467/2021', '2021-04-18', '2021-06-09', '', 4, 5, 1, '2025-04-23 16:41:32', '2025-04-23 16:41:32', NULL, 0, 0),
(298, 4, 1, 1, '468/2021', '2021-04-18', '2021-04-18', '', 2, 5, 1, '2025-04-23 16:46:20', '2025-05-05 09:59:09', NULL, 0, 0),
(299, 4, 1, 3, '17/2021', '2021-04-19', '2021-04-19', '', 4, 5, 1, '2025-04-23 16:51:38', '2025-05-05 09:59:38', NULL, 0, 0),
(300, 3, 1, 3, '18/2021', '2021-04-22', '2021-05-04', '', 4, 5, 1, '2025-04-23 16:56:10', '2025-04-23 16:56:10', NULL, 0, 0),
(301, 4, 1, 3, '19/2021', '2021-04-21', '2021-04-21', '', 2, 5, 1, '2025-04-23 16:59:08', '2025-05-05 10:00:28', NULL, 0, 0),
(302, 4, 1, 3, '20/2021', '2021-04-20', '2021-10-29', '', 4, 5, 1, '2025-04-23 17:01:28', '2025-04-23 17:01:28', NULL, 0, 0);
INSERT INTO `Procedimentos` (`ID`, `SituacaoID`, `TipoID`, `OrigemID`, `NumeroProcedimento`, `DataFato`, `DataInstauracao`, `MotivoAparente`, `EscrivaoID`, `DelegadoID`, `DelegaciaID`, `DataCriacao`, `DataAtualizacao`, `MeioEmpregadoID`, `Dependente`, `Favorito`) VALUES
(303, 4, 1, 3, '22/2021', '2021-04-29', '2021-07-30', '', 4, 5, 1, '2025-04-23 17:04:22', '2025-04-23 17:04:22', NULL, 0, 0),
(304, 4, 1, 1, '520/2021', '2021-05-01', '2021-06-11', '', 4, 5, 1, '2025-04-23 17:06:53', '2025-04-23 17:06:53', NULL, 0, 0),
(305, 7, 1, 1, '512/2021', '2021-05-01', '2021-05-01', '', 2, 5, 1, '2025-04-23 17:10:10', '2025-05-05 10:00:55', NULL, 0, 0),
(306, 4, 1, 1, '23/2021', '2021-05-02', '2021-12-14', '', 2, 5, 1, '2025-04-23 17:13:12', '2025-04-23 17:13:12', NULL, 0, 0),
(307, 4, 1, 1, '595/2021', '2021-05-21', '2021-05-21', '', 2, 5, 1, '2025-04-23 17:18:50', '2025-05-05 10:02:39', NULL, 0, 0),
(308, 4, 1, 1, '621/2021', '2021-05-30', '2021-05-30', '', 2, 5, 1, '2025-04-23 17:22:57', '2025-05-05 10:09:45', NULL, 0, 0),
(309, 4, 1, 1, '622/2021', '2021-05-30', '2021-06-28', '', 4, 5, 1, '2025-04-23 17:26:48', '2025-04-23 17:26:48', NULL, 0, 0),
(310, 7, 1, 1, '675/2021', '2021-06-14', '2021-06-21', '', 4, 5, 1, '2025-04-23 17:29:13', '2025-04-23 17:29:13', NULL, 0, 0),
(311, 4, 1, 1, '774/2021', '2021-07-16', '2021-07-23', '', 2, 5, 1, '2025-04-23 17:34:04', '2025-04-23 17:34:04', NULL, 0, 0),
(312, 4, 1, 1, '31/2021', '2021-08-07', '2021-09-22', '', 2, 5, 1, '2025-04-23 17:36:54', '2025-04-23 17:36:54', NULL, 0, 0),
(313, 4, 1, 1, '860/2021', '2021-08-08', '2021-08-17', '', 2, 5, 1, '2025-04-23 17:41:08', '2025-04-23 17:41:08', NULL, 0, 0),
(314, 4, 1, 1, '32/2021', '2021-08-13', '2021-08-25', '', 4, 5, 1, '2025-04-23 17:45:02', '2025-04-23 17:45:02', NULL, 0, 0),
(315, 4, 1, 1, '33/2021', '2021-08-18', '2021-08-25', '', 2, 5, 1, '2025-04-23 17:47:10', '2025-04-23 17:47:10', NULL, 0, 0),
(316, 4, 1, 1, '34/2021', '2021-08-20', '2021-11-22', '', 4, 5, 1, '2025-04-23 17:49:34', '2025-04-23 17:49:34', NULL, 0, 0),
(317, 4, 1, 3, '35/2021', '2021-09-04', '2021-10-27', '', 4, 5, 1, '2025-04-23 17:53:36', '2025-04-23 17:53:36', NULL, 0, 0),
(318, 4, 1, 1, '37/2021', '2021-09-08', '2021-11-05', '', 4, 5, 1, '2025-04-24 14:27:45', '2025-04-24 14:27:45', NULL, 0, 0),
(319, 7, 1, 3, '38/2021', '2021-09-16', '2021-10-08', 'VÍTIMA É TRANSEXUAL E APÓS REALIZAR UM PROGRAMA SEXUAL COM O AUTOR, ESTE VOLTOU AO LOCAL ONDE A DEIXOU E EFETUOU UM DISPARO DE ARMA DE FOGO EM SEU ROSTO', 2, 5, 1, '2025-04-24 14:32:53', '2025-04-24 14:32:53', NULL, 0, 0),
(320, 4, 1, 1, '39/2021', '2021-09-21', '2021-12-28', '', 2, 5, 1, '2025-04-24 14:36:01', '2025-04-24 14:36:01', NULL, 0, 0),
(321, 7, 1, 3, '40/2021', '2021-09-21', '2021-09-28', '', 4, 5, 1, '2025-04-24 14:41:01', '2025-04-24 14:41:01', NULL, 0, 0),
(322, 4, 1, 1, '1051/2021', '2021-09-22', '2021-10-01', '', 4, 5, 1, '2025-04-24 14:45:38', '2025-04-24 14:45:38', NULL, 0, 0),
(323, 4, 1, 1, '1066/2021', '2021-09-28', '2021-10-06', '', 4, 5, 1, '2025-04-24 14:50:57', '2025-04-24 14:50:57', NULL, 0, 0),
(324, 4, 1, 1, '1079/2021', '2021-10-01', '2021-11-03', '', 2, 5, 1, '2025-04-24 15:03:25', '2025-04-24 15:03:25', NULL, 0, 0),
(325, 4, 1, 1, '1084/2021', '2021-10-03', '2021-10-08', '', 4, 5, 1, '2025-04-24 15:08:32', '2025-04-24 15:08:32', NULL, 0, 0),
(326, 4, 1, 1, '1197/2021', '2021-11-03', '2021-11-05', '', 4, 5, 1, '2025-04-24 15:11:44', '2025-04-24 15:11:44', NULL, 0, 0),
(327, 4, 1, 1, '1209/2021', '2021-11-03', '2021-11-09', '', 4, 5, 1, '2025-04-24 15:18:02', '2025-04-24 15:18:02', NULL, 0, 0),
(328, 4, 1, 1, '1351/2021', '2021-12-01', '2021-12-10', '', 4, 5, 1, '2025-04-24 15:27:11', '2025-04-24 15:27:11', NULL, 0, 0),
(329, 4, 1, 3, '44/2021', '2021-12-06', '0022-02-24', '', 2, 5, 1, '2025-04-24 15:32:58', '2025-04-24 15:32:58', NULL, 0, 0),
(330, 4, 1, 1, '45/2021', '2021-12-08', '2021-04-29', '', 2, 5, 1, '2025-04-24 15:36:11', '2025-04-24 15:36:11', NULL, 0, 0),
(331, 4, 1, 1, '1402/2021', '2021-12-09', '2021-12-17', '', 2, 5, 1, '2025-04-24 15:39:24', '2025-04-24 15:39:24', NULL, 0, 0),
(332, 4, 1, 1, '1398/2021', '2021-12-11', '2021-12-16', '', 2, 5, 1, '2025-04-24 15:44:26', '2025-04-24 15:44:26', NULL, 0, 0),
(333, 4, 1, 1, '1410/2021', '2021-12-12', '2021-12-17', '', 2, 5, 1, '2025-04-24 15:48:14', '2025-04-24 15:48:14', NULL, 0, 0),
(334, 4, 1, 3, '47/2021', '2021-12-14', '2022-01-13', '', 2, 5, 1, '2025-04-24 15:53:46', '2025-04-24 15:53:46', NULL, 0, 0),
(335, 4, 1, 1, '1455/2021', '2021-12-21', '2021-12-29', '', 2, 5, 1, '2025-04-24 16:03:06', '2025-04-24 16:03:06', NULL, 0, 0),
(336, 4, 1, 1, '1464/2021', '2021-12-22', '2021-12-30', '', 2, 5, 1, '2025-04-24 16:08:57', '2025-04-24 16:08:57', NULL, 0, 0),
(337, 7, 1, 1, '1473/2021', '2021-12-25', '2022-01-03', '', 2, 5, 1, '2025-04-24 16:13:16', '2025-04-24 16:13:16', NULL, 0, 0),
(338, 4, 1, 3, '1/2022', '2022-01-04', '2022-05-26', '', 4, 5, 1, '2025-04-24 16:24:06', '2025-04-24 16:24:06', NULL, 0, 0),
(339, 4, 1, 1, '31/2022', '2022-01-09', '2022-01-18', 'AUTOR JÁ AMEAÇAVA A VITIMA ANTES DO CRIME E NO DIA DO FATO O ENCONTROU EM BAR, OCASIAO Q PEGOU UMA FACA E DESFERIU GOLPES DA VÍTIMA', 2, 5, 1, '2025-04-24 16:30:22', '2025-04-24 16:30:22', NULL, 0, 0),
(340, 4, 1, 1, '46/2022', '2022-01-12', '2022-01-21', 'VITIMA TERIA ROUBADO O CELULAR DO AUTOR , O QUAL ENCONTROU COM ELE ALGUNS DIAS DEPOIS E COMO A VITIMA NAO DEVOLVEU O CEULAR, ELE O MATOU', 2, 5, 1, '2025-04-24 16:35:41', '2025-04-24 16:35:41', NULL, 0, 0),
(341, 4, 1, 1, '57/2022', '2022-01-15', '2022-01-24', 'A VITMA É AMIGA DA ESPOSA DO INVESTIGADO, A QUAL FAZ USO DE DROGAS EM SUA CASA E O AUTOR NAO GOSTA', 2, 5, 1, '2025-04-24 16:44:22', '2025-04-24 16:44:22', NULL, 0, 0),
(342, 4, 1, 1, '72/2022', '2022-01-17', '2022-01-26', '', 2, 5, 1, '2025-04-24 16:48:21', '2025-04-24 16:48:21', NULL, 0, 0),
(343, 4, 1, 3, '05/2022', '2022-01-21', '2022-03-30', 'VITIMA INTERVEIO EM UMA BRIGA DE SUA ENTEADA COM O AUTOR, PAI DO FILHO DELA, E FOI ESFAQUEADO PELO AUTOR', 2, 5, 1, '2025-04-24 16:53:54', '2025-04-24 16:53:54', NULL, 0, 0),
(344, 4, 1, 1, '92/2022', '2022-01-23', '2022-02-01', 'GCM PERSEGUIU O INVESTIGADO Q EFETUOU DISPAROS CONTRA OS GUARDAS CIVIS', 2, 5, 1, '2025-04-24 16:59:45', '2025-04-24 16:59:45', NULL, 0, 0),
(345, 4, 1, 1, '128/2022', '2022-01-30', '2022-02-07', 'VÍTIMA E AUTORA SAO USUARIAS DE DROGAS E DISCUTIRAM PQ A VITIMA ACUSOU UMA AMIGA DA AUTORA DE FURTO', 2, 5, 1, '2025-04-24 17:06:08', '2025-04-24 17:06:08', NULL, 0, 0),
(346, 4, 1, 3, '08/2022', '2022-01-31', '2022-04-29', 'VÍITMA É USUARIO DE DROGAS E FOI ESFAQUEADO POR AUTOR AINDA DESCONHECIDO EM UMA BOCA DE FUMO - COTA MINISTERIAL', 2, 5, 1, '2025-04-25 14:49:29', '2025-04-25 14:49:29', NULL, 0, 0),
(347, 7, 1, 3, '11/2022', '2022-02-11', '2022-03-03', '', 2, 5, 1, '2025-04-25 15:09:04', '2025-04-25 15:09:04', NULL, 0, 0),
(348, 4, 1, 3, '13/2022', '2022-03-08', '2022-05-10', '', 2, 5, 1, '2025-04-25 15:15:52', '2025-04-25 15:15:52', NULL, 0, 0),
(349, 4, 1, 3, '14/2022', '2022-03-13', '2022-04-08', 'VÍTIMA PAGOU PARA AUTOR PARA TENTAR CONTRA SUA VIDA A FIM DE SUA NAMORADA FICAR COM DÓ E REATAR COM ELE\r\n(DISPARO DE ARMA DE FOGO, PORTE ILEGAL DE DE ARMA DE FOGO DE USO PERMITIDO, POSSE IRREGULAR DE ARMA DE FOGO DE USO PERMITIDO)', 2, 5, 1, '2025-04-25 15:22:52', '2025-04-25 15:22:52', NULL, 0, 0),
(350, 4, 1, 3, '16/2022', '2022-03-14', '2022-03-24', 'GIH ACIONADO NA MADRUGADA, NÃO TEVE LOCAL, POIS FOI ENCAMINHADO PARA UPA; FOI ATRAS DO AUTOR E CONSEGUIU OBTER ÊXITO NO FLAGRANTE; APREENDIDA KAMILLA CONFESSOU TER ENTREGUE A FACA PARA MATEUS MATÁ-LO. QUE MATEUS ESFAQUEOU DEPOIS FUGIU COM KAMILLA, SENDO ENCONTRADO HORAS DEPOIS PELA MANHÃ.', 4, 5, 1, '2025-04-25 15:27:37', '2025-04-25 15:27:37', NULL, 0, 0),
(351, 4, 1, 1, '348/2022', '2022-03-20', '2022-03-23', 'VÍTIMAS E AUTORES SAO VIZINHOS E DISCUTIRAM DEVIDO O SOM ALTO E OS AUTORES PEGARAM UMA ARMA DE FOGO E EFETUARAM DISPAROS NA DIREÇAO DA CASA DAS VITIMAS, ACERTANDO O MURO', 2, 5, 1, '2025-04-25 15:32:31', '2025-04-25 15:32:31', NULL, 0, 0),
(352, 4, 1, 3, '17/2022', '2022-03-20', '2022-09-27', 'VÍTIMA E PROPRIETARIA DE UM BAR E DISCUTIU COM UM CLIENTE. E DURANTE A DISCUSSÃO O AUTOR ESFAQUEOU A VITIMA QUE VEIO A OBITO NO LOCAL', 4, 5, 1, '2025-04-25 15:38:32', '2025-04-25 15:38:32', NULL, 0, 0),
(353, 4, 1, 1, '393/2022', '2022-03-27', '2022-03-24', 'VÍTIMA E AUTOR TINHAM JÁ TINHAM UM DESENTEDIMENTO ANTIGO E NO DIA DO CRIME, A VITIMA FOI EM CASA, PEGOU UMA FACA E VOLTOU AO BAR E ACERTOU O AUTOR. O AUTOR TOMOU A FACA DA VITIMA E O ESFAQUEOU, VINDO A OBITO NO LOCAL', 2, 5, 1, '2025-04-25 15:46:15', '2025-04-25 15:46:15', NULL, 0, 0),
(354, 4, 1, 1, '516/2022', '2022-04-25', '2022-05-13', 'O AUTOR NÃO POSSUI CNH, ESTAVA EMBRIAGADO E NO MOMENTO DOS FATOS COLIDIU O VEÍCULO, OCASIONANDO UMA VÍTIMA FATAL', 2, 5, 1, '2025-04-25 15:54:19', '2025-04-25 15:54:19', NULL, 0, 0),
(355, 4, 1, 3, '19/2022', '2022-04-24', '2022-05-05', 'VITIMA E AUTOR ERAM NAMORADOS E BRIGARAM NA NOITE ANTERIOR AO CRIME E FORAM PARA UM MATAGAL E O AUTOR VOLTOU SOZINHO E A VITIMA FOI ENCONTRADA EM UM LIXAO COM UMA LESAO NA CABEÇA', 4, 5, 1, '2025-04-25 15:59:37', '2025-04-25 15:59:37', NULL, 0, 0),
(356, 7, 1, 3, '21/2022', '2022-05-11', '2022-05-25', 'CONFORNTO POLICIAL', 4, 5, 1, '2025-04-25 16:06:28', '2025-04-25 16:06:28', NULL, 0, 0),
(357, 4, 1, 1, '630/2022', '2022-05-22', '2022-06-15', 'AUTOR EMBRIAGADO COLIDIU COM 06 VEICULOS E ESTAVA EM UMA AMAROK E ACABOU PRENSANDO A VITIMA NO MEIO DOS CARROS QUE NAO RESISTIU E MORREU NA HORA', 4, 5, 1, '2025-04-25 16:13:00', '2025-04-25 16:13:00', NULL, 0, 0),
(358, 4, 1, 3, '23/2022', '2022-05-20', '2022-09-27', 'DISCUSSAO DE COMERCIO/AUTOR CHEGOU NA PORTA DA CASA DAS VITIMAS E EFETUOU SEIS DISPAROS DE ARMA DE FOGO EM DIREÇÃO A RESIDENCIA, MAS NAO ALVEJOU NINGUEM', 4, 5, 1, '2025-04-25 16:26:45', '2025-04-25 16:26:45', NULL, 0, 0),
(359, 4, 1, 3, '25/2022', '2022-06-06', '2023-02-24', 'VITIMA ESTAVA EM UM BAR E FOI ALVEJADA POR UM INDIVIDUO QUE CHEGOU EM UMA MOTOCICLETA E EFETUOU DISPAROS DE ARMA DE FOGO EM SUA DIREÇÃO, VINDO A OBITO NO LOCAL', 2, 5, 1, '2025-04-25 16:34:24', '2025-04-25 16:34:24', NULL, 0, 0),
(360, 7, 1, 3, '27/2022', '2022-06-16', '2022-12-27', 'VÍTIMA ESTAVA EM UMA FESTA QUANDO DISCUTIU COM OS AUTORES POR ESTAREM ATIRANDO PARA CIMA , MOMENTO QUE FOI ALVEJADO NO PEITO. CONSTA AINDA QUE OS DISPAROS ATINGIRAM OUTRA PESSOA', 2, 5, 1, '2025-04-25 16:40:42', '2025-04-25 16:40:42', NULL, 0, 0),
(361, 7, 1, 1, '760/2022', '2022-06-22', '2022-07-20', 'VÍTIMA HAVIA SAÍDO CEDO E COMO NÃO RETORNOU PARA SUA RESIDÊNCIA, FAMILIARES  ACIONARAM A EMPRESA DO RASTREADOR DO VEÍCULO E ACIOARAM A EQUIPE DE POLICIAMENTO QUE AO CHEGAR NO REFERIDO LOCAL ENCONTROU O VEÍCULO E EM SEGUIDA O CORPO DA VÍTIMA FOI ENCONTRADO PRÓXIMO A UMA FAZENDA QUE A VÍTIMA HAVIA COMPRADO RECENTEMENTE', 2, 5, 1, '2025-04-25 16:48:37', '2025-04-25 16:48:37', NULL, 0, 0),
(362, 4, 1, 3, '30/2022', '2022-06-27', '2022-07-27', 'DESENTENDIMENTO ENTRE A VÍTIMA E O SUPOSTO AUTOR', 4, 5, 1, '2025-04-25 16:51:56', '2025-04-25 16:51:56', NULL, 0, 0),
(363, 4, 1, 3, '42/2022', '2022-06-29', '2022-06-29', 'A VÍTIMA FOI ENCONTRADA SEM VIDA DENTRO DE SUA RESIDÊNCIA, E ALGUNS PERTENCES DO LOCAL HAVIAM SIDO SUBTRAÍDOS - GEPATRI', 2, 5, 1, '2025-04-25 17:11:11', '2025-05-05 09:52:09', NULL, 0, 0),
(364, 4, 1, 1, '846/2022', '2022-07-15', '2022-11-30', 'VÍTIMA DISCUTIU DIAS ATRÁS DO FATO COM O AUTOR', 2, 5, 1, '2025-04-25 17:21:55', '2025-04-25 17:21:55', NULL, 0, 0),
(365, 4, 1, 3, '34/2022', '2022-07-27', '2022-07-27', '', 4, 5, 1, '2025-04-25 17:39:14', '2025-05-05 09:52:49', NULL, 0, 0),
(366, 4, 1, 1, '2506316553', '2025-04-27', '2025-04-28', 'DESACORDO DURANTE PROGRAMA SEXUAL', 2, 5, 1, '2025-04-28 09:37:30', '2025-05-21 16:13:02', NULL, 0, 0),
(367, 4, 1, 1, '1037/2022', '2022-08-29', '2022-09-06', 'RIXA ENTRE AUTOR E VÍTIMA, POIS O AUTOR GOSTARIA DE COMERCIALIZAR DROGAS NO ESTABELECIEMNTO DA VÍTIMA, A QUAL NÃO PERMITIU', 2, 5, 1, '2025-04-28 14:28:26', '2025-04-28 14:28:26', NULL, 0, 0),
(368, 4, 1, 3, '39/2022', '2022-09-02', '2022-06-28', '', 2, 5, 1, '2025-04-28 14:32:11', '2025-04-28 14:32:11', NULL, 0, 0),
(369, 4, 1, 3, '40/2022', '2022-09-06', '2022-11-30', 'A VÍTIMA TRABALHAVA NA CHÁCARA MENCIONADA PARA A NAMORADA DO AUTOR, POREM NO MOMENTO EM QUE O AUTOR \"DEU UMA ORDEM\" PARA QUE A VÍTIMA CUMPRISSE, ESTA INFORMOU QUE NÃO CUMPRIRIA, POIS RECEBIA ORDENS APENAS DE SUA PATROA', 2, 5, 1, '2025-04-28 14:36:07', '2025-04-28 14:36:07', NULL, 0, 0),
(370, 4, 1, 1, '1128/2022', '2022-09-21', '2022-10-04', 'A VÍTIMA ESTAVA EM SUA VENDA QUANDO O AUTOR CHEGOU EMBRIAGADO SOLICITANDO BEBIDA ALCOÓLICA, PORÉM COMO NÃO HOUVE ÊXITO EM SEU PEDIDO, EFETUOU UM DISPARO DE ARMA DE FOGO EM DESFAVOR DA VÍTIMA E POSTERIORMENTE EVADIU-SE DO LOCAL', 2, 5, 1, '2025-04-28 14:41:17', '2025-04-28 14:41:17', NULL, 0, 0),
(371, 4, 1, 1, '1135/2022', '2022-09-22', '2022-10-03', 'AUTOR ATIROU NA ESPOSA AO CHEGAR EM CASA EMBRIAGADO, FORAM APREENDIDAS UM REVOLVER E UMA ESPINGARDA', 4, 5, 1, '2025-04-28 14:56:18', '2025-04-28 14:56:18', NULL, 0, 0),
(372, 4, 1, 1, '1184/2022', '2022-10-05', '2022-10-13', '', 2, 5, 1, '2025-04-28 15:00:55', '2025-04-28 15:00:55', NULL, 0, 0),
(373, 4, 1, 1, '1231/2022', '2022-10-18', '2022-10-26', 'AS VÍTIMAS ESTAVAM NO BAR AMERICA BEAR, NO MOMENTO EM QUE HOUVE UM DESENTENDIMENTO ENTRE AS PARTES E EM SEGUIDA O AUTOR EM POSSE DE ARMA FOGO EFETUOU UM DISPARO EM DESFAVOR DAS VÍTIMAS', 4, 5, 1, '2025-04-28 15:08:56', '2025-04-28 15:08:56', NULL, 0, 0),
(374, 4, 1, 3, '44/2022', '2022-11-04', '2022-11-11', '', 2, 5, 1, '2025-04-28 15:14:16', '2025-04-28 15:14:16', NULL, 0, 0),
(375, 4, 1, 3, '45/2022', '2022-11-07', '2023-03-17', 'A VÍTIMA ESTAVA EM UM BAR, E APÓS DISCUSSÃO COM O AUTOR SOFREU GOLPES DE ARMA BRANCA, LOCAL ONDE VEIO A ÓBITO', 2, 5, 1, '2025-04-28 15:25:37', '2025-04-28 15:25:37', NULL, 0, 0),
(376, 4, 1, 1, '1351/2022', '2022-01-12', '2023-01-21', 'MOTIVO APARENTE NÃO IDENTIFICADO', 2, 5, 1, '2025-04-28 15:30:49', '2025-04-28 15:30:49', NULL, 0, 0),
(377, 4, 1, 1, '1386/2022', '2022-11-21', '2022-12-16', 'O AUTOR DISSE QUE A VÍTIMA ESTAVA  DESOBEDECENDO E POR ESTE MOTIVO,  JOGOU A VÍTIMA SOBRE A CAMA NA INTENÇÃO DE QUE A CRIANÇA PERMANECESSE NO LOCAL QUIETO., OCASIÃO EM QUE A VÍTIMA VEIO A ÓBITO APÓS AS AGRESSÕS', 4, 5, 1, '2025-04-28 15:36:01', '2025-04-28 15:36:01', NULL, 0, 0),
(378, 4, 1, 1, '1455/2022', '2022-12-05', '2023-01-02', 'VITIMA E USUARIO DE DROGAS E FOI COMPRAR DROGAS DO AUTOR E SE DESETENDERAM', 4, 5, 1, '2025-04-28 15:39:06', '2025-04-28 15:39:06', NULL, 0, 0),
(379, 4, 1, 1, '1477/2022', '2022-12-12', '2022-12-20', 'A VÍTIMA ESTAVA EM UMA FESTA NO MOMENTO EM QUE DISCUTIU COM O AUTOR. VALE RESSALTAR QUE  SEGUNDO OS AUTORES A VÍTIMA É SUA RIVAL NO BAIRO PROMISSÃO', 4, 5, 1, '2025-04-28 15:42:20', '2025-04-28 15:42:20', NULL, 0, 0),
(380, 4, 1, 1, '1498/2022', '2022-12-15', '2023-01-09', 'O AUTOR TINHA UM RELACIONAMENTO COM A VÍTIMA ELAINE BARBOSA E AO CHEGAR EM SUA RESIDÊNCIA, EFETUOU DIVERSOS DISPAROS EM DESFAVOR DAS REFERIDAS VÍTIMAS', 2, 5, 1, '2025-04-28 15:47:06', '2025-04-28 15:47:06', NULL, 0, 0),
(381, 4, 1, 3, '49/2022', '2022-12-15', '2022-05-04', 'A VÍTIMA FOI A CASA DE KAROLLYNE ENCONTRAR SUA NAMORADA QUE ESTAVA AO LOCAL, PORÉM NO MOMENTO EM QUE A VÍTIMA FOI ATENDIDO NO PORTÃO DA REFERIDA RESIDÊNCIA, SOFREU DISPAROS DE ARMA DE FOGO EM SEU DESFAVOR,  VÍTIMA FOI ALVEJADA DIA 14/12/2022 E VEIO A OBITO NO HOSPITAL DIA 21/12/2022', 2, 5, 1, '2025-04-28 15:53:03', '2025-04-28 15:53:03', NULL, 0, 0),
(382, 4, 1, 1, '1528/2022', '2022-12-20', '2022-12-29', 'CADAVER FOI ENCONTRADO ENTERRADO NO FUNDO DE UMA RESIDENCIA', 4, 5, 1, '2025-04-28 15:57:25', '2025-04-28 15:57:25', NULL, 0, 0),
(383, 4, 1, 1, '1550/2022', '2022-01-26', '2023-01-03', 'A  TESTEMUNHA VIU O MOMENTO EM QUE O AUTOR A VÍTIMA ENTRARAM EM VIAS DE FATO E APÓS A VÍTIMA FICAR DESACORDADA, SAÍRAM DO LOCAL TOMANDO RUMO IGNORADO', 2, 5, 1, '2025-04-28 16:02:45', '2025-04-28 16:02:45', NULL, 0, 0),
(384, 7, 1, 3, '2/2023', '2023-01-02', '2023-07-26', '', 2, 5, 1, '2025-04-28 16:07:49', '2025-04-28 16:07:49', NULL, 0, 0),
(385, 4, 1, 1, '03/2020', '2020-01-16', '2020-03-25', '', 2, 5, 1, '2025-04-28 16:55:30', '2025-04-28 16:55:30', NULL, 0, 0),
(386, 4, 1, 1, '1205/2020', '2020-10-22', '2020-10-28', '', 4, 5, 1, '2025-04-28 17:13:41', '2025-04-28 17:13:41', NULL, 0, 0),
(387, 4, 1, 1, '41/2020', '2020-11-08', '2021-09-14', '', 4, 5, 1, '2025-04-28 17:26:53', '2025-04-28 17:26:53', NULL, 0, 0),
(388, 4, 1, 1, '270/2021', '2021-07-26', '2021-03-05', '', 4, 5, 1, '2025-04-28 17:34:04', '2025-04-28 17:34:04', NULL, 0, 0),
(389, 4, 1, 1, '753/2021', '2021-07-11', '2021-07-11', '', 2, 5, 1, '2025-04-28 17:44:38', '2025-05-05 09:48:43', NULL, 0, 0),
(390, 6, 1, 3, '02/2020', '2020-01-13', '2020-08-29', '', 4, 5, 1, '2025-04-29 14:38:17', '2025-04-29 14:38:17', NULL, 0, 0),
(391, 1, 1, 3, '39/2020', '2020-10-27', '2020-10-30', '', 4, 5, 1, '2025-04-29 14:44:36', '2025-04-29 14:44:36', NULL, 0, 0),
(392, 4, 1, 1, '50/2020', '2020-12-19', '2020-12-29', '', 2, 5, 1, '2025-04-29 14:47:36', '2025-04-29 14:47:36', NULL, 0, 0),
(393, 4, 1, 1, '51/2020', '2020-12-20', '2020-12-20', '', 2, 5, 1, '2025-04-29 14:51:54', '2025-05-05 09:29:59', NULL, 0, 0),
(394, 1, 1, 3, '06/2021', '2021-01-30', '2021-01-30', 'VÍITIMA NAO TEM PASSAGEM E FOI ENCONTRADA EM UMA ESTRADA DE TERRA COM DOIS DISPAROS DE ARMA DE FOGO NA REGIÃO DA CABEÇA.', 2, 5, 1, '2025-04-29 14:57:01', '2025-05-05 09:30:29', NULL, 0, 0),
(395, 1, 1, 3, '13/2021', '2021-03-22', '2021-03-22', '', 4, 5, 1, '2025-04-29 14:59:18', '2025-05-05 09:30:59', NULL, 0, 0),
(396, 1, 1, 3, '21/2021', '2021-04-29', '2021-04-29', '', 4, 5, 1, '2025-04-29 15:00:53', '2025-05-05 09:31:25', NULL, 0, 0),
(397, 1, 1, 3, '29/2021', '2021-07-20', '2021-07-20', 'ENCONTRO DE CADAVER', 4, 5, 1, '2025-04-29 15:04:01', '2025-05-05 09:32:28', NULL, 0, 0),
(398, 1, 1, 1, '28/2021', '2021-07-12', '2021-07-12', '', 4, 5, 1, '2025-04-29 15:05:27', '2025-05-05 09:32:07', NULL, 0, 0),
(399, 1, 1, 1, '48/2021', '2021-12-23', '2021-04-29', '', 4, 5, 1, '2025-04-29 15:09:07', '2025-04-29 15:09:07', NULL, 0, 0),
(400, 1, 1, 3, '50/2021', '2021-12-24', '2021-12-24', '', 2, 5, 1, '2025-04-29 15:14:00', '2025-05-05 09:33:03', NULL, 0, 0),
(401, 1, 1, 3, '02/2022', '2021-01-02', '2021-01-02', 'AS VITIMAS E AUTORES SAO VIZINHOS E OS VIZINHOS INVADIRAM A CASA DA VITMA E QUEBRARAM MOVEIS', 4, 5, 1, '2025-04-29 15:18:02', '2025-05-05 09:35:34', NULL, 0, 0),
(402, 1, 1, 3, '06/2022', '2022-01-21', '2022-01-29', 'VITIMA XINGOU UMA MULHER NO BAR E O AUTOR INTERVEIO NA BRIGA E O ESFAQUEOU', 4, 5, 1, '2025-04-29 15:22:46', '2025-04-29 15:22:46', NULL, 0, 0),
(403, 1, 1, 3, '09/2022', '2022-02-02', '2022-02-02', 'VÍTIMA ESTAVA RECOLHIDO NA CPP DE RIO VERDE E APOS COMSEGUIR SEU ALVARA DE SOLTURA FOI LEVADO PARA O CAT DE RIO VERDE E DEPOIS PARA JATAI, SENDO QUE SOFREU UMA PARADA CARDÍACA ANTES DE CHEGAR EM JATAI E VEIO A OBITO NO HOSPITAL DE JATAI.', 2, 5, 1, '2025-04-29 15:27:56', '2025-05-05 09:40:16', NULL, 0, 0),
(404, 1, 1, 3, '10/2022', '2022-02-09', '2022-02-19', 'VÍTIMA E USUARIA DE DROGAS E FOI ALVEJADA POR INDIVIDUO QUE ESTAVA EM UM VEICULO', 2, 5, 1, '2025-04-29 15:31:27', '2025-04-29 15:31:27', NULL, 0, 0),
(405, 1, 1, 3, '18/2022', '2022-03-31', '2022-04-10', 'VÍTIMA FOI ALVEJADA POR 5 DISPAROS DE ARMA DE FOGO, É CATADORA DE LATINHAS E USUARIO DE DROGAS', 2, 5, 1, '2025-04-29 15:35:58', '2025-04-29 15:35:58', NULL, 0, 0),
(406, 1, 1, 3, '20/2022', '2022-04-30', '2022-04-09', 'VÍTIMA PARTICIPAVA DE UM EVENTO NO CLUBE DO LAÇO, OCASIAO QUE FOI ALVEJADO POR DISPAROS DE ARMA DE FOGO DURANTE A FESTA', 2, 5, 1, '2025-04-29 15:41:16', '2025-04-29 15:41:16', NULL, 0, 0),
(407, 1, 1, 3, '22/2022', '2022-05-18', '2022-05-27', '', 4, 5, 1, '2025-04-29 15:46:18', '2025-04-29 15:46:18', NULL, 0, 0),
(408, 1, 1, 3, '29/2022', '2022-06-27', '2022-06-06', 'USUÁRIA DE DROGAS, A QUAL RECEBIA VÁRIOS PARCEIROS EM SUA CASA', 4, 5, 1, '2025-04-29 15:49:30', '2025-04-29 15:49:30', NULL, 0, 0),
(409, 1, 1, 3, '32/2022', '2022-07-20', '2022-07-29', 'VITIMA É ENCONTRADA DENTRO DE SUA RESIDÊNCIA SEM VIDA,SEGURANDO UMA ARMA BRANCA (FACA)', 2, 5, 1, '2025-04-29 15:55:17', '2025-04-29 15:55:17', NULL, 0, 0),
(410, 1, 1, 3, '33/2022', '2022-07-27', '2022-08-05', 'VÍTIMA USUÁRIA DE DROGAS ENCONTRADA EM VIA PÚBLICA COM LESÕES DE FACA', 4, 5, 1, '2025-04-29 15:59:58', '2025-04-29 15:59:58', NULL, 0, 0),
(411, 1, 1, 3, '36/2022', '2022-08-08', '2022-08-17', 'A VÍTIMA VEIO A ÓBITO EM DECORRÊNCIADE INTERVENÇÃO POLICIAL DURANTE O CONFRONTO ARAMDO,CONFORME APURADO NOS AUTOS DOS AGENTES ENVOLVIDOS', 4, 5, 1, '2025-04-29 16:09:47', '2025-04-29 16:09:47', NULL, 0, 0),
(412, 1, 1, 3, '37/2022', '2022-08-08', '2022-08-17', 'A VÍTIMA FOI ENCONTRADA MORTA NO INTERIOR DE SUA RESIDÊNCIA', 2, 5, 1, '2025-04-29 16:18:38', '2025-04-29 16:18:38', NULL, 0, 0),
(413, 1, 1, 3, '48/2022', '2022-12-02', '2022-12-11', 'A VÍTIMA SUSPEITA QUE SEUS FAMILIARES ESTEJAM ADMINISTRANDO SUBSTÂNCIAS TÓXICAS,POSSIVELMENTE METAIS PESADOS COM A INTENÇÃO DE CAUSAR SUA MORTE E,ASSIM,TOMAR POSSE DE SUA PARTE NA HERANÇA', 2, 5, 1, '2025-04-29 16:29:30', '2025-04-29 16:29:30', NULL, 0, 0),
(414, 1, 1, 3, '51/2022', '2022-12-22', '2022-12-31', 'A VÍTIMA SE ENVOLVEU EM UMA BRIGA E,NA OCASIÃO,TERIA SUBTRAÍDO UMA ARMA DE FOGO,SENDO ESSE I POSSÍVEL MOTIVO DOS FATOS OCORRIDOS', 2, 5, 1, '2025-04-29 16:38:47', '2025-04-29 16:38:47', NULL, 0, 0),
(415, 1, 1, 3, '1/2023', '2023-01-02', '2023-01-11', 'A VÍTIMA PARTICIPAVA DE UMA CONFRATENIZAÇÃO QUANDO SE DESENTENDEU COM O AUTOR,VINDO SER ATINGIDA POR GOLPE DE ARMA BRANCA EM SEGUIDA', 4, 5, 1, '2025-04-29 17:01:49', '2025-04-29 17:01:49', NULL, 0, 0),
(416, 1, 1, 3, '08/2023', '2023-01-25', '2023-02-03', 'A VÍTIMA FOI AGREDIDA POR DOIS INDIVÍDUOS NO ESTACIONAMENTO DA FACULDADE ALMEIDA RODRIGUES', 2, 5, 1, '2025-04-29 17:07:16', '2025-04-29 17:07:16', NULL, 0, 0),
(417, 1, 1, 3, '11/2023', '2023-02-09', '2023-02-18', 'A VÍTIMA ESTAVA TRABALHANDO HAVIA UMA SEMANA NA FAZENDA DO INVESTIGADO, PORÉM APÓS TRÊS DIAS DE TRABALHO PEDIU DEMISSÃO E APÓS DESENTENDIMENTO O AUTOR PEGOU SUA ESPINGARDA E EFETUOU DISPAROS EM DESFAVOR DA VÍTIMA', 4, 5, 1, '2025-04-29 17:13:07', '2025-04-29 17:13:07', NULL, 0, 0),
(418, 1, 1, 3, '12/2023', '2023-02-24', '2023-03-05', '', 2, 5, 1, '2025-04-29 17:17:23', '2025-04-29 17:17:23', NULL, 0, 0),
(419, 1, 1, 3, '21/2023', '2023-04-18', '2023-04-27', '', 4, 5, 1, '2025-04-29 17:21:13', '2025-04-29 17:21:13', NULL, 0, 0),
(420, 1, 1, 3, '23/2023', '2023-05-30', '2023-06-08', '', 2, 5, 1, '2025-04-29 17:27:54', '2025-04-29 17:27:54', NULL, 0, 0),
(421, 1, 1, 3, '25/2023', '2023-06-21', '2023-06-30', '', 2, 5, 1, '2025-04-29 17:31:41', '2025-04-29 17:31:41', NULL, 0, 0),
(422, 1, 1, 3, '28/2023', '2023-07-08', '2023-07-18', 'SEGUNDA CONSTA A VÍTIMA ESTAVA B~EBADA INCOMODANDO OS CLIENTES E FOI AGREDIDA PELA PROPRIETÁRIA DO BAR E POR SEU EXCOMPANHEIRO', 4, 5, 1, '2025-04-29 17:35:15', '2025-04-29 17:35:15', NULL, 0, 0),
(423, 1, 1, 1, '1033/2023', '2023-08-21', '2023-08-30', '', 4, 5, 1, '2025-04-29 17:40:00', '2025-04-29 17:40:00', NULL, 0, 0),
(424, 1, 1, 3, '34/2023', '2023-08-28', '2023-09-06', '', 4, 5, 1, '2025-04-29 17:47:14', '2025-04-29 17:47:14', NULL, 0, 0),
(425, 1, 1, 3, '39/2023', '2023-10-03', '2023-10-12', 'VITIMAS ESTAVAM EM BAR E ENTRARAM EM VIAS DE FATO COM O AUTOR', 2, 5, 1, '2025-04-30 14:46:03', '2025-04-30 14:46:03', NULL, 0, 0),
(426, 1, 1, 3, '44/2023', '2023-11-02', '2023-11-11', 'VITIMA E AUTORES SE DESENTENDERAM APOS UM ACIDENTE DE TRANSITO', 2, 5, 1, '2025-04-30 14:51:06', '2025-04-30 14:51:06', NULL, 0, 0),
(427, 1, 1, 3, '32/2023', '2023-08-02', '2023-08-11', '', 4, 5, 1, '2025-04-30 14:54:13', '2025-04-30 14:54:13', NULL, 0, 0),
(428, 1, 1, 3, '2506332193', '2025-05-13', '2025-05-15', 'UMA EQUIPE DA POLÍCIA MILITAR (CPE) AO TENTAR REALIZAR A ABORDAGEM DE UM VEÍCULO VW/GOL, COR BRANCA, PLACAS KEO-9830, FOI SURPREENDIDA COM O DESEMBARQUE DO MOTORISTA COM O VEÍCULO AINDA EM MOVIMENTO E DESFERINDO DISPAROS DE ARMA DE FOGO CONTRA A GUARNIÇÃO, TORNANDO NECESSÁRIO O REVIDE DA INJUSTA AGRESSÃO, OCASIONANDO A MORTE DE ZILIOMAR DA SILVA FERREIRA', 2, 5, 1, '2025-05-15 10:12:01', '2025-05-15 10:12:01', NULL, 0, 0),
(430, 4, 1, 3, 'teste', '2025-05-16', '2025-05-16', 'TESTE', 2, 5, 1, '2025-05-16 09:06:46', '2025-05-16 09:07:21', NULL, 0, 0),
(431, 4, 1, 3, '15/2022', '2022-03-11', '2022-03-14', 'DESENTENDIMENTO COM A EX ESPOSA.', 4, 5, 1, '2025-05-21 15:26:05', '2025-05-21 15:26:05', NULL, 0, 0),
(432, 5, 1, 3, '106/2017', '2017-11-20', '2017-11-21', 'BRIGA ENTRE COLEGAS FAZENDO USO DE BEBIDA ALCOÓLICA.', 4, 5, 1, '2025-05-21 15:38:40', '2025-05-21 15:38:40', NULL, 0, 0),
(433, 4, 1, 3, '08/2018', '2017-05-16', '2017-05-16', 'MORTE POR INTERVENÇÃO POLICIAL', 4, 5, 1, '2025-05-22 09:44:20', '2025-05-22 09:44:20', NULL, 0, 0),
(434, 7, 1, 3, '55/2018', '2018-07-03', '2018-07-03', '.', 4, 5, 1, '2025-05-22 11:16:32', '2025-05-22 11:16:32', NULL, 0, 0),
(435, 6, 1, 3, '59/2015', '2015-05-25', '2015-05-28', '.', 4, 5, 1, '2025-05-22 11:22:47', '2025-05-22 11:22:47', NULL, 0, 0),
(436, 4, 1, 3, '04/2022', '2022-01-21', '2022-01-21', '.', 4, 5, 1, '2025-05-22 11:26:27', '2025-05-22 11:26:27', NULL, 0, 0),
(437, 7, 1, 3, '131/2013', '2013-11-21', '2013-11-21', '.', 4, 5, 1, '2025-05-22 11:30:25', '2025-05-22 11:30:25', NULL, 0, 0),
(438, 4, 1, 3, '06/2023', '2023-01-23', '2023-01-23', 'BRIGA EM BAR ENTRE PARENTES.', 4, 5, 1, '2025-05-22 11:54:00', '2025-05-22 11:54:00', NULL, 0, 0),
(439, 4, 1, 3, '03/2023', '2023-01-18', '2023-01-18', 'MORTE POR INTERVENÇÃO POLICIAL.', 4, 5, 1, '2025-05-22 17:03:47', '2025-05-22 17:03:47', NULL, 0, 0),
(440, 10, 2, 2, '2510339531', '2025-05-22', '2025-05-23', 'SUICIDIO', 4, 5, 1, '2025-05-23 09:14:55', '2025-05-23 09:14:55', NULL, 0, 0),
(441, 4, 1, 1, '2506339351', '2025-05-22', '2025-05-22', 'DISCUSSÃO ENTRE USUÁRIOS DE DROGAS.', 4, 5, 1, '2025-05-23 09:44:54', '2025-05-30 11:37:39', NULL, 0, 0),
(442, 4, 1, 1, '2506340553', '2025-05-23', '2025-05-23', 'IGNORADO.', 2, 5, 1, '2025-05-26 10:51:17', '2025-05-30 11:45:15', NULL, 0, 0),
(443, 1, 1, 1, '2506342886', '2025-05-26', '2025-05-27', '', 4, 5, 1, '2025-05-29 14:37:31', '2025-05-29 14:37:31', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ProcedimentosMeiosEmpregados`
--

CREATE TABLE `ProcedimentosMeiosEmpregados` (
  `ID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `MeioEmpregadoID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ProcedimentosMeiosEmpregados`
--

INSERT INTO `ProcedimentosMeiosEmpregados` (`ID`, `ProcedimentoID`, `MeioEmpregadoID`) VALUES
(487, 55, 10),
(488, 56, 11),
(489, 57, 11),
(490, 58, 11),
(491, 59, 10),
(492, 60, 10),
(493, 61, 10),
(494, 62, 11),
(495, 63, 10),
(496, 64, 20),
(497, 65, 10),
(498, 66, 10),
(499, 67, 10),
(500, 68, 11),
(501, 69, 10),
(502, 70, 21),
(503, 71, 11),
(504, 72, 11),
(505, 73, 11),
(506, 74, 10),
(507, 75, 15),
(508, 76, 11),
(509, 77, 11),
(510, 78, 11),
(511, 79, 10),
(512, 80, 10),
(513, 81, 10),
(514, 82, 10),
(515, 83, 11),
(516, 84, 20),
(517, 85, 10),
(518, 86, 10),
(519, 87, 11),
(520, 88, 20),
(521, 89, 11),
(522, 90, 11),
(523, 91, 11),
(524, 92, 15),
(525, 93, 11),
(526, 94, 11),
(527, 94, 15),
(528, 95, 11),
(529, 96, 10),
(530, 97, 16),
(531, 98, 12),
(532, 99, 10),
(533, 100, 11),
(534, 100, 12),
(535, 101, 11),
(536, 102, 11),
(537, 103, 21),
(538, 104, 11),
(539, 105, 10),
(540, 106, 11),
(541, 107, 11),
(542, 108, 21),
(543, 109, 11),
(544, 109, 15),
(545, 110, 10),
(546, 110, 12),
(547, 111, 10),
(548, 112, 11),
(549, 113, 15),
(550, 114, 11),
(551, 115, 10),
(552, 116, 11),
(553, 117, 10),
(554, 118, 11),
(555, 119, 10),
(556, 120, 20),
(557, 121, 10),
(558, 122, 10),
(559, 123, 11),
(560, 124, 10),
(561, 125, 20),
(562, 126, 11),
(563, 127, 10),
(564, 128, 10),
(565, 129, 11),
(566, 130, 22),
(567, 131, 10),
(568, 132, 11),
(569, 133, 13),
(570, 134, 11),
(571, 135, 10),
(572, 136, 11),
(573, 137, 10),
(574, 138, 10),
(575, 139, 10),
(576, 140, 10),
(577, 141, 12),
(578, 142, 10),
(579, 143, 10),
(580, 144, 15),
(581, 145, 12),
(582, 146, 20),
(583, 147, 10),
(584, 148, 11),
(585, 149, 10),
(586, 150, 10),
(587, 151, 23),
(588, 152, 11),
(589, 153, 12),
(590, 154, 10),
(591, 155, 12),
(592, 156, 10),
(593, 157, 23),
(594, 158, 24),
(595, 159, 10),
(596, 160, 10),
(597, 161, 12),
(598, 162, 11),
(599, 163, 11),
(600, 164, 11),
(601, 165, 11),
(602, 166, 11),
(603, 167, 12),
(604, 168, 10),
(605, 169, 20),
(606, 170, 10),
(607, 171, 10),
(608, 172, 10),
(609, 173, 23),
(610, 174, 11),
(611, 175, 11),
(612, 176, 20),
(613, 177, 20),
(614, 178, 21),
(615, 179, 20),
(616, 180, 20),
(617, 181, 11),
(618, 182, 21),
(619, 183, 10),
(620, 184, 10),
(621, 185, 10),
(622, 186, 12),
(623, 187, 20),
(624, 188, 11),
(625, 189, 10),
(626, 190, 11),
(627, 191, 11),
(628, 192, 10),
(629, 193, 10),
(630, 194, 10),
(631, 195, 11),
(632, 196, 10),
(633, 197, 10),
(634, 198, 20),
(635, 199, 10),
(636, 200, 10),
(637, 201, 20),
(638, 202, 11),
(639, 203, 11),
(640, 204, 10),
(641, 205, 11),
(642, 206, 10),
(643, 207, 10),
(644, 208, 10),
(645, 209, 10),
(646, 210, 11),
(647, 211, 10),
(648, 212, 10),
(649, 213, 11),
(650, 214, 20),
(651, 215, 20),
(652, 216, 10),
(654, 218, 20),
(655, 219, 20),
(656, 220, 20),
(657, 221, 20),
(658, 222, 20),
(659, 223, 20),
(660, 224, 20),
(661, 225, 20),
(662, 226, 20),
(663, 227, 20),
(664, 228, 20),
(665, 229, 20),
(666, 230, 10),
(667, 231, 20),
(668, 232, 20),
(669, 233, 20),
(670, 234, 20),
(671, 235, 20),
(672, 236, 20),
(673, 237, 20),
(674, 238, 20),
(675, 239, 20),
(676, 240, 20),
(677, 241, 20),
(678, 242, 11),
(679, 243, 20),
(680, 244, 11),
(681, 245, 10),
(682, 246, 20),
(683, 247, 20),
(684, 248, 20),
(685, 249, 20),
(686, 250, 20),
(687, 251, 20),
(688, 252, 20),
(689, 253, 20),
(690, 254, 20),
(691, 255, 10),
(692, 256, 20),
(693, 257, 20),
(694, 258, 20),
(695, 259, 20),
(696, 260, 20),
(697, 261, 10),
(698, 262, 20),
(699, 263, 20),
(700, 264, 20),
(701, 265, 10),
(702, 266, 20),
(703, 267, 20),
(704, 268, 20),
(705, 269, 10),
(706, 270, 20),
(707, 271, 20),
(708, 272, 20),
(709, 273, 10),
(710, 274, 20),
(711, 275, 20),
(712, 276, 10),
(713, 277, 11),
(714, 278, 20),
(715, 279, 20),
(716, 280, 11),
(717, 281, 20),
(718, 282, 10),
(719, 283, 10),
(720, 284, 11),
(721, 285, 23),
(722, 286, 20),
(723, 287, 11),
(724, 288, 11),
(725, 289, 10),
(726, 290, 20),
(727, 291, 20),
(728, 292, 20),
(729, 293, 20),
(730, 294, 20),
(731, 295, 20),
(732, 296, 20),
(733, 297, 20),
(734, 298, 20),
(735, 299, 20),
(736, 300, 20),
(737, 301, 20),
(738, 302, 20),
(739, 303, 20),
(740, 304, 20),
(741, 305, 20),
(742, 306, 20),
(743, 307, 20),
(744, 308, 20),
(745, 309, 20),
(746, 310, 20),
(747, 311, 20),
(748, 312, 20),
(749, 313, 11),
(750, 314, 20),
(751, 315, 20),
(752, 316, 20),
(753, 317, 10),
(754, 318, 20),
(755, 319, 10),
(756, 320, 20),
(757, 321, 10),
(758, 322, 20),
(759, 323, 11),
(760, 324, 20),
(761, 325, 20),
(762, 326, 20),
(763, 327, 20),
(764, 328, 20),
(765, 329, 10),
(766, 330, 10),
(767, 331, 20),
(768, 332, 20),
(769, 333, 20),
(770, 334, 11),
(771, 335, 20),
(772, 336, 20),
(773, 337, 20),
(774, 338, 10),
(775, 339, 11),
(776, 340, 11),
(777, 341, 15),
(778, 342, 10),
(779, 343, 11),
(780, 344, 20),
(781, 345, 11),
(782, 346, 11),
(783, 347, 20),
(784, 348, 20),
(785, 349, 10),
(786, 350, 11),
(787, 351, 10),
(788, 352, 11),
(789, 353, 11),
(790, 354, 21),
(791, 355, 20),
(792, 356, 10),
(793, 357, 21),
(794, 358, 10),
(795, 359, 10),
(796, 360, 10),
(797, 361, 23),
(798, 362, 11),
(799, 363, 11),
(800, 364, 11),
(801, 365, 10),
(802, 366, 10),
(803, 367, 10),
(804, 368, 20),
(805, 369, 10),
(806, 370, 10),
(807, 371, 10),
(808, 372, 20),
(809, 373, 10),
(810, 374, 10),
(811, 375, 11),
(812, 376, 20),
(813, 377, 27),
(814, 378, 11),
(815, 379, 11),
(816, 380, 10),
(817, 381, 10),
(818, 382, 15),
(819, 383, 15),
(820, 384, 10),
(821, 385, 20),
(822, 386, 20),
(823, 387, 20),
(824, 388, 10),
(825, 389, 20),
(826, 390, 10),
(827, 391, 10),
(828, 392, 15),
(829, 393, 20),
(830, 394, 10),
(831, 395, 20),
(832, 396, 20),
(833, 397, 20),
(834, 398, 20),
(835, 399, 20),
(836, 400, 20),
(837, 401, 10),
(838, 402, 11),
(839, 403, 28),
(840, 404, 10),
(841, 405, 10),
(842, 406, 10),
(843, 407, 21),
(844, 408, 11),
(845, 409, 11),
(846, 410, 11),
(847, 411, 10),
(848, 412, 15),
(849, 413, 22),
(850, 414, 10),
(851, 415, 11),
(852, 416, 29),
(853, 417, 10),
(854, 418, 20),
(855, 419, 11),
(856, 420, 20),
(857, 421, 10),
(858, 422, 15),
(859, 423, 11),
(860, 424, 10),
(861, 425, 11),
(862, 426, 11),
(863, 427, 20),
(864, 428, 10),
(866, 430, 16),
(867, 431, 10),
(868, 432, 15),
(869, 433, 10),
(870, 434, 10),
(871, 435, 10),
(872, 436, 10),
(873, 437, 10),
(874, 438, 11),
(875, 439, 10),
(876, 440, 12),
(877, 441, 15),
(878, 442, 30),
(879, 443, 11);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ProcessosJudiciais`
--

CREATE TABLE `ProcessosJudiciais` (
  `ID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `Numero` varchar(50) NOT NULL,
  `Descricao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `ProcessosJudiciais`
--

INSERT INTO `ProcessosJudiciais` (`ID`, `ProcedimentoID`, `Numero`, `Descricao`) VALUES
(161, 55, '6132438-96.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(164, 57, '6106823-07.2024.8.09.0137', 'IP'),
(166, 58, '6096752-43.2024.8.09.0137', 'IP'),
(168, 60, '5989614-17.2024.8.09.0137', 'APF'),
(169, 61, '5990409-23.2024.8.09.0137', 'IP'),
(171, 62, '6009300-82.2024.8.09.0137', 'IP'),
(174, 66, '6002883-26.2024.8.09.0137', 'IP'),
(177, 67, '6074412-08.2024.8.09.0137', 'IP'),
(180, 69, '5983869-56.2024.8.09.0137', 'Temporária Allan, Ronaldo e Andréia'),
(181, 69, '5993746-20.2024.8.09.0137', 'Quebra de sigilo telefônico'),
(182, 69, '6059228-12.2024.8.09.0137', 'Temporária Ferrari'),
(183, 69, '6064179-49.2024.8.09.0137', 'Quebra de sigilo telemático e bancário'),
(184, 69, '6087493-24.2024.8.09.0137', 'Comunicação da Prisão de Ferrari'),
(186, 70, '6139747-71.2024.8.09.0137', 'IP'),
(188, 71, '5931416-84.2024.8.09.0137', 'IP'),
(189, 72, '', ''),
(194, 76, '5797657-24.2024.8.09.0137', 'IP'),
(195, 77, '5794181-65.2024.8.09.0137', 'IP'),
(196, 78, '5797260-62.2024.8.09.0137', 'IP'),
(198, 80, '5814103-05.2024.8.09.0137', 'IP'),
(201, 82, '5336593-87.2024.8.09.0134', 'Busca Diogo'),
(202, 82, '5104893-77.2024.8.09.0134', 'Telemática'),
(203, 83, '5694613-86.2024.8.09.0137', 'IP'),
(206, 87, '5431257-04.2024.8.09.0137', 'IP'),
(207, 88, '5405098-24.2024.8.09.0137', 'IP'),
(209, 90, '5363862-92.2024.8.09.0137', 'IP'),
(220, 93, '5310965-87.2024.8.09.0137', 'IP'),
(221, 94, '5310861-95.2024.8.09.0137', 'IP'),
(222, 95, '5311361-64.2024.8.09.0137', 'IP'),
(226, 98, '5267788-73.2024.8.09.0137', 'IP'),
(230, 102, '5337900-67.2024.8.09.0137', 'IP'),
(231, 103, '5145883-04.2024.8.09.0137', 'IP'),
(236, 60, '9090909-09.0909.0.90.9090', 'IP'),
(241, 113, '5028551-16.0000.0.00.0000', 'IP'),
(242, 114, '6158700-73.2024.8.09.0011', 'APF'),
(243, 115, '6159378-88.2024.8.09.0011', 'APF(processo arquivado)'),
(246, 59, '', ''),
(248, 120, '', ''),
(249, 117, '', ''),
(250, 123, '5223557-92.2023.8.09.0137', 'Medidas Cautelares'),
(251, 124, '', ''),
(252, 128, '5580110-23.0000.0.00.0000', 'IP'),
(253, 127, '5544159-94.0000.0.00.0000', 'IP'),
(255, 129, '6166512-69.2024.8.09.0011', 'APF'),
(257, 80, '5830411-19.2024.8.09.0137', 'Quebra Telemática - Vítima'),
(259, 131, '0151122-89.2015.8.09.0137', 'IP'),
(261, 129, '5012922-65.2025.8.09.0137', 'IP'),
(262, 132, '', ''),
(263, 133, '5452033-93.0000.0.00.0000', 'IP'),
(264, 55, '5023807-41.2025.8.09.0137', 'PROCESSO ARQUIVADO'),
(265, 136, '5023695-72.2025.8.09.0137', 'APF'),
(266, 55, '5039168-98.2025.8.09.0137', 'processo arquivado'),
(267, 136, '5047115-09.2025.8.09.0137', 'IP'),
(269, 55, '5076143-12.2025.8.09.0011', 'processo arquivado'),
(271, 115, '5088940-30.2025.8.09.0137', 'IP'),
(272, 142, '5095603-82.2025.8.09.0137', 'APF(processo arquivado)'),
(273, 82, '5100146-50.2025.8.09.0134', 'Quebra Sigilo Bancário'),
(274, 55, '5113372-16.2025.8.09.0137', 'Medidas Cautelares'),
(275, 142, '5114803-85.2025.8.09.0137', 'IP'),
(276, 68, '5937630-81.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(277, 64, '5114941-52.2025.8.09.0137', 'IP'),
(279, 146, '', ''),
(280, 145, '', ''),
(281, 147, '5665140-60.2021.8.09.0137', 'Inquérito Policial'),
(282, 148, '5135702-07.2025.8.09.0137', 'APF(processo arquivado)'),
(284, 149, '5147396-70.2025.8.09.0137', 'Medidas Cautelares'),
(285, 69, '5937626-44.2024.8.09.0011', 'APF(processo arquivado)'),
(286, 148, '5160489-03.2025.8.09.0137', 'IP'),
(287, 56, '6131841-30.2024.8.09.0137', 'IP'),
(288, 150, '5163118-37.2025.8.09.0137', 'APF'),
(289, 150, '5177283-02.2025.8.09.0137', 'IP'),
(290, 125, '5185370-44.2025.8.09.0137', 'Medidas Cautelares'),
(291, 114, '5198924-46.2025.8.09.0137', 'IP'),
(292, 151, '', ''),
(294, 157, '5221504-70.2025.8.09.0137', 'Medidas Cautelares'),
(296, 160, '5225614-15.2025.8.09.0137', 'Medidas Cautelares'),
(297, 163, '5565819-81.2023.8.09.0137', 'IP'),
(298, 63, '5248015-08.2025.8.09.0137', 'IP'),
(299, 164, '5249182-60.2025.8.09.0137', 'APF'),
(300, 140, '5250495-56.2025.8.09.0137', 'IP'),
(302, 166, '5265599-16.2025.8.09.0137', 'APF'),
(303, 158, '', ''),
(304, 168, '5280274-56.2025.8.09.0137', 'APF'),
(305, 169, '', ''),
(306, 168, '5280274-56.2025.8.09.0137', 'Medidas Cautelares'),
(315, 166, '5291575-97.2025.8.09.0137', 'IP'),
(332, 195, '5598730-49.5098.0.85.4720', 'VITIMA E AUTOR BRIGARAM NA PORTA DE UMA BEBIDA GELADA '),
(334, 197, '5654168-50.2023.8.09.0137', 'TENTATIVA DE HOMICÍDIO'),
(350, 216, '5500330-05.2020.8.09.0137', 'NÃO IDENTIFICADO O MOTIVO APARENTE '),
(363, 244, '5368893-35.2020.8.09.0137', 'MOTIVO APARENTE NÃO IDENTIFICADO '),
(365, 255, '5586037-33.2023.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(368, 265, '5581682-77.2023.8.09.0137', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(370, 269, '5417728-49.____._.__.____', 'CONFRONTO POLICIAL'),
(372, 272, '5626945-50.2020.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(373, 273, '5686402-66.2021.8.09.0137', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(375, 277, '', ''),
(377, 279, '5018001-64.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(379, 281, '', ''),
(380, 282, '5104694-51.2021.8.09.0137', 'DISCUSSAO POR CAUSA DA COMPRA DE UM APARELHO CELULAR'),
(381, 283, '5283000-42.0218.0.90.137_', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(383, 285, '5120475-16.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DESENTENDIMENTO'),
(388, 291, '5220706-51.2021.8.09.0137', ' NÃO IDENTIFICADO'),
(391, 294, '5156262-09.2021.8.09.0137', 'NÃO IDENTIFICADO'),
(396, 308, '', ''),
(397, 313, '5408779-07.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(402, 320, '', ''),
(405, 323, '5505585-07.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(411, 329, '5102035-35.2022.8.09.0137', 'A morte de intervenção policial gerou comoção e acendeu o debate sobre o uso excessivo da força pelas autoridades.'),
(415, 334, '5686402-66.2021.8.09.0137', 'MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(427, 346, '5247767-47.____._.__.____', 'VÍITMA  ERA USUARIO DE DROGAS E FOI ESFAQUEADO POR AUTOR '),
(430, 349, '5139184-72.022_._.__.____', 'Coletividade'),
(434, 353, '5174243-17.2022.8.09.0137', 'Vítima e autor tinham já tinham um desentedimento antigo'),
(437, 356, '5325077-95.____._.__.____', 'CONFRONTO POLICIAL'),
(440, 359, '5407641-68.2022.8.09.0137', 'RUA DAS ROSAS, QD. 03, LT. 03, N. 21, SERPRO'),
(446, 366, '5322860-98.2025.8.09.0011', 'APF'),
(449, 369, '5572743-45.____._.__.____', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(450, 370, '5576238-97.2022.8.09.0137', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(454, 374, '5680065-17.2022.8.09.0011', 'POSSE IRREGULAR DE ARMA DE FOGO'),
(460, 380, '5761710-74.2022.8.09.0137', 'DESENTENDIMENTO ENTRE RAFAEL E ELAINE E POSTERIORMENTE O AUTOR TOMOU TAIS ATITUDES'),
(469, 391, '5074699-90.____._.__.____', 'NÃO IDENTIFICADO'),
(470, 393, '5683959-11.2022.8.09.0137', 'HOMICIDIO'),
(471, 378, '5742400-82.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(472, 376, '5014527-51.2022.8.09.0137', 'IP'),
(473, 174, '5051594-16.2023.8.09.0137', 'IP'),
(474, 394, '5616632-83.2021.8.09.0137', 'DOIS DISPAROS DE ARMA DE FOGO'),
(475, 176, '5079810-84.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(476, 175, '5073082-27.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(477, 375, '5159762-15.2023.8.09.0137', 'IP'),
(478, 177, '5126176-84.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(479, 185, '5236164-30.2023.8.09.0011', 'PROCESSO ARQUIVADO'),
(480, 179, '5134360-29.2023.8.09.0137', 'IP'),
(481, 187, '5247958-58.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(482, 188, '5302676-05.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(483, 189, '5317423-57.2023.8.09.0137', 'IP'),
(484, 184, '5375874-75.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(485, 182, '5189672-77.2023.8.09.0011', 'IP'),
(486, 180, '5416611-23.2023.8.09.0137', 'IP'),
(487, 178, '5100864-09.2023.8.09.0137', 'IP'),
(488, 173, '5699312-23.2024.5.80.9013', 'IP'),
(489, 384, '5473769-36.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(490, 190, '5519686-78.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(491, 172, '5533621-88.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(492, 404, '5578095-47.2022.8.09.0137', 'TRAFICO DE DROGAS'),
(493, 409, '5742527-20.2022.8.09.0137', 'VITIMA ENCONTRADA DENTRO DE SUA RESIDENCIA SEM VIDA'),
(494, 422, '5573169-23.2023.8.09.0137', 'BRIGA'),
(495, 423, '5545175-10.2023.8.09.0137', 'BRIGA EM FESTA'),
(496, 424, '5603572-72.2023.8.09.0137', 'RIXA'),
(497, 162, '5333841-02.2025.8.09.0137', 'Medidas Cautelares'),
(498, 192, '5510270-86.2023.8.09.0137', 'IP'),
(499, 267, '5581735-58.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(500, 193, '5544730-89.2023.8.09.0011', 'RIXA ENTRE GRUPO RIVAIS'),
(501, 219, '5581889-43.2023.8.09.0051', 'PROCESSO ARQUIVADO'),
(502, 194, '5578280-85.2023.8.09.0137', 'RIXA'),
(503, 196, '5618241-33.2023.8.09.0137', 'BRIGA EM BAR '),
(504, 200, '5657449-24.2023.8.09.0137', 'TENTATIVA DE HOMICÍDIO, PORTE DE ARMA DE FOGO E TRÁFICO DE DROGAS'),
(505, 234, '5433409-64.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(506, 202, '5676873-52.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(507, 183, '5698848-33.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(508, 191, '5536688-61.2023.8.09.0137', 'BRIGA'),
(509, 203, '5696773-21.2023.8.09.0137', 'DESENTENDIMENTO COMERCIAL'),
(510, 204, '5707656-17.2023.8.09.0011', 'BRIGA ENTRE IRMÃOS'),
(511, 205, '5707646-70.2023.8.09.0011', 'PROCESSO ARQUIVADO'),
(512, 213, '5006156-30.2024.8.09.0137', 'BRIGA DE FACÇÃO'),
(513, 208, '5736675-68.2023.8.09.0011', 'PROCESSO ARQUIVADO'),
(514, 209, '5754231-83.2023.8.09.0011', 'CIUMES DO AUTOR EM RELAÇÃO A VITIMA'),
(515, 210, '5770130-34.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(516, 211, '5788510-95.2023.8.09.0011', 'BRIGA DE VIZINHOS'),
(517, 206, '5733545-80.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(518, 207, '5831449-03.2023.8.09.0137', 'Rixa por causa de traição'),
(519, 212, '5847788-37.2023.8.09.0137', 'BRIGA POR DÍVIDA'),
(520, 337, '5687576-13.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(521, 339, '5007688-10.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(522, 340, '5014527-51.2022.8.09.0137', ' A vitima teria furtado o celular do autor do crime'),
(523, 341, '5019764-66.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(524, 342, '5022669-44.2022.8.09.0137', 'ARMA DE FOGO E GARFO'),
(525, 344, '5033886-84.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(526, 345, '5047359-40.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(527, 347, '5086782-07.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(528, 351, '5156763-26.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(529, 350, '5146200-70.2022.8.09.0137', 'DESAVENÇA'),
(530, 343, '5121262-11.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(531, 355, '5240227-45.2022.8.09.0137', 'VITIMA E AUTOR BRIGARAM '),
(532, 348, '5174424-18.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(533, 354, '5244507-59.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(534, 338, '5308365-64.2022.8.09.0137', 'Gerou comoção e acendeu o debate sobre o uso excessivo da força pelas autoridades.'),
(535, 357, '5299351-56.2022.8.09.0137', ' acabou prensando a vitima no meio dos carros que nao resistiu e morreu na hora'),
(536, 368, '5631153-96.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(537, 361, '5364429-94.2022.8.09.0137', 'PAGAMENTO DE RECOMPENSA'),
(538, 362, '5451794-89.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(539, 352, '5166244-13.2022.8.09.0137', 'BRIGA DE BAR'),
(540, 371, '5584506-33.2022.8.09.0011', 'PROCESSO ARQUIVADO'),
(541, 372, '5612642-50.2022.8.09.0137', 'DESCRIÇÃO NÃO IDENTIFICADA'),
(542, 360, '5781521-20.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(543, 387, '5478996-75.2022.8.09.0158', 'MOTIVO APARENTE NÃO IDENTIFICADO '),
(544, 395, '', ''),
(545, 396, '', ''),
(546, 398, '', ''),
(547, 397, '', ''),
(548, 400, '', ''),
(549, 401, '', ''),
(550, 403, '', ''),
(551, 389, '', ''),
(552, 365, '', ''),
(553, 290, '', ''),
(554, 298, '', ''),
(557, 307, '', ''),
(558, 270, '', ''),
(559, 232, '', ''),
(560, 235, '', ''),
(561, 238, '', ''),
(562, 328, '5635287-06.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(563, 306, '5376022-57.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(564, 331, '5659114-46.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(565, 333, '5659543-13.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(566, 335, '5681772-64.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(567, 336, '5685557-34.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(568, 58, '6070261-86.2024.8.09.0011', 'APF(processo arquivado)'),
(569, 112, '5038226-03.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(570, 109, '5094818-67.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(571, 99, '5607412-56.2024.8.09.0137', 'IP'),
(572, 105, '5380567-68.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(573, 100, '5336840-59.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(574, 96, '5283618-69.2024.8.09.0011', 'IP'),
(575, 89, '5367512-50.2024.8.09.0137', 'IP'),
(576, 91, '5329745-65.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(577, 86, '5558889-13.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(578, 85, '5563136-37.2024.8.09.0137', 'IP'),
(579, 81, '5726136-09.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(580, 74, '6049667-61.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(581, 79, '6052620-95.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(582, 75, '5863340-08.2024.8.09.0137', 'IP'),
(583, 73, '5817154-14.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(584, 71, '5862346-77.2024.8.09.0137', 'APF(processo arquivado)'),
(585, 70, '5898169-05.2024.8.09.0011', 'APF(processo arquivado)'),
(586, 67, '5971530-65.2024.8.09.0137', 'processo arquivado'),
(587, 67, '6008344-76.2024.8.09.0137', 'processo arquivado'),
(588, 66, '5947058-97.2024.8.09.0137', 'APF(processo arquivado)'),
(589, 65, '5948168-34.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(590, 62, '5987462-83.2024.8.09.0011', 'APF(processo arquivado)'),
(591, 57, '6089991-83.2024.8.09.0011', 'APF(processo arquivado)'),
(592, 56, '6108073-75.2024.8.09.0137', 'APF(processo arquivado)'),
(593, 119, '5011019-92.2025.8.09.0137', 'processo arquivado'),
(594, 119, '5117214-04.2025.8.09.0137', 'processo arquivado'),
(595, 170, '5473769-36.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(596, 383, '5779282-33.2022.8.09.0011', 'PROCESSO ARQUIVADO'),
(597, 82, '5818642-57.2023.8.09.0134', 'processo arquivado'),
(598, 381, '5272994-05.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(599, 363, '5384581-66.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(600, 367, '5522857-77.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(601, 358, '5305847-04.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(602, 373, '5639743-62.2022.8.09.0137', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(603, 382, '5775033-39.2022.8.09.0011', 'PROCESSO ARQUIVADO'),
(604, 364, '5416794-28.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(605, 377, '5710297-22.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(606, 276, '5001121-94.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(607, 278, '5007373-16.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(608, 280, '5027637-54.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(609, 284, '5056723-70.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(610, 287, '5068327-28.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(611, 288, '5070771-34.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(612, 289, '5093421-75.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(613, 388, '5093421-75.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(614, 286, '5077581-25.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(615, 292, '5122105-10.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(616, 299, '5190838-28.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(617, 301, '5195032-71.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(618, 296, '5180406-47.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(619, 330, '5247984-90.2022.8.09.0137', 'PROCESSO ARQUIVADO'),
(620, 300, '5306529-90.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(621, 304, '5212734-30.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(622, 310, '5291833-49.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(623, 297, '5187866-85.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(624, 309, '5268344-80.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(625, 311, '5364259-59.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(626, 303, '5224034-86.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(627, 230, '5212162-41.2021.8.09.0051', 'CONFRONTO POLICIAL'),
(628, 314, '5444424-93.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(629, 254, '5456311-74.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(630, 312, '5423385-40.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(631, 322, '5496629-02.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(632, 321, '5488099-09.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(633, 319, '5500729-97.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(634, 325, '5517376-70.2021.8.09.0137', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(635, 293, '5675402-98.2023.8.09.0137', 'PROCESSO ARQUIVADO'),
(636, 302, '', ''),
(637, 324, '5514912-73.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(638, 318, '5524747-85.2021.8.09.0137', 'FEMINICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(639, 326, '5570739-69.2021.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(640, 327, '5571974-71.2021.8.09.0137', 'HOMICIDIO E TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(641, 261, '5608626-87.2021.8.09.0137', 'PROCESSO ARQUIVADO'),
(642, 242, '5352101-06.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(643, 245, '5377489-08.2020.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(644, 257, '5491416-49.2020.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(645, 258, '5501112-12.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(646, 259, '5512572-93.2020.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(647, 260, '5518858-87.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(648, 386, '5526036-87.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(649, 263, '5558067-63.2020.8.09.0137', 'PROCESSO ARQUIVADO'),
(650, 271, '5610112-44.2020.8.09.0137', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(651, 379, '5752291-20.2022.8.09.0011', 'PROCESSO ARQUIVADO'),
(652, 199, '5787304-56.2023.8.09.0137', 'TENTATIVA DE HOMICÍDIO E HOMICÍDIO/RIXA POR TRAFICO'),
(653, 181, '5450449-54.2023.8.09.0137', 'BRIGA/TENTADO'),
(654, 107, '5109284-66.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(655, 106, '5125041-03.2024.8.09.0137', 'IP'),
(656, 104, '5337100-39.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(657, 97, '5379826-28.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(658, 101, '5163655-67.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(659, 92, '5327965-90.2024.8.09.0011', 'PROCESSO ARQUIVADO'),
(660, 137, '5203318-96.2025.8.09.0137', 'PROCESSO ARQUIVADO'),
(661, 165, '5265709-77.2025.8.09.0011', 'PROCESSO ARQUIVADO'),
(662, 165, '5284415-21.2025.8.09.0137', 'IP'),
(663, 241, '5574217-17.2023.8.09.0137', 'MOTIVO NÃO IDENTIFICADO '),
(664, 110, '5137379-09.2024.8.09.0137', 'PROCESSO ARQUIVADO'),
(665, 111, '5061814-39.2024.8.09.0137', 'IP'),
(668, 430, '', ''),
(669, 431, '5184523-47.2022.8.09.0137', 'COTA MINISTERIAL'),
(670, 432, '5593919-46.2023.8.09.0137', 'HOMICIDIO'),
(671, 366, '5394797-81.2025.8.09.0137', 'IP'),
(672, 433, '0147671-51.2018.8.09.0137', 'IP'),
(673, 434, '5686492-06.2023.8.09.0137', 'IP'),
(674, 435, '0077954-20.2016.8.09.0137', 'IP'),
(675, 436, '5182577-40.2022.8.09.0137', 'IP'),
(676, 437, '0166773-64.2015.8.09.0137', 'IP'),
(677, 438, '5356576-97.2023.8.09.0137', 'IP'),
(678, 439, '5371488-02.2023.8.09.0137', 'IP'),
(679, 441, '5400282-62.2025.8.09.0137', 'APF'),
(680, 442, '5404503-78.2025.8.09.0011', 'APF'),
(681, 157, '5347774-42.2025.8.09.0137', 'COMUNICAÇÃO PRISÃO'),
(682, 157, '5394036-50.2025.8.09.0137', 'PEDIDO DE LIBERDADE'),
(683, 157, '5412242-15.2025.8.09.0137', 'IP'),
(684, 443, '5410065-78.2025.8.09.0137', 'APF'),
(685, 441, '5423735-86.2025.8.09.0137', 'IP'),
(686, 442, '5423750-55.2025.8.09.0137', 'IP'),
(687, 164, '5423817-20.2025.8.09.0137', 'IP');

-- --------------------------------------------------------

--
-- Estrutura para tabela `RAIs`
--

CREATE TABLE `RAIs` (
  `ID` int NOT NULL,
  `ProcedimentoID` int NOT NULL,
  `Numero` varchar(50) NOT NULL,
  `Descricao` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `RAIs`
--

INSERT INTO `RAIs` (`ID`, `ProcedimentoID`, `Numero`, `Descricao`) VALUES
(369, 61, '38467750', 'APF'),
(373, 64, '38206540', 'RAI de Registro'),
(382, 72, '37584447', 'Rai Principal'),
(392, 76, '37367750', 'Rai da Prisão'),
(393, 77, '37362013', 'Rai do homicídio'),
(394, 78, '37075303', 'RAI do encontro de cadáver'),
(402, 84, '36345088', 'Registro do fato'),
(405, 87, '35919757', 'Registro do fato'),
(406, 88, '35897764', 'Registro do fato'),
(408, 90, '35506622', 'Registro do fato'),
(414, 60, '38467863', 'IP'),
(415, 93, '35406124', 'RAI de Registro'),
(416, 94, '35403352', 'RAI de Registro'),
(417, 95, '35357409', 'RAI de Registro'),
(421, 98, '35164378', 'RAI de Registro'),
(425, 102, '34627824', 'RAI de Registro'),
(426, 103, '34608666', 'RAI de Registro'),
(431, 108, '23656179', 'RAI de Registro'),
(437, 113, '33412071', 'RAI de Registro'),
(438, 114, '39479462', 'APF'),
(439, 114, '39469990', 'Registro do Fato'),
(447, 59, '38727236', 'IP'),
(453, 121, '39361888', 'Registro do fato'),
(454, 117, '39361888', 'Registro do fato'),
(455, 122, '28268113', 'Registro do fato'),
(456, 123, '29173534', 'Registro do fato'),
(458, 124, '28388167', 'Registro do fato'),
(459, 125, '27610646', 'Registro do fato'),
(460, 126, '29184056', 'Registro do fato'),
(461, 127, '24825491', 'Registro do fato'),
(462, 128, '26268557', 'Registro do fato'),
(463, 118, '39479034', 'Registro do fato'),
(466, 130, '37023603', 'RAI irmão'),
(467, 130, '37078062', 'Registro do óbito em Jataí'),
(468, 80, '37028726', 'RAI do encontro de cadáver'),
(472, 131, '6518611', 'BOPM'),
(475, 129, '39564904', 'APF'),
(476, 129, '39563000', 'RAI dos Fatos'),
(485, 133, '25150951', 'Registro do fato'),
(486, 132, '39689526', 'VPI'),
(487, 134, '39725835', 'SUICIDIO'),
(491, 135, '39779979', 'APREENSÃO DA ARMA'),
(492, 136, '39752440', 'HOMICIDIO'),
(493, 136, '39772494', 'APF'),
(494, 116, '39479034', 'Registro do fato'),
(512, 139, '39867059', 'Registro do fato'),
(513, 140, '39823172', 'CONFRONTO POLICIAL'),
(514, 120, '39489257', 'Localização do desaparecido'),
(515, 120, '39468250', 'Registro de desaparecimento'),
(518, 138, '39850101', 'Rai do homicídio'),
(519, 138, '39954486', 'Diligências GIH'),
(530, 141, '40010109', 'suicidio'),
(550, 143, '40189571', 'CONFRONTO POLICIAL'),
(551, 144, '40185752', 'TENTATIVA'),
(557, 146, '40260553', 'MORTE NATURAL'),
(558, 145, '40236194', 'SUICIDIO'),
(559, 147, '21361600', 'Registro do fato'),
(571, 152, '40569718', 'TENTATIVA'),
(572, 153, '40489744', 'SUICIDIO'),
(573, 150, '40532295', 'TENTATIVA'),
(574, 150, '40535525', 'FLAGRANTE'),
(575, 149, '40376544', 'Registro do fato'),
(576, 154, '40696289', 'RAI DO FATO'),
(577, 155, '40649491', 'SUICIDIO PM'),
(578, 155, '40649907', 'SUICIDIO GCM'),
(579, 156, '38790754', 'RAI DO FATO'),
(580, 151, '40338685', 'LESÃO'),
(583, 159, '40420094', 'TENTATIVA'),
(584, 160, '40420094', 'TENTATIVA'),
(585, 161, '40705192', 'SUICIDIO'),
(586, 149, '40744215', 'Buscas - Casa Marcos'),
(587, 149, '40745737', 'Buscas - Casa Isaias (Pitbull)'),
(588, 149, '40744042', 'Buscas - Casa Wanderson'),
(589, 149, '40744320', 'Cumprimento - Atalaia'),
(590, 162, '40880175', 'RAI DE INSTAURAÇÃO'),
(591, 162, '40877665', 'RAI EDSON (MARIDO DA AVÓ DO AUTOR)'),
(592, 162, '40877361', 'RAI UPA, INFORMANDO ESFAQUEAMENTO'),
(593, 163, '11816566', 'RAI DO FATO'),
(594, 63, '38395033', 'RAI de Registro'),
(596, 164, '41011936', 'RAI DO FATO'),
(598, 166, '41102528', 'APF'),
(599, 166, '41100647', 'HOMICIDIO'),
(600, 167, '41110456', 'SUICIDIO'),
(601, 158, '40721934', 'Registro do fato'),
(602, 168, '41165795', 'APF'),
(603, 168, '41159099', 'HOMICIDIO'),
(605, 169, '41193525', 'MORTE A ESCLARECER'),
(609, 171, '28132697', 'TENTADO'),
(625, 186, '41191997', 'SUICIDIO'),
(634, 195, '31765236', 'VITIMA E AUTOR BRIGARAM NA PORTA DE UMA BEBIDA GELADA '),
(636, 197, '32172819', 'TENTATIVA DE HOMICÍDIO'),
(637, 198, '41082881', 'ENCONTRO DE CADAVER'),
(638, 198, '41015848', 'DESAPARECIMENTO'),
(641, 201, '32211510', 'LATROCINIO, ESTUPRO E TENTATIVA DE OCULTAÇÃO DE CADAVER'),
(654, 216, '13412087', 'NÃO IDENTIFICADO O MOTIVO APARENTE '),
(655, 218, '5197881.50', 'MOTIVO APARENTE NÃO IDENTIFICADO'),
(656, 244, '15771240', 'HOMICÍDIO'),
(658, 247, '5415786.84', 'MOTIVO APARENTE NÃO IDENTIFICADO PARA PROVOCAR O CRIME'),
(659, 251, '5443430-02', 'ENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(660, 252, '5453211-48', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(661, 253, '5453429.76', 'POSSE DE ARMA DE FOGO'),
(663, 255, '16280075', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(668, 265, '17069860', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(669, 273, '17305139', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(670, 274, '5030375.36', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(671, 275, ' 5655353.41', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(674, 277, '17737349', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DESENTENDIMENTO'),
(676, 279, '17884804', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(678, 282, '1816608', 'DISCUSSAO POR CAUSA DA COMPRA DE UM APARELHO CELULAR'),
(679, 283, '17253372', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(681, 285, '17838672/18125820', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DESENTENDIMENTO'),
(686, 291, '5220706.51', ' NÃO IDENTIFICADO'),
(689, 294, '5156262-09', 'NÃO IDENTIFICADO '),
(704, 316, ' 5312451-39', 'NÃO IDENTIFICADO'),
(708, 320, '', ''),
(711, 323, '21343539', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(713, 329, '22362103', 'A morte de intervenção policial gerou comoção e acendeu o debate sobre o uso excessivo da força pelas autoridades.'),
(714, 334, '22478189', 'HOMICIDIO'),
(723, 346, '23133431', 'BRIGA ENTRE USUARIOS DE DROGAS'),
(725, 349, '23756002', 'SIMULOU CRIME'),
(729, 353, '23987209', 'BRIGA EM BAR'),
(733, 356, '24701192', 'CONFRONTO POLICIAL'),
(736, 359, '25061278', 'BRIGA POR CIUMES'),
(745, 369, '26377112', 'TENTATIVA DE HOMICIDIO'),
(746, 370, '26597988', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(753, 380, '27809308', 'DESENTENDIMENTO ENTRE RAFAEL E ELAINE E POSTERIORMENTE O AUTOR TOMOU TAIS ATITUDES'),
(759, 391, '16897189', ' HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(760, 378, '27642084', 'DISCUSSAO POR DROGAS'),
(761, 174, '28437843', 'HOMICÍDIO'),
(764, 176, '28593863', 'FÓRUM DE RIO VERDE-GO'),
(765, 175, '28556032', 'DISCUSSÃO DEVIDA A PERDA DE UM CELULAR'),
(766, 375, '27252632', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(767, 177, '28856491', 'LATROCINIO'),
(768, 185, '29583255/29573757', 'DESENTENDIMENTO COMERCIAL - VENDA DE VEÍCULO'),
(769, 179, '28989907', 'SEM MOTIVO APARENTE'),
(770, 187, '29675711', 'TRÁFICO DE DROGAS'),
(771, 188, '30083926', 'CIÚMES DO AUTOR EM RELAÇÃO A VÍTIMA'),
(772, 189, '30177461', 'BRIGA/TENTADO '),
(773, 184, '29623852', 'CONFRONTO POLICIAL'),
(774, 182, '29269596', 'ACIDENTE DE TRÂNSITO/HOMICÍDIO'),
(776, 180, '29161989', 'LESÃO CORPORAL E MAUS TRATOS'),
(777, 178, '28768552', 'HOMICÍDIO'),
(778, 173, '28259764', 'Tentado'),
(779, 384, '28043359', 'CONFRONTO POLICIAL'),
(780, 402, '23001056', 'TENTATIVA DE HOMICIDIO/BRIGA EM BAR'),
(781, 190, '30914757', 'BRIGA FAMILIAR'),
(782, 172, '28303022', 'MORTE POR INTERVENÇÃO POLICIAL'),
(784, 404, '23267169', 'BRIGA DE USUARIOS'),
(785, 405, '24038018', 'TRÁFICO DE DROGAS'),
(786, 406, '24497291', 'BRIGA EM FESTA'),
(787, 408, '25346170', 'TRAFICO DE DROGAS'),
(788, 409, '25674573', 'HOMICÍDIO'),
(789, 410, '25774720 / 25778931', 'USUARIO DE DROGAS EM VIA PÚBLICA'),
(790, 411, '25929233', 'Foi registrada,como morte decorrente de intervenção policial'),
(791, 412, '25939615', 'A vítima foi localizada em sua residência,ja em ôbito'),
(792, 413, '27450468', 'A VÍTIMA COMPARECEU RELATANDO SUSPEITAR QUE FAMILIARES ESTEJAM-LHE,ADMINISTRANDO SUBSTÂNCIAS TÓXICAS,POSSIVELMENTE METAIS PESADOS,COM A INTENÇÃO DE CAUSAR SUA MORTE,VISANDO A DIVISÃO ANTECIPADA DE BENS RELACIONADOS HÁ HERANÇA'),
(793, 414, '27881266', 'Conforme apurado,a vítima se envolveu em uma briga e,durante o conflito,teria subtraído uma arma de fogo,sendo esse o possível motivo dos fatos narrados na presente ocorrência'),
(794, 415, '28039633', 'Durante uma confraternização,a vítima se desentendeu com o autor e,logo após o conflito,foi atingida por golpe de arma branca'),
(795, 416, '28228978', ' AGREDIDA POR DOIS INDIVÍDUOS NO ESTACIONAMENTO DA FACULDADE'),
(796, 417, '28511323', 'DESENTENDIMENTO ENTRE VÍTIMA E AUTOR'),
(797, 418, '28821701', 'TENTATIVA DE HOMICÍDIO E LESÃO CORPORAL SEGUIDA DE MORTE'),
(798, 419, '29323373', 'A VÍTIMA ALEGA QUE O AUTOR O ACUSOU DE ROUBO E O ESFAQUEOU'),
(799, 421, '30641892', 'POSSE DE ARMA DE FOGO'),
(800, 422, '30903109', 'TENTATIVA DE HOMICIDIO'),
(801, 423, '31548612', 'BRIGA EM FESTA'),
(802, 424, '31588524', 'RIXA'),
(803, 425, '32235721', 'BRIGA DE BAR'),
(804, 426, '32680984', 'Briga no transito'),
(805, 427, '31255613', 'PSICOLOGIA - VÍTIMAS ENLUTADAS'),
(806, 192, '31215612', 'ENVOLVIMENTO DE FACÇÃO'),
(807, 193, '31544902', 'RIXA ENTRE GRUPO RIVAIS'),
(808, 194, '31715252', 'RIXA'),
(810, 200, '32203810', 'TENTATIVA DE HOMICÍDIO, PORTE DE ARMA DE FOGO E TRÁFICO DE DROGAS'),
(811, 202, '32315748 / 32311736', 'TENTATIVA DE HOMICÍDIO/DESENTENDIMENTO NA PORTA DA BOATE'),
(812, 183, '29578909', 'BRIGA POR DIVIDA '),
(815, 203, '32449098', 'DESENTENDIMENTO COMERCIAL'),
(816, 204, '32528557 / 32534002', 'BRIGA ENTRE IRMÃOS'),
(817, 205, '32524993', 'BRIGA ENTRE USUÁRIOS DE DROGAS'),
(818, 213, '33470784', 'BRIGA DE FACÇÃO'),
(819, 208, '32715141', 'RIXA'),
(820, 209, '32804554', 'CIUMES DO AUTOR EM RELAÇÃO A VITIMA'),
(821, 210, '32939390', 'BRIGA DE VIZINHOS'),
(822, 211, '33026715', 'BRIGA DE VIZINHOS'),
(823, 206, '32533807', 'RIXA'),
(824, 207, '32624780', 'Rixa por causa de traição'),
(825, 212, '33381975', 'BRIGA POR DÍVIDA'),
(826, 339, '22835007', 'Autor já ameaçava a vitima'),
(827, 340, '22875672', 'Roubo de celular '),
(828, 341, '22917780', 'BRIGA FAMILIAR'),
(829, 342, '22944722', 'BRIGA ENTRE FUNCIONARIOS'),
(830, 344, '230022129', 'MORTE POR INTERVENÇÃO POLICIAL (TENTADO)'),
(831, 345, '23100373', 'Vítima e autora sao usuarias de drogas e discutiram pq a vitima acusou uma amiga '),
(832, 347, '23300545', 'NÃO IDENTIFICADO'),
(834, 351, '23870839', 'BRIGA DE VIZINHOS'),
(835, 350, '23792045', 'DESAVENÇA'),
(836, 343, '22991844', 'BRIGA FAMILIAR'),
(837, 355, '24430198', 'BRIGA DE CASAL '),
(838, 354, '24413900', 'ACIDENTE DE TRÂNSITO/HOMICÍDIO'),
(839, 338, '22708242', 'A morte de intervenção policial '),
(840, 357, '24834543', 'ACIDENTE DE TRÂNSITO/HOMICÍDIO'),
(841, 361, '25260632', 'PAGAMENTO DE RECOMPENSA'),
(842, 362, '25350512', 'DESENTENDIMENTO'),
(843, 352, '23871351', ' durante a discussão o autor esfaqueou a vitima que veio a obito no local '),
(844, 371, '26621713', 'BRIGA FAMILIAR'),
(845, 360, '25193338', 'BRIGA EM FESTA'),
(846, 394, '18069303', 'HOMICIDIO'),
(847, 401, '22743660', 'BRIGA ENTRE VIZINHOS'),
(848, 403, '23168763', 'BRIGA NA CELA'),
(850, 365, '25774927', 'ENCONTRADO EM VIA PÚBLICA, COM LESÕES DE ARMA DE FOGO'),
(853, 306, '19264411', 'NÃO IDENTIFICADO'),
(854, 305, '2130608-72.021', 'descrição não identificada'),
(856, 196, '31985105/31990940', 'BRIGA EM BAR'),
(857, 58, '38937166', 'RAI de Registro'),
(859, 112, '33922200', 'RAI de Registro'),
(860, 109, '34101784', 'RAI de Registro'),
(861, 99, '34225999', 'RAI de Registro'),
(862, 105, '34419227', 'RAI de Registro'),
(863, 100, '34567691', 'RAI de Registro'),
(864, 96, '35253372', 'RAI de Registro'),
(866, 91, '35498159', 'Registro do fato'),
(867, 86, '36214815', 'Registro do fato'),
(868, 85, '36241067', 'Registro do fato'),
(869, 83, '36409870', 'Registro do fato'),
(870, 81, '37005103', 'APF'),
(871, 74, '37010239', 'Registro do fato'),
(872, 79, '37184979 ', 'IP'),
(873, 79, '37004833', 'RAI dos Fatos'),
(874, 75, '37374879', 'Registro do fato'),
(875, 73, '37475876 ', 'APF'),
(876, 73, '37449900', 'RAI dos Fatos'),
(877, 71, '37714521', 'Rai Principal'),
(878, 70, '37919636', 'Rai Principal'),
(879, 69, '38125835', 'APF'),
(880, 68, '38152496', 'APF'),
(881, 67, '38158047', 'RAI de Registro'),
(882, 66, '38206102', 'RAI de Registro'),
(883, 65, '38227644', 'RAI de Registro'),
(884, 62, '38457064', 'RAI de Registro'),
(885, 57, '39050259', 'RAI de Registro'),
(886, 56, '39116139', 'Homicídio'),
(887, 56, '39145992', 'APF'),
(896, 55, '39229799', 'RAI de Instauração'),
(897, 55, '39776673', 'Cumprimento de Prisão e Buscas'),
(898, 55, '39779979 ', 'Apreensão Pistola Órion'),
(899, 55, '39801991', 'Apreensão Armas Sharlingthon'),
(900, 55, '40071674', 'Prisão Erich Marques'),
(901, 55, '40087611', 'Buscas - Casa do Erich'),
(902, 55, '40097722', 'Buscas - Casa da noiva do Erich'),
(903, 55, '40609631', 'Buscas - Ribeirão Cascalheiras/MT'),
(905, 115, '39470747', 'Registro do fato'),
(906, 119, '39517140', 'Registro do fato'),
(907, 119, '39603393', 'Localização de PAFs '),
(908, 170, '28043359', 'CONFRONTO POLICIAL'),
(909, 383, '27957131', 'DISCUSSAO ENTRE AUTOR E VÍTIMA'),
(910, 191, '30912849', 'BRIGA'),
(911, 82, '33083351', 'Rai do homicídio'),
(912, 381, '27811114', 'DESENTENDIMENTO ENTRE ENVOLVIDOS'),
(913, 363, '25322753', 'RUA E, VILA MARIANA, AO LADO DO LAVA JATO'),
(914, 367, '26250241', ' DESENTENDIMENTO POR TRÁFICO DE DROGAS '),
(915, 358, '24573916', 'DISCUSSAO DE COMERCIO'),
(917, 373, '26980514', 'DISCUSSÃO ENTRE AUTOR E VÍTIMA'),
(918, 382, '27893812', 'BRIGA POR DROGAS'),
(919, 364, '25592221', 'RUA SÃO TOMAZ DE AQUINO, QD. 39, LT. 4, SETOR PAUSANES'),
(920, 377, '27461349 E 27465581', ' O AUTOR NA INTENÇÃO DE PERMANECER A VÍTIMA NO LOCAL, USOU A AGRESSÃO FÍSICA'),
(921, 276, '17746376', 'HOMICIDIO/DISCUSSAO FAMILIAR'),
(922, 278, '17823038', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(923, 280, '17953395', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(924, 284, '18159387/18171620', 'Briga por causa de drogas'),
(925, 287, '18230923', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE DENTIFICADO COMO BRIGA DE VIZINHOS'),
(926, 288, '18261003', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE BRIGA FAMILIAR'),
(927, 289, '18422773', 'IDENTIFICOU-SE A EXISTÊNCIA DE DESAFETOS NO AMBIENTE  INTERPESSOAIS ENTRE OS COLABORADORES '),
(928, 388, '18422773', 'DESAFETOS'),
(929, 286, '18045605', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(930, 292, '5122105.10', 'NÃO IDENTIFICADO'),
(931, 299, '', ''),
(932, 301, '', ''),
(933, 296, '', ''),
(934, 304, '', ''),
(935, 310, '', ''),
(936, 297, '', ' '),
(937, 309, '', ''),
(938, 311, '', ''),
(939, 303, '', ''),
(940, 230, '', ''),
(941, 313, '20613530', 'NÃO IDENTIFICADO'),
(942, 314, '', ''),
(943, 254, '', ''),
(945, 312, '', ''),
(946, 322, '21218709', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(947, 321, '21218709', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(948, 319, '21173034', 'Vítima é transexual e após realizar um programa sexual com o autor'),
(949, 293, '18696258', 'NÃO IDENTIFICADO'),
(950, 317, '5562383-85', 'NÃO IDENTIFICADO'),
(951, 302, '5334953-45', 'NÃO IDENTIFICADO'),
(952, 318, '21054411', 'FEMINICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(953, 327, '21836942', 'HOMICIDIO E TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(954, 214, '5312849-93', 'TENTATIVA DE HOMICÍDIO'),
(955, 227, '0290646-77', 'TENTATIVA DE HOMICÍDIO'),
(956, 229, '5578327-59', 'HOMICIDIO E TENTATIVA DE HOMICIDIO'),
(957, 233, '5233510-85', 'MOTIVO APARENTE NÃO IDENTIFICADO '),
(958, 236, '5249647-45', 'NÃO IDENTIFICADO'),
(959, 239, '5298907-91', 'NÃO IDENTIFICADO'),
(960, 223, '5314719-76', 'NÃO IDENTIFICADO'),
(961, 240, '5301305-11', 'NÃO IDENTIFICADO'),
(962, 245, '15859659', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(963, 390, '5569131-65', 'HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(964, 259, '5512572.93', 'TENTATIVA DE HOMICIDIO/MOTIVO APARENTE NÃO IDENTIFICADO PARA COMETER O CRIME'),
(965, 379, '27755286', 'RIVALIDADE ENTRE VÍTIMA E AUTOR'),
(966, 199, '32177565', 'TENTATIVA DE HOMICÍDIO E HOMICÍDIO/RIXA POR TRAFICO'),
(967, 181, '29274561', 'TENTADO/BRIGA'),
(968, 107, '34399096', 'RAI de Registro'),
(969, 106, '34497153', 'RAI de Registro'),
(970, 104, '34516918', 'RAI de Registro'),
(971, 97, '34613299', 'RAI de Registro'),
(972, 101, '34699001', 'RAI de Registro'),
(973, 92, '35475189', 'Registro do fato'),
(974, 137, '39772870', 'CONFRONTO'),
(977, 142, '40156559', 'TENTATIVA'),
(978, 148, '40370039', 'APF'),
(979, 148, '40358577', 'TENTATIVA DE HOMICÍDIO'),
(980, 165, '41086531', 'HOMICIDIO E APF'),
(982, 110, '34013336', 'RAI de Registro'),
(983, 111, '34071061', 'RAI de Registro'),
(984, 428, '41736758', 'RAI DE INSTAURAÇÃO'),
(985, 89, '35451332', 'Registro do fato'),
(986, 89, '35455394', 'localização de objetos'),
(987, 431, '23748412', 'TENTATIVA'),
(988, 432, '4724094', 'HOMICIDIO'),
(989, 366, '41474113', 'RAI DO FATO'),
(990, 433, '5337727', 'CONFRONTO POLICIAL'),
(991, 434, '6898806', 'TENTATIVA'),
(992, 435, '42/2015', 'HOMICIDIO'),
(993, 436, '23003929', 'CONFRONTO POLICIAL'),
(994, 437, '70948582/2011', 'HOMICIDIO'),
(995, 438, '28336310', 'HOMICIDIO'),
(996, 439, '28269427', 'CONFRONTO POLICIAL'),
(997, 440, '41863752', 'SUICIDIO'),
(999, 441, '41869812', 'FEMINICIDIO'),
(1001, 442, '41889101', 'HOMICIDIO'),
(1002, 157, '40338685', 'TENTATIVA'),
(1003, 157, '41617098', 'CUMPRIMENTO PRISÃO TEMPORÁRIA'),
(1004, 157, '41617098', 'CUMPRIMENTO DE PRISÃO TEMPORÁRIA'),
(1005, 157, '41617098', 'CUMPRIMENTO DE PRISÃO TEMPORÁRIA'),
(1006, 157, '41617098', 'CUMPRIMENTO DE BUSCA E APREENSÃO'),
(1007, 162, '41964863', 'Prisão Lucas'),
(1008, 443, '41941717', 'TENTATIVA');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Senhas`
--

CREATE TABLE `Senhas` (
  `id` int NOT NULL,
  `sistema` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `login` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `observacoes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Senhas`
--

INSERT INTO `Senhas` (`id`, `sistema`, `url`, `login`, `senha`, `observacoes`) VALUES
(4, 'PROJUDI', 'https://projudi.tjgo.jus.br', '01926156960', 'bXF2Z3F4SFdzY240dTFIRmJzSk02dz09OjpjWJDHmkdmjw/XPfGBOETJ', ''),
(5, 'GMAIL', 'https://gmail.com', 'rioverdegih@gmail.com', 'TjEwT3gwS2kzRjRlNnZhMlN1VXcwZz09OjoOvUACWLeT/0Wln/VtYmlS', ''),
(6, 'ZIMBRA - GIH', 'https://correio.policiacivil.go.gov.br/', 'gih-rioverde@policiacivil.go.gov.br', 'bzBLMGxYZll5S05teFBpN3RjanJwdz09Ojqnoxg4Ap0Edc90Pr0kWFqU', ''),
(7, 'ZIMBRA - Adelson', 'https://correio.policiacivil.go.gov.br/', 'adelsoncj@policiacivil.go.gov.br', 'YWtDZXpHVGl1Z29FMFhJUTBhUXArZz09OjqcdLkZgyzfqnYo8+GU0Bzs', ''),
(8, 'Monitoramento de Trânsito (Cerca)', 'https://portal.vizentec.com.br/DTFSSO-Portal/', 'guilhermecr', 'eTVNaVJKVHRNRHZ4SXdST0hWU2tEZz09Ojq+wD1S7zm/ehvbxeIFFCC/', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `SituacoesObjeto`
--

CREATE TABLE `SituacoesObjeto` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `SituacoesObjeto`
--

INSERT INTO `SituacoesObjeto` (`ID`, `Nome`) VALUES
(1, 'Apreendido'),
(2, 'Devolvido'),
(3, 'Destruído'),
(4, 'Extraviado'),
(9, 'Encaminhado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `SituacoesProcedimento`
--

CREATE TABLE `SituacoesProcedimento` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Cor` varchar(20) NOT NULL,
  `Categoria` enum('IP','VPI','Todos','Desaparecimento') NOT NULL DEFAULT 'Todos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `SituacoesProcedimento`
--

INSERT INTO `SituacoesProcedimento` (`ID`, `Nome`, `Cor`, `Categoria`) VALUES
(1, 'Apurando com autoria', 'badge-primary', 'IP'),
(2, 'Apurando sem autoria', 'badge-secondary', 'IP'),
(3, 'Apurando crime a esclarecer', 'badge-warning', 'IP'),
(4, 'Enviado ao PJ com autoria', 'badge-success', 'IP'),
(5, 'Enviado ao PJ sem autoria', 'badge-info', 'IP'),
(6, 'Enviado ao PJ com pedido de dilação de prazo', 'badge-danger', 'IP'),
(7, 'Enviado ao PJ com pedido de arquivamento', 'badge-dark', 'IP'),
(8, 'Apurando (VPI)', 'badge-primary', 'VPI'),
(9, 'Arquivada (VPI)', 'badge-secondary', 'VPI'),
(10, 'Encaminhada para outra delegacia (VPI)', 'badge-warning', 'VPI'),
(11, 'Convertida para IP (VPI)', 'badge-danger', 'VPI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `SolicitacoesCautelares`
--

CREATE TABLE `SolicitacoesCautelares` (
  `ID` int NOT NULL,
  `ProcedimentoID` int DEFAULT NULL,
  `ProcessoJudicial` varchar(255) DEFAULT NULL,
  `DataSolicitacao` date NOT NULL,
  `Observacoes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `SolicitacoesCautelares`
--

INSERT INTO `SolicitacoesCautelares` (`ID`, `ProcedimentoID`, `ProcessoJudicial`, `DataSolicitacao`, `Observacoes`) VALUES
(16, 55, '6132438-96.2024.8.09.0137', '2024-12-13', ''),
(17, 123, '5223557-92.2023.8.09.0137', '2023-04-10', 'Prisão Temporária de Felipe da Silva Santos'),
(20, 80, '5830411-19.2024.8.09.0137', '2024-08-28', 'Quebra de sigilo dos dados da vítima'),
(22, 119, '5011019-92.2025.8.09.0137', '2025-01-09', ''),
(23, 55, '5039168-98.2025.8.09.0137', '2025-01-21', ''),
(24, 82, '5100146-50.2025.8.09.0134', '2025-02-10', ''),
(25, 55, '5113372-16.2025.8.09.0137', '2025-02-13', 'PEDIDO DE BUSCA E APREENSÃO - MICAEL AUGUSTO ALVES DE SALES - MOZARLÂNDIA-GO'),
(26, 119, '5117214-04.2025.8.09.0137', '2025-02-14', ''),
(27, 149, '5147396-70.2025.8.09.0137', '2025-02-25', ''),
(28, 125, '5185370-44.2025.8.09.0137', '2025-03-12', ''),
(29, 157, '5221504-70.2025.8.09.0137', '2025-03-24', 'PEDIDO DE PRISÃO TEMPORÁRIA'),
(30, 157, '5221504-70.2025.8.09.0137', '2025-03-24', 'PEDIDO DE BUSCA E APREENSÃO'),
(31, 160, '5225614-15.2025.8.09.0137', '2025-03-25', 'PEDIDO DE PRISÃO TEMPORÁRIA E BUSCA E APREENSÃO'),
(32, 168, '5280274-56.2025.8.09.0137', '2025-04-10', 'PREISÃO TEMPORÁRIA DO ATIRADOR FORAGIDO - WESLEY'),
(33, 162, '5333841-02.2025.8.09.0137', '2025-04-30', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `TiposCautelar`
--

CREATE TABLE `TiposCautelar` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `TiposCautelar`
--

INSERT INTO `TiposCautelar` (`ID`, `Nome`) VALUES
(1, 'Busca e Apreensão'),
(4, 'Interceptação Telefônica'),
(2, 'Prisão Preventiva'),
(3, 'Prisão Temporária'),
(5, 'Quebra de Sigilo Bancário'),
(6, 'Quebra de Sigilo de Dados Telemáticos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `TiposMovimentacao`
--

CREATE TABLE `TiposMovimentacao` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Prioridade` enum('Normal','Alta') NOT NULL DEFAULT 'Normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `TiposMovimentacao`
--

INSERT INTO `TiposMovimentacao` (`ID`, `Nome`, `Prioridade`) VALUES
(1, 'Requisição MP', 'Normal'),
(2, 'Prisões', 'Alta'),
(3, 'Outros', 'Normal'),
(4, 'OMP', 'Normal'),
(5, 'Remessa de IP', 'Alta'),
(6, 'Perícias', 'Normal'),
(8, 'Local de Crime', 'Normal'),
(9, 'Ofícios', 'Normal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `TiposMovimentacaoObjeto`
--

CREATE TABLE `TiposMovimentacaoObjeto` (
  `ID` int NOT NULL,
  `Nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Descricao` text COLLATE utf8mb4_unicode_ci,
  `DataCriacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Cor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'bg-secondary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `TiposMovimentacaoObjeto`
--

INSERT INTO `TiposMovimentacaoObjeto` (`ID`, `Nome`, `Descricao`, `DataCriacao`, `Cor`) VALUES
(1, 'Entrada', 'Entrada inicial do objeto no sistema', '2025-06-02 13:53:53', 'bg-success'),
(2, 'Saída para Perícia', 'Objeto encaminhado para perícia', '2025-06-02 13:53:53', 'bg-info'),
(3, 'Retorno da Perícia', 'Objeto retornou da perícia', '2025-06-02 13:53:53', 'bg-info'),
(4, 'Encaminhamento ao Depósito Judicial', 'Objeto encaminhado ao depósito judicial', '2025-06-02 13:53:53', 'bg-primary'),
(5, 'Devolução', 'Objeto devolvido ao proprietário', '2025-06-02 13:53:53', 'bg-warning text-dark'),
(6, 'Destruição', 'Objeto destruído', '2025-06-02 13:53:53', 'bg-danger'),
(7, 'Transferência', 'Objeto transferido para outra unidade', '2025-06-02 13:53:53', 'bg-secondary'),
(8, 'Outros', 'Outros tipos de movimentação', '2025-06-02 13:53:53', 'bg-secondary');

-- --------------------------------------------------------

--
-- Estrutura para tabela `TiposObjeto`
--

CREATE TABLE `TiposObjeto` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `TiposObjeto`
--

INSERT INTO `TiposObjeto` (`ID`, `Nome`) VALUES
(1, 'Celular'),
(2, 'Drogas'),
(3, 'Veículo'),
(4, 'Arma de Fogo'),
(6, 'Arma Branca'),
(7, 'Munição'),
(8, 'Outros');

-- --------------------------------------------------------

--
-- Estrutura para tabela `TiposProcedimento`
--

CREATE TABLE `TiposProcedimento` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `TiposProcedimento`
--

INSERT INTO `TiposProcedimento` (`ID`, `Nome`) VALUES
(1, 'IP'),
(2, 'VPI');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Usuarios`
--

CREATE TABLE `Usuarios` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Senha` varchar(255) NOT NULL,
  `Telefone` varchar(15) DEFAULT NULL,
  `Funcao` enum('SuperAdmin','Admin','User') NOT NULL,
  `CargoID` int NOT NULL,
  `DelegaciaID` int NOT NULL,
  `TrocarSenha` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Usuarios`
--

INSERT INTO `Usuarios` (`ID`, `Nome`, `Usuario`, `Senha`, `Telefone`, `Funcao`, `CargoID`, `DelegaciaID`, `TrocarSenha`) VALUES
(2, 'Fernando Michel de Freitas', 'admin', '$2y$10$MSrULUmoBG9Ke3g9SwkzGe.5NcdIVokUiww69Y.wZDGlV0DFT.5b2', '5564999225006', 'SuperAdmin', 2, 1, 0),
(4, 'Rafaela Dallagnol Secorun', 'rafaela', '$2y$10$NV804UvAYdgBIpthrBFBvu19QeWaMX2DfzvT0k8uvQBJrk4zjeExa', NULL, 'User', 2, 1, 0),
(5, 'Adelson Candeo Junior', 'adelson', '$2y$10$MSrULUmoBG9Ke3g9SwkzGe.5NcdIVokUiww69Y.wZDGlV0DFT.5b2', NULL, 'User', 1, 1, 0),
(6, 'José Alves da Silva Júnior', 'jose.junior', '$2y$10$MSrULUmoBG9Ke3g9SwkzGe.5NcdIVokUiww69Y.wZDGlV0DFT.5b2', NULL, 'User', 2, 1, 0),
(7, 'Mirianne', 'mirianne', '$2y$10$MSrULUmoBG9Ke3g9SwkzGe.5NcdIVokUiww69Y.wZDGlV0DFT.5b2', NULL, 'User', 4, 1, 0),
(8, 'Karina', 'karina', '$2y$10$MSrULUmoBG9Ke3g9SwkzGe.5NcdIVokUiww69Y.wZDGlV0DFT.5b2', NULL, 'User', 2, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Veiculos`
--

CREATE TABLE `Veiculos` (
  `id` int NOT NULL,
  `placa` varchar(10) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `cor` varchar(20) NOT NULL,
  `observacoes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Veiculos`
--

INSERT INTO `Veiculos` (`id`, `placa`, `marca`, `modelo`, `cor`, `observacoes`) VALUES
(3, 'SDM9D13', 'Hyundai', 'HB20', 'Branco', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Vitimas`
--

CREATE TABLE `Vitimas` (
  `ID` int NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Idade` int DEFAULT NULL,
  `ProcedimentoID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Vitimas`
--

INSERT INTO `Vitimas` (`ID`, `Nome`, `Idade`, `ProcedimentoID`) VALUES
(131, 'CASSIO BRUNO BARROSO', 48, 55),
(132, 'LUCAS PAULO BORGES', 28, 56),
(133, 'ELDA PEREIRA DA SILVA', 40, 57),
(134, 'CAROLINA DA SILVA LIMA', 23, 58),
(135, 'FLAVIO DE JESUS NERY', 30, 59),
(136, 'A COLETIVIDADE', 1, 60),
(137, 'JOAO SEVERINO MACEDO', 66, 61),
(138, 'FELIPE DE SÁ REIS', 22, 61),
(139, 'JOAO BATISTA DA SILVA', 56, 62),
(140, 'MARIA LUCIA DOS SANTOS GOULART SILVA', 55, 62),
(141, 'ANIZIO RICARDO PEREIRA SILVA', 27, 63),
(142, 'LEANDRO SANTOS ROSA', 1, 64),
(143, 'A COLETIVIDADE', 1, 65),
(144, 'JULIANO PETERSON SOUZA DURIGON', 46, 66),
(145, 'OCLEVERSON PEREIRA DA SILVA', 47, 67),
(146, 'JONILTON DE SOUZA QUEIROZ', 31, 68),
(147, 'CASSIO BRUNO BARROSO', 48, 69),
(148, 'EDILSON GOMES SILVA', 30, 70),
(149, 'SAMUEL COSTA', 23, 71),
(150, 'FERNANDO VINNICIUS COIMBRA DE MORAIS', 30, 72),
(151, 'ALESSANDRA MORAIS DA CONCEIÇÃO', 52, 72),
(152, 'ITAMAR', 55, 72),
(153, 'CARLOS EDUARDO DO CARMO FRANCA', 23, 73),
(154, 'A COLETIVIDADE', 1, 74),
(155, 'FABRICIO BERNARDES', 41, 75),
(156, 'ANTÔNIO SIVERNANDES SILVA', 41, 76),
(157, 'ANTONIO CARLOS CARVALHO MARINHO', 53, 77),
(158, 'JIVANILSON NASCIMENTO DOS SANTOS', 33, 78),
(159, 'RAIMUNDO FARIAS SILVA', 27, 79),
(160, 'MATHEUS ALMEIDA RIBEIRO', 25, 80),
(161, 'ROGERIO OLIVEIRA NASCIMENTO FILHO', 21, 81),
(162, 'GABRIEL FERREIRA SILVA', 25, 81),
(163, 'JEFFERSON CURY', 83, 82),
(164, 'LEONARDO RIBEIRO NALESSO', 40, 82),
(165, 'NILTON OLIVEIRA FERNANDES', 31, 83),
(166, 'O ESTADO', 1, 84),
(167, 'DANILO FERREIRA PERES', 26, 85),
(168, 'ADÃO NASCIMENTO BASTOS', 32, 86),
(169, 'MARAISA MARTINS DE OLIVEIRA', 37, 86),
(170, 'TANIA CUNHA ARAUJO', 62, 86),
(171, 'SERGIO VITOR BATISTA OLIVEIRA MELO', 23, 87),
(172, 'A SAÚDE PÚBLICA', 1, 88),
(173, 'VINÍCIUS SILVA VIEIRA', 24, 89),
(174, 'KATHERINE LILIANA ROJAS TORRES', 35, 90),
(175, 'GEORGE DIAS PEREIRA', 29, 91),
(176, 'CELIOMAR AQUINO DA SILVA', 47, 92),
(177, 'NIELE SANTOS DIAS', 26, 93),
(178, 'VANDERLEI NOGUEIRA DOS SANTOS', 36, 94),
(179, 'LEANDRO JOSÉ DA SILVA', 31, 95),
(180, 'JEANNE SANCHES NASCIMENTO', 32, 96),
(181, 'GUILLER GONÇALVES DE SOUZA', 19, 96),
(182, 'NÃO IDENTIFICADA', 1, 97),
(183, 'IVAN DOS SANTOS', 41, 98),
(184, 'DIEGO DOS SANTOS SALES', 34, 99),
(185, 'AUIR LOPES GONÇALVES ', 59, 100),
(186, 'RAINE LOPES GONÇALVES', 55, 100),
(187, 'BRUNO SOUSA DA SILVA', 25, 101),
(188, 'DANIEL DONEGA DE OLIVEIRA', 38, 102),
(189, 'OTÁVIO ALVES DE ALMEIDA', 15, 103),
(190, 'ALBERTO CARLOS DE ALMEIDA', 50, 103),
(191, 'ALDA CELESTE ALVES BATISTA DE ALMEIDA', 54, 103),
(192, 'ANNA PAULA SILVA', 27, 104),
(193, 'FERNANDO MARTINS DE ANDRADE JUNQUEIRA', 61, 105),
(194, 'ANDRÉ GOMES DA SILVA', 22, 106),
(195, 'FABIO JUNIOR APOLINARIO VILELA', 36, 107),
(196, 'SINOMAR MENDES TEODORO', 45, 108),
(197, 'JÉSSICA MARTINS DE SOUSA', 31, 109),
(198, 'DALVANIRA ALVES MARTINS FUGA', 44, 110),
(199, 'OSVALDO FUGA FILHO', 1, 110),
(200, 'MAX ALEN DE FREITAS SOUZA', 43, 111),
(201, 'WESILON DE SOUZA RAMOS', 57, 112),
(202, 'FRANSCISCO CARLOS SILVA MENDES', 16, 113),
(203, 'ANDERSON GAMA DOS SANTOS', 26, 114),
(204, 'DIEGO SILVA LIMA', 27, 115),
(205, 'VALTER FERREIRA DE LIMA', 56, 115),
(206, 'PAULO AUGUSTO DA SILVA', 59, 116),
(207, 'FABIO RODRIGUES CARVALHO', 29, 117),
(208, 'PAULO AUGUSTO DA SILVA', 59, 118),
(209, 'TACIO LUIZ DA SILVA', 24, 119),
(210, 'MARCOS VINICIUS OLIVEIRA CARDOSO', 13, 120),
(211, 'FABIO RODRIGUES CARVALHO', 29, 121),
(212, 'CARLOS EDUARDO SOARES DE OLIVEIRA', 16, 122),
(213, 'GIRLENO BEZERRA DE LIMA', 37, 123),
(214, 'JOSE DAVI EUFRASIO DOS SANTOS', 30, 123),
(215, 'WALISSON DUARTE SILVA', 1, 124),
(216, 'SAÚDE PÚBLICA', 1, 124),
(217, 'LUCIVANIA CAMPOS MOREIRA', 1, 125),
(218, 'ADALBERTO NEVES LIMA', 34, 126),
(219, 'TIAGO DE MEDEIROS BRANDÃO', 1, 127),
(220, 'ACINETO ALVES DA SILVA NETO', 1, 127),
(221, 'ALEX APARECIDO GOMES DOS SANTOS', 1, 127),
(222, 'EDUARDO RODRIGUES FERREIRA', 1, 127),
(223, 'MAYKON DOUGLAS SANTANA SOUSA', 22, 128),
(224, 'MARCIEL ALEXANDRE DA SILVA', 35, 129),
(225, 'ADÃO JUSTINO DOS SANTOS', 1, 130),
(226, 'MAXWEL CARDOSO BORGES', 30, 131),
(227, 'GILVAN DA CONCEIÇÃO', 50, 132),
(228, 'MARIA DO CARMO MARTINS DA SILVA', 45, 133),
(229, 'JOSÉ GABRIEL DAS NEVES', 75, 134),
(230, 'CÁSSIO BRUNO BARROZO', 40, 135),
(231, 'LUILIO MARCIEL VIEIRA', 41, 136),
(232, 'MAURICIO SANTOS BARBOSA', 30, 137),
(233, 'JUCELIO ROQUE DE SOUSA', 54, 138),
(234, 'ILSON PAULA DE LIMA', 52, 139),
(235, 'ROGÉRIO ALVES DE ASSIS', 41, 140),
(236, 'MARCOS CESAR PEREIRA LOPES', 27, 140),
(237, 'THIAGO ARAÚJO ALEXANDRE', 34, 141),
(238, 'ANDRE LUIZ CABRAL DE GOUVEIA', 39, 142),
(239, 'LUCAS JESUS DE SOUZA', 30, 143),
(240, 'CARLOS HENRIQUE DA VEIGA RODRIGUES', 31, 144),
(241, 'MARIA JOSÉ TELES DA MOTA', 75, 145),
(242, 'EDER CARLOS ROSA MORAES', 59, 146),
(243, 'YAGO NUNES DE OLIVEIRA', 28, 147),
(244, 'JOSIVALDO DA SILVA', 38, 148),
(245, 'IGNORADA', 1, 149),
(246, 'CASSIO PEREIRA BORGES', 24, 150),
(247, 'JOELSON DA SILVA DOS SANTOS', 24, 151),
(248, 'TIAGO OLIVEIRA', 32, 152),
(249, 'JOSÉ ADONIAS DOS SANTOS FILHO', 72, 152),
(250, 'MARIA DA PENHA DE OLIVEIRA', 60, 152),
(251, 'DIOMAR RAMOS DA CUNHA', 52, 153),
(252, 'RICARDO MEDEIROS ALEIXO', 40, 154),
(253, 'JAVAN DA SILVA MEDEIROS', 38, 155),
(254, 'HELIO SANTANA DE ARAUJO', 22, 156),
(255, 'JOELSON DA SILVA DOS SANTOS', 24, 157),
(256, 'FERNANDA OLIVEIRA GARCIA', 43, 158),
(257, 'LAILSON DA COSTA DO ROSÁRIO', 32, 159),
(258, 'LAILSON DA COSTA DO ROSÁRIO', 32, 160),
(259, 'VIVALDO SANTOS MARINHO', 35, 161),
(260, 'LAURINDO DE SOUZA VIEIRA JUNIOR', 45, 162),
(261, 'FELYPE BUENO VIEIRA', 24, 162),
(262, 'KLAYTON CERQUEIRA SANTOS', 27, 163),
(263, 'WESLEY PERES CAETANO', 39, 164),
(264, 'MURILO DA SILVA CRUVINEL', 36, 165),
(265, 'CARLOS EDUARDO DE OLIVEIRA COSTA', 26, 166),
(266, 'VALDEMIR DOS SANTOS', 24, 167),
(267, 'WELLINGTON CARDOSO DOS SANTOS', 28, 168),
(268, 'CLAUDIA CRUVINEL MARQUES SANTIAGO', 51, 169),
(269, 'MAX WILLIAN SOARES SILVA', 30, 170),
(270, 'MARCOS RENATO MORAES DA SILVA', 18, 171),
(271, 'JEIEL ROCHA DE SOUSA (CONSUMADO) E MARCIO JUNQUEIRA DA SILVA ( TENTADO)', 46, 172),
(272, 'ISAC DOS SANTOS NERYS', 41, 173),
(273, 'FELIPE FELICIANO DA SILVA', 25, 174),
(274, 'ULISSES APOLINARIO DOS SANTOS', 44, 175),
(275, 'FÓRUM DE RIO VERDE-GO', 30, 176),
(276, 'JONATAS MARQUES DOS SANTOS', 20, 177),
(277, 'GUILHERME DE MELO FERREIRA ', 23, 178),
(278, 'JONATAS MARQUES DOS SANTOS', 19, 179),
(279, 'AUGUSTO CEZAR PAIXÃO DA SILVA', 19, 180),
(280, 'MESSIAS GONÇALVES DE OLIVEIRA', 49, 181),
(281, 'RODRIGO ALVES FERREIRA', 41, 182),
(282, 'WEMERSON GARCIA CARMO', 27, 183),
(283, 'JOAO BATISTA PORFIRO DE MORAES', 26, 183),
(284, 'FLAVIO PEREIRA MAIA', 38, 184),
(285, 'SÉRGIO REIS DE SOUSA', 42, 185),
(286, 'WESLEY FERNANDES DOS SANTOS LUVISA', 21, 186),
(287, 'A COLETIVIDADE', 20, 187),
(288, 'MARINA  DE LOURDES FAGUNDES OLIVEIRA', 41, 188),
(289, 'CILIOMAR DIAS CAMPOS', 34, 189),
(290, 'CRISTIANO ALVES SILVA (suicídio)', 40, 190),
(291, 'FERNANDO NASCIMENTO SILVA', 32, 191),
(292, 'LUCIANO DA SILVA SANTOS', 25, 192),
(293, 'WELITON ARAUJO SILVA', 21, 193),
(294, 'GUSTAVO SADESKI PAIXÃO', 22, 194),
(295, 'ANDRÉ JUNIOR SILVA (HOMICIDIO) ', 46, 195),
(296, ' WARLEY CARDOSO DE SOUZA(LESÃO)', 46, 195),
(297, 'CARLOS HENRIQUE DE FREITAS GONÇALVES', 23, 196),
(298, 'PAULO VITOR XAVIER', 20, 197),
(299, 'CACILDA PEREIRA BUENO', 71, 198),
(300, 'LUIZ FERNANDO ROSA ESCOBAR ', 21, 199),
(301, 'VITOR PAULINO SANTANA', 21, 199),
(302, 'MARCOS GOMES FEITOSA', 21, 199),
(303, ' KATIUSCIA SANTANA GARCIA(tentado)', 21, 199),
(304, 'BRUNO MANOEL LOPES DOS SANTOS ', 21, 199),
(305, 'DEVISON MELO PEREIRA', 29, 200),
(306, 'LARISSA ARAÚJO SILVA', 20, 201),
(307, 'SILAS FERDINAN DE OLIVEIRA', 41, 202),
(308, 'SIDNEY RODRIGUES SILVA CUNHA', 33, 203),
(309, ' MANOEL MESSIAS ALVES DA CRUZ', 56, 203),
(310, 'WILLIAN RIBEIRO MARTINS', 33, 204),
(311, 'JOSÉ CARVALHO DOS SANTOS', 61, 205),
(312, 'EDIVANILSON GOUVEA DO COUTO JUNIOR', 24, 206),
(313, 'MATEUS ALVES BARBOSA', 18, 207),
(314, 'REJANE DOS SANTOS ROSA', 65, 208),
(315, 'GERSON JEFERSON PEREIRA ARAÚJO ', 65, 208),
(316, 'IGOR ANGELO DOS SANTOS OLIVEIRA', 26, 209),
(317, 'ARTUR PEREIRA JUNIOR', 33, 210),
(318, 'REGINALDO ROSA DA SILVA', 38, 211),
(319, 'CLAYTON PEREIRA GOMES DE ABREU', 31, 212),
(320, 'DANIEL MEDEIROS DE OLIVEIRA', 35, 213),
(321, 'JOSE LUCAS MOURÃO ALVES', 19, 214),
(322, 'ORLANDO ALVES DA SILVA', 19, 215),
(323, 'INCOLUMIDADE PUBLICA', 19, 216),
(325, 'JACKSON BORGES SOUZA', 20, 218),
(326, 'EDSON JOSE DOS SANTOS', 20, 219),
(327, 'FERNANDA ALMEIDA DE LIMA ', 20, 220),
(328, ' LEANDRO SOUZA NASCIMENTO ', 20, 220),
(329, 'ROBERVAL PEREIRA', 50, 221),
(330, 'ROSANGELA DE AQUINO', 20, 222),
(331, 'MARLOS CLEI DA SILVA MENDES', 20, 223),
(332, 'WILSON MORAIS DE OLIVEIRA', 20, 224),
(333, 'GELCI VIAN', 40, 225),
(334, 'JOAO BATISTA DA SILVA', 20, 226),
(335, 'AMARIO MARTINS DA SILVA', 20, 227),
(336, 'WANDERSON FERREIRA DA SILVA', 20, 228),
(337, 'ALMIR AUGUSTO DE SOUZA FERREIRA', 20, 229),
(338, 'PATRICIA DE SOUZA VIANA', 20, 229),
(339, 'FILIPE VILELA DE OLIVEIRA', 20, 230),
(340, 'ROZENILDA PEREIRA GUIMARAES', 20, 231),
(341, 'ANGELA SILVA JUSTINO', 19, 232),
(342, 'RAFAEL SOUSA TEIXEIRA', 19, 233),
(343, 'HERIBERTO OLIVEIRA SILVA', 20, 234),
(344, 'ANTONIO JOSE DOS SANTOS', 20, 235),
(345, 'LUIS CARLOS MARQUES DE OLIVEIRA', 20, 236),
(346, 'ROGERIO SOARES DA SILVA', 20, 237),
(347, 'ALTINO DE OLIVEIRA JUNIOR', 20, 238),
(348, 'ALEX DE SOUSA', 20, 239),
(349, 'JOSE FABIANO CAETANO', 20, 240),
(350, 'BEBÊ NEONATO - SEM IDENTIFICAÇÃO', 1, 241),
(351, 'JACOB PAES DE OLIVEIRA ', 65, 242),
(352, ' JACOB PAES DE OLIVEIRA JUNIOR', 25, 242),
(353, 'JOSÉ CÍCERO MACENA', 20, 243),
(354, 'JOSE RUBENS DA SILVA FILHO', 30, 244),
(355, 'ANTONIO SEBASTIÃO PRACHEDE FERREIRA', 39, 245),
(356, 'JOAO VICTOR LOPES PRACHEDE FERREIRA ', 18, 245),
(357, 'MATHEUS BAUHER FERREIRA SILVA', 20, 246),
(358, 'BRUNO RAFAEL DIAS DA SILVA', 20, 247),
(359, 'MATEUS BAUER DIAS DA SILVA', 20, 248),
(360, 'DAVI LUCAS ALVES', 20, 249),
(361, 'DAVI LUCAS ALVES', 20, 250),
(362, 'JAILSON ALVES DOS SANTOS', 20, 251),
(363, 'JOAO PEDRO OLIVEIRA SOUSA', 20, 252),
(364, 'INCOLUMIDADE PUBLICA', 20, 253),
(365, 'ADRIEL JERRE MOREIRA SOARES ', 20, 254),
(366, ' LUCILA CARRIJO DE OLIVEIRA', 20, 254),
(367, 'WEMERSON FERREIRA RODRIGUES', 27, 255),
(368, 'ADRIANO DE JESUS CHAVES', 20, 256),
(369, 'JOSE AIRTON RODRIGUES FILHO', 20, 257),
(370, 'IGOR SILVA FARIA', 20, 258),
(371, 'BRUNNER JULIANO CABRAL SILVA', 20, 259),
(372, 'JOSE VITORIO FERREIRA MARQUES', 20, 260),
(373, 'MAICON EVANIR BEZ BETTI', 20, 261),
(374, 'RONY EVANDRO SERRÃO MACEDO', 20, 262),
(375, 'DANIEL PIRES DE SOUSA FILHO', 40, 263),
(376, 'ALACI GONÇALVES SANTIAGO', 20, 264),
(377, 'WESLEI PEREIRA DOS SANTOS', 30, 265),
(378, 'MAICON DOS SANTOS OLIVEIRA', 20, 266),
(379, 'JOSUE DOMINGOS GOMES', 21, 267),
(380, 'LEANDRO PEREIRA SILVA', 20, 268),
(381, 'CARLOS ANTONIO VIEIRA NASCIMENTO FILHO', 20, 269),
(382, 'WESLEY FRANCO DE LIMA', 20, 270),
(383, 'LUCAS BENTO TAVARES e WELLINGOTN SILVEIRO DA SILVA', 20, 271),
(384, ' WELLINGOTN SILVEIRO DA SILVA', 20, 271),
(385, 'JOAO PEDRO GONÇALVES DOS PASSOS', 20, 272),
(386, 'HIGOR MESSIAS SOARES ', 20, 273),
(387, 'JOSE CARLOS ARANTES DA SILVA ', 20, 273),
(388, 'IDELFRAN CORREIA DOS SANTOS', 20, 274),
(389, 'WEDSON REIS DE OLIVEIRA', 20, 275),
(390, 'MARCIO HENRIQUE OLIVEIRA SILVA', 30, 276),
(391, 'MARCOS TADEU FREDERICO SEVERO', 29, 277),
(392, 'VANESSA DE SOUZA NASCIMENTO', 29, 278),
(393, 'JANE ANDRADE DE OLIVEIRA', 29, 278),
(394, 'MARCIA CRISTINA SOUZA PAULINO', 29, 279),
(395, 'WIGNER BRITO ALVES LEITE', 17, 280),
(396, 'RAFAEL FRANCESCATO DE LIMA', 28, 281),
(397, 'MATUES SOUSA CUNHA', 28, 281),
(398, 'JOSE CARLOS DA SILVA JUNIOR ', 28, 281),
(399, 'NEILTON DA SILVA COELHO ', 28, 281),
(400, 'UELTON DA SILVA AMARAL', 29, 282),
(401, 'EMILY BORGES DE LELIS', 15, 283),
(402, 'UBIRAJARA SILVA DOS SANTOS', 52, 284),
(403, 'PEDRO EMILIO DE ARAUJO', 66, 285),
(404, 'EDIMAR PEREIRA DA SILVA JUNIOR', 30, 286),
(405, 'WASHINGTON OLIVEIRA ALVES', 37, 287),
(406, 'MARCOS NOGUEIRA REZENDE', 45, 288),
(407, 'LAILTON DA SILVA SANTOS', 20, 289),
(408, 'NATANAEL ALMEIDA FERREIRA JUNIOR', 20, 290),
(409, 'DARLAN MAGALHÃES VILELA', 20, 291),
(410, 'ANDREY MARTINS OLIVEIRA DA SILVA', 19, 292),
(411, 'ULISSES ACACIO MOREIRA', 20, 293),
(412, 'GABRIEL FRANKLIN MARQUES DE AGUIAR', 20, 294),
(413, 'GEOVANE FERREIRA DE PAIVA', 20, 295),
(414, 'FABYANNA NEVES DA SILVA', 20, 296),
(415, 'NATANAEL FERREIRA', 20, 297),
(416, 'HELIANE MONTEIRO DOS SANTOS', 20, 298),
(417, 'JOCIEL SOUSA DE JESUS', 20, 298),
(418, 'LEANDRO RODRIGUES DE OLIVEIRA', 20, 299),
(419, 'ELIVAN SOARES SILVA', 20, 300),
(420, 'VITOR RODRIGUES DE OLIVEIRA', 20, 300),
(421, ' YAGO LEMOS BARRETO', 20, 300),
(422, 'ISAEL DA SILVA SANTOS', 20, 301),
(423, 'EDMUNDO RAMOS ALVES DE OLIVEIRA', 40, 302),
(424, 'ALISSON DOS SANTOS SILVA', 20, 303),
(425, 'CARLOS ALEXANDRE ALMEIDA OLHER', 20, 304),
(426, 'LAZARO MARTINS GOMES', 20, 305),
(427, 'JAIR ANTONIO DA COSTA', 30, 306),
(428, ' RICARDO CAUAN LINS DOS SANTOS', 20, 307),
(429, 'VALDEMIR FERREIRA DOS SANTOS', 20, 307),
(430, 'LEONARDO MACHADO BARBOSA', 20, 308),
(431, 'ANDERSON DONIZETE CAMPOS', 20, 309),
(432, 'FRANCISCO JORGE SUNÇÃO DE SOUSA', 20, 310),
(433, 'ITOR JUNIOR PIRES', 20, 311),
(434, 'EVANDRO TIBA SANTOS', 30, 312),
(435, 'NEILTON GUILHERME PAZ FILHO', 27, 313),
(436, 'PAULIANA DUARTE DA SILVA', 27, 314),
(437, 'DANILO JESUS DOS SANTOS', 27, 315),
(438, 'RICARDO ROSA SOUZA', 27, 316),
(439, 'HEVELYN MONTINE SANTOS', 30, 317),
(440, 'ROSINEIDE NASCIMENTO', 20, 318),
(441, 'ALESSANDRA', 29, 319),
(442, 'GEOVANE FERREIRA DE PAIVA', 29, 319),
(443, 'VARLEY DAVID DA COSTA', 30, 320),
(444, 'JOSE RIBAMAR AZEVEDO', 30, 321),
(445, 'JOSE RIBAMAR AZEVEDO', 30, 322),
(446, 'PEDRO RODRIGUES MACHADO', 68, 323),
(447, 'RAIMUNDO JOSÉ ARAUJO DE SOUZA', 50, 324),
(448, 'MARCELO DOS SANTOS SOUSA', 20, 325),
(449, 'CARLOS RODRIGUES DOS SANTOS SOBRINHO', 30, 326),
(450, 'ANDERSON RAMON SOUSA BORGES', 30, 327),
(451, 'DIVINO JOHNNY PEREIRA BORGES', 30, 327),
(452, 'HANGRA BARBOSA ALVES DE LIMA', 22, 328),
(453, 'EDUARDO RICARDO ALVES', 22, 329),
(454, 'MILLER REZENDE DE SOUZA', 22, 330),
(455, 'EVERSON GUILHERME PEREIRA RIBEIRO', 30, 331),
(456, ' LUCIANO VIEIRA DE OLIVEIRA', 22, 332),
(457, 'WISLEY BARBOSA DA COSTA', 22, 332),
(458, 'ELIELTON NUNES ', 23, 333),
(459, 'SARA CRISTINA BISPO SOUSA', 22, 333),
(460, 'JAIME FELIX DAS NEVES FILHO', 27, 334),
(461, 'RENATO PEIXOTO', 27, 335),
(462, 'JOSE MARTINS SOARES', 27, 336),
(463, ' MARIA ALENILTA DUTRA', 27, 337),
(464, 'MARCOS ANTONIO SILVA ALVES ', 27, 337),
(465, 'PAULO JOAO BOTELHO GUIMARAES', 27, 337),
(466, 'ONILDO ALUIZIO SOUZA SILVA NETO', 36, 338),
(467, 'CRISTIANO LOPES DE MELO', 35, 339),
(468, 'GUSTAVO JOSE PEREIRA', 23, 340),
(469, 'LUZINEIA ROSA FERREIRA', 32, 341),
(470, 'JOSE RUBEM DE SOUZA', 32, 342),
(471, 'EDILSON NERES', 29, 343),
(472, ' PAULO VITOR DE SOUSA SILVA - GCM', 30, 344),
(473, 'RENAN EDUARDO PEREIRA SANTOS ', 30, 344),
(474, 'JOCIENE BISPO DOS SANTOS', 28, 345),
(475, 'NELSON REGIO MOREIRA DOS SANTOS', 40, 346),
(476, 'JOAO ADILSON DA SILVA', 30, 347),
(477, 'FUNCIONARIOS, ALUNOS DO CURSO DE PSICOLOGIA', 18, 348),
(478, 'COLETIVIDADE ', 20, 349),
(479, 'EWERTON SOUSA MACEDO', 47, 350),
(480, 'FAUSTO MARTINS RIBEIRO FILHO', 27, 351),
(481, 'RAIANA DOS SANTOS ', 22, 351),
(482, 'EDILEZIO DA SILVA OLIVEIRA', 48, 352),
(483, 'VANDERLEI FERREIRA NASCIMENTO', 55, 353),
(484, 'ANA LAURA MATTOS SOUZA ', 30, 354),
(485, ' FRANCIELLE MATTOS GOLDINHO', 30, 354),
(486, 'MARIANA MATTOS SOUZA', 30, 354),
(487, 'MARIA HELENA MATTOS SOUZA ', 30, 354),
(488, 'VICENTE RAMOS DE OLIVEIRA SOUZA', 30, 354),
(489, 'FABRICIA STEFANI ARRUDA DE CASTRO', 27, 355),
(490, 'MARLON SILVA OLIVEIRA JUNIOR', 30, 356),
(491, 'SEBASTIAO DE PAIVA OLIVEIRA', 60, 357),
(492, 'LUCIANA GONÇALVES SILVA MORAES ', 43, 358),
(493, 'WESLEY MORAES DOS SANTOS', 38, 358),
(494, 'DANIEL ALVES OLIVEIRA', 24, 359),
(495, ' CAIO GOMES VIERA', 25, 360),
(496, 'GUSTAVO ALEXANDRE CEZARIO (consumado)', 25, 360),
(497, 'THAYS DO CARMO MATTOS (tentado)', 25, 360),
(498, 'WELLINGTON LUIZ FERREIRA FREITAS', 67, 361),
(499, 'GILCLEITON SOARES LOPES', 26, 362),
(500, 'FRANCISCO DA SILVA (GEPATRI)', 73, 363),
(501, 'JEFFERSON CAMPOS DOURADO', 26, 364),
(502, 'THIAGO LOPES DA SILVA ', 33, 364),
(503, 'Não identificada', 20, 365),
(504, 'MIGUEL DE JESUS BARROS DA COSTA', 22, 366),
(505, 'AVELINO FRANISCO VANI', 64, 367),
(506, 'O ESTADO', 30, 368),
(507, 'ADRIANO DE JESUS CARACIOLA', 29, 369),
(508, 'ATAÍDES SOUSA PERES', 63, 370),
(509, 'SONIA DA SILVA FERREIRA', 50, 371),
(510, 'JOSÉ MARCOS MANICOBA LUSTOSA', 30, 372),
(511, ' ANA PAULA ARANTES', 30, 373),
(512, 'PATRICK FERREIRA DA SILVA', 24, 373),
(513, 'COLETIVIDADE', 30, 374),
(514, 'BRUNO LUCIO CARDOSO', 42, 375),
(515, 'GUSTAVO JOSE PEREIRA', 30, 376),
(516, 'DERICK LUCA SILVA BRAGA', 2, 377),
(517, 'JOAO DOS SANTOS SILVA', 30, 378),
(518, 'SONILDO SANTANA BATISTA JUNIOR', 22, 379),
(519, ' AGATHA MARIA DE SOUSA', 3, 380),
(520, 'ELAINE BARBOSA DE SOUSA', 28, 380),
(521, 'SARA SUNSHINE SOUSA BONIFACIO', 5, 380),
(522, 'GABRIEL SOUZA DA CRUZ', 18, 381),
(523, 'LUCAS WILAMYS DA SILVA', 31, 382),
(524, 'MARCOS CABRAL SALES', 30, 383),
(525, 'MAX WILLIAN SOARES SILVA', 18, 384),
(526, 'EMANUELLE SOUZA BATISTA', 20, 385),
(527, 'DELCIMAR TELES DE QUEIROZ', 40, 386),
(528, 'MAICON DOS SANTOS OLIVEIRA', 20, 387),
(529, 'LAILTON DA SILVA SANTOS', 20, 388),
(530, 'JESSE DA SILVA LIMA', 30, 389),
(531, 'MARCELO APARECIDO PEREIRA DOS SANTOS', 44, 390),
(532, 'ACACIO BATISTA FELIPE', 43, 391),
(533, 'LEANDRO GARCIA SOUSA', 40, 392),
(534, 'RIVADAVIO DA CONCEIÇÃO ARAUJO', 30, 393),
(535, 'THIAGO PEDRO FERREIRA', 32, 394),
(536, 'LUCIO ADRIANO SCHNEIDER', 39, 395),
(537, 'NEYVAN NERY DA SILVA', 39, 396),
(538, 'JOSE RUBSON DE OLIVEIRA', 39, 397),
(539, 'EVERALDO HERMINIO DOS SANTOS', 39, 398),
(540, 'CHARLES GOUVEIA FERREIRA', 30, 399),
(541, 'WESLEY DA SILVA MARTINS', 30, 400),
(542, 'GUILHERME DE OLIVEIRA SOUZA', 22, 401),
(543, 'THAIS MICHELE SANTOS FERREIRA', 25, 401),
(544, 'ALBERT PEREIRA', 46, 402),
(545, 'GEORGE VINICIUS LARA', 39, 403),
(546, 'JOAO BATISTA DA SILVA', 50, 404),
(547, 'JOAO MARIA DOS SANTOS', 44, 405),
(548, 'JOAO PEDRO DOS SANTOS RIBEIRO', 21, 406),
(549, 'LEANDRO DE OLIVEIRA', 30, 407),
(550, 'JOSÉ CARLOS DA COSTA DA SILVA', 36, 408),
(551, 'RODRIGO LAABS', 30, 409),
(552, ' MARCELO SILVA TELES', 40, 410),
(553, 'MAYK HENRIQUE SOUZA DA SILVA', 20, 411),
(554, ' APARECIDO FRANCISCO DE JESUS', 55, 412),
(555, 'ERNESTO SITTA FILHO', 63, 413),
(556, 'NILTON TELES PEREIRA', 33, 414),
(557, 'LEONARDO THALES BARROS DOURADO', 30, 415),
(558, 'JOSIMAR DE OLIVEIRA SILVA', 33, 416),
(559, 'FRANCISCO BARBOSA LULA NETO', 37, 417),
(560, 'FABIO SIPRIANO DA SILVA ', 30, 418),
(561, 'IDAEL SILVA DE SOUSA', 30, 418),
(562, 'LUCAS DA SILVA DOS SANTOS', 25, 419),
(563, 'KAMYLA PANIAGO RODRIGUES', 20, 420),
(564, ' STENER ANTONIO DOS SANTOS', 20, 420),
(565, 'MARCIA PEREIRA CABRAL DE SOUSA', 30, 421),
(566, 'CAIQUE HENRIQUE BASILIO DE LIMA', 29, 422),
(567, 'ELEONEL SILVA DOS SANTOS ', 23, 423),
(568, 'FÁBIO JUNIOR FERREIRA DA SILVA', 22, 423),
(569, 'CHRISTIAN PROFIRO DA CRUZ', 16, 424),
(570, 'MAURICIO ALVES TORRES LISBOA', 34, 425),
(571, ' SIDNEI GARCIA SANTOS', 28, 425),
(572, 'LORENÇO MENDONÇA MELO', 58, 426),
(573, 'MAELCIO SANTOS DE PORTUGAL', 20, 427),
(574, 'ZILIOMAR DA SILVA FERREIRA', 31, 428),
(576, 'TESTE', 20, 430),
(577, 'JOSIEL ROBERTO FERREIRA DE OLIVEIRA', 25, 431),
(578, 'MANOEL GONÇALVES FERREIRA', 60, 432),
(579, 'LUCAS SILVA OLIVEIRA', 17, 433),
(580, 'JHON LENON FERNANDES COSTA', 27, 434),
(581, 'CARLOS HENRIQUE BERALDO DE FREITAS', 31, 435),
(582, 'WILTON MARTINS DE FREITAS', 53, 436),
(583, 'CRISTIANO ROSA', 26, 437),
(584, 'OZIAS SANTANA DA SILVA', 37, 438),
(585, 'FRANKLIN DE SOUSA FERREIRA', 25, 439),
(586, 'WHATSON MARTINS KARAMOTO', 42, 440),
(587, 'ANAYSA CARDOSO MATOS', 23, 441),
(588, 'CHARLES DANIEL FIGUEIREDO', 51, 442),
(589, 'JOSÉ OSVALDO GOMES DA SILVA', 21, 443);

-- --------------------------------------------------------

--
-- Estrutura para tabela `Vitimas_Crimes`
--

CREATE TABLE `Vitimas_Crimes` (
  `ID` int NOT NULL,
  `VitimaID` int NOT NULL,
  `CrimeID` int NOT NULL,
  `Modalidade` enum('Tentado','Consumado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `Vitimas_Crimes`
--

INSERT INTO `Vitimas_Crimes` (`ID`, `VitimaID`, `CrimeID`, `Modalidade`) VALUES
(309, 137, 1, 'Consumado'),
(310, 138, 1, 'Tentado'),
(316, 142, 17, 'Consumado'),
(327, 150, 1, 'Tentado'),
(328, 151, 27, 'Consumado'),
(329, 152, 27, 'Consumado'),
(338, 156, 1, 'Consumado'),
(339, 157, 1, 'Consumado'),
(340, 158, 1, 'Consumado'),
(350, 166, 23, 'Consumado'),
(355, 171, 1, 'Tentado'),
(356, 172, 23, 'Consumado'),
(358, 174, 2, 'Consumado'),
(365, 136, 12, 'Consumado'),
(366, 177, 1, 'Tentado'),
(367, 178, 1, 'Tentado'),
(368, 179, 1, 'Tentado'),
(373, 183, 1, 'Consumado'),
(378, 188, 1, 'Tentado'),
(379, 189, 26, 'Tentado'),
(380, 190, 22, 'Consumado'),
(381, 191, 26, 'Consumado'),
(386, 196, 17, 'Consumado'),
(395, 202, 1, 'Consumado'),
(396, 203, 1, 'Consumado'),
(405, 135, 14, 'Consumado'),
(410, 211, 14, 'Consumado'),
(411, 207, 14, 'Consumado'),
(412, 212, 1, 'Consumado'),
(413, 213, 1, 'Tentado'),
(414, 214, 1, 'Tentado'),
(417, 215, 14, 'Consumado'),
(418, 216, 23, 'Consumado'),
(419, 217, 2, 'Consumado'),
(420, 218, 1, 'Tentado'),
(421, 219, 1, 'Consumado'),
(422, 220, 1, 'Consumado'),
(423, 221, 1, 'Consumado'),
(424, 222, 1, 'Consumado'),
(425, 223, 1, 'Consumado'),
(426, 208, 1, 'Tentado'),
(428, 225, 17, 'Consumado'),
(429, 160, 1, 'Consumado'),
(432, 226, 1, 'Consumado'),
(435, 224, 1, 'Tentado'),
(441, 228, 2, 'Consumado'),
(442, 227, 22, 'Consumado'),
(443, 229, 16, 'Consumado'),
(445, 230, 28, 'Consumado'),
(446, 231, 1, 'Consumado'),
(447, 206, 1, 'Tentado'),
(454, 234, 14, 'Consumado'),
(455, 235, 14, 'Consumado'),
(456, 236, 14, 'Consumado'),
(457, 210, 27, 'Consumado'),
(459, 233, 1, 'Consumado'),
(462, 237, 16, 'Consumado'),
(468, 239, 14, 'Consumado'),
(469, 240, 1, 'Tentado'),
(474, 242, 17, 'Consumado'),
(475, 241, 16, 'Consumado'),
(476, 243, 14, 'Consumado'),
(484, 248, 1, 'Tentado'),
(485, 249, 22, 'Consumado'),
(486, 250, 22, 'Consumado'),
(487, 251, 16, 'Consumado'),
(488, 246, 1, 'Tentado'),
(489, 245, 28, 'Consumado'),
(490, 252, 14, 'Consumado'),
(491, 253, 16, 'Consumado'),
(492, 254, 17, 'Consumado'),
(493, 247, 1, 'Tentado'),
(496, 257, 1, 'Tentado'),
(497, 258, 1, 'Tentado'),
(498, 259, 16, 'Consumado'),
(499, 260, 1, 'Tentado'),
(500, 261, 1, 'Tentado'),
(501, 262, 1, 'Tentado'),
(502, 141, 14, 'Consumado'),
(504, 263, 1, 'Tentado'),
(506, 265, 1, 'Consumado'),
(507, 266, 16, 'Consumado'),
(508, 256, 16, 'Consumado'),
(509, 267, 1, 'Consumado'),
(511, 268, 17, 'Consumado'),
(515, 270, 15, 'Consumado'),
(532, 286, 16, 'Consumado'),
(541, 295, 1, 'Consumado'),
(542, 296, 22, 'Consumado'),
(544, 298, 1, 'Tentado'),
(545, 299, 17, 'Consumado'),
(552, 306, 2, 'Consumado'),
(569, 322, 1, 'Consumado'),
(570, 323, 12, 'Consumado'),
(572, 325, 1, 'Tentado'),
(574, 327, 2, 'Consumado'),
(575, 328, 16, 'Consumado'),
(576, 329, 1, 'Consumado'),
(577, 330, 1, 'Consumado'),
(579, 332, 1, 'Consumado'),
(580, 333, 1, 'Consumado'),
(581, 334, 1, 'Consumado'),
(583, 336, 1, 'Consumado'),
(587, 340, 1, 'Tentado'),
(593, 346, 1, 'Tentado'),
(600, 353, 1, 'Consumado'),
(601, 354, 1, 'Consumado'),
(604, 357, 31, 'Consumado'),
(605, 358, 31, 'Consumado'),
(606, 359, 1, 'Consumado'),
(607, 360, 1, 'Consumado'),
(608, 361, 32, 'Consumado'),
(609, 362, 31, 'Consumado'),
(610, 363, 31, 'Consumado'),
(611, 364, 12, 'Consumado'),
(614, 367, 31, 'Tentado'),
(615, 368, 1, 'Consumado'),
(622, 374, 31, 'Consumado'),
(624, 376, 31, 'Consumado'),
(625, 377, 1, 'Consumado'),
(626, 378, 22, 'Consumado'),
(628, 380, 1, 'Consumado'),
(629, 381, 14, 'Consumado'),
(633, 385, 31, 'Consumado'),
(634, 386, 1, 'Consumado'),
(635, 387, 1, 'Tentado'),
(636, 388, 1, 'Consumado'),
(637, 389, 31, 'Consumado'),
(640, 391, 31, 'Tentado'),
(643, 394, 31, 'Tentado'),
(653, 400, 1, 'Consumado'),
(654, 401, 31, 'Tentado'),
(656, 403, 31, 'Tentado'),
(662, 409, 31, 'Tentado'),
(665, 412, 1, 'Consumado'),
(666, 413, 33, 'Tentado'),
(692, 437, 31, 'Consumado'),
(693, 438, 31, 'Tentado'),
(699, 443, 31, 'Tentado'),
(702, 446, 31, 'Tentado'),
(709, 453, 14, 'Consumado'),
(712, 456, 31, 'Tentado'),
(713, 457, 31, 'Tentado'),
(716, 460, 1, 'Consumado'),
(731, 475, 1, 'Tentado'),
(734, 478, 28, 'Consumado'),
(739, 483, 1, 'Consumado'),
(747, 490, 14, 'Consumado'),
(751, 494, 1, 'Consumado'),
(764, 507, 1, 'Tentado'),
(765, 508, 28, 'Consumado'),
(770, 513, 39, 'Consumado'),
(776, 519, 2, 'Consumado'),
(777, 520, 2, 'Consumado'),
(778, 521, 2, 'Tentado'),
(783, 526, 1, 'Consumado'),
(789, 532, 1, 'Consumado'),
(790, 533, 1, 'Consumado'),
(792, 517, 1, 'Tentado'),
(793, 515, 1, 'Tentado'),
(794, 273, 28, 'Consumado'),
(799, 275, 29, 'Consumado'),
(800, 274, 1, 'Consumado'),
(801, 514, 1, 'Consumado'),
(803, 276, 30, 'Consumado'),
(805, 285, 15, 'Consumado'),
(806, 540, 1, 'Tentado'),
(807, 278, 28, 'Consumado'),
(808, 287, 23, 'Consumado'),
(809, 288, 2, 'Consumado'),
(810, 289, 1, 'Tentado'),
(812, 284, 14, 'Consumado'),
(813, 281, 1, 'Consumado'),
(816, 279, 22, 'Consumado'),
(817, 277, 26, 'Consumado'),
(818, 272, 28, 'Consumado'),
(819, 525, 14, 'Consumado'),
(820, 544, 1, 'Tentado'),
(821, 290, 16, 'Consumado'),
(822, 271, 15, 'Consumado'),
(824, 546, 1, 'Tentado'),
(825, 547, 1, 'Consumado'),
(826, 548, 1, 'Consumado'),
(827, 549, 26, 'Consumado'),
(828, 550, 1, 'Consumado'),
(829, 551, 1, 'Consumado'),
(830, 552, 1, 'Consumado'),
(831, 553, 14, 'Consumado'),
(832, 554, 11, 'Consumado'),
(833, 555, 1, 'Tentado'),
(834, 556, 1, 'Tentado'),
(835, 557, 1, 'Tentado'),
(836, 558, 1, 'Tentado'),
(837, 559, 1, 'Tentado'),
(838, 560, 1, 'Tentado'),
(839, 561, 40, 'Tentado'),
(840, 562, 1, 'Tentado'),
(841, 563, 28, 'Consumado'),
(842, 564, 28, 'Consumado'),
(843, 565, 12, 'Consumado'),
(844, 566, 1, 'Tentado'),
(845, 567, 1, 'Tentado'),
(846, 568, 1, 'Tentado'),
(847, 569, 1, 'Tentado'),
(848, 570, 1, 'Tentado'),
(849, 571, 1, 'Tentado'),
(850, 572, 1, 'Tentado'),
(851, 573, 28, 'Consumado'),
(852, 292, 1, 'Consumado'),
(853, 379, 31, 'Tentado'),
(854, 293, 1, 'Tentado'),
(855, 326, 14, 'Consumado'),
(856, 294, 1, 'Tentado'),
(858, 305, 1, 'Tentado'),
(859, 343, 1, 'Tentado'),
(860, 307, 1, 'Tentado'),
(861, 282, 1, 'Tentado'),
(862, 283, 1, 'Tentado'),
(865, 308, 1, 'Tentado'),
(866, 309, 1, 'Tentado'),
(867, 310, 1, 'Consumado'),
(868, 311, 1, 'Consumado'),
(869, 320, 1, 'Tentado'),
(870, 314, 1, 'Consumado'),
(871, 315, 1, 'Tentado'),
(872, 316, 1, 'Consumado'),
(873, 317, 1, 'Consumado'),
(874, 318, 1, 'Tentado'),
(875, 312, 1, 'Consumado'),
(876, 313, 15, 'Consumado'),
(877, 319, 1, 'Tentado'),
(878, 463, 1, 'Tentado'),
(879, 464, 1, 'Tentado'),
(880, 465, 1, 'Tentado'),
(881, 467, 1, 'Consumado'),
(882, 468, 1, 'Consumado'),
(883, 469, 1, 'Tentado'),
(884, 470, 1, 'Tentado'),
(885, 472, 14, 'Tentado'),
(886, 473, 14, 'Tentado'),
(887, 474, 1, 'Tentado'),
(888, 476, 34, 'Consumado'),
(891, 480, 1, 'Tentado'),
(892, 481, 1, 'Tentado'),
(893, 479, 1, 'Consumado'),
(894, 471, 1, 'Tentado'),
(895, 489, 2, 'Consumado'),
(896, 477, 28, 'Consumado'),
(897, 484, 28, 'Consumado'),
(898, 485, 28, 'Consumado'),
(899, 486, 28, 'Consumado'),
(900, 487, 1, 'Consumado'),
(901, 488, 28, 'Consumado'),
(902, 466, 14, 'Consumado'),
(903, 491, 26, 'Consumado'),
(904, 506, 37, 'Consumado'),
(905, 498, 32, 'Consumado'),
(906, 499, 22, 'Consumado'),
(907, 482, 1, 'Consumado'),
(908, 509, 1, 'Consumado'),
(909, 510, 22, 'Consumado'),
(910, 495, 1, 'Consumado'),
(911, 496, 1, 'Consumado'),
(912, 497, 1, 'Tentado'),
(913, 528, 22, 'Consumado'),
(914, 534, 1, 'Consumado'),
(915, 535, 1, 'Consumado'),
(916, 536, 1, 'Tentado'),
(917, 537, 1, 'Tentado'),
(918, 539, 1, 'Tentado'),
(919, 538, 28, 'Consumado'),
(920, 541, 1, 'Consumado'),
(921, 542, 1, 'Tentado'),
(922, 543, 1, 'Tentado'),
(923, 545, 1, 'Consumado'),
(924, 530, 1, 'Tentado'),
(926, 503, 1, 'Consumado'),
(927, 396, 1, 'Consumado'),
(928, 397, 1, 'Consumado'),
(929, 398, 22, 'Consumado'),
(930, 399, 22, 'Consumado'),
(931, 408, 1, 'Consumado'),
(932, 416, 31, 'Tentado'),
(933, 417, 31, 'Tentado'),
(937, 428, 31, 'Tentado'),
(938, 429, 31, 'Tentado'),
(939, 430, 31, 'Tentado'),
(940, 382, 1, 'Consumado'),
(944, 341, 2, 'Consumado'),
(945, 344, 1, 'Consumado'),
(946, 347, 1, 'Consumado'),
(947, 452, 2, 'Consumado'),
(948, 427, 1, 'Consumado'),
(949, 455, 31, 'Tentado'),
(950, 458, 31, 'Consumado'),
(951, 459, 31, 'Consumado'),
(952, 461, 1, 'Tentado'),
(953, 462, 1, 'Tentado'),
(954, 426, 31, 'Consumado'),
(956, 297, 1, 'Tentado'),
(957, 134, 2, 'Consumado'),
(959, 201, 1, 'Tentado'),
(960, 197, 2, 'Consumado'),
(961, 184, 1, 'Tentado'),
(962, 193, 16, 'Consumado'),
(963, 185, 1, 'Tentado'),
(964, 186, 16, 'Consumado'),
(965, 180, 1, 'Tentado'),
(966, 181, 1, 'Consumado'),
(968, 175, 1, 'Consumado'),
(969, 168, 1, 'Tentado'),
(970, 169, 1, 'Tentado'),
(971, 170, 1, 'Tentado'),
(972, 167, 1, 'Consumado'),
(973, 165, 1, 'Tentado'),
(974, 161, 1, 'Tentado'),
(975, 162, 1, 'Tentado'),
(976, 154, 15, 'Consumado'),
(977, 159, 22, 'Consumado'),
(978, 155, 1, 'Tentado'),
(979, 153, 1, 'Tentado'),
(980, 149, 1, 'Tentado'),
(981, 148, 26, 'Consumado'),
(982, 147, 1, 'Consumado'),
(983, 146, 1, 'Consumado'),
(984, 145, 1, 'Consumado'),
(985, 144, 1, 'Consumado'),
(986, 143, 12, 'Consumado'),
(987, 139, 1, 'Consumado'),
(988, 140, 1, 'Tentado'),
(989, 133, 2, 'Consumado'),
(990, 132, 1, 'Consumado'),
(991, 131, 1, 'Consumado'),
(992, 204, 1, 'Tentado'),
(993, 205, 1, 'Tentado'),
(994, 209, 1, 'Consumado'),
(995, 269, 14, 'Consumado'),
(996, 524, 1, 'Tentado'),
(997, 291, 1, 'Tentado'),
(998, 163, 1, 'Consumado'),
(999, 164, 1, 'Tentado'),
(1000, 522, 1, 'Consumado'),
(1001, 500, 1, 'Consumado'),
(1002, 505, 1, 'Tentado'),
(1003, 492, 15, 'Consumado'),
(1004, 493, 15, 'Consumado'),
(1007, 511, 1, 'Tentado'),
(1008, 512, 1, 'Tentado'),
(1009, 523, 1, 'Consumado'),
(1010, 501, 1, 'Consumado'),
(1011, 502, 1, 'Tentado'),
(1012, 516, 1, 'Consumado'),
(1013, 390, 1, 'Consumado'),
(1014, 392, 31, 'Tentado'),
(1015, 393, 31, 'Tentado'),
(1016, 395, 31, 'Tentado'),
(1017, 402, 1, 'Consumado'),
(1018, 405, 31, 'Tentado'),
(1019, 406, 31, 'Tentado'),
(1020, 407, 31, 'Tentado'),
(1021, 529, 1, 'Tentado'),
(1022, 404, 31, 'Tentado'),
(1023, 410, 31, 'Tentado'),
(1024, 418, 1, 'Consumado'),
(1025, 422, 31, 'Tentado'),
(1026, 414, 31, 'Tentado'),
(1027, 454, 1, 'Consumado'),
(1028, 419, 14, 'Consumado'),
(1029, 420, 14, 'Consumado'),
(1030, 421, 14, 'Consumado'),
(1031, 425, 31, 'Consumado'),
(1032, 432, 31, 'Consumado'),
(1033, 415, 1, 'Consumado'),
(1034, 431, 31, 'Tentado'),
(1035, 433, 31, 'Tentado'),
(1036, 424, 1, 'Consumado'),
(1037, 339, 14, 'Consumado'),
(1038, 435, 31, 'Tentado'),
(1039, 436, 16, 'Consumado'),
(1040, 365, 31, 'Consumado'),
(1041, 366, 31, 'Consumado'),
(1043, 434, 1, 'Consumado'),
(1044, 445, 31, 'Tentado'),
(1045, 444, 31, 'Tentado'),
(1046, 441, 1, 'Consumado'),
(1047, 442, 1, 'Consumado'),
(1048, 448, 1, 'Consumado'),
(1049, 411, 31, 'Tentado'),
(1050, 439, 1, 'Consumado'),
(1051, 423, 31, 'Tentado'),
(1052, 447, 31, 'Tentado'),
(1053, 440, 2, 'Consumado'),
(1054, 449, 31, 'Tentado'),
(1055, 450, 31, 'Tentado'),
(1056, 451, 1, 'Consumado'),
(1057, 373, 14, 'Consumado'),
(1058, 321, 1, 'Tentado'),
(1059, 335, 1, 'Tentado'),
(1060, 337, 1, 'Consumado'),
(1061, 338, 1, 'Tentado'),
(1062, 342, 1, 'Consumado'),
(1063, 345, 1, 'Tentado'),
(1064, 348, 1, 'Tentado'),
(1065, 331, 1, 'Tentado'),
(1066, 349, 1, 'Consumado'),
(1067, 351, 1, 'Consumado'),
(1068, 352, 1, 'Consumado'),
(1069, 355, 31, 'Tentado'),
(1070, 356, 31, 'Tentado'),
(1071, 531, 1, 'Consumado'),
(1072, 369, 31, 'Consumado'),
(1074, 370, 31, 'Consumado'),
(1075, 371, 31, 'Consumado'),
(1076, 372, 1, 'Consumado'),
(1077, 527, 1, 'Tentado'),
(1078, 375, 1, 'Consumado'),
(1079, 383, 31, 'Consumado'),
(1080, 384, 31, 'Consumado'),
(1081, 518, 1, 'Tentado'),
(1082, 300, 1, 'Consumado'),
(1083, 301, 1, 'Consumado'),
(1084, 302, 28, 'Tentado'),
(1085, 303, 28, 'Tentado'),
(1086, 304, 28, 'Tentado'),
(1087, 280, 28, 'Consumado'),
(1088, 195, 1, 'Consumado'),
(1089, 194, 1, 'Tentado'),
(1090, 192, 1, 'Tentado'),
(1091, 182, 17, 'Consumado'),
(1092, 187, 1, 'Consumado'),
(1093, 176, 1, 'Consumado'),
(1094, 232, 14, 'Consumado'),
(1095, 238, 1, 'Tentado'),
(1096, 244, 1, 'Tentado'),
(1097, 264, 1, 'Consumado'),
(1098, 350, 31, 'Tentado'),
(1101, 198, 2, 'Consumado'),
(1102, 199, 16, 'Consumado'),
(1103, 200, 11, 'Consumado'),
(1104, 200, 15, 'Consumado'),
(1105, 200, 12, 'Consumado'),
(1106, 574, 14, 'Consumado'),
(1107, 173, 1, 'Consumado'),
(1111, 576, 37, 'Consumado'),
(1112, 577, 1, 'Tentado'),
(1113, 578, 1, 'Consumado'),
(1114, 504, 1, 'Tentado'),
(1115, 579, 14, 'Consumado'),
(1116, 580, 1, 'Tentado'),
(1117, 581, 1, 'Consumado'),
(1118, 582, 14, 'Consumado'),
(1119, 583, 1, 'Consumado'),
(1120, 584, 1, 'Consumado'),
(1121, 585, 14, 'Consumado'),
(1122, 586, 16, 'Consumado'),
(1124, 587, 2, 'Consumado'),
(1126, 588, 1, 'Consumado'),
(1127, 255, 1, 'Tentado'),
(1128, 589, 1, 'Tentado');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `Anotacoes`
--
ALTER TABLE `Anotacoes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`),
  ADD KEY `UsuarioCriadorID` (`UsuarioCriadorID`);

--
-- Índices de tabela `ArmaCalibre`
--
ALTER TABLE `ArmaCalibre`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `ArmaEspecie`
--
ALTER TABLE `ArmaEspecie`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `ArmaMarca`
--
ALTER TABLE `ArmaMarca`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `ArmaModelo`
--
ALTER TABLE `ArmaModelo`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `ArmasFogo`
--
ALTER TABLE `ArmasFogo`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ObjetoID` (`ObjetoID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`),
  ADD KEY `EspecieID` (`EspecieID`),
  ADD KEY `CalibreID` (`CalibreID`),
  ADD KEY `MarcaID` (`MarcaID`),
  ADD KEY `ModeloID` (`ModeloID`),
  ADD KEY `ProcessoJudicialID` (`ProcessoJudicialID`);

--
-- Índices de tabela `Cargos`
--
ALTER TABLE `Cargos`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `ComentariosDesaparecimentos`
--
ALTER TABLE `ComentariosDesaparecimentos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `DesaparecidoID` (`DesaparecidoID`),
  ADD KEY `UsuarioCriadorID` (`UsuarioCriadorID`);

--
-- Índices de tabela `compromissos`
--
ALTER TABLE `compromissos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `Crimes`
--
ALTER TABLE `Crimes`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `CumprimentosCautelares`
--
ALTER TABLE `CumprimentosCautelares`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `SolicitacaoCautelarID` (`SolicitacaoCautelarID`),
  ADD KEY `TipoCautelarID` (`TipoCautelarID`);

--
-- Índices de tabela `Delegacias`
--
ALTER TABLE `Delegacias`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `Desaparecidos`
--
ALTER TABLE `Desaparecidos`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `DocumentosMovimentacao`
--
ALTER TABLE `DocumentosMovimentacao`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `MovimentacaoID` (`MovimentacaoID`);

--
-- Índices de tabela `EnvolvidosCumprimentoCautelar`
--
ALTER TABLE `EnvolvidosCumprimentoCautelar`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `CumprimentoCautelarID` (`CumprimentoCautelarID`);

--
-- Índices de tabela `FavoritosUsuarios`
--
ALTER TABLE `FavoritosUsuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `UsuarioID` (`UsuarioID`,`ProcedimentoID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`);

--
-- Índices de tabela `Investigados`
--
ALTER TABLE `Investigados`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`);

--
-- Índices de tabela `ItensSolicitacaoCautelar`
--
ALTER TABLE `ItensSolicitacaoCautelar`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `SolicitacaoCautelarID` (`SolicitacaoCautelarID`),
  ADD KEY `TipoCautelarID` (`TipoCautelarID`);

--
-- Índices de tabela `LocaisArmazenagem`
--
ALTER TABLE `LocaisArmazenagem`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `MeiosEmpregados`
--
ALTER TABLE `MeiosEmpregados`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `Movimentacoes`
--
ALTER TABLE `Movimentacoes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TipoID` (`TipoID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Índices de tabela `MovimentacoesObjeto`
--
ALTER TABLE `MovimentacoesObjeto`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ObjetoID` (`ObjetoID`),
  ADD KEY `TipoMovimentacaoID` (`TipoMovimentacaoID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Índices de tabela `Objetos`
--
ALTER TABLE `Objetos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TipoObjetoID` (`TipoObjetoID`),
  ADD KEY `SituacaoID` (`SituacaoID`),
  ADD KEY `LocalArmazenagemID` (`LocalArmazenagemID`);

--
-- Índices de tabela `Oficios`
--
ALTER TABLE `Oficios`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`),
  ADD KEY `ResponsavelID` (`ResponsavelID`),
  ADD KEY `fk_movimentacao` (`MovimentacaoID`);

--
-- Índices de tabela `OrigensProcedimentos`
--
ALTER TABLE `OrigensProcedimentos`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `Policiais`
--
ALTER TABLE `Policiais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `funcional` (`funcional`);

--
-- Índices de tabela `Procedimentos`
--
ALTER TABLE `Procedimentos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `SituacaoID` (`SituacaoID`),
  ADD KEY `TipoID` (`TipoID`),
  ADD KEY `OrigemID` (`OrigemID`),
  ADD KEY `EscrivaoID` (`EscrivaoID`),
  ADD KEY `DelegadoID` (`DelegadoID`),
  ADD KEY `DelegaciaID` (`DelegaciaID`);

--
-- Índices de tabela `ProcedimentosMeiosEmpregados`
--
ALTER TABLE `ProcedimentosMeiosEmpregados`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`),
  ADD KEY `MeioEmpregadoID` (`MeioEmpregadoID`);

--
-- Índices de tabela `ProcessosJudiciais`
--
ALTER TABLE `ProcessosJudiciais`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`);

--
-- Índices de tabela `RAIs`
--
ALTER TABLE `RAIs`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`);

--
-- Índices de tabela `Senhas`
--
ALTER TABLE `Senhas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `SituacoesObjeto`
--
ALTER TABLE `SituacoesObjeto`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `SituacoesProcedimento`
--
ALTER TABLE `SituacoesProcedimento`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `SolicitacoesCautelares`
--
ALTER TABLE `SolicitacoesCautelares`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `TiposCautelar`
--
ALTER TABLE `TiposCautelar`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `TiposMovimentacao`
--
ALTER TABLE `TiposMovimentacao`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `TiposMovimentacaoObjeto`
--
ALTER TABLE `TiposMovimentacaoObjeto`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `TiposObjeto`
--
ALTER TABLE `TiposObjeto`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `TiposProcedimento`
--
ALTER TABLE `TiposProcedimento`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nome` (`Nome`);

--
-- Índices de tabela `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD KEY `CargoID` (`CargoID`),
  ADD KEY `DelegaciaID` (`DelegaciaID`);

--
-- Índices de tabela `Veiculos`
--
ALTER TABLE `Veiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- Índices de tabela `Vitimas`
--
ALTER TABLE `Vitimas`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ProcedimentoID` (`ProcedimentoID`);

--
-- Índices de tabela `Vitimas_Crimes`
--
ALTER TABLE `Vitimas_Crimes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `VitimaID` (`VitimaID`),
  ADD KEY `CrimeID` (`CrimeID`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `Anotacoes`
--
ALTER TABLE `Anotacoes`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `ArmaCalibre`
--
ALTER TABLE `ArmaCalibre`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `ArmaEspecie`
--
ALTER TABLE `ArmaEspecie`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `ArmaMarca`
--
ALTER TABLE `ArmaMarca`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `ArmaModelo`
--
ALTER TABLE `ArmaModelo`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `ArmasFogo`
--
ALTER TABLE `ArmasFogo`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `Cargos`
--
ALTER TABLE `Cargos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `ComentariosDesaparecimentos`
--
ALTER TABLE `ComentariosDesaparecimentos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `compromissos`
--
ALTER TABLE `compromissos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `Crimes`
--
ALTER TABLE `Crimes`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `CumprimentosCautelares`
--
ALTER TABLE `CumprimentosCautelares`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `Delegacias`
--
ALTER TABLE `Delegacias`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `Desaparecidos`
--
ALTER TABLE `Desaparecidos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de tabela `DocumentosMovimentacao`
--
ALTER TABLE `DocumentosMovimentacao`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `EnvolvidosCumprimentoCautelar`
--
ALTER TABLE `EnvolvidosCumprimentoCautelar`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de tabela `FavoritosUsuarios`
--
ALTER TABLE `FavoritosUsuarios`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `Investigados`
--
ALTER TABLE `Investigados`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=854;

--
-- AUTO_INCREMENT de tabela `ItensSolicitacaoCautelar`
--
ALTER TABLE `ItensSolicitacaoCautelar`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `LocaisArmazenagem`
--
ALTER TABLE `LocaisArmazenagem`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `MeiosEmpregados`
--
ALTER TABLE `MeiosEmpregados`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `Movimentacoes`
--
ALTER TABLE `Movimentacoes`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT de tabela `MovimentacoesObjeto`
--
ALTER TABLE `MovimentacoesObjeto`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `Objetos`
--
ALTER TABLE `Objetos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de tabela `Oficios`
--
ALTER TABLE `Oficios`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `OrigensProcedimentos`
--
ALTER TABLE `OrigensProcedimentos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `Policiais`
--
ALTER TABLE `Policiais`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `Procedimentos`
--
ALTER TABLE `Procedimentos`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=444;

--
-- AUTO_INCREMENT de tabela `ProcedimentosMeiosEmpregados`
--
ALTER TABLE `ProcedimentosMeiosEmpregados`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=880;

--
-- AUTO_INCREMENT de tabela `ProcessosJudiciais`
--
ALTER TABLE `ProcessosJudiciais`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=688;

--
-- AUTO_INCREMENT de tabela `RAIs`
--
ALTER TABLE `RAIs`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

--
-- AUTO_INCREMENT de tabela `Senhas`
--
ALTER TABLE `Senhas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `SituacoesObjeto`
--
ALTER TABLE `SituacoesObjeto`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `SituacoesProcedimento`
--
ALTER TABLE `SituacoesProcedimento`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `SolicitacoesCautelares`
--
ALTER TABLE `SolicitacoesCautelares`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `TiposCautelar`
--
ALTER TABLE `TiposCautelar`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `TiposMovimentacao`
--
ALTER TABLE `TiposMovimentacao`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `TiposMovimentacaoObjeto`
--
ALTER TABLE `TiposMovimentacaoObjeto`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `TiposObjeto`
--
ALTER TABLE `TiposObjeto`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `TiposProcedimento`
--
ALTER TABLE `TiposProcedimento`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `Usuarios`
--
ALTER TABLE `Usuarios`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `Veiculos`
--
ALTER TABLE `Veiculos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `Vitimas`
--
ALTER TABLE `Vitimas`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=590;

--
-- AUTO_INCREMENT de tabela `Vitimas_Crimes`
--
ALTER TABLE `Vitimas_Crimes`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1129;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `Anotacoes`
--
ALTER TABLE `Anotacoes`
  ADD CONSTRAINT `Anotacoes_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`),
  ADD CONSTRAINT `Anotacoes_ibfk_2` FOREIGN KEY (`UsuarioCriadorID`) REFERENCES `Usuarios` (`ID`);

--
-- Restrições para tabelas `ArmasFogo`
--
ALTER TABLE `ArmasFogo`
  ADD CONSTRAINT `ArmasFogo_ibfk_1` FOREIGN KEY (`ObjetoID`) REFERENCES `Objetos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ArmasFogo_ibfk_2` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ArmasFogo_ibfk_3` FOREIGN KEY (`EspecieID`) REFERENCES `ArmaEspecie` (`ID`),
  ADD CONSTRAINT `ArmasFogo_ibfk_4` FOREIGN KEY (`CalibreID`) REFERENCES `ArmaCalibre` (`ID`),
  ADD CONSTRAINT `ArmasFogo_ibfk_5` FOREIGN KEY (`MarcaID`) REFERENCES `ArmaMarca` (`ID`),
  ADD CONSTRAINT `ArmasFogo_ibfk_6` FOREIGN KEY (`ModeloID`) REFERENCES `ArmaModelo` (`ID`),
  ADD CONSTRAINT `ArmasFogo_ibfk_7` FOREIGN KEY (`ProcessoJudicialID`) REFERENCES `ProcessosJudiciais` (`ID`);

--
-- Restrições para tabelas `ComentariosDesaparecimentos`
--
ALTER TABLE `ComentariosDesaparecimentos`
  ADD CONSTRAINT `ComentariosDesaparecimentos_ibfk_1` FOREIGN KEY (`DesaparecidoID`) REFERENCES `Desaparecidos` (`ID`),
  ADD CONSTRAINT `ComentariosDesaparecimentos_ibfk_2` FOREIGN KEY (`UsuarioCriadorID`) REFERENCES `Usuarios` (`ID`);

--
-- Restrições para tabelas `compromissos`
--
ALTER TABLE `compromissos`
  ADD CONSTRAINT `compromissos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `Usuarios` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `CumprimentosCautelares`
--
ALTER TABLE `CumprimentosCautelares`
  ADD CONSTRAINT `CumprimentosCautelares_ibfk_1` FOREIGN KEY (`SolicitacaoCautelarID`) REFERENCES `SolicitacoesCautelares` (`ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `CumprimentosCautelares_ibfk_2` FOREIGN KEY (`TipoCautelarID`) REFERENCES `TiposCautelar` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `DocumentosMovimentacao`
--
ALTER TABLE `DocumentosMovimentacao`
  ADD CONSTRAINT `DocumentosMovimentacao_ibfk_1` FOREIGN KEY (`MovimentacaoID`) REFERENCES `Movimentacoes` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `EnvolvidosCumprimentoCautelar`
--
ALTER TABLE `EnvolvidosCumprimentoCautelar`
  ADD CONSTRAINT `EnvolvidosCumprimentoCautelar_ibfk_1` FOREIGN KEY (`CumprimentoCautelarID`) REFERENCES `CumprimentosCautelares` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `FavoritosUsuarios`
--
ALTER TABLE `FavoritosUsuarios`
  ADD CONSTRAINT `FavoritosUsuarios_ibfk_1` FOREIGN KEY (`UsuarioID`) REFERENCES `Usuarios` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `FavoritosUsuarios_ibfk_2` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Investigados`
--
ALTER TABLE `Investigados`
  ADD CONSTRAINT `Investigados_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`);

--
-- Restrições para tabelas `ItensSolicitacaoCautelar`
--
ALTER TABLE `ItensSolicitacaoCautelar`
  ADD CONSTRAINT `ItensSolicitacaoCautelar_ibfk_1` FOREIGN KEY (`SolicitacaoCautelarID`) REFERENCES `SolicitacoesCautelares` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ItensSolicitacaoCautelar_ibfk_2` FOREIGN KEY (`TipoCautelarID`) REFERENCES `TiposCautelar` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `Movimentacoes`
--
ALTER TABLE `Movimentacoes`
  ADD CONSTRAINT `Movimentacoes_ibfk_1` FOREIGN KEY (`TipoID`) REFERENCES `TiposMovimentacao` (`ID`),
  ADD CONSTRAINT `Movimentacoes_ibfk_2` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`),
  ADD CONSTRAINT `Movimentacoes_ibfk_3` FOREIGN KEY (`UsuarioID`) REFERENCES `Usuarios` (`ID`);

--
-- Restrições para tabelas `MovimentacoesObjeto`
--
ALTER TABLE `MovimentacoesObjeto`
  ADD CONSTRAINT `MovimentacoesObjeto_ibfk_1` FOREIGN KEY (`ObjetoID`) REFERENCES `Objetos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `MovimentacoesObjeto_ibfk_2` FOREIGN KEY (`TipoMovimentacaoID`) REFERENCES `TiposMovimentacaoObjeto` (`ID`),
  ADD CONSTRAINT `MovimentacoesObjeto_ibfk_3` FOREIGN KEY (`UsuarioID`) REFERENCES `Usuarios` (`ID`);

--
-- Restrições para tabelas `Objetos`
--
ALTER TABLE `Objetos`
  ADD CONSTRAINT `Objetos_ibfk_1` FOREIGN KEY (`TipoObjetoID`) REFERENCES `TiposObjeto` (`ID`),
  ADD CONSTRAINT `Objetos_ibfk_2` FOREIGN KEY (`SituacaoID`) REFERENCES `SituacoesObjeto` (`ID`),
  ADD CONSTRAINT `Objetos_ibfk_3` FOREIGN KEY (`LocalArmazenagemID`) REFERENCES `LocaisArmazenagem` (`ID`);

--
-- Restrições para tabelas `Oficios`
--
ALTER TABLE `Oficios`
  ADD CONSTRAINT `fk_movimentacao` FOREIGN KEY (`MovimentacaoID`) REFERENCES `Movimentacoes` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Oficios_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `Oficios_ibfk_2` FOREIGN KEY (`ResponsavelID`) REFERENCES `Usuarios` (`ID`) ON DELETE SET NULL;

--
-- Restrições para tabelas `Procedimentos`
--
ALTER TABLE `Procedimentos`
  ADD CONSTRAINT `Procedimentos_ibfk_1` FOREIGN KEY (`SituacaoID`) REFERENCES `SituacoesProcedimento` (`ID`),
  ADD CONSTRAINT `Procedimentos_ibfk_2` FOREIGN KEY (`TipoID`) REFERENCES `TiposProcedimento` (`ID`),
  ADD CONSTRAINT `Procedimentos_ibfk_3` FOREIGN KEY (`OrigemID`) REFERENCES `OrigensProcedimentos` (`ID`),
  ADD CONSTRAINT `Procedimentos_ibfk_4` FOREIGN KEY (`EscrivaoID`) REFERENCES `Usuarios` (`ID`),
  ADD CONSTRAINT `Procedimentos_ibfk_5` FOREIGN KEY (`DelegadoID`) REFERENCES `Usuarios` (`ID`),
  ADD CONSTRAINT `Procedimentos_ibfk_6` FOREIGN KEY (`DelegaciaID`) REFERENCES `Delegacias` (`ID`);

--
-- Restrições para tabelas `ProcedimentosMeiosEmpregados`
--
ALTER TABLE `ProcedimentosMeiosEmpregados`
  ADD CONSTRAINT `ProcedimentosMeiosEmpregados_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ProcedimentosMeiosEmpregados_ibfk_2` FOREIGN KEY (`MeioEmpregadoID`) REFERENCES `MeiosEmpregados` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ProcessosJudiciais`
--
ALTER TABLE `ProcessosJudiciais`
  ADD CONSTRAINT `ProcessosJudiciais_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`);

--
-- Restrições para tabelas `RAIs`
--
ALTER TABLE `RAIs`
  ADD CONSTRAINT `RAIs_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`);

--
-- Restrições para tabelas `Usuarios`
--
ALTER TABLE `Usuarios`
  ADD CONSTRAINT `Usuarios_ibfk_1` FOREIGN KEY (`CargoID`) REFERENCES `Cargos` (`ID`),
  ADD CONSTRAINT `Usuarios_ibfk_2` FOREIGN KEY (`DelegaciaID`) REFERENCES `Delegacias` (`ID`);

--
-- Restrições para tabelas `Vitimas`
--
ALTER TABLE `Vitimas`
  ADD CONSTRAINT `Vitimas_ibfk_1` FOREIGN KEY (`ProcedimentoID`) REFERENCES `Procedimentos` (`ID`);

--
-- Restrições para tabelas `Vitimas_Crimes`
--
ALTER TABLE `Vitimas_Crimes`
  ADD CONSTRAINT `Vitimas_Crimes_ibfk_1` FOREIGN KEY (`VitimaID`) REFERENCES `Vitimas` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Vitimas_Crimes_ibfk_2` FOREIGN KEY (`CrimeID`) REFERENCES `Crimes` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
