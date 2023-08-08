<?php
    require_once "plugin.php";
    
    $notes = listDataset("notes", array("id" => $_GET["id"]));
    $notesorted = [];
    foreach ($notes as $note) {
        array_unshift($notesorted, $note);
    }

    // This saves the workorder information so I can populate the page.
    $workorder = workorderHold("items", $_GET["id"]);

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            deleteEntry("notes", $_POST['delete']);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-note'])) {
            saveEntry("notes", $_GET["id"], $_POST["note"], $_POST["status"], "", "", null);
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
        echo "<title>" . $workorder["name"] . " - Axolotl Inventory</title>";
    ?>
    
    <link href="style/item-view.css" rel="stylesheet" type="text/css">
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
                <h1 style='float: right; margin-right: 20px;'>" . $workorder["name"] . "</h1>";
            echo "<br><p class='desc'>Device Description: " . $workorder["info"] . " </p>";
            echo "<br><p class='desc'>Device Serial: " . $workorder["imei"] . " </p>";
            echo "<br><p class='desc' style='padding-bottom: 27px;'>Device IMEI: " . $workorder["serial"] . " </p>";
            echo "<p style='float: right; padding-bottom: 35px; padding-top: 0px;'> Current Status: " . $workorder["status"] . " </p>";
        ?>
    </div>
    
    <div>
        <div id="note-list">
            <h1>Notes</h1>
            <br>
            <table id="items">
                <tbody id="item-grid">
                <tr>
                    <th id="col-remove"></th>
                    <th id="col-desc">Note</th>
                    <th id="col-desc">Status</th>
                    <th id="col-date">Date</th>
                </tr>
                <tr>
                    <form method="POST">
                        <td><button type="submit" name="add-note">Add</button></td>
                        <td><textarea type="text" name="note" id="note" placeholder="Note..."></textarea></td>
                        <td><select name="status" id="status">
                                <option value="Awaiting Diagnostics">Awaiting Diagnostics</option>
                                <option value="Awaiting Repair">Awaiting Repair</option>
                                <option value="Repair in Progress">Repair in Progress</option>
                                <option value="Need to Order Parts">Need to Order Parts</option>
                                <option value="Unrepairable">Unrepairable</option>
                                <option value="Repaired">Repaired</option>
                            </select></td>
                        <td><p class='note'><?php echo date("m/d/Y")?></p></td>
                    </form>
                </tr>
                <?php
                foreach ($notesorted as $note) {
                    echo "<tr name=\"" . $note["id"] . "\">";
                    echo "<td><form action=\"\" method=\"POST\">
                              <button type='submit'>🗑️</button>
                              <input type=\"hidden\"  name=\"delete\"  value=\"" . $note["id"] . "\"/>
                          </td></form>";
                    echo "<td> <p class='note'>". $note["note"] . "</p> </th>";
                    echo "<td> ". $note["status"] . " </th>";
                    echo "<td> ". $note["timestamp"] . "</th>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
