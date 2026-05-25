-- =============================================================
--  init.sql  —  Quero Passagem  |  Schema completo
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- TABELA: users
-- =============================================================
CREATE TABLE IF NOT EXISTS users (
                                   id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                   nome VARCHAR(255) NOT NULL,
                                   email VARCHAR(255) NOT NULL,
                                   password VARCHAR(255) NOT NULL,
                                   created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

                                   PRIMARY KEY (id),
                                   UNIQUE KEY uq_users_email (email)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;



-- =============================================================
-- TABELA: viacoes
-- =============================================================
CREATE TABLE IF NOT EXISTS viacoes (
                                     id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                     nome VARCHAR(255) NOT NULL,
                                     url VARCHAR(255) NOT NULL,
                                     cidade VARCHAR(255) NOT NULL,

                                     status ENUM('ativo', 'inativo')
                                       NOT NULL DEFAULT 'ativo',

                                     logo VARCHAR(255) DEFAULT NULL,

                                     criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

                                     alterado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                                       ON UPDATE CURRENT_TIMESTAMP,

                                     PRIMARY KEY (id)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================
-- TABELA: viacoes_historico
-- =============================================================
CREATE TABLE IF NOT EXISTS viacoes_historico (

                                               id INT UNSIGNED NOT NULL AUTO_INCREMENT,

                                               viacao_id INT UNSIGNED DEFAULT NULL,

                                               user_id INT UNSIGNED DEFAULT NULL,

                                               acao ENUM('CREATE', 'UPDATE', 'DELETE')
                                                               NOT NULL,

  -- snapshot dos dados antes da alteração
                                               antes LONGTEXT DEFAULT NULL,

  -- snapshot dos dados depois da alteração
                                               depois LONGTEXT DEFAULT NULL,

                                               criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

                                               PRIMARY KEY (id),

                                               CONSTRAINT fk_historico_viacao
                                                 FOREIGN KEY (viacao_id)
                                                   REFERENCES viacoes(id)
                                                   ON DELETE SET NULL
                                                   ON UPDATE CASCADE,

                                               CONSTRAINT fk_historico_user
                                                 FOREIGN KEY (user_id)
                                                   REFERENCES users(id)
                                                   ON DELETE SET NULL
                                                   ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================
-- ÍNDICES
-- =============================================================

CREATE INDEX idx_viacoes_nome
  ON viacoes(nome);

CREATE INDEX idx_viacoes_status
  ON viacoes(status);

CREATE INDEX idx_historico_viacao
  ON viacoes_historico(viacao_id);

CREATE INDEX idx_historico_user
  ON viacoes_historico(user_id);

CREATE INDEX idx_historico_acao
  ON viacoes_historico(acao);

CREATE INDEX idx_historico_criado_em
  ON viacoes_historico(criado_em);


-- =============================================================
-- USUÁRIO ADMINISTRADOR PADRÃO
-- senha: Admin@1234
-- =============================================================

INSERT INTO users (
  nome,
  email,
  password
)
VALUES (
         'Administrador',
         'admin@admin.com',

         -- Gere uma nova hash em produção
         '$2y$10$PLRH.CR2UV9KZWcrOL1nNucY8RULGwM0.7fLZfO.KtQhIeN3xsVXK'
       )

ON DUPLICATE KEY UPDATE
  id = id;


SET FOREIGN_KEY_CHECKS = 1;
