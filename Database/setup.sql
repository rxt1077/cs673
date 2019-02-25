USE rxt1077;

CREATE TABLE user (
    first VARCHAR(255) NOT NULL,
    last VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    hash CHAR(60) NOT NULL,
    emailConfirmed BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (email)
);
