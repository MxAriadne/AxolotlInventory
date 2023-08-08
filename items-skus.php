<?php
    require_once "plugin.php";
    
    $items = listDataset("sku_item", array("id" => $_GET["id"]));
    $itemsorted = [];
    foreach ($items as $item) {
        array_unshift($itemsorted, $item);
    }

    // This saves the workorder information so I can populate the page.
    $sku = workorderHold("sku", $_GET["id"]);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            deleteEntry("sku_item", $_POST['delete']);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-note'])) {
            saveEntry("sku_item", $_GET["id"], $_POST["Price"], $_POST["status"], $_POST["Retailer"], $_POST["Order"], $_POST["quantity"]);
            unset($_POST["add-note"]);
            header("refresh: 0");
        }

    }
    
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <?php
        echo "<title>" . $sku["name"] . " - Axolotl Inventory</title>";
    ?>
    
    <link href="style/sku-view.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="top-div">
        
        <?php
            echo "<table>
                      <tr class=\"nav-row\">
                      <td><button class=\"nav-btn\" type='button'><a href='index.php'>Home</a></button></td>
                      <td><button class=\"nav-btn\"><a href='items.php'>Items</a></button></td>
                      <td><button class=\"nav-btn\"><a href='financials.php'>Financials</a></button></td>
                      </tr>
                </table>
                <h1 style='float: right; margin-right: 20px;'>" . $sku["name"] . "</h1>";
            echo "<br><p class='desc' style='padding-bottom: 27px;'>Current Quantity: " . $sku["quantity"] . " </p>";
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
            <h1>Total Item Inventory</h1>
            <input type="text" id="Search" onkeyup="searchItems()" placeholder="Search labels...">
            
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
                <tr>
                    <form method="POST">
                        <td><button type="submit" name="add-note">Add</button></td>
                        <td></td>
                        <td><select name="status" id="status">
                                <option value="In Transit">In Transit</option>
                                <option value="Available">Available</option>
                                <option value="Defective">Defective</option>
                                <option value="Broken">Broken</option>
                                <option value="Trashed">Trashed</option>
                                <option value="Sold">Sold</option>
                            </select></td>
                        <td><textarea type="text" name="Price" id="Price" placeholder="Price..."></textarea></td>
                        <td><textarea type="text" name="Retailer" id="Retailer" placeholder="Retailer..."></textarea></td>
                        <textarea hidden name="quantity" id="quantity" value="<?php echo $sku["quantity"]?>"></textarea>
                        <td><textarea type="text" name="Order" id="Order" placeholder="Order #..."></textarea></td>
                        <td><p class='note'><?php echo date("m/d/Y")?></p></td>
                    </form>
                </tr>
                <?php
                foreach ($itemsorted as $item) {
                    echo "<tr class=\"target\" name=\"" . $item["id"] . "\">";
                    echo "<td><form action=\"\" method=\"POST\">
                              <button type='submit'>🗑️</button>
                              <input type=\"hidden\"  name=\"delete\"  value=\"" . $item["id"] . "\"/>
                          </td></form>";
                    echo "<td> ". $item["id"] . " </th>";
                    echo "<td> ". $item["status"] . " </th>";
                    echo "<td> ". $item["price"] . " </th>";
                    echo "<td> ". $item["retailer"] . " </th>";
                    echo "<td> ". $item["order_no"] . "</th>";
                    echo "<td> ". $item["timestamp"] . "</th>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
