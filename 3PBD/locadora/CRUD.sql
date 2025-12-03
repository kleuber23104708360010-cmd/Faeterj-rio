
INSERT INTO categorias_veiculo (nome_categoria, descricao) VALUES
('SUV', 'Veículos utilitários esportivos.'),
('Econômico', 'Carros compactos e de baixo consumo.');


INSERT INTO lojas (nome, endereco, cidade, estado, tipo_loja) VALUES
('Matriz Centro', 'Rua Tal, 100', 'São Paulo', 'SP', 'Cidade'),
('Aeroporto Congonhas', 'Av. Washington Luís', 'São Paulo', 'SP', 'Aeroporto');

INSERT INTO clientes (nome, cpf, email, telefone, endereco) VALUES
('João Silva', '111.111.111-11', 'joao.silva@teste.com', '(11) 98765-4321', 'Rua das Flores, 10'),
('Maria Oliveira', '222.222.222-22', 'maria.oliveira@teste.com', '(21) 99887-7665', 'Av. Atlântica, 500');


INSERT INTO veiculos (placa, marca, modelo, ano, cor, id_categoria, id_loja_atual, status) VALUES
('ABC1D23', 'Jeep', 'Renegade', 2023, 'Preto', 1, 1, 'Disponível'),
('XYZ9A87', 'Fiat', 'Mobi', 2022, 'Branco', 2, 2, 'Disponível'),
('RTY4E56', 'Hyundai', 'HB20', 2024, 'Prata', 2, 1, 'Locado'); 

--Pesquisar Cliente por Nome
SELECT
    id_cliente,
    nome,
    cpf,
    email
FROM
    clientes
WHERE
    nome = 'João Silva';

--atualizar cliente por cpf
UPDATE clientes
SET
    telefone = '(11) 91234-5678',
    endereco = 'Rua Nova, 200'
WHERE
    cpf = '111.111.111-11';

SELECT * FROM clientes WHERE cpf = '111.111.111-11';

--deletar cliente por cpf
DELETE FROM clientes
WHERE
    cpf = '222.222.222-22';

SELECT * FROM clientes WHERE cpf = '222.222.222-22';

--cadastrar aluguel 
INSERT INTO locacoes (id_cliente, id_veiculo, id_loja_retirada, id_loja_devolucao, data_retirada, data_devolucao_prevista, periodo_dias, valor_final) VALUES
(1, 1, 1, 2, "2025-01-15", DATE_ADD("2025-01-15", INTERVAL 7 DAY), 7, 350.00);

UPDATE veiculos
SET status = 'Locado'
WHERE id_veiculo = 1;

--pesquisar aluguel por cpf 
SELECT
    L.id_locacao,
    C.nome AS nome_cliente,
    V.placa,
    L.data_retirada,
    L.data_devolucao_prevista
FROM
    locacoes L
JOIN
    clientes C ON L.id_cliente = C.id_cliente
JOIN
    veiculos V ON L.id_veiculo = V.id_veiculo
WHERE
    C.cpf = '111.111.111-11';

--atualizar locacao por cpf
UPDATE veiculos
SET
    status = 'Disponível',
    id_loja_atual = 2
WHERE
    id_veiculo = 1;

SELECT id_veiculo, placa, status, id_loja_atual FROM veiculos WHERE id_veiculo = 1;

--remover locacao
DELETE FROM locacoes
WHERE
    id_locacao = 1;


SELECT * FROM locacoes WHERE id_locacao = 1;

--buscar carro disponivel na data desejada mesmo que esteja atualmente alugado,
--utilizando ALIAS para facilitar os JOIN 
SET @DATA_RETIRADA_DESEJADA = '2025-03-01';
SET @DATA_DEVOLUCAO_DESEJADA = '2025-03-07';

SELECT
    V.id_veiculo,
    V.placa,
    V.marca,
    V.modelo,
    L.data_retirada AS inicio_locacao_conflito,
    L.data_devolucao_prevista AS fim_locacao_conflito
FROM
    veiculos V
JOIN
    locacoes L ON V.id_veiculo = L.id_veiculo
WHERE
    @DATA_RETIRADA_DESEJADA < L.data_devolucao_prevista
    AND @DATA_DEVOLUCAO_DESEJADA > L.data_retirada
GROUP BY
    V.id_veiculo, V.placa, V.marca, V.modelo, L.data_retirada, L.data_devolucao_prevista;



--teste de locacao com cobranca adicional
INSERT INTO locacoes (id_cliente, id_veiculo, id_loja_retirada, id_loja_devolucao, data_retirada, data_devolucao_prevista, periodo_dias, valor_final) VALUES
(1, 1, 1, 2, '2025-12-01', '2025-12-08', 7, 400.00);

INSERT INTO pagamentos (id_locacao, numero_cartao, nome_titular, data_validade, valor_inicial) VALUES
(2, '4000123456789012', 'JOAO SILVA', '12/28', 400.00);

INSERT INTO checkups (id_locacao, km_devolucao, observacoes) VALUES
(2, 15000, 'Veículo devolvido limpo. Sem avarias.');

INSERT INTO itens_checkup (id_checkup, tipo_item, descricao, valor_adicional) VALUES
(1, 'Multa', 'Multa por excesso de velocidade na rodovia.', 120.00);

-- Atualizar valor_final da locacao somando aos possiveis valores de valor_adicional do checkup
UPDATE locacoes
SET valor_final = valor_final + (
    SELECT COALESCE(SUM(valor_adicional), 0) FROM itens_checkup WHERE id_checkup = 1
)
WHERE id_locacao = 2;



--pesquisando locacao Completa com valor inicial e o valor adicional de checkup
SELECT
    L.id_locacao,
    C.nome AS cliente,
    V.placa,
    L.valor_final,
    P.valor_inicial,
    I.descricao AS item_cobranca_extra,
    I.valor_adicional
FROM
    locacoes L
JOIN
    clientes C ON L.id_cliente = C.id_cliente
JOIN
    veiculos V ON L.id_veiculo = V.id_veiculo
LEFT JOIN
    pagamentos P ON L.id_locacao = P.id_locacao
LEFT JOIN
    checkups CH ON L.id_locacao = CH.id_locacao
LEFT JOIN
    itens_checkup I ON CH.id_checkup = I.id_checkup
WHERE
    L.id_locacao = 2;