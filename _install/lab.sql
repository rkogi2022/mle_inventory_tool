-- Stores each consumable item’s master data (name, code, unit, reorder level).
CREATE TABLE consumable_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    item_code VARCHAR(50) UNIQUE NOT NULL,
    unit VARCHAR(50) NOT NULL,
    reorder_level INT DEFAULT 0,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tracks every movement for every consumable
CREATE TABLE consumable_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    transaction_type ENUM('receipt','issue') NOT NULL,
    quantity INT NOT NULL,
    transaction_date DATE NOT NULL,
    receiver_name VARCHAR(255),
    issuer_name VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_item
        FOREIGN KEY (item_id)
        REFERENCES consumable_items(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_staff
        FOREIGN KEY (created_by)
        REFERENCES staff_login(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- recording the gear inventory
CREATE TABLE gear_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(50) NOT NULL,       -- e.g., Jacket, Boots, Helmet
    number_procured INT NOT NULL DEFAULT 0,
    number_issued INT NOT NULL DEFAULT 0,
    in_store INT NOT NULL DEFAULT 0,      -- calculated as number_procured - number_issued
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- track the gear circulation among staffs
CREATE TABLE gear_items_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    date_issued DATE NOT NULL,
    item_condition VARCHAR(20) DEFAULT 'Good',
    signed_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_gear_tracking_staff FOREIGN KEY (staff_id)
        REFERENCES staff_login(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_gear_tracking_item FOREIGN KEY (item_id)
        REFERENCES gear_inventory(id)
        ON DELETE CASCADE

) ENGINE=InnoDB;

-- non-consumables tracking
-- Serialized Non-Consumables Table
CREATE TABLE nonconsumables_serialized (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    serial_number VARCHAR(150) UNIQUE NOT NULL,
    specification VARCHAR(150) NULL,
    accessories TEXT NULL,
    quantity_received INT DEFAULT 1,
    quantity_in_store INT DEFAULT 1,
    date_received DATE NOT NULL,
    last_checked_date DATE NULL,
    current_status ENUM(
        'instock',
        'dispatched',
        'under_service',
        'faulty',
        'disposed'
    ) DEFAULT 'instock',
    condition_status ENUM(
        'good',
        'fair',
        'needs_service',
        'faulty',
        'not_functional'
    ) DEFAULT 'good',
    recipient_name VARCHAR(150) NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- Condition Log Table (Serialized Items)
CREATE TABLE nonconsumables_serialized_condition_log (
    id INT AUTO_INCREMENT PRIMARY KEY,

    serialized_id INT NOT NULL,

    condition_status VARCHAR(100) NOT NULL,
    remarks TEXT NULL,
    checked_by VARCHAR(150) NULL,
    date_checked DATE NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (serialized_id)
        REFERENCES nonconsumables_serialized(id)
        ON DELETE CASCADE
);
-- Serialized Movements Table
CREATE TABLE nonconsumables_serialized_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,

    serialized_id INT NOT NULL,

    movement_type ENUM(
        'received',
        'dispatched',
        'returned',
        'sent_for_service',
        'disposed'
    ) NOT NULL,

    movement_date DATE NOT NULL,
    destination VARCHAR(150) NULL,
    remarks TEXT NULL,
    recorded_by VARCHAR(150) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (serialized_id)
        REFERENCES nonconsumables_serialized(id)
        ON DELETE CASCADE
);


-- Bulk Non-Consumables Table
CREATE TABLE nonconsumables_bulk (
    id INT AUTO_INCREMENT PRIMARY KEY,

    item_name VARCHAR(150) NOT NULL,
    specification VARCHAR(150) NULL,
    description TEXT NULL,

    total_received INT DEFAULT 0,
    total_dispatched INT DEFAULT 0,
    total_returned INT DEFAULT 0,
    total_disposed INT DEFAULT 0,

    quantity_in_store INT GENERATED ALWAYS AS
    (
        (total_received + total_returned)
        - (total_dispatched + total_disposed)
    ) STORED,

    condition_status ENUM(
        'good',
        'fair',
        'mixed',
        'faulty'
    ) DEFAULT 'good',

    last_checked_date DATE NULL,
    remarks TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bulk Movements Table
CREATE TABLE nonconsumables_bulk_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,

    bulk_id INT NOT NULL,

    movement_type ENUM(
        'received',
        'dispatched',
        'returned',
        'disposed',
        'damaged'
    ) NOT NULL,

    quantity INT NOT NULL,
    movement_date DATE NOT NULL,

    destination VARCHAR(150) NULL,
    remarks TEXT NULL,
    recorded_by VARCHAR(150) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (bulk_id)
        REFERENCES nonconsumables_bulk(id)
        ON DELETE CASCADE
);
