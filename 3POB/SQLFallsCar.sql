CREATE TABLE CIDADE (
    id_cidade INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE LOJA (
    id_loja INT PRIMARY KEY,
    id_cidade INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('Aeroporto', 'Cidade')),
    FOREIGN KEY (id_cidade) REFERENCES CIDADE(id_cidade)
);

CREATE TABLE CLIENTE (
    id_cliente INT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20)
);

CREATE TABLE AUTOMOVEL (
    id_automovel INT PRIMARY KEY,
    id_loja_atual INT NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    valor_diaria DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('Livre', 'Alugado', 'Reservado', 'Manutencao')),
    FOREIGN KEY (id_loja_atual) REFERENCES LOJA(id_loja)
);

CREATE TABLE RESERVA (
    id_reserva INT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_automovel_alocado INT,
    loja_retirada_prevista INT NOT NULL,
    data_reserva DATE NOT NULL,
    data_retirada_prevista DATE NOT NULL,
    periodo_dias INT NOT NULL CHECK (periodo_dias IN (7, 15, 30)),
    canal_reserva VARCHAR(20) NOT NULL CHECK (canal_reserva IN ('Internet', 'Telefone', 'Loja')),
    status_reserva VARCHAR(30) NOT NULL CHECK (status_reserva IN ('Confirmada', 'Pendente', 'Cancelada', 'Convertida_Locacao')),
    FOREIGN KEY (id_cliente) REFERENCES CLIENTE(id_cliente),
    FOREIGN KEY (id_automovel_alocado) REFERENCES AUTOMOVEL(id_automovel),
    FOREIGN KEY (loja_retirada_prevista) REFERENCES LOJA(id_loja)
);

CREATE TABLE LOCACAO (
    id_locacao INT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_automovel INT NOT NULL,
    id_reserva INT UNIQUE,
    loja_retirada INT NOT NULL,
    loja_devolucao_prevista INT NOT NULL,
    loja_devolucao_real INT,
    data_retirada DATE NOT NULL,
    data_devolucao_prevista DATE NOT NULL,
    data_devolucao_real DATE,
    periodo_dias INT NOT NULL CHECK (periodo_dias IN (7, 15, 30)),
    valor_total DECIMAL(10, 2) NOT NULL,
    inclui_motorista BOOLEAN NOT NULL DEFAULT FALSE,
    status_locacao VARCHAR(20) NOT NULL CHECK (status_locacao IN ('Em Andamento', 'Concluida', 'Cancelada')),
    FOREIGN KEY (id_cliente) REFERENCES CLIENTE(id_cliente),
    FOREIGN KEY (id_automovel) REFERENCES AUTOMOVEL(id_automovel),
    FOREIGN KEY (id_reserva) REFERENCES RESERVA(id_reserva),
    FOREIGN KEY (loja_retirada) REFERENCES LOJA(id_loja),
    FOREIGN KEY (loja_devolucao_prevista) REFERENCES LOJA(id_loja),
    FOREIGN KEY (loja_devolucao_real) REFERENCES LOJA(id_loja)
