<?php
    namespace Axolotl\Purchasing;

    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/vendor/autoload.php";
    require_once $path;
    
    use Axolotl\Helper;
    
    $helper = new Helper();
    
    date_default_timezone_set('America/Chicago');
    $date = date("m/d/Y\ng:i a");
    
    $po = $helper->listDataset("purchase_order", null);
    
    $_GLOBALS['spent'] = 0;
    foreach ($po as $item) {
        $_GLOBALS['spent'] = $_GLOBALS['spent'] + $item['total_price'];
    }
    
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
        if (isset($_POST['delete'])) {
            $optional = array("parent_id"=>$_POST['delete'], "child"=>"po_items");
            $helper->deleteEntry("purchase_order", $_POST['delete'], $optional);
            unset($_POST["delete"]);
            header("refresh: 0");
        }
    
        if (isset($_POST['view'])) {
            $helper->viewEntry("purchase_order", $_POST['view']);
            unset($_POST["view"]);
            header("refresh: 0");
        }
    
        if (isset($_POST['add-item'])) {
            $helper->saveEntry("purchase_order",
                array("retailer"=>"'" . $_POST["retailer"] . "'",
                    "status"=>"'Not submitted'",
                    "total_price"=>00.00,
                    "order_no"=>"'" . $_POST['order_no'] . "'",
                    "timestamp"=> "'" . $date . "'"));
            
            unset($_POST["add-item"]);
            header("refresh: 0");
        }
    
        if (isset($_POST['export'])) {
            // We save the table name and date in the session so output-csv has access to it.
            $_SESSION["month"] = $_POST["month"];
            $_SESSION["year"] = $_POST["year"];
            $_SESSION["type"] = "purchase_order";
            // Redirect to the file, this specifically auto downloads the CSV for the relevant data saved in session
            header('Location: ../Output/output-csv.php');
        }
    
    }

?>
<!DOCTYPE html><html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Purchasing - Axolotl Inventory</title>
    
    <!-- Global CSS -->
    <link href="../Style/global.css" rel="stylesheet" type="text/css">
    <!-- Import CSS for item list. -->
    <link href="../Style/purchasing.css" rel="stylesheet" type="text/css">
    <!-- Import JS for Bootstrap. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Import JS for jQuery. -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

</head>
<body>

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

<!-- Nav bar -->
<div id="left-data-box">

    <button disabled="">Purchase Orders</button>
    <table>
        <th><button class="nav-btn"><a href="../Inventory/inventory.php">Inventory</a></button></th>
        <th><button class="nav-btn"><a href="../Workorders/workorders.php">Workorders</a></button></th>
    </table>

    <br>

    <p class="tab">&gt; Create new purchase order?</p>
    <div id="add-tab">
        <form method="post" id="add-form">

            <input type="text" name="retailer" id="retailer" placeholder="Retailer..." required><br>
            <input type="text" name="order_no" id="order_no" placeholder="Order ID..." required><br>

            <button type="submit" name="add-item" onclick="window.location.reload()"><p class="add-btn">Submit</p></button>

        </form>
    </div>

    <table id="export-csv-table">
        <br><br>
        <form method="post">
            <tr><td><button type="submit" name="export">Export to CSV</button><td></tr>
            <tr><td><select name="month">
                        <option value="01">01 - January</option>
                        <option value="02">02 - February</option>
                        <option value="03">03 - March</option>
                        <option value="04">04 - April</option>
                        <option value="05">05 - May</option>
                        <option value="06">06 - June</option>
                        <option value="07">07 - July</option>
                        <option value="08">08 - August</option>
                        <option value="09">09 - September</option>
                        <option value="10">10 - October</option>
                        <option value="11">11 - November</option>
                        <option value="12">12 - December</option>
                    </select>

                    <select name="year">
                        <?php
                        $date = date("Y") - 5;
                        for ($i = 0; $i < 5; $i++) {
                            $date++;
                            echo "<option value='$date'>$date</option>";
                        }
                        ?>
                    </select></td></tr>
        </form>
    </table>
</div>

<div id="right-data-box">
    <table>
        <tbody>
        <tr>
            <td style="padding-right: 10px">
                <input type="text" id="Search" onkeyup="searchItems()" placeholder="Search...">
                <?php echo "<button>Spent: $" . $_GLOBALS['spent'] . "</button>";?>
            </td>
        </tr>
        </tbody>
    </table>
    <br>

    <table id="items">
        <tbody id="item-grid">
        <tr>
            <th>x</th>
            <th>ID</th>
            <th>Retailer</th>
            <th>Status</th>
            <th>Price</th>
            <th>Order ID</th>
            <th>Date</th>
        </tr>
        <?php
        foreach ($po as $item) {
            echo "<tr class=\"target\" name=\"" . $item["id"] . "\">";
            echo "<form action=\"\" method=\"POST\"><td>
                          <button type='submit'>🔎</button>
                          <input type=\"hidden\"  name=\"view\"  value=\"" . $item["id"] . "\"/>
                      </form>
                      <form action=\"\" method=\"POST\">
                          <button type='submit'>🗑️</button>
                          <input type=\"hidden\"  name=\"delete\"  value=\"" . $item["id"] . "\"/>
                      </td></form>";
            echo "<td> ". $item["id"] . "</td>";
            echo "<td> ". $item["retailer"] . " </td>";
            echo "<td> ". $item["status"] . " </td>";
            echo "<td> ". $item["total_price"] . " </td>";
            echo "<td> ". $item["order_no"] . " </td>";
            echo "<td> ". $item["timestamp"] . " </td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
