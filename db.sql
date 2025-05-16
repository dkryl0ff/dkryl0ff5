
CREATE TABLE applications (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    biography TEXT,
    contract_agreed BOOLEAN NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE programming_languages (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE application_languages (
    application_id INT(10) UNSIGNED NOT NULL,
    language_id INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id),
    FOREIGN KEY (language_id) REFERENCES programming_languages(id),
    PRIMARY KEY (application_id, language_id)
);


INSERT INTO programming_languages (name) VALUES 
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), ('Python'),
('Java'), ('Haskell'), ('Clojure'), ('Prolog'), ('Scala'), ('Go');

CREATE TABLE users (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    application_id INT(10) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    PRIMARY KEY (id)
);
