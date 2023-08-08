<?php
//THIS IS FOR DISPLAYING THE PARTS AND THEIR COSTS ETC NOT FOR WORKORDERS

    require_once "plugin.php";

    $inventory = listDataset("sku", null);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            deleteEntry("sku", $_POST['delete']);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['view'])) {
            viewEntry("sku", $_POST['view']);
            unset($_POST["view"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-item'])) {
            saveEntry("sku", $_POST['name'], null, 0, null, null, null);
            unset($_POST["add-item"]);
            header("refresh: 0");
        }

    }

?>
<!DOCTYPE html><html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Items - Axolotl Inventory</title>

    <!-- Import CSS for item list. -->
    <link href="style/inventory-list.css" rel="stylesheet" type="text/css">
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
        <th><button class="nav-btn"><a href="index.php">Devices</a></button></th>
        <th><button class="nav-btn"><a href="financials.php">Financials</a></button></th>
    </table>
    
    <br>

    <p class="tab">&gt; Add item to inventory?</p>
    <div id="add-tab">
        <form method="post" id="add-form">

            <input type="text" name="name" id="name" placeholder="Item name..." required><br>

           <button type="submit" name="add-item" onclick="window.location.reload()"><p class="add-btn">Submit</p></button>

        </form>
    </div>
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
