DROP TABLE IF EXISTS t_comments;
DROP TABLE IF EXISTS t_tasks;
DROP TABLE IF EXISTS t_users;
DROP TABLE IF EXISTS t_sessions;
CREATE TABLE t_users (
    pk_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
CREATE TABLE t_tasks (
    pk_task INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    due_date DATE,
    FK_user INT,
    FOREIGN KEY (FK_user) REFERENCES t_users(pk_user) ON DELETE
    SET NULL
);
CREATE TABLE t_comments (
    pk_comment INT AUTO_INCREMENT PRIMARY KEY,
    fk_task INT,
    fk_user INT,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fk_task) REFERENCES t_tasks(pk_task) ON DELETE CASCADE,
    FOREIGN KEY (fk_user) REFERENCES t_users(pk_user) ON DELETE CASCADE
);
CREATE TABLE t_sessions (
    pk_session VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES t_users(pk_user) ON DELETE CASCADE
);
INSERT INTO t_users (username, password)
VALUES (
        'Dimitri',
        '$2y$10$T2VSk2t6/XXm5jY4iMZt8OpybdJxkJPSq8LzED/aCGlK78gYGXeXi'
    ),
    (
        'Luciano',
        '$2y$10$EmD.FIfMCAh/6svCDLrrDOBTicz16gOgXqOFY5luq2/RmCilotLW.'
    );