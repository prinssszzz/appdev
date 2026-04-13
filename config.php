<?php
$conn = mysqli_connect("localhost", "root", "", "appdev2");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>