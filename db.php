<?php
$pdo = new PDO("mysql:host=localhost;dbname=ertis", "root", "Azaza09za");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
session_start();
?>