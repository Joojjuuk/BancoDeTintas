-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2024 às 20:49
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `banco de tintas`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `adm`
--

CREATE TABLE `adm` (
  `nome` varchar(255) DEFAULT NULL,
  `email_inst` varchar(255) NOT NULL,
  `senha` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `adm`
--

INSERT INTO `adm` (`nome`, `email_inst`, `senha`) VALUES
('teste', 'admin@teste.com', 'testeadm01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `aprovar`
--

CREATE TABLE `aprovar` (
  `fk_adm_email_inst` varchar(255) NOT NULL,
  `fk_pedido_pedir_dt_retira` varchar(40) NOT NULL,
  `fk_pedido_pedir_id_usuario` int(11) NOT NULL,
  `fk_pedido_pedir_cod_tinta` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `autorizar`
--

CREATE TABLE `autorizar` (
  `fk_doacao_doar_dias_disp` varchar(40) NOT NULL,
  `fk_doacao_doar_id_usuario` int(11) NOT NULL,
  `fk_doacao_doar_cod_tinta` int(11) NOT NULL,
  `fk_adm_email_inst` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao_doar`
--

CREATE TABLE `doacao_doar` (
  `horario_disp` varchar(40) DEFAULT NULL,
  `dias_disp` varchar(40) NOT NULL,
  `fk_tintas_cod_tinta` int(11) NOT NULL,
  `fk_usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entidade`
--

CREATE TABLE `entidade` (
  `razao_social` varchar(255) NOT NULL,
  `CNPJ` varchar(20) NOT NULL,
  `fk_usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_pedir`
--

CREATE TABLE `pedido_pedir` (
  `dt_retirada` varchar(40) NOT NULL,
  `finalidade` varchar(255) DEFAULT NULL,
  `fk_usuario_id_usuario` int(11) NOT NULL,
  `fk_tintas_cod_tinta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoa_fisica`
--

CREATE TABLE `pessoa_fisica` (
  `nome_completo` varchar(255) DEFAULT NULL,
  `CPF` varchar(20) NOT NULL,
  `dt_nascimento` date DEFAULT NULL,
  `fk_usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ponto_coleta`
--

CREATE TABLE `ponto_coleta` (
  `cod_ponto` int(11) NOT NULL,
  `CEP` varchar(10) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ponto_coleta`
--

INSERT INTO `ponto_coleta` (`cod_ponto`, `CEP`, `endereco`, `cidade`, `latitude`, `longitude`) VALUES
(1, '13201-160', 'Av. União dos Ferroviários, 1760 - Centro', 'Jundiaí - SP', -23.1817, -46.8831),
(2, '13219-807', 'Av. Comendador Antônio Borin, 2602 - Jardim Colonial', 'Jundiaí - SP', -23.1703, -46.8476),
(3, '13206-765', 'Rua Cica, 90 - Vila Angelica', 'Jundiaí - SP', -23.2039, -46.8809);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tintas`
--

CREATE TABLE `tintas` (
  `cor_tinta` varchar(255) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `aplicacao` varchar(255) DEFAULT NULL,
  `marca` varchar(255) DEFAULT NULL,
  `imagem` blob DEFAULT NULL,
  `embalagem` varchar(255) DEFAULT NULL,
  `acabamento` varchar(255) DEFAULT NULL,
  `cod_tinta` int(11) NOT NULL,
  `dt_validade` date DEFAULT NULL,
  `fk_ponto_coleta_cod_ponto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `cep`, `email`, `senha`, `endereco`, `cidade`) VALUES
(1, 'adm', 'admparacolocartintas@adm.com', 'adm', 'adm', 'adm');
--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `adm`
--
ALTER TABLE `adm`
  ADD PRIMARY KEY (`email_inst`);

--
-- Índices de tabela `aprovar`
--
ALTER TABLE `aprovar`
  ADD PRIMARY KEY (`fk_adm_email_inst`,`fk_pedido_pedir_id_usuario`,`fk_pedido_pedir_cod_tinta`),
  ADD KEY `Estrangeira Email Inst` (`fk_adm_email_inst`) USING BTREE,
  ADD KEY `Estrangeira Data Ret` (`fk_pedido_pedir_dt_retira`),
  ADD KEY `Estrangeira ID User` (`fk_pedido_pedir_id_usuario`),
  ADD KEY `Estrangeira Cod Tinta` (`fk_pedido_pedir_cod_tinta`);

--
-- Índices de tabela `autorizar`
--
ALTER TABLE `autorizar`
  ADD PRIMARY KEY (`fk_doacao_doar_id_usuario`,`fk_doacao_doar_cod_tinta`,`fk_adm_email_inst`) USING BTREE,
  ADD KEY `Estrangeira Dia Disp` (`fk_doacao_doar_dias_disp`),
  ADD KEY `Estrangeira ID User` (`fk_doacao_doar_id_usuario`),
  ADD KEY `Estrangeira Cod Tinta` (`fk_doacao_doar_cod_tinta`),
  ADD KEY `Estrangeira Email Inst` (`fk_adm_email_inst`) USING BTREE;

--
-- Índices de tabela `doacao_doar`
--
ALTER TABLE `doacao_doar`
  ADD PRIMARY KEY (`fk_tintas_cod_tinta`,`fk_usuario_id_usuario`) USING BTREE,
  ADD KEY `Estrangeira Cod Tinta` (`fk_tintas_cod_tinta`),
  ADD KEY `Estrangeira ID User` (`fk_usuario_id_usuario`);

--
-- Índices de tabela `entidade`
--
ALTER TABLE `entidade`
  ADD PRIMARY KEY (`CNPJ`,`fk_usuario_id_usuario`),
  ADD KEY `Estrangeira ID User` (`fk_usuario_id_usuario`) USING BTREE;

--
-- Índices de tabela `pedido_pedir`
--
ALTER TABLE `pedido_pedir`
  ADD PRIMARY KEY (`fk_usuario_id_usuario`,`fk_tintas_cod_tinta`),
  ADD KEY `Estrangeira ID User` (`fk_usuario_id_usuario`),
  ADD KEY `Estrangeira Cod Tinta` (`fk_tintas_cod_tinta`);

--
-- Índices de tabela `pessoa_fisica`
--
ALTER TABLE `pessoa_fisica`
  ADD PRIMARY KEY (`CPF`,`fk_usuario_id_usuario`),
  ADD KEY `Estrangeira ID User` (`fk_usuario_id_usuario`);

--
-- Índices de tabela `ponto_coleta`
--
ALTER TABLE `ponto_coleta`
  ADD PRIMARY KEY (`cod_ponto`);

--
-- Índices de tabela `tintas`
--
ALTER TABLE `tintas`
  ADD PRIMARY KEY (`cod_tinta`,`fk_ponto_coleta_cod_ponto`) USING BTREE,
  ADD KEY `Estrangeira PontoColeta` (`fk_ponto_coleta_cod_ponto`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ponto_coleta`
--
ALTER TABLE `ponto_coleta`
  MODIFY `cod_ponto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tintas`
--
ALTER TABLE `tintas`
  MODIFY `cod_tinta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aprovar`
--
ALTER TABLE `aprovar`
  ADD CONSTRAINT `aprovar_ibfk_1` FOREIGN KEY (`fk_adm_email_inst`) REFERENCES `adm` (`email_inst`),
  ADD CONSTRAINT `aprovar_ibfk_2` FOREIGN KEY (`fk_pedido_pedir_cod_tinta`) REFERENCES `pedido_pedir` (`fk_tintas_cod_tinta`),
  ADD CONSTRAINT `aprovar_ibfk_3` FOREIGN KEY (`fk_pedido_pedir_id_usuario`) REFERENCES `pedido_pedir` (`fk_usuario_id_usuario`);

--
-- Restrições para tabelas `autorizar`
--
ALTER TABLE `autorizar`
  ADD CONSTRAINT `autorizar_ibfk_1` FOREIGN KEY (`fk_adm_email_inst`) REFERENCES `adm` (`email_inst`),
  ADD CONSTRAINT `autorizar_ibfk_2` FOREIGN KEY (`fk_doacao_doar_cod_tinta`) REFERENCES `doacao_doar` (`fk_tintas_cod_tinta`),
  ADD CONSTRAINT `autorizar_ibfk_3` FOREIGN KEY (`fk_doacao_doar_id_usuario`) REFERENCES `doacao_doar` (`fk_usuario_id_usuario`);

--
-- Restrições para tabelas `doacao_doar`
--
ALTER TABLE `doacao_doar`
  ADD CONSTRAINT `doacaoDoar_ibfk1` FOREIGN KEY (`fk_tintas_cod_tinta`) REFERENCES `tintas` (`cod_tinta`),
  ADD CONSTRAINT `doacaoDoar_ibfk2` FOREIGN KEY (`fk_usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `entidade`
--
ALTER TABLE `entidade`
  ADD CONSTRAINT `entidade_ibfk1` FOREIGN KEY (`fk_usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `pedido_pedir`
--
ALTER TABLE `pedido_pedir`
  ADD CONSTRAINT `pedidoPedir_ibfk1` FOREIGN KEY (`fk_tintas_cod_tinta`) REFERENCES `tintas` (`cod_tinta`),
  ADD CONSTRAINT `pedidoPedir_ibfk2` FOREIGN KEY (`fk_usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `pessoa_fisica`
--
ALTER TABLE `pessoa_fisica`
  ADD CONSTRAINT `pessoafisica_ibfk1` FOREIGN KEY (`fk_usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `tintas`
--
ALTER TABLE `tintas`
  ADD CONSTRAINT `tintas_ibfk1` FOREIGN KEY (`fk_ponto_coleta_cod_ponto`) REFERENCES `ponto_coleta` (`cod_ponto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
