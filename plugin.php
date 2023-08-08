<?php

/*
 * plugin.php
 * Stores commonly used values and functions so they can be easily
 * changed wherever necessary.
 *
 * This file is only viewable on the server side, and is additionally blocked
 * via .htaccess
 *
 */

//Starting a session to store user id locally.
session_start();
//Store server IP.
const SERVER = 'localhost';
//Store database username.
const USERNAME = 'axolotl';
//Store database password.
const PASSWORD = '';
//Store database name.
const DATABASE = 'axolotlinventory';

/*
 * consoleLog
 * JavaScript based implementation used for debugging purposes.
 *
 */

function consoleLog($output, $forward = true): void
{
    $logger = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($forward) {
        $logger = '<script>' . $logger . '</script>';
    }
    echo $logger;
}

/*
 * sqlConnect
 * Attempts to connect to a MySQL database using the provided
 * constants.
 *
 */
function sqlConnect(): bool|mysqli
{
    // New SQL connection.
    $mysqli = new MySQLi(SERVER, USERNAME, PASSWORD, DATABASE);
    // If connection succeeds...
    if ($mysqli->connect_errno == 0) {
        // Set charset and return $mysqli
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
        // Else...
    } else {
        // Provide an error log of the events.
        consoleLog("SQL connection failure. Error code " . $mysqli->connect_errno
            . ". Please verify the server address, name, username, and password provided.");
        // Return false (ie: connection failure)
        return false;
    }
}

# Type is either items or notes (represents the DB table)
# Var is the variable name, ie: id, timestamp, etc.
# Val is the new value for the section.
 function updateEntry($type, $var, $val, $id) {
    $mysqli = sqlConnect();
    if ($mysqli) {
        $stmt = $mysqli->prepare("UPDATE $type SET $var = ? WHERE id=?");
        $stmt->bind_param('si',  $val, $id);
        $stmt->execute();
    } else {
        return null;
    }
}

# Type is either items or notes (represents the DB table)
# Var is the variable name, ie: id, timestamp, etc.
# Val is the new value for the section.
function saveEntry($type, $name, $info, $status, $serial, $imei, $quantity) {
    date_default_timezone_set('America/Chicago');
    $date = date("m/d/Y\ng:i a");
    $mysqli = sqlConnect();
    if ($mysqli) {
        if ($type == "items") {
            $stmt = $mysqli->prepare("INSERT INTO $type(name, info, timestamp, status, serial, imei) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $name, $info, $date, $status, $serial, $imei);
        } elseif ($type == "notes") {
            $stmt = $mysqli->prepare("INSERT INTO $type(item_id, note, timestamp, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $info, $date, $status);
            updateEntry("items", "status", $status, $name);
        } elseif ($type == "sku") {
            $stmt = $mysqli->prepare("INSERT INTO $type(name, quantity) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $status);
        } elseif ($type == "sku_item") {
            $stmt = $mysqli->prepare("INSERT INTO $type(item_id, price, status, timestamp, retailer, order_no) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('idssss', $name, $info, $status, $date, $serial, $imei);
        }
        $stmt->execute();
    } else {
        return null;
    }
}

# Deletes MySQL entry
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
function deleteEntry($type, $id) {
    $mysqli = sqlConnect();
    if ($mysqli) {
        if ($type == "items") {
            $stmt = $mysqli->prepare("DELETE FROM notes WHERE item_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt = $mysqli->prepare("DELETE FROM $type WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        } else {
            $stmt = $mysqli->prepare("DELETE FROM $type WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        }

    } else {
        return null;
    }
}

# Deletes MySQL entry
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
function viewEntry($type, $id) {
    if ($type == "items") {
        header("Location: workorder.php?id=$id");
    } else if ($type == "sku") {
        header("Location: items-skus.php?id=$id");
    }
}

# Holds info of the selected workorder for the notes page
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
function workorderHold($type, $id) {
    $mysqli = sqlConnect();
    if ($mysqli) {
        $stmt = $mysqli->prepare("SELECT * FROM $type WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $output = $result->fetch_assoc();

        if ($output == null) {
            consoleLog("Something is very wrong, this item does not exist.");
            return null;
        } else {
            return $output;
        }
    } else {
        return null;
    }
}

/*
 * Soon to be generic function for listing notes or items in DB
 */
function listDataset($type, $optional) {
    $mysqli = sqlConnect();
    if ($mysqli) {
        if (!isset($optional["id"])) {
            $stmt = $mysqli->prepare("SELECT * FROM $type");
        } else {
            $stmt = $mysqli->prepare("SELECT * FROM $type WHERE item_id=?");
            $stmt->bind_param('i', $optional["id"]);
            if ($type == "sku_item") {
                $result = $mysqli->query("SELECT * FROM sku_item WHERE item_id=" . $optional["id"] . "");
                $row_cnt = $result->num_rows;
                updateEntry("sku", "quantity", $row_cnt, $optional["id"]);
            }
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result == null) {
            consoleLog("Something is very wrong, this item does not exist.");
            return null;
        } else {
            return $result;
        }
    } else {
        return null;
    }
}
