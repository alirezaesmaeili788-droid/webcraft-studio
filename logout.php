<?php
session_start();

/* Session komplett löschen */
$_SESSION = [];
session_destroy();

/* Zur Hauptseite OHNE Login */
header("Location: index.php");
exit;
