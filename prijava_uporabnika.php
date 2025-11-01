
<?php
session_start(); // Začne sejo

include 'povezavaPHP.php';

// Preberemo podatke iz obrazca
$email = $_POST['email'];
$password = $_POST['password'];

// Pripravi poizvedbo glede na email
$stmt = $conn->prepare("SELECT id_uporabnika, uporabnisko_ime, `password`, `role`, email FROM uporabniki WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 🔒 Primerjava gesla (če še ne uporabljaš password_hash, pusti enostavno primerjavo)
    if ($password === $user['password']) {
        // Shrani podatke v sejo
        $_SESSION['id_uporabnika'] = $user['id_uporabnika'];
        $_SESSION['uporabnisko_ime'] = $user['uporabnisko_ime'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        header("Location: zacetna.php");
        exit();
    } else {
        echo "❌ Napačno geslo.";
    }
} else {
    echo "❌ Uporabnik s tem emailom ne obstaja.";
}

$stmt->close();
$conn->close();
?>