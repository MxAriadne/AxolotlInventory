<?php

namespace Axolotl\Output;

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/vendor/autoload.php";
require_once $path;

use Axolotl\Helper;

$helper = new Helper();

$helper->consoleLog("Funk yeah concrete" . $_GET['id']);
if (isset($_GET['origin'])) {
    echo "<script>
            window.print();
          </script>";
    //sleep(5);

    //header('Location: ../Workorders/' . $_GET['origin']);
}

