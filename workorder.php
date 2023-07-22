<?php
    require_once "plugin.php";

    $notes = listDataset("notes", array("id"=>$_GET["id"]));

    /*$workorder = workorderHold("items", $_GET["id"]);

    consoleLog($workorder[0]);
    foreach ($workorder as $wo) {
        consoleLog($wo);
    }*/

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['delete'])) {
            deleteEntry("notes", $_POST['delete']);
            unset($_POST["delete"]);
            header("refresh: 0");
        }

        if (isset($_POST['add-note'])) {
            saveEntry("notes", $_GET["id"], $_POST["note"], $_POST["status"]);
            unset($_POST["add-note"]);
            header("refresh: 0");
        }

    }
?>
<!DOCTYPE html><html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title> - Axolotl Inventory</title>

    <!-- Import CSS for item list. -->

</head>
<body>
    <div id="item-modal" class="modal">
        <div id="note-list" class="modal-content">
            <h1>Notes</h1>
            <br>
            <table id="items">
                <tbody id="item-grid">
                <tr>
                    <th id="col-remove">x</th>
                    <th id="col-desc">Note</th>
                    <th id="col-desc">Status</th>
                    <th id="col-date">Date</th>
                </tr>
                <tr>
                    <form method="POST">
                        <td><button type="submit" name="add-note"><p>Add</p></button></td>
                        <td><input type="text" name="note" id="note" placeholder="Note..."></td>
                        <td><select name="status">
                                <option value="">Awaiting Diagnostics</option>
                                <option value="">Awaiting Repair</option>
                                <option value="">Repair in Progress</option>
                                <option value="">Need to Order Parts</option>
                                <option value="">Unrepairable</option>
                                <option value="">Repaired</option>
                            </select></td>
                    </form>
                </tr>
                <?php
                foreach ($notes as $note) {
                    echo "<tr name=\"" . $note["id"] . "\">";
                    echo "<td><form action=\"\" method=\"POST\">
                              <button type='submit'>🗑️</button>
                              <input type=\"hidden\"  name=\"delete\"  value=\"" . $note["id"] . "\"/>
                          </td></form>";
                    echo "<td> ". $note["note"] . " </th>";
                    echo "<td> ". $note["status"] . " </th>";
                    echo "<td> ". $note["timestamp"] . " | " . $note["id"] . " </th>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
