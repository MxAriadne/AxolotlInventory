<?php
    require_once "plugin.php";

    $wordorder = listDataset("items", null);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            deleteEntry("items", $_POST['delete']);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['view'])) {
            viewEntry("items", $_POST['view']);
            unset($_POST["view"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-item'])) {
            saveEntry("items", $_POST['name'], $_POST['info'], "Awaiting Diagnostics");
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
    <link href="style/list.css" rel="stylesheet" type="text/css">
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

    <button disabled=""><a href="index.php"><img src="images/home.png" alt="Home"></a>  Items stored</button>
    <br>

    <p class="tab">&gt; Add item to inventory?</p>
    <div id="add-tab">
        <form method="post" id="add-form">

            <input type="text" name="name" id="name" placeholder="Item name..."><br>

            <input type="text" style="height: 150px;" name="info" id="desc" placeholder="Item description...">

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
            <th id="col-remove">x</th>
            <th id="col-id">ID</th>
            <th id="col-name">Name</th>
            <th id="col-desc">Description</th>
            <th id="col-desc">Status</th>
            <th id="col-date">Date</th>
        </tr>
        <?php
            foreach ($wordorder as $wo) {
                echo "<tr class=\"target\" name=\"" . $wo["id"] . "\">";
                echo "<form action=\"\" method=\"POST\"><td>
                          <button type='submit'>🔎</button>
                          <input type=\"hidden\"  name=\"view\"  value=\"" . $wo["id"] . "\"/>
                      </form>
                      <form action=\"\" method=\"POST\">
                          <button type='submit'>🗑️</button>
                          <input type=\"hidden\"  name=\"delete\"  value=\"" . $wo["id"] . "\"/>
                      </td></form>";
                echo "<td> ". $wo["id"] . "</th>";
                echo "<td> ". $wo["name"] . " </th>";
                echo "<td> ". $wo["info"] . " </th>";
                echo "<td> ". $wo["status"] . " </th>";
                echo "<td> ". $wo["timestamp"] . " </th>";
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
