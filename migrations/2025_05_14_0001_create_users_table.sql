
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    frequency VARCHAR(10) CHECK (frequency IN ('hourly', 'daily')) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    confirmed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );