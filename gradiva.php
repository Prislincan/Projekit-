<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// Preverimo prijavo
if (!isset($_SESSION['role'])) {
    die("Dostop zavrnjen. Najprej se prijavi.");
}

$is_ucitelj = $_SESSION['role'] === 'ucitelj';
$is_ucenec = $_SESSION['role'] === 'ucenec';
$email = $_SESSION['email'];

$id_predmeta_iz_url = isset($_GET['id_predmeta']) ? intval($_GET['id_predmeta']) : null;

// 🔹 Pridobi ID učitelja ali učenca
if ($is_ucitelj) {
    $sql = "SELECT id_ucitelja FROM ucitelji u 
            JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika 
            WHERE up.email = ?";
} elseif ($is_ucenec) {
    $sql = "SELECT id_ucenca FROM ucenci u 
            JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika 
            WHERE up.email = ?";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$id_ucitelja = $is_ucitelj ? $user['id_ucitelja'] : null;
$id_ucenca = $is_ucenec ? $user['id_ucenca'] : null;

// 🔹 Pridobi predmete glede na vlogo
if ($is_ucitelj) {
    $predmeti = $conn->query("
        SELECT p.id_predmeta, p.ime_predmeta 
        FROM predmeti p
        JOIN ucitelj_predmet up ON p.id_predmeta = up.id_predmeta
        WHERE up.id_ucitelja = $id_ucitelja
    ");
} elseif ($is_ucenec) {
    $predmeti = $conn->query("
        SELECT p.id_predmeta, p.ime_predmeta 
        FROM predmeti p
        JOIN ucenec_predmet up ON p.id_predmeta = up.id_predmeta
        WHERE up.id_ucenca = $id_ucenca
    ");
}

// 🔹 Nalaganje gradiva (samo učitelj)
if ($is_ucitelj && isset($_POST['nalozi'])) {
    $id_predmeta = intval($_POST['id_predmeta']);
    $naslov = $_POST['naslov'];
    $opis = $_POST['opis'];

    $upload_folder = "uploads_gradiva/";
    $target_dir = __DIR__ . "/" . $upload_folder;
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_name = time() . "_" . basename($_FILES['datoteka']['name']);
    $absolute_path = $target_dir . $file_name;
    $relative_path = $upload_folder . $file_name;

    if (move_uploaded_file($_FILES['datoteka']['tmp_name'], $absolute_path)) {
        $stmt = $conn->prepare("INSERT INTO gradiva (id_ucitelja, id_predmeta, naslov, opis, datoteka) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $id_ucitelja, $id_predmeta, $naslov, $opis, $relative_path);
        $stmt->execute();
        $stmt->close();
        $msg = "Gradivo uspešno naloženo ✅";
    } else {
        $msg = "Napaka pri nalaganju datoteke ❌";
    }
}

// 🔹 Brisanje gradiva (samo učitelj)
if ($is_ucitelj && isset($_GET['delete'])) {
    $id_gradiva = intval($_GET['delete']);
    $check = $conn->query("SELECT datoteka FROM gradiva WHERE id_gradiva = $id_gradiva AND id_ucitelja = $id_ucitelja");
    if ($check->num_rows === 1) {
        $row = $check->fetch_assoc();
        $file_path = $row['datoteka'];
        $conn->query("DELETE FROM gradiva WHERE id_gradiva = $id_gradiva");
        if (file_exists($file_path)) unlink($file_path);
        $msg = "Gradivo izbrisano ✅";
    } else {
        $msg = "Ne moreš izbrisati gradiva, ki ga nisi naložil ❌";
    }
}

// 🔹 Pridobi gradiva za izbrani predmet
$gradiva = false;
if ($id_predmeta_iz_url) {
    if ($is_ucitelj) {
        $gradiva = $conn->query("
            SELECT g.*, p.ime_predmeta 
            FROM gradiva g 
            JOIN predmeti p ON g.id_predmeta = p.id_predmeta 
            WHERE g.id_ucitelja = $id_ucitelja AND g.id_predmeta = $id_predmeta_iz_url
            ORDER BY g.nalozeno DESC
        ");
    } elseif ($is_ucenec) {
        $gradiva = $conn->query("
            SELECT g.*, p.ime_predmeta, u.uporabnisko_ime
            FROM gradiva g
            JOIN predmeti p ON g.id_predmeta = p.id_predmeta
            JOIN ucitelji uc ON g.id_ucitelja = uc.id_ucitelja
            JOIN uporabniki u ON uc.uporabnik_id = u.id_uporabnika
            WHERE g.id_predmeta = $id_predmeta_iz_url
            ORDER BY g.nalozeno DESC
        ");
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Gradiva</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1><?= $is_ucitelj ? "Upravljanje gradiv" : "Gradiva predmetov" ?></h1>
        <p><?= $is_ucitelj ? "Dodaj, urejaj ali izbriši učna gradiva za svoje predmete." : "Preglej in prenesi gradiva, ki jih objavijo tvoji učitelji." ?></p>
    </header>

    <!-- 🔹 Navigacija -->
    <nav class="main-nav">
        <a href="gradiva.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'gradiva.php' ? 'active' : '' ?>">Gradiva</a>
        <a href="oddaja_nalog.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'oddaja_nalog.php' ? 'active' : '' ?>">Naloge</a>

        <?php if ($is_ucenec): ?>
            <a href="upravljanje_predmetov.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'upravljanje_predmetov.php' ? 'active' : '' ?>">Upravljanje predmetov</a>
        <?php endif; ?>
    </nav>

    <main>
        <!-- 📘 Izbor predmeta -->
        <div class="card">
            <h2><?= $is_ucitelj ? "Moji predmeti" : "Predmeti, ki jih obiskujem" ?></h2>
            <form method="GET" action="gradiva.php">
                <select name="id_predmeta" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Izberi predmet --</option>
                    <?php while ($p = $predmeti->fetch_assoc()): ?>
                        <option value="<?= $p['id_predmeta'] ?>" <?= ($id_predmeta_iz_url == $p['id_predmeta']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['ime_predmeta']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <?php if ($id_predmeta_iz_url): ?>

        <!-- 📤 Nalaganje gradiva (učitelj) -->
        <?php if ($is_ucitelj): ?>
        <div class="card">
            <h2>Dodaj novo gradivo</h2>
            <form method="POST" enctype="multipart/form-data" class="assignment-form">
                <input type="hidden" name="id_predmeta" value="<?= $id_predmeta_iz_url ?>">
                <div class="form-group">
                    <label for="naslov">Naslov gradiva</label>
                    <input type="text" id="naslov" name="naslov" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="opis">Opis gradiva (neobvezno)</label>
                    <textarea id="opis" name="opis" class="form-input"></textarea>
                </div>
                <div class="form-group">
                    <label for="datoteka">Datoteka</label>
                    <input type="file" id="datoteka" name="datoteka" class="form-input" required>
                </div>
                <button type="submit" name="nalozi" class="btn btn-success">📤 Naloži gradivo</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- 📂 Seznam gradiv -->
        <div class="card">
            <h2><?= $is_ucitelj ? "Moja gradiva" : "Gradiva učiteljev" ?></h2>
            <?php if (isset($msg)) echo "<p><strong>$msg</strong></p>"; ?>

            <?php if ($gradiva && $gradiva->num_rows > 0): ?>
                <ul class="material-list">
                    <?php while ($g = $gradiva->fetch_assoc()): ?>
                        <li>
                            <div style="flex:1;">
                                <h3><?= htmlspecialchars($g['naslov']) ?></h3>
                                <p><?= htmlspecialchars($g['opis']) ?></p>
                                <?php if ($is_ucenec): ?>
                                    <small>Dodano od: <?= htmlspecialchars($g['uporabnisko_ime']) ?></small><br>
                                <?php endif; ?>
                                <small>Naloženo: <?= $g['nalozeno'] ?></small><br>
                                <a href="<?= htmlspecialchars($g['datoteka']) ?>" target="_blank" class="btn btn-outline btn-sm">📄 Odpri gradivo</a>
                            </div>
                            <?php if ($is_ucitelj): ?>
                                <a href="gradiva.php?delete=<?= $g['id_gradiva'] ?>&id_predmeta=<?= $id_predmeta_iz_url ?>" class="btn btn-danger btn-sm" onclick="return confirm('Res želiš izbrisati to gradivo?')">🗑 Izbriši</a>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Ni naloženih gradiv za ta predmet.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        © 2025 eUčilnica – Vsa pravica pridržana.
    </footer>
</div>
</body>
</html>