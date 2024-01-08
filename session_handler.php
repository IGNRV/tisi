<?php
require_once 'db.php'; // Asegúrate de incluir tu archivo de conexión a la base de datos

function openSession($savePath, $sessionName) {
  global $conn; // Usa la conexión a la base de datos existente
  return true;
}

function closeSession() {
  return true;
}

function readSession($id) {
  global $conn;
  $result = $conn->query("SELECT data FROM sessions WHERE id = '$id'");
  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    return $row['data'];
  } else {
    return "";
  }
}

function writeSession($id, $data) {
  global $conn;
  $timestamp = time();
  $conn->query("REPLACE INTO sessions VALUES ('$id', '$data', '$timestamp')");
  return true;
}

function destroySession($id) {
  global $conn;
  $conn->query("DELETE FROM sessions WHERE id = '$id'");
  return true;
}

function cleanSession($maxLifetime) {
  global $conn;
  $old = time() - $maxLifetime;
  $conn->query("DELETE FROM sessions WHERE timestamp < '$old'");
  return true;
}

session_set_save_handler("openSession", "closeSession", "readSession", "writeSession", "destroySession", "cleanSession");
?>