<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Preveri, ali je admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// ✅ Dodaj učitelja
if ($is_admin && isset($_POST['dodaj'])) {
    $uporabnik_id = !empty($_POST['uporabnik_id']) ? intval($_POST['uporabnik_id']) : null;
    $naziv = $_POST['naziv'];
    $kabinet = $_POST['kabinet'];
    $telefon = $_POST['telefon'];
    $aktiven = isset($_POST['aktiven']) ? 1 : 0;

    if (!$uporabnik_id && !empty($_POST['novo_uporabnisko_ime']) && !empty($_POST['nov_email']) && !empty($_POST['novo_geslo'])) {
        $novo_uporabnisko_ime = $_POST['novo_uporabnisko_ime'];
        $nov_email = $_POST['nov_email'];
        $novo_geslo = $_POST['novo_geslo'];
        $vloga = 'ucitelj';

        $check = $conn->prepare("SELECT id_uporabnika FROM uporabniki WHERE uporabnisko_ime = ? OR email = ?");
        $check->bind_param("ss", $novo_uporabnisko_ime, $nov_email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $msg = "❌ Uporabnik s tem uporabniškim imenom ali emailom že obstaja.";
        } else {
            $stmt_user = $conn->prepare("INSERT INTO uporabniki (uporabnisko_ime, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt_user->bind_param("ssss", $novo_uporabnisko_ime, $nov_email, $novo_geslo, $vloga);
            $stmt_user->execute();
            $uporabnik_id = $stmt_user->insert_id;
            $stmt_user->close();
        }
    }

    if ($uporabnik_id) {
        $stmt = $conn->prepare("INSERT INTO ucitelji (uporabnik_id, naziv, kabinet, telefon, aktiven) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $uporabnik_id, $naziv, $kabinet, $telefon, $aktiven);
        $stmt->execute();
        $stmt->close();
        header("Location: ucitelji.php?msg=added");
        exit();
    } else {
        $msg = "❌ Napaka: uporabnik ni bil izbran ali ustvarjen.";
    }
}

// ✅ Brisanje
if ($is_admin && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Najprej pridobi uporabnik_id
    $result = $conn->query("SELECT uporabnik_id FROM ucitelji WHERE id_ucitelja = $id");
    $ucitelj = $result->fetch_assoc();
    $uporabnik_id = $ucitelj['uporabnik_id'];

    // Izbriši učitelja
    $conn->query("DELETE FROM ucitelji WHERE id_ucitelja = $id");

    // Izbriši tudi uporabnika
    $conn->query("DELETE FROM uporabniki WHERE id_uporabnika = $uporabnik_id");

    header("Location: ucitelji.php");
    exit();
}

// ✅ Urejanje
if ($is_admin && isset($_POST['uredi'])) {
    $id = intval($_POST['id_ucitelja']);
    $naziv = $_POST['naziv'];
    $kabinet = $_POST['kabinet'];
    $telefon = $_POST['telefon'];
    $aktiven = isset($_POST['aktiven']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE ucitelji SET naziv=?, kabinet=?, telefon=?, aktiven=? WHERE id_ucitelja=?");
    $stmt->bind_param("sssii", $naziv, $kabinet, $telefon, $aktiven, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ucitelji.php");
    exit();
}

// ✅ Pridobi učitelje
$sql = "SELECT uc.id_ucitelja, up.uporabnisko_ime, up.email, uc.naziv, uc.kabinet, uc.telefon, uc.aktiven
        FROM ucitelji uc
        JOIN uporabniki up ON uc.uporabnik_id = up.id_uporabnika";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="sl">
<head>
<meta charset="UTF-8">
<title>Učitelji</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    margin: 0;
    padding: 20px;
    color: #1f2937;
}

.container {
    max-width: 1100px;
    margin: 0 auto;
}

header h1 {
    text-align: center;
    color: #4f46e5;
    font-size: 32px;
    margin-bottom: 30px;
}

/* Glavna kartica */
.card {
    background: #fff;
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
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
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

/* Celice */
.subject-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 15px;
}

/* Vrstica hover */
.subject-table tr:hover td {
    background-color: #f9fafb;
}

/* Gumbi */
.btn {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    color: white;
    border: none;
    transition: all 0.2s ease;
    margin-right: 5px;
}

.btn-sm {
    font-size: 13px;
    padding: 6px 12px;
}

.btn-primary {
    background-color: #4f46e5;
}

.btn-primary:hover {
    background-color: #4338ca;
    transform: translateY(-1px);
}

.btn-danger {
    background-color: #ef4444;
}

.btn-danger:hover {
    background-color: #dc2626;
    transform: translateY(-1px);
}

.btn-outline {
    background: white;
    border: 2px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-1px);
}

/* Tabela – zadnji stolpec (dejanja) */
.subject-table td:last-child {
    white-space: nowrap;
}

/* Polja v formi */
.form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    margin-bottom: 10px;
}

.form-input:focus {
    border-color: #4f46e5;
    outline: none;
}

/* Checkbox */
input[type="checkbox"] {
    transform: scale(1.2);
    margin-right: 5px;
}

/* Footer */
footer {
    text-align: center;
    color: #f3f4f6;
    font-size: 14px;
    margin-top: 30px;
}
</style>
</head>
<body>

<div class="container">
    <header>
        <h1>👨‍🏫 Seznam učiteljev</h1>
    </header>

    <div class="card">
        <table class="subject-table">
            <thead>
                <tr>
                    <th>Uporabniško ime</th>
                    <th>Email</th>
                    <th>Naziv</th>
                    <th>Kabinet</th>
                    <th>Telefon</th>
                    <th>Aktiven</th>
                    <?php if ($is_admin): ?><th>Dejanja</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['uporabnisko_ime']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['naziv']) ?></td>
                    <td><?= htmlspecialchars($row['kabinet']) ?></td>
                    <td><?= htmlspecialchars($row['telefon']) ?></td>
                    <td><?= $row['aktiven'] ? '✅ Da' : '❌ Ne' ?></td>
                    <?php if ($is_admin): ?>
                    <td>
                        <a class="btn btn-primary btn-sm" href="ucitelji.php?edit=<?= $row['id_ucitelja'] ?>">✏️ Uredi</a>
                        <a class="btn btn-danger btn-sm" href="ucitelji.php?delete=<?= $row['id_ucitelja'] ?>" onclick="return confirm('Res želiš izbrisati tega učitelja?')">🗑️ Izbriši</a>
                        <a class="btn btn-outline btn-sm" href="dodeli_predmete.php?id_ucitelja=<?= $row['id_ucitelja'] ?>">📚 Dodeli predmete</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if ($is_admin): ?>
        <div class="card">
            <h2>➕ Dodaj novega učitelja</h2>
            <form method="POST" class="assignment-form">
                <div class="form-group">
                    <label>Uporabniško ime:</label>
                    <input type="text" name="novo_uporabnisko_ime" class="form-input">
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="nov_email" class="form-input">
                </div>

                <div class="form-group">
                    <label>Geslo:</label>
                    <input type="password" name="novo_geslo" class="form-input">
                </div>

                <hr>

                <div class="form-group">
                    <label>Naziv (npr. prof., mag.):</label>
                    <input type="text" name="naziv" class="form-input">
                </div>

                <div class="form-group">
                    <label>Kabinet:</label>
                    <input type="text" name="kabinet" class="form-input">
                </div>

                <div class="form-group">
                    <label>Telefon:</label>
                    <input type="text" name="telefon" class="form-input">
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="aktiven" value="1" checked> Aktiven</label>
                </div>

                <button type="submit" name="dodaj" class="btn btn-success">💾 Dodaj učitelja</button>
            </form>
        </div>

        <?php if (isset($_GET['edit'])):
            $id = intval($_GET['edit']);
            $edit_result = $conn->query("SELECT * FROM ucitelji WHERE id_ucitelja = $id");
            $ucitelj = $edit_result->fetch_assoc();
        ?>
        <div class="card">
            <h2>✏️ Uredi učitelja</h2>
            <form method="POST" class="assignment-form">
                <input type="hidden" name="id_ucitelja" value="<?= $ucitelj['id_ucitelja'] ?>">

                <div class="form-group">
                    <label>Naziv:</label>
                    <input type="text" name="naziv" value="<?= htmlspecialchars($ucitelj['naziv']) ?>" class="form-input">
                </div>

                <div class="form-group">
                    <label>Kabinet:</label>
                    <input type="text" name="kabinet" value="<?= htmlspecialchars($ucitelj['kabinet']) ?>" class="form-input">
                </div>

                <div class="form-group">
                    <label>Telefon:</label>
                    <input type="text" name="telefon" value="<?= htmlspecialchars($ucitelj['telefon']) ?>" class="form-input">
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="aktiven" value="1" <?= $ucitelj['aktiven'] ? 'checked' : '' ?>> Aktiven</label>
                </div>

                <button type="submit" name="uredi" class="btn btn-primary">💾 Shrani spremembe</button>
                <a href="ucitelji.php" class="btn btn-outline">Prekliči</a>
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
