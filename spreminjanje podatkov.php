<?php
session_start();
include 'povezavaPHP.php';

if (isset($_POST['shrani_uporabnisko_ime'])) {
    $novo_ime = trim($_POST['uporabnisko_ime']);
    $id_uporabnika = $_SESSION['id_uporabnika'];

    if (!empty($novo_ime)) {
        $stmt = $conn->prepare("UPDATE uporabniki SET uporabnisko_ime = ? WHERE id_uporabnika = ?");
        $stmt->bind_param("si", $novo_ime, $id_uporabnika);

        if ($stmt->execute()) {
            $_SESSION['uporabnisko_ime'] = $novo_ime;
            header("location: profilna stran.php");
            exit(); 
        } else {
            echo "❌ Napaka pri posodabljanju uporabniškega imena.";
        }

        $stmt->close();
    } else {
        echo "⚠️ Polje za uporabniško ime ne sme biti prazno.";
    }
}

if (isset($_POST['shrani_geslo'])) {
    $novo_geslo = trim($_POST['password']);
    $id_uporabnika = $_SESSION['id_uporabnika'];

    if (!empty($novo_geslo)) {
        $stmt = $conn->prepare("UPDATE uporabniki SET `password` = ? WHERE id_uporabnika = ?");
        $stmt->bind_param("si", $novo_geslo, $id_uporabnika);

        if ($stmt->execute()) {
            $_SESSION['password'] = $novo_geslo;
            header("location: profilna stran.php");
            exit(); 
        } else {
            echo "❌ Napaka pri posodabljanju uporabniškega imena.";
        }

        $stmt->close();
    } else {
        echo "⚠️ Polje za uporabniško ime ne sme biti prazno.";
    }
}


if (isset($conn)) {
    $conn->close();
}
?>