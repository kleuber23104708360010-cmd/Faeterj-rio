CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    data_cadastro DATE DEFAULT (CURRENT_DATE)
)engine=InnoDB;

CREATE TABLE lojas (
    id_loja INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    tipo_loja VARCHAR(20) NOT NULL CHECK (tipo_loja IN ('Aeroporto', 'Cidade'))
)engine=InnoDB;

CREATE TABLE categorias_veiculo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(50) NOT NULL,
    descricao TEXT
)engine=InnoDB;

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
)engine=InnoDB;

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
)engine=InnoDB;

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
)engine=InnoDB;

CREATE TABLE checkups (
    id_checkup INT AUTO_INCREMENT PRIMARY KEY,
    id_locacao INT NOT NULL,
    data_checkup DATE DEFAULT (CURRENT_DATE),
    km_devolucao INT,
    observacoes TEXT,
    FOREIGN KEY (id_locacao) REFERENCES locacoes(id_locacao) ON DELETE CASCADE
)engine=InnoDB;

CREATE TABLE itens_checkup (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_checkup INT NOT NULL,
    tipo_item VARCHAR(30) NOT NULL CHECK (tipo_item IN ('Avaria', 'Multa', 'Limpeza', 'Outros')),
    descricao TEXT,
    valor_cobranca DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_checkup) REFERENCES checkups(id_checkup) ON DELETE CASCADE
)engine=InnoDB;

CREATE TABLE taxas (
    id_taxa INT AUTO_INCREMENT PRIMARY KEY,
    nome_taxa VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao TEXT
)engine=InnoDB;

INSERT INTO taxas (nome_taxa, valor, descricao)
VALUES ('Locação Diferente Loja', 50.00, 'Taxa fixa quando carro é retirado em loja diferente da alocação original, mas na mesma cidade');