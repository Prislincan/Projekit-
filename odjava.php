<?php
session_start();
session_unset(); // pobriše vse spremenljivke seje
session_destroy(); // uniči sejo
header("Location: zacetna.php");
exit();
?>