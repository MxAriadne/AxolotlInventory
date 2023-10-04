<?php
    namespace Axolotl;

    // Runs the autoload
    $path = "../../vendor/autoload.php";
    require_once $path;
    
    $helper = new Helper();

    date_default_timezone_set('America/Chicago');
    $date = date("m/d/Y\ng:i a");

    $wordorder = $helper->listDataset("items", null);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            $optional = array("parent_id"=>$_POST['delete'], "child"=>"notes");
            $helper->deleteEntry("items", $_POST['delete'], $optional);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['view'])) {
            $helper->viewEntry("items", $_POST['view']);
            unset($_POST["view"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-item'])) {
            $helper->saveEntry("items",
                array("name"=>"'" . $_POST['name'] . "'",
                    "info"=>"'" . $_POST['info'] . "'",
                    "status"=>"'Awaiting Diagnostics'",
                    "serial"=>"'" . $_POST['serial'] . "'",
                    "timestamp"=>"'" . $date . "'",
                    "imei"=>"'" . $_POST['imei'] . "'"));

            unset($_POST["add-item"]);
            header("refresh: 0");
        }

        if (isset($_POST['export'])) {
            // We save the table name and date in the session so output-csv has access to it.
            $_SESSION["month"] = $_POST["month"];
            $_SESSION["year"] = $_POST["year"];
            $_SESSION["type"] = "items";
            // Redirect to the file, this specifically auto downloads the CSV for the relevant data saved in session
            header('Location: ../Output/output-csv.php');
        }
        
        if (isset($_POST['print'])) {
            //header('Location: ../Output/output-barcode.php?id=' . $_POST['print'] . '&origin=workorders.php');
            //echo "<script> popUpAndPrint(); </script>";
        }
        
    }

?>
<!DOCTYPE html><html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Workorders - Axolotl Inventory</title>

    <!-- Import CSS for item list. -->
    <link href="../Style/global.css" rel="stylesheet" type="text/css">
    <link href="../Style/workorders.css" rel="stylesheet" type="text/css">
    <!-- Import JS for Bootstrap. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Import JS for jQuery. -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

    <!-- Barcode Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.3/JsBarcode.all.min.js"></script>

</head>
<body>

<svg id="barcode"></svg>

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

    function popUpAndPrint(name, id)
    {
        JsBarcode("#barcode", id, {text: id + ' - ' + name});

        let container = $('#barcode');
        let printWindow = window.open('', 'PrintMap',
            'width=' + 1024 + ',height=' + 512);
        printWindow.document.writeln(barcode.outerHTML);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    }
    setTimeout(popUpAndPrint, 500);
    
    /*function popUpAndPrint(name, id)
    {
        
        let printWindow = window.open('', 'PrintMap');
        printWindow.document.writeln();
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    }*/

    document.addEventListener('DOMContentLoaded', function () {
        $('.tab').on('click', function(){
            $(this).next().slideToggle(700);
        });
    });
</script>

<!-- Nav bar -->
<div id="left-data-box">

    <button disabled="">Devices stored</button>
    <table>
        <th><button class="nav-btn"><a href="../Inventory/inventory.php">Inventory</a></button></th>
        <th><button class="nav-btn"><a href="../Purchasing/purchasing.php">Purchasing</a></button></th>
    </table>
    
    <br>

    <p class="tab">&gt; Add device to inventory?</p>
    <div id="add-tab">
        <form method="post" id="add-form">

            <input type="text" name="name" id="name" placeholder="Item name..." required><br>
            
            <input type="text" name="serial" id="name" placeholder="Item serial..."><br>
            
            <input type="text" name="imei" id="name" placeholder="Item IMEI..."><br>

            <input type="text" style="height: 150px;" name="info" id="desc" placeholder="Item description...">

           <button type="submit" name="add-item" onclick="window.location.reload()"><p class="add-btn">Submit</p></button>

        </form>
    </div>
    <br><br>
    <table id="export-csv-table">
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
            <th>Name</th>
            <th>Description</th>
            <th>Serial/IMEI</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php
            foreach ($wordorder as $wo) {
                echo "<tr class=\"target\" name=\"" . $wo["id"] . "\">";
                echo "<td><form action=\"\" method=\"POST\">
                          <button type='submit'>🔎</button>
                          <input type=\"hidden\"  name=\"view\"  value=\"" . $wo["id"] . "\"/>
                      </form>
                      <form action=\"\" method=\"POST\">
                          <button type='submit'>🗑️</button>
                          <input type=\"hidden\"  name=\"delete\"  value=\"" . $wo["id"] . "\"/>
                      </form>
                      <form action=\"\" method=\"POST\">
                          <button onclick='popUpAndPrint(\"". $wo["name"] . "\", \"" . $wo["id"] . "\")'>🖨</button>
                          <!--<input type=\"hidden\"  name=\"print\"  value=\"" . $wo["id"] . "\"/>-->
                      </td>
                      </form>";
                echo "<td> ". $wo["id"] . "</td>";
                echo "<td> ". $wo["name"] . " </td>";
                echo "<td> ". $wo["info"] . " </td>";
                if ($wo["serial"] && $wo["imei"]) {
                    echo "<td>Serial: ". $wo["serial"] . "<br>IMEI: " . $wo["imei"] . " </td>";
                } elseif ($wo["serial"]) {
                    echo "<td>Serial: ". $wo["serial"] . "</td>";
                } elseif ($wo["imei"]) {
                    echo "<td>IMEI: ". $wo["imei"] . "</td>";
                } else {
                    echo "<td>N/A</td>";
                }
                echo "<td> ". $wo["status"] . " </td>";
                echo "<td> ". $wo["timestamp"] . " </td>";
                echo "</tr>";
            }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
