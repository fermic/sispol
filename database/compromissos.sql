CREATE TABLE IF NOT EXISTS compromissos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    cor VARCHAR(7) DEFAULT '#3788d8',
    tipo ENUM('reuniao', 'audiencia', 'prazo', 'outro') NOT NULL DEFAULT 'reuniao',
    visibilidade ENUM('privado', 'todos') NOT NULL DEFAULT 'todos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 