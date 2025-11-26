CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    data_cadastro DATE DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB;

CREATE TABLE lojas (
    id_loja INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    tipo_loja VARCHAR(20) NOT NULL CHECK (tipo_loja IN ('Aeroporto', 'Cidade'))
) ENGINE=InnoDB;

CREATE TABLE categorias_veiculo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(50) NOT NULL,
    descricao TEXT
) ENGINE=InnoDB;

CREATE TABLE veiculos (
    id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    cor VARCHAR(30),
    id_categoria INT,
    id_loja_atual INT,
    status VARCHAR(20) NOT NULL DEFAULT 'Disponível' CHECK (status IN ('Disponível', 'Reservado', 'Locado', 'Manutenção')),
    FOREIGN KEY (id_categoria) REFERENCES categorias_veiculo(id_categoria) ON DELETE SET NULL,
    FOREIGN KEY (id_loja_atual) REFERENCES lojas(id_loja) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE locacoes (
    id_locacao INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_veiculo INT NOT NULL,
    id_loja_retirada INT NOT NULL,
    id_loja_devolucao INT NOT NULL,
    data_retirada DATE NOT NULL,
    data_devolucao_prevista DATE NOT NULL,
    periodo_dias INT NOT NULL CHECK (periodo_dias IN (7, 15, 30)),
    com_motorista BOOLEAN DEFAULT FALSE,
    taxa_distante DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) NOT NULL,
    data_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (id_veiculo) REFERENCES veiculos(id_veiculo) ON DELETE RESTRICT,
    FOREIGN KEY (id_loja_retirada) REFERENCES lojas(id_loja) ON DELETE RESTRICT,
    FOREIGN KEY (id_loja_devolucao) REFERENCES lojas(id_loja) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    id_locacao INT NOT NULL,
    numero_cartao VARCHAR(20) NOT NULL,
    nome_titular VARCHAR(100) NOT NULL,
    data_validade VARCHAR(7) NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_pagamento VARCHAR(20) DEFAULT 'Confirmado',
    FOREIGN KEY (id_locacao) REFERENCES locacoes(id_locacao) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE checkups (
    id_checkup INT AUTO_INCREMENT PRIMARY KEY,
    id_locacao INT NOT NULL,
    data_checkup DATE DEFAULT (CURRENT_DATE),
    km_devolucao INT,
    observacoes TEXT,
    FOREIGN KEY (id_locacao) REFERENCES locacoes(id_locacao) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE itens_checkup (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_checkup INT NOT NULL,
    tipo_item VARCHAR(30) NOT NULL CHECK (tipo_item IN ('Avaria', 'Multa', 'Limpeza', 'Outros')),
    descricao TEXT,
    valor_cobranca DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_checkup) REFERENCES checkups(id_checkup) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE taxas (
    id_taxa INT AUTO_INCREMENT PRIMARY KEY,
    nome_taxa VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT
) ENGINE=InnoDB;

INSERT INTO taxas (nome_taxa, valor, descricao)
VALUES ('Locação Diferente Loja', 50.00, 'Taxa fixa quando carro é retirado em loja diferente da alocação original, mas na mesma cidade');

CREATE INDEX idx_veiculos_id_categoria ON veiculos(id_categoria);
CREATE INDEX idx_veiculos_id_loja_atual ON veiculos(id_loja_atual);
CREATE INDEX idx_locacoes_id_cliente ON locacoes(id_cliente);
CREATE INDEX idx_locacoes_id_veiculo ON locacoes(id_veiculo);
CREATE INDEX idx_locacoes_id_loja_retirada ON locacoes(id_loja_retirada);
CREATE INDEX idx_locacoes_data_retirada ON locacoes(data_retirada);
CREATE INDEX idx_pagamentos_id_locacao ON pagamentos(id_locacao);
CREATE INDEX idx_checkups_id_locacao ON checkups(id_locacao);
CREATE INDEX idx_itens_checkup_tipo ON itens_checkup(tipo_item);
CREATE INDEX idx_itens_checkup_id_checkup ON itens_checkup(id_checkup);

CREATE VIEW vw_veiculos_mais_alugados AS
SELECT
    l.cidade AS cidade_loja,
    cv.nome_categoria,
    v.marca,
    v.modelo,
    COUNT(*) AS total_locacoes
FROM locacoes loc
JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
JOIN categorias_veiculo cv ON v.id_categoria = cv.id_categoria
JOIN lojas l ON loc.id_loja_retirada = l.id_loja
GROUP BY l.cidade, cv.nome_categoria, v.marca, v.modelo
ORDER BY l.cidade, cv.nome_categoria, total_locacoes DESC;

CREATE VIEW vw_faturamento_loja_mes AS
SELECT
    loj.nome AS nome_loja,
    loj.cidade,
    loj.estado,
    DATE_FORMAT(loc.data_retirada, '%Y-%m-01') AS mes_referencia,
    SUM(loc.valor_total) AS faturamento_total
FROM locacoes loc
JOIN lojas loj ON loc.id_loja_retirada = loj.id_loja
GROUP BY loj.id_loja, loj.nome, loj.cidade, loj.estado, mes_referencia
ORDER BY mes_referencia DESC, faturamento_total DESC;

CREATE VIEW vw_taxa_avaria_por_modelo AS
WITH locacoes_por_modelo AS (
    SELECT
        v.marca,
        v.modelo,
        COUNT(*) AS total_locacoes
    FROM locacoes loc
    JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
    GROUP BY v.marca, v.modelo
),
avarias_por_modelo AS (
    SELECT
        v.marca,
        v.modelo,
        COUNT(DISTINCT loc.id_locacao) AS locacoes_com_avaria
    FROM locacoes loc
    JOIN veiculos v ON loc.id_veiculo = v.id_veiculo
    JOIN checkups ch ON loc.id_locacao = ch.id_checkup
    JOIN itens_checkup ic ON ch.id_checkup = ic.id_checkup
    WHERE ic.tipo_item = 'Avaria'
    GROUP BY v.marca, v.modelo
)
SELECT
    l.marca,
    l.modelo,
    l.total_locacoes,
    COALESCE(a.locacoes_com_avaria, 0) AS locacoes_com_avaria,
    ROUND(
        (COALESCE(a.locacoes_com_avaria, 0) / NULLIF(l.total_locacoes, 0)) * 100,
        2
    ) AS taxa_avaria_percentual
FROM locacoes_por_modelo l
LEFT JOIN avarias_por_modelo a
    ON l.marca = a.marca AND l.modelo = a.modelo
ORDER BY taxa_avaria_percentual DESC;