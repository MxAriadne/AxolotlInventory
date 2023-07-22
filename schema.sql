CREATE DATABASE IF NOT EXISTS axolotlinventory;
USE axolotlinventory;

DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS notes;

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

    # This is the status of the item, ie: inprogress, unrepairable, repaired, scrap, etc.
    status TEXT

);

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