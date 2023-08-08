CREATE DATABASE IF NOT EXISTS axolotlinventory;
USE axolotlinventory;

DROP TABLE IF EXISTS items;
CREATE TABLE items
(
    # This is the WO number of this item.
    id        INT AUTO_INCREMENT PRIMARY KEY,

    # This is the name of the item obv.
    name      TEXT,

    # This is the brief summary that will display on the main page.
    info      TEXT,

    # Just the date.
    timestamp TEXT,
    
    # Serial for item.
    serial TEXT,
    
    # IMEI for item.
    imei TEXT,

    # This is the status of the item, ie: inprogress, unrepairable, repaired, scrap, etc.
    status TEXT

);

DROP TABLE IF EXISTS notes;
CREATE TABLE notes
(
    # This is simply the number that represents this item
    # within the website.
    id        INT AUTO_INCREMENT PRIMARY KEY,

    # This is the WO number for the item.
    item_id   INTEGER,

    # This is literally just the date.
    timestamp TEXT,

    # This is the status that is being pushed to the item via this note.
    status TEXT,

    # The content of the note.
    note      TEXT,

    # This is grabbing the WO number from the item table.
    FOREIGN KEY (item_id) REFERENCES items (id)
);

DROP TABLE IF EXISTS sku;
CREATE TABLE sku
(
    # This is the SKU number of this item.
    id        INTEGER(6) ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,

    # This is the name of the item obv.
    name      TEXT,

    # This is the status of the item, ie: defective, sold, available.
    quantity INTEGER

);

DROP TABLE IF EXISTS sku_item;
CREATE TABLE sku_item
(
    # This is simply the number that represents this item
    # within the website.
    id        INTEGER(6) ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,

    # This is the SKU number for the item.
    item_id    INTEGER(6) ZEROFILL,

    # This is the price paid for the item obv.
    price   DOUBLE,

    # This is the status of the item, ie: defective, sold, available.
    status TEXT,

    # Date it was purchased.
    timestamp TEXT,

    # Place it was bought from.
    retailer TEXT,

    # This is literally just the date.
    order_no TEXT,

    # This is grabbing the SKU from the table.
    FOREIGN KEY (item_id) REFERENCES sku (id)
);