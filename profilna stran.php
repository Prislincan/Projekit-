<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Če ni prijavljen, preusmeri na login
if (!isset($_SESSION['uporabnisko_ime'])) {
    header("Location: login_stran.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Moj profil</title>
<link rel="stylesheet" href="style.css">
<style>
.profile-container {
    max-width: 700px;
    margin: 50px auto;
    background: #fff;
    border-radius: 20px;
    padding: 30px 40px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-header h2 {
    color: #4f46e5;
    font-size: 28px;
    margin-bottom: 5px;
}

.profile-header p {
    color: #6b7280;
    font-size: 15px;
}

.profile-section {
    margin-top: 25px;
}

.profile-section h3 {
    font-size: 18px;
    color: #374151;
    margin-bottom: 10px;
}

.profile-form {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.profile-form input[type="text"],
.profile-form input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    margin-top: 10px;
    margin-bottom: 10px;
    outline: none;
    transition: border 0.2s ease;
}

.profile-form input:focus {
    border-color: #4f46e5;
}

.profile-form button {
    background: #4f46e5;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.profile-form button:hover {
    background: #4338ca;
}

.success-msg {
    background: #dcfce7;
    color: #166534;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}

.error-msg {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}
</style>
</head>

<body>
<div class="container">
    <div class="profile-container">
        <div class="profile-header">
            <h2>👤 Pozdravljen, <?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?>!</h2>
            <p>Tukaj lahko spremeniš svoje uporabniško ime ali geslo.</p>
        </div>

        <!-- ✅ Sprememba uporabniškega imena -->
        <div class="profile-section">
            <h3>Sprememba uporabniškega imena</h3>
            <form action="spreminjanje podatkov.php" method="POST" class="profile-form">
                <label>Trenutno uporabniško ime:</label>
                <input type="text" value="<?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?>" disabled>

                <label>Novo uporabniško ime:</label>
                <input type="text" name="uporabnisko_ime" placeholder="Vnesi novo uporabniško ime" required>

                <button type="submit" name="shrani_uporabnisko_ime">💾 Shrani spremembo</button>
            </form>
        </div>

        <!-- ✅ Sprememba gesla -->
        <div class="profile-section">
            <h3>Sprememba gesla</h3>
            <form action="spreminjanje podatkov.php" method="POST" class="profile-form">
                <label>Novo geslo:</label>
                <input type="password" name="password" placeholder="Vnesi novo geslo" required>

                <button type="submit" name="shrani_geslo">💾 Shrani geslo</button>
            </form>
        </div>

        <div style="text-align:center; margin-top: 20px;">
            <a href="zacetna.php" class="btn btn-outline">⬅ Nazaj na glavno stran</a>
        </div>
    </div>
</div>
</body>
</html>
