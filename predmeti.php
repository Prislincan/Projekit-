<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// Preverimo vlogo
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_ucitelj = isset($_SESSION['role']) && $_SESSION['role'] === 'ucitelj';
$is_ucenec = isset($_SESSION['role']) && $_SESSION['role'] === 'ucenec';

// 🔹 DODAJANJE novega predmeta
if ($is_admin && isset($_POST['dodaj'])) {
    $ime = $_POST['ime_predmeta'];
    $opis = $_POST['opis'];

    if (!empty($ime)) {
        $stmt = $conn->prepare("INSERT INTO predmeti (ime_predmeta, opis) VALUES (?, ?)");
        $stmt->bind_param("ss", $ime, $opis);
        $stmt->execute();
        $stmt->close();
        header("Location: predmeti.php");
        exit();
    }
}

// 🔹 BRISANJE predmeta
if ($is_admin && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM predmeti WHERE id_predmeta = $id");
    header("Location: predmeti.php");
    exit();
}

// 🔹 POSODOBITEV predmeta
if ($is_admin && isset($_POST['uredi'])) {
    $id = intval($_POST['id_predmeta']);
    $ime = $_POST['ime_predmeta'];
    $opis = $_POST['opis'];

    $stmt = $conn->prepare("UPDATE predmeti SET ime_predmeta = ?, opis = ? WHERE id_predmeta = ?");
    $stmt->bind_param("ssi", $ime, $opis, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: predmeti.php");
    exit();
}

// 🔹 PRIDOBIVANJE predmetov glede na vlogo
if ($is_admin) {
    $result = $conn->query("SELECT * FROM predmeti");
}

elseif ($is_ucitelj) {
    $email = $_SESSION['email'];
    $sql = "
        SELECT p.*
        FROM predmeti p
        JOIN ucitelj_predmet up ON p.id_predmeta = up.id_predmeta
        JOIN ucitelji u ON up.id_ucitelja = u.id_ucitelja
        JOIN uporabniki uu ON u.uporabnik_id = uu.id_uporabnika
        WHERE uu.email = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
}

elseif ($is_ucenec) {
    $email = $_SESSION['email'];
    $sql = "
        SELECT p.*
        FROM predmeti p
        JOIN ucenec_predmet up ON p.id_predmeta = up.id_predmeta
        JOIN ucenci u ON up.id_ucenca = u.id_ucenca
        JOIN uporabniki uu ON u.uporabnik_id = uu.id_uporabnika
        WHERE uu.email = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
}

else {
    $result = $conn->query("SELECT * FROM predmeti WHERE 1=0");
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Predmeti</title>
    <style>
        <style>
body {
    font-family: 'Inter', Arial, sans-serif;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    margin: 0;
    padding: 40px 0;
    color: #1f2937;
}

/* Glavni okvir */
.container {
    max-width: 1100px;
    margin: 0 auto;
}

/* Naslov strani */
header h1 {
    text-align: center;
    color: #4f46e5;
    font-size: 32px;
    margin-bottom: 30px;
}

/* Kartica z vsebino */
.card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    padding: 25px 30px;
    margin-bottom: 25px;
}

/* Tabela */
.subject-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.05);
}

/* Glava tabele */
.subject-table th {
    background-color: #4f46e5;
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 12px 15px;
    font-size: 15px;
}

/* Vrstice */
.subject-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e5e7eb;
    background-color: #fff;
    font-size: 15px;
}

/* Učinek ob prehodu */
.subject-table tr:hover td {
    background-color: #f9fafb;
}

/* Povezave v tabeli */
.subject-link {
    text-decoration: none;
    color: #4f46e5;
    font-weight: 600;
}

.subject-link:hover {
    text-decoration: underline;
}

/* Gumbi za urejanje in brisanje */
.btn {
    display: inline-block;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    text-decoration: none;
    color: white;
}

.btn-primary {
    background-color: #4f46e5;
}

.btn-primary:hover {
    background-color: #4338ca;
}

.btn-danger {
    background-color: #ef4444;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-outline {
    background: none;
    border: 1px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline:hover {
    background-color: #4f46e5;
    color: white;
}

/* Forma za dodajanje predmeta */
.assignment-form .form-group {
    margin-bottom: 15px;
}

.form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
}

.form-input:focus {
    border-color: #4f46e5;
    outline: none;
}

/* Footer */
footer {
    text-align: center;
    color: #f3f4f6;
    font-size: 14px;
    margin-top: 30px;
}
</style>
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>
            <?php if ($is_ucenec): ?>
                Moji predmeti
            <?php elseif ($is_ucitelj): ?>
                Predmeti, ki jih poučujem
            <?php else: ?>
                Upravljanje predmetov
            <?php endif; ?>
        </h1>
    </header>

    <div class="card">
        <table class="subject-table">
            <thead>
                <tr>
                    <th>Ime predmeta</th>
                    <th>Opis</th>
                    <?php if ($is_admin): ?><th>Dejanja</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($is_ucitelj || $is_ucenec): ?>
                                <a href="gradiva.php?id_predmeta=<?= $row['id_predmeta'] ?>" class="subject-link">
                                    <?= htmlspecialchars($row['ime_predmeta']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($row['ime_predmeta']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['opis']) ?></td>
                        <?php if ($is_admin): ?>
                        <td>
                            <a class="btn btn-primary btn-sm" href="predmeti.php?edit=<?= $row['id_predmeta'] ?>">✏️ Uredi</a>
                            <a class="btn btn-danger btn-sm" href="predmeti.php?delete=<?= $row['id_predmeta'] ?>" onclick="return confirm('Res želiš izbrisati ta predmet?')">🗑️ Izbriši</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if ($is_admin): ?>
        <div class="card">
            <h2>➕ Dodaj nov predmet</h2>
            <form method="POST" class="assignment-form">
                <div class="form-group">
                    <label>Ime predmeta:</label>
                    <input type="text" name="ime_predmeta" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Opis:</label>
                    <input type="text" name="opis" class="form-input">
                </div>

                <button type="submit" name="dodaj" class="btn btn-success">💾 Dodaj predmet</button>
            </form>
        </div>

        <?php if (isset($_GET['edit'])): ?>
            <?php
            $id = intval($_GET['edit']);
            $edit_result = $conn->query("SELECT * FROM predmeti WHERE id_predmeta = $id");
            $predmet = $edit_result->fetch_assoc();
            ?>
            <div class="card">
                <h2>✏️ Uredi predmet</h2>
                <form method="POST" class="assignment-form">
                    <input type="hidden" name="id_predmeta" value="<?= $predmet['id_predmeta'] ?>">
                    <div class="form-group">
                        <label>Ime predmeta:</label>
                        <input type="text" name="ime_predmeta" class="form-input" value="<?= htmlspecialchars($predmet['ime_predmeta']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Opis:</label>
                        <input type="text" name="opis" class="form-input" value="<?= htmlspecialchars($predmet['opis']) ?>">
                    </div>

                    <button type="submit" name="uredi" class="btn btn-primary">💾 Shrani spremembe</button>
                    <a href="predmeti.php" class="btn btn-outline">Prekliči</a>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
        <footer>
        © 2025 eUčilnica – Vsa pravica pridržana.
    </footer>
</div>
</body>
</html>

