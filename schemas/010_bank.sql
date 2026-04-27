CREATE TABLE IF NOT EXISTS bank (
    uid BINARY(16) NOT NULL,
    card_number_hash VARCHAR(255) NOT NULL COMMENT 'PHP default password_hash() output for the card number',
    exp_date CHAR(5) NOT NULL COMMENT 'MM/YY',
    cvv_hash VARCHAR(255) NOT NULL COMMENT 'PHP default password_hash() output for the CVV',
    cardholder_name VARCHAR(255) NOT NULL,
    billing_address VARCHAR(255) NULL,

    CONSTRAINT fk_bank_user FOREIGN KEY (uid)
        REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;