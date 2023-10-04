<?php

namespace Axolotl\Output;

// Runs the autoload
$path = "../../vendor/autoload.php";
require_once $path;

use Axolotl\Helper;

$helper = new Helper();

$mysqli = $helper->sqlConnect();
if ($mysqli) {
    
    $date = date("m");
    $sku = array("name" => "unknown");
    
    // Query to select data from your table
    if ($_SESSION['type'] === "items" || $_SESSION['type'] === "purchase_order") {
        $query = "SELECT * FROM " . $_SESSION['type'] . " WHERE timestamp REGEXP '" . $_SESSION['month'] . "\/[0-9]*\/" . $_SESSION['year'] . "'";
    } elseif ($_SESSION['type'] === "sku_item") {
        $sku = mysqli_query($mysqli, "SELECT * FROM sku WHERE id= " . $_SESSION['id'])->fetch_assoc();
        $query = "SELECT * FROM " . $_SESSION['type'] . " WHERE parent_id=" . $_SESSION['id'];
    } else {
        $sku = array("name" => "PO#" . $_SESSION['id']);
        $query = "SELECT * FROM " . $_SESSION['type'];
    }
    $result = mysqli_query($mysqli, $query);

    // Prepare CSV data
    $csv_data = array();

    // Fetch column headers
    $columns = array();
    while ($fieldInfo = mysqli_fetch_field($result)) {
        $columns[] = strtoupper($fieldInfo->name);
    }
    $csv_data[] = $columns;

    // Fetch rows
    while ($row = mysqli_fetch_assoc($result)) {
        $csv_data[] = $row;
    }

    // Close the database connection
    mysqli_close($mysqli);

    // Generate CSV content
    $output = fopen('php://Output', 'w');
    foreach ($csv_data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);

    header('Content-Type: text/csv');
    header("Cache-Control: no-store, no-cache");
    header('Content-Disposition: attachment; filename="' . $_SESSION['month'] . '-' . $_SESSION['year'] . '-' . $_SESSION['type'] . '-' . $sku["name"] . '.csv"');

} else {
    return null;
}
