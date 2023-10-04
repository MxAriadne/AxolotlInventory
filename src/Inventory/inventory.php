<?php
    namespace Axolotl\Inventory;

    // Runs the autoload
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/vendor/autoload.php";
    require_once $path;

    // Most functions are contained in helper.php
    use Axolotl\Helper;

    // New instance to run functions.
    $helper = new Helper();
    
    // This saves the entire inventory table as an array.
    $inventory = $helper->listDataset("sku", null);
    
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // The 'delete' element is attached to every SKU so once it's sent via the button in the table
        // it will call the delete function while passing the relevant IDs
        if (isset($_POST['delete'])) {
            // Optional values are stored in this array
            $optional = array("parent_id"=>$_POST['delete'], "child"=>"sku_item");
            
            // When deleting an item from inventory we need to pass the parent sku id and the child sku id
            // These are stored in optional since deleteEntry() is a generic function
            $helper->deleteEntry("sku", $_POST['delete'], $optional);

            // Not technically necessary for POST but don't want to accidentally cause
            // needless MySQL errors.
            unset($_POST["delete"]);
            
            // Reload the page to reflect the new table.
            header("refresh: 0");
        }

        // The 'view' element is attached to every SKU so once it's sent via the button in the table
        // it will call the view function while passing the relevant IDs
        if (isset($_POST['view'])) {
            $helper->viewEntry("sku", $_POST['view']);
            
            // Not technically necessary for POST but don't want to accidentally cause
            // needless MySQL errors.
            unset($_POST["view"]);
            
            // Reload the page to reflect the new table.
            header("refresh: 0");
        }

        // The 'add-item' element is contained in #left-data-box and it's simply passthrough for form data
        // to be saved in the inventory table.
        if (isset($_POST['add-item'])) {
            // saveEntry is a generic function, we pass an array of variable names and values for those variables.
            $helper->saveEntry("sku",
                array("name"=>"'" . $_POST['name'] . "'",
                    "quantity"=>0));

            // Not technically necessary for POST but don't want to accidentally cause
            // needless MySQL errors.
            unset($_POST["add-item"]);
            
            // Reload the page to reflect the new table.
            header("refresh: 0");
        }

        if (isset($_POST['export'])) {
            // We save the table name in the session so output-csv has access to it.
            $_SESSION["type"] = "sku";
            // Redirect to the file, this specifically auto downloads the CSV for the relevant data saved in session
            header('Location: ../Output/output-csv.php');
        }

    }

?>
<!DOCTYPE html><html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Inventory - Axolotl Inventory</title>

    <!-- Import CSS for item list. -->
    <link href="../Style/global.css" rel="stylesheet" type="text/css">
    <link href="../Style/inventory.css" rel="stylesheet" type="text/css">
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

    <button disabled="">Items stored</button>
    <table>
        <th><button class="nav-btn"><a href="../Workorders/workorders.php">Workorders</a></button></th>
        <th><button class="nav-btn"><a href="../Purchasing/purchasing.php">Purchasing</a></button></th>
    </table>
    
    <br>

    <p class="tab">&gt; Add item to inventory?</p>
    <div id="add-tab">
        <form method="post" id="add-form">

            <input type="text" name="name" id="name" placeholder="Item name..." required><br>

           <button type="submit" name="add-item" onclick="window.location.reload()"><p class="add-btn">Submit</p></button>

        </form>
    </div>

    <table id="export-csv-table">
        <br><br>
        <form method="post">
            <tr><td><button type="submit" name="export">Export to CSV</button><td></tr>
        </form>
    </table>
</div>

<div id="right-data-box">
    <table>
        <tbody>
        <tr>
            <td style="padding-right: 10px">
                <input type="text" id="Search" onkeyup="searchItems()" placeholder="Search...">
            </td>
        </tr>
        </tbody>
    </table>
    <br>

    <table id="items">
        <tbody id="item-grid">
        <tr>
            <th>x</th>
            <th>SKU</th>
            <th>Name</th>
            <th>Quantity</th>
        </tr>
        <?php
            foreach ($inventory as $item) {
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
                echo "<td> ". $item["name"] . " </td>";
                echo "<td> ". $item["quantity"] . " </td>";
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
