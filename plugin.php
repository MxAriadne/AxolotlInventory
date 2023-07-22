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
        $stmt = $mysqli->prepare("UPDATE $type SET ? = ? WHERE id=?");
        $stmt->bind_param('ssi', $var, $val, $id);
        $stmt->execute();
    } else {
        return null;
    }
}

# Type is either items or notes (represents the DB table)
# Var is the variable name, ie: id, timestamp, etc.
# Val is the new value for the section.
function saveEntry($type, $name, $info, $status) {
    $date = date("m/d/Y");
    $mysqli = sqlConnect();
    if ($mysqli) {
        if ($type == "items") {
            $stmt = $mysqli->prepare("INSERT INTO $type(name, info, timestamp, status) VALUES (?, ?, ?, ?)");
        } else {
            $stmt = $mysqli->prepare("INSERT INTO $type(item_id, note, timestamp, status) VALUES (?, ?, ?, ?)");
        }
        $stmt->bind_param('ssss', $name, $info, $date, $status);
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
        $stmt = $mysqli->prepare("DELETE FROM $type WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    } else {
        return null;
    }
}

# Deletes MySQL entry
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
function viewEntry($type, $id) {
    header("Location: workorder.php?id=$id");
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
        //$result = mysqli_fetch_array($stmt, MYSQLI_ASSOC);

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

/*
 * Soon to be generic function for listing notes or items in DB
 */
function listDataset($type, $optional) {
    $mysqli = sqlConnect();
    if ($mysqli) {
        if (!isset($optional["id"])) {
            $stmt = $mysqli->prepare("SELECT * FROM $type");
        } else {
            consoleLog("Fuck");
            $stmt = $mysqli->prepare("SELECT * FROM $type WHERE item_id=?");
            $stmt->bind_param('i', $optional["id"]);
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
