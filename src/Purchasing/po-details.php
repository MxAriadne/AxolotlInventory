<?php
    
    namespace Axolotl\Purchasing;
    
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/vendor/autoload.php";
    require_once $path;
    
    use Axolotl\Helper;
    
    $helper = new Helper();
    
    date_default_timezone_set('America/Chicago');
    $date = date("m/d/Y\ng:i a");
    
    $pos = $helper->listDataset("po_items", array("id" => $_GET["id"]));
    $itemsorted = [];
    foreach ($pos as $item) {
        array_unshift($itemsorted, $item);
    }
    
    $_GLOBALS['spent'] = 0;
    foreach ($pos as $item) {
        $_GLOBALS['spent'] = $_GLOBALS['spent'] + ($item['quantity']*$item['price']);
    }
    
    // This saves the purchase order parent information so I can populate the page.
    $po = $helper->dataHold("purchase_order", $_GET["id"]);
    
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (isset($_POST['delete'])) {
            $helper->deleteEntry("po_items", $_POST['delete'], null);
            unset($_POST["delete"]);
            header("refresh: 0");
        }
    
        if (isset($_POST['add-note'])) {
            $helper->saveEntry("po_items",
                array("name"=>"'" . $_POST["Name"] . "'",
                    "parent_id"=>$_GET["id"],
                    "price"=>$_POST["Price"],
                    "quantity"=>$_POST["quantity"],
                    "sku"=> $_POST["SKU"]));
    
            unset($_POST["add-note"]);
            header("refresh: 0");
        }
    
        if (isset($_POST['export'])) {
            // We save the table name, date, and parent id in the session so output-csv has access to it.
            $_SESSION["month"] = date('m');
            $_SESSION["year"] = date('Y');
            $_SESSION["id"] = $_GET["id"];
            $_SESSION["type"] = "po_items";
            // Redirect to the file, this specifically auto downloads the CSV for the relevant data saved in session
            header('Location: ../Output/output-csv.php');
        }
        
        if (isset($_POST['order'])) {
            $helper->updateEntry("purchase_order", "status", 'Submitted', $_GET['id']);
            $helper->updateEntry("purchase_order", "total_price", $_GLOBALS['spent'], $_GET['id']);
            $helper->updateEntry("sku", "quantity", $item["quantity"], $_GET['id']);
            try {
            foreach ($pos as $item) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $helper->saveEntry("sku_item",
                        array("parent_id"=>$item["sku"],
                            "status"=>"'Available'",
                            "price"=>$item["price"],
                            "retailer"=>"'" . $po["retailer"] . "'",
                            "order_no"=>"'" . $po["order_no"] . "'",
                            "timestamp"=> "'" . $date . "'"));
                }
            }
            } catch (\Exception $e) {
                // This only fails if the textarea values are invalid, thus this error message:
                echo '<script>alert("The data you\'ve entered is invalid. Please try again.")</script>';
            }
        }
    
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">

    <link href="../Style/po-details.css" rel="stylesheet" type="text/css">

    <!-- Import JS for Bootstrap. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Import JS for jQuery. -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>


    <title><?php echo "PO #" . $po["id"] . " - Axolotl Inventory"; ?></title>
    
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
              <h1 style='float: right; margin-right: 20px; margin-top: 30px; '>Purchase Order #" . $po["id"] . "</h1>";
        
        echo "<br><p class='desc' style='padding-bottom: 27px;'>Total Price: $" . $_GLOBALS['spent'] . " </p>";
        
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
                            <h1>Items On Order</h1>
                            <input type="text" id="Search" onkeyup="searchItems()" placeholder="Search labels...">
                            <button type="submit" name="export">Export to CSV</button>
                            <button type="submit" name="order">Receive Order</button>
                        </td>
                        <td></td>
                    </tr>
                </form>
    
            </table>
    
            <br>
            <table id="items">
                <tbody id="item-grid">
                <tr>
                    <th></th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
                <tr>
                    <form method="POST">
                        <td><button type="submit" name="add-note">Add</button></td>
                        <td><textarea type="text" name="SKU" id="SKU" placeholder="SKU..."></textarea></td>
                        <td><textarea type="text" name="Name" id="Name" placeholder="Name..."></textarea></td>
                        <td><textarea type="text" name="quantity" id="quantity" placeholder="Quantity..."></textarea></td>
                        <td><textarea type="text" name="Price" id="Price" placeholder="Price..."></textarea></td>
                    </form>
                </tr>
                <?php
                foreach ($itemsorted as $item) {
                    echo "<tr class=\"target\" name=\"" . $item["id"] . "\">";
                    echo "<td><form action=\"\" method=\"POST\">
                                  <button type='submit'>🗑️</button>
                                  <input type=\"hidden\"  name=\"delete\"  value=\"" . $item["id"] . "\"/>
                              </td>";
                    echo "<td> ". $item["sku"] . " </th>";
                    echo "<td> ". $item["name"] . " </th>";
                    echo "<td> ". $item["quantity"] . " </th>";
                    echo "<td> $". $item["price"] . " </th>";
                    echo "</tr></form>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    </body>
</html>
