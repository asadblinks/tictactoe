<?php

include_once 'db.php';
if (isset($_SESSION)) {
  foreach ($_SESSION as $key => $val) {
    unset($_SESSION[$key]);
  }
}

header('Location: access.php');
?>