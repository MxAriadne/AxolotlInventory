<?php
    namespace Axolotl\Inventory;

    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/vendor/autoload.php";
    require_once $path;
    
    use Axolotl\Helper;
    
    $helper = new Helper();
    
    $items = $helper->listDataset("sku_item", array("id" => $_GET["id"]));
    $itemsorted = [];
    foreach ($items as $item) {
        array_unshift($itemsorted, $item);
    }

    // This saves the workorder information so I can populate the page.
    $sku = $helper->dataHold("sku", $_GET["id"]);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete-btn'])) {
            $helper->deleteEntry("sku_item", $_POST['delete'], null);
            unset($_POST["delete"]);
            header("refresh: 0");
        }
        
        if (isset($_POST['update'])) {
            $helper->updateEntry("sku_item", "status", $_POST['status'], $_POST['delete']);
            header("refresh: 0");
        }

        if (isset($_POST['export'])) {
            // We save the table name, date, and parent id in the session so output-csv has access to it.
            $_SESSION["month"] = date('m');
            $_SESSION["year"] = date('Y');
            $_SESSION["id"] = $_GET["id"];
            $_SESSION["type"] = "sku_item";
            
            // Redirect to the file, this specifically auto downloads the CSV for the relevant data saved in session
            header('Location: ../Output/output-csv.php');
        }

    }
    
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title><?php echo "" . $sku["name"] . " - Axolotl Inventory"; ?></title>
    
    <link href="../Style/inventory-details.css" rel="stylesheet" type="text/css">
    <link href="../Style/global.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="top-div">
        
        <?php
            echo "<table>
                    <tr class=\"nav-row\">
                        <td><button class=\"nav-btn\" type='button'><a href='../Workorders/workorders.php'>Workorders</a></button></td>
                        <td><button class=\"nav-btn\"><a href='../Inventory/inventory.php'>Inventory</a></button></td>
                        <td><button class=\"nav-btn\"><a href='../Purchasing/purchasing.php'>Purchasing</a></button></td>
                    </tr>
                 </table>
                <h1 Style='float: right; margin-right: 20px;'>" . $sku["name"] . "</h1>";
            echo "<br><p class='desc' Style='padding-bottom: 27px;'>Current Quantity: " . $sku["quantity"] . " </p>";
        ?>
        
    </div>

    <script>
        function searchItems() {
            let input = document.getElementById("Search");
            let filter = input.value.toLowerCase();
            let nodes = document.getElementsByClassName('target');

            for (let i of nodes) {
                if (i.innerText.toLowerCase().includes(filter)) {
                    i.style.display = "";
                } else {
                    i.style.display = "none";
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            $('.tab').on('click', function(){
                $(this).next().slideToggle(700);
            });
        });
    </script>
    
    <div>
        <div id="note-list">
            
            <table id="export-csv-table">
                <br><br>
                <form method="post">
                    <tr>
                        <td>
                            <h1>Total Item Inventory</h1>
                            <input type="text" id="Search" onkeyup="searchItems()" placeholder="Search labels...">
                            <button type="submit" name="export">Export to CSV</button>
                        </td>
                        <td></td>
                    </tr>
                </form>
                
            </table>
            
            <br>
            <table id="items">
                <tbody id="item-grid">
                <tr>
                    <th id="col-remove"></th>
                    <th id="col-desc">ID</th>
                    <th id="col-desc">Status</th>
                    <th id="col-date">Price</th>
                    <th id="col-date">Retailer</th>
                    <th id="col-date">Order #</th>
                    <th id="col-date">Date</th>
                </tr>
                <?php
                foreach ($itemsorted as $item) {
                    echo "<tr class=\"target\" name=\"" . $item["id"] . "\">";
                    echo "<td><form action=\"\" method=\"POST\">
                              <button name='update' type='submit'>💾</button>
                              <button name='delete-btn' type='submit'>🗑️</button>
                              <input type=\"hidden\"  name=\"delete\"  value=\"" . $item["id"] . "\"/>
                          </td>";
                    echo "<td> ". $item["id"] . " </th>";
                    echo "<td><select name=\"status\" id=\"status\">
                                <option value=\"\" disabled selected>" . $item["status"] . "</option>

                                <option value=\"In Transit\">In Transit</option>
                                <option value=\"Available\">Available</option>
                                <option value=\"Defective\">Defective</option>
                                <option value=\"Broken\">Broken</option>
                                <option value=\"Trashed\">Trashed</option>
                                <option value=\"Sold\">Sold</option>
                            </select></td>";
                    echo "<td> ". $item["price"] . " </th>";
                    echo "<td> ". $item["retailer"] . " </th>";
                    echo "<td> ". $item["order_no"] . "</th>";
                    echo "<td> ". $item["timestamp"] . "</th>";
                    echo "</tr></form>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
