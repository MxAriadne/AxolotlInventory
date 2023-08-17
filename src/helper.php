<?php

/*
 * helper.php
 * Stores commonly used values and functions so they can be easily
 * changed wherever necessary.
 *
 * This file is only viewable on the server side, and is additionally blocked
 * via .htaccess
 *
 */

namespace Axolotl;

//Import MySQLi
use Exception;
use mysqli;

//Store server IP.
const SERVER = 'localhost';
//Store database username.
const USERNAME = 'axolotl';
//Store database password.
const PASSWORD = '';
//Store database name.
const DATABASE = 'axolotlinventory';

session_start();

class Helper
{
    /*
     * consoleLog
     * JavaScript based implementation used for debugging purposes.
     *
     */

    public function consoleLog($output, $forward = true): void
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
    public function sqlConnect(): bool|mysqli
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
            $this->consoleLog("SQL connection failure. Error code " . $mysqli->connect_errno
                . ". Please verify the server address, name, username, and password provided.");
            // Return false (ie: connection failure)
            return false;
        }
    }

# Type is either items or notes (represents the DB table)
# Var is the variable name, ie: id, timestamp, etc.
# Val is the new value for the section.
    public function updateEntry($type, $var, $val, $id)
    {
        $mysqli = $this->sqlConnect();
        if ($mysqli) {
            $stmt = $mysqli->prepare("UPDATE $type SET $var = ? WHERE id=?");
            $stmt->bind_param('si', $val, $id);
            $stmt->execute();
        } else {
            return null;
        }
    }

    /*
     * saveEntry
     *
     * $type:               The name of the table that will be selected.
     * $new_entry:          This is an array with keys representing SQL columns and values representing the data to be saved.
     *
     * This simply takes new table values, cleans them, and then inserts them into the specified table.
     * Due to the way INSERT works, the key/values do not need to be in the same order as the schema.
     *
     */
    public function saveEntry($type, $new_entry)
    {
        // Attempts to connect to the database.
        $mysqli = $this->sqlConnect();

        // If the connection is successful...
        if ($mysqli) {
            // Clean the keys and assign them as column names
            $variables = mysqli_real_escape_string($mysqli, implode(', ', array_keys($new_entry)));
            // Clean the values and use them as the actual data inserted
            $values = implode(', ', array_values($new_entry));
            // Prep the statement...
            $stmt = $mysqli->prepare("INSERT INTO $type($variables) VALUES ($values)");
            // Execute...
            $stmt->execute();
        } else {
            // If the connection is not successful, return null.
            return null;
        }
    }

    /*
     * deleteEntry
     *
     * $type:               The name of the table that will be selected.
     * $id:                 The ID of what is being deleted.
     * $optional:           This is an array of optional parameters. In this case, the ID of the child items that must be deleted before the parent ID can be deleted.
     *
     * This function removes the $id from the table $type, if $optional is defined then we remove the children of $id element.
     *
     */
    public function deleteEntry($type, $id, $optional)
    {
        // Attempts to connect to the database.
        $mysqli = $this->sqlConnect();

        // If the connection is successful...
        if ($mysqli) {
            if (isset($optional['parent_id'])) {
                $stmt = $mysqli->prepare("DELETE FROM " . $optional['child'] . " WHERE parent_id=" . $optional['parent_id']);
                $stmt->execute();
            }
            // Attempt to execute the statement...
            try {
                // Prep the statement...
                $stmt = $mysqli->prepare("DELETE FROM $type WHERE id=" . $id);
                // Execute...
                $stmt->execute();
            } catch (Exception $e) {
                // This only fails if the textarea values are invalid, thus this error message:
                echo '<script>alert("The data you\'ve entered is invalid. Please try again.")</script>';
            }
        } else {
            // If the connection is not successful, return null.
            return null;
        }
    }

# Deletes MySQL entry
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
    public function viewEntry($type, $id)
    {
        if ($type == "items") {
            header("Location: workorder-details.php?id=$id");
        } elseif ($type == "sku") {
            header("Location: inventory-details.php?id=$id");
        } elseif ($type == "purchase_order") {
            header("Location: po-details.php?id=$id");
        }
    }

# Holds info of the selected workorder for the notes page
# $type refers to the table name, ie: items or notes
# id refers to the primary key of the item in the table
    public function dataHold($type, $id)
    {
        $mysqli = $this->sqlConnect();
        if ($mysqli) {
            // Attempt to execute the statement...
            try {
                // Prep the statement...
                $stmt = $mysqli->prepare("SELECT * FROM $type WHERE id=" . $id);
                // Execute...
                $stmt->execute();
            } catch (Exception $e) {
                // This only fails if the textarea values are invalid, thus this error message:
                echo '<script>alert("The data you\'ve entered is invalid. Please try again.")</script>';
            }

            $result = $stmt->get_result();
            $output = $result->fetch_assoc();

            if ($output == null) {
                $this->consoleLog("Something is very wrong, this item does not exist.");
                return null;
            } else {
                return $output;
            }
        } else {
            return null;
        }
    }

    /*
     * TODO: to be generic function for listing notes or items in DB
     */
    public function listDataset($type, $optional)
    {
        $mysqli = $this->sqlConnect();
        if ($mysqli) {
            if (!isset($optional["id"])) {
                $stmt = $mysqli->prepare("SELECT * FROM $type");
            } else {
                $stmt = $mysqli->prepare("SELECT * FROM $type WHERE parent_id=?");
                $stmt->bind_param('i', $optional["id"]);
                if ($type == "sku_item") {
                    $result = $mysqli->query("SELECT * FROM sku_item WHERE parent_id=" . $optional["id"] . "");
                    $row_cnt = $result->num_rows;
                    $this->updateEntry("sku", "quantity", $row_cnt, $optional["id"]);
                }
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result == null) {
                $this->consoleLog("Something is very wrong, this item does not exist.");
                return null;
            } else {
                return $result;
            }
        } else {
            return null;
        }
    }
}
