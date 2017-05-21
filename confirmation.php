<?php

require_once 'db.php';
$conn = connect();

if (isset($_GET['passkey'])) {
  echo '<pre>';
  include_once 'db.php';
  $passkey = $_GET['passkey'];
  $sql = "INSERT INTO " . TABLE_USER . " (fname, email, pass) SELECT fname, email, pass FROM " . TABLE_TEMP_USER . " WHERE c_code = '$passkey'";
  $result = db_query($conn, $sql);
  $row = mysql_affected_rows();
  if ($row) {
    $uid = mysql_insert_id();
    $type = 'user';
    $time = time();
    $sql = "UPDATE " . TABLE_USER . " SET type = '$type', timestamp = '$time' WHERE id = $uid";
    $result = db_query($conn, $sql);
    $value = "c_code = '" . $passkey . "'";
    $done = delete($conn, TABLE_TEMP_USER, $value);
    if ($done && $result) {
      $s = "UPDATE " . TABLE_USER . " SET uid = $uid WHERE id = $uid";
      $r = db_query($conn, $s);
      echo 'Your account has been ACTIVATED and VERFIED!!';
    } else {
      echo 'Not done';
    }
  } else {
    echo 'Sorry, We are unable to verify your account';
  }
  echo '</pre>';
}