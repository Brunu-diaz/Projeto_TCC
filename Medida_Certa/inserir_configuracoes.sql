-- Script para inserir dados na tabela configuracoes
-- Execute no phpMyAdmin no banco sistema_agua

USE sistema_agua;

-- Primeiro, deleta dados antigos (se houver)
TRUNCATE TABLE configuracoes;

-- Insere as configurações padrão
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('valor_m3', '3.50', 'Valor em R$ por metro cúbico de água'),
('taxa_esgoto', '15', 'Taxa de esgoto em percentual (%)'),
('dia_vencimento', '10', 'Dia padrão de vencimento das faturas'),
('alerta_vazamento', '1', 'Ativar alertas de possível vazamento (1=sim, 0=não)'),
('alerta_inadimplencia', '1', 'Ativar alertas de inadimplência (1=sim, 0=não)'),
('modo_manutencao', '0', 'Modo de manutenção do sistema (1=ativo, 0=desativo)');

-- Verifica os dados inseridos
SELECT * FROM configuracoes;
