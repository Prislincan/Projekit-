<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Preveri prijavo
if (!isset($_SESSION['role'])) {
    die("Dostop zavrnjen. Najprej se prijavi.");
}

$is_ucitelj = $_SESSION['role'] === 'ucitelj';
$is_ucenec = $_SESSION['role'] === 'ucenec';
$email = $_SESSION['email'];

// ✅ Pridobi ID učitelja ali učenca
if ($is_ucitelj) {
    $sql = "SELECT id_ucitelja FROM ucitelji u 
            JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika 
            WHERE up.email = ?";
} else {
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

// ✅ Preveri, ali učenec ureja nalogo
$id_urejanja = isset($_GET['uredi']) ? intval($_GET['uredi']) : 0;
$urejana_naloga = null;

if ($is_ucenec && $id_urejanja > 0) {
    $stmt = $conn->prepare("SELECT * FROM oddaje_nalog WHERE id_oddaje = ? AND id_ucenca = ?");
    $stmt->bind_param("ii", $id_urejanja, $id_ucenca);
    $stmt->execute();
    $res = $stmt->get_result();
    $urejana_naloga = $res->fetch_assoc();
    $stmt->close();
}

// ✅ Pridobi predmete
if ($is_ucitelj) {
    $predmeti = $conn->query("
        SELECT p.id_predmeta, p.ime_predmeta 
        FROM predmeti p
        JOIN ucitelj_predmet up ON p.id_predmeta = up.id_predmeta
        WHERE up.id_ucitelja = $id_ucitelja
    ");
} else {
    $predmeti = $conn->query("
        SELECT p.id_predmeta, p.ime_predmeta 
        FROM predmeti p
        JOIN ucenec_predmet up ON p.id_predmeta = up.id_predmeta
        WHERE up.id_ucenca = $id_ucenca
    ");
}

// ✅ Oddaja nove naloge
if ($is_ucenec && isset($_POST['oddaj_nalogo'])) {
    $id_predmeta = intval($_POST['id_predmeta']);
    $naslov = trim($_POST['naslov']);
    $komentar = trim($_POST['komentar']);

    if (!empty($id_predmeta) && !empty($naslov) && isset($_FILES['datoteka'])) {
        $upload_folder = "uploads_naloge/";
        $target_dir = __DIR__ . "/" . $upload_folder;
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES['datoteka']['name']);
        $absolute_path = $target_dir . $file_name;
        $relative_path = $upload_folder . $file_name;

        if (move_uploaded_file($_FILES['datoteka']['tmp_name'], $absolute_path)) {
            $stmt = $conn->prepare("
                INSERT INTO oddaje_nalog (id_ucenca, id_predmeta, naslov, datoteka, komentar_ucenca)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisss", $id_ucenca, $id_predmeta, $naslov, $relative_path, $komentar);
            $stmt->execute();
            $stmt->close();

            $msg = "Naloga uspešno oddana ✅";
        } else {
            $msg = "Napaka pri nalaganju datoteke ❌";
        }
    }
}

// ✅ Posodobitev obstoječe naloge (urejanje)
if ($is_ucenec && isset($_POST['posodobi_nalogo'])) {
    $id_oddaje = intval($_POST['id_oddaje']);
    $komentar = trim($_POST['komentar']);
    $naslov = trim($_POST['naslov']);
    $zamenjaj_datoteko = isset($_FILES['datoteka']) && $_FILES['datoteka']['size'] > 0;

    $old = $conn->query("SELECT datoteka FROM oddaje_nalog WHERE id_oddaje = $id_oddaje AND id_ucenca = $id_ucenca")->fetch_assoc();
    $old_path = $old['datoteka'];

    if ($zamenjaj_datoteko) {
        $upload_folder = "uploads_naloge/";
        $target_dir = __DIR__ . "/" . $upload_folder;
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES['datoteka']['name']);
        $absolute_path = $target_dir . $file_name;
        $relative_path = $upload_folder . $file_name;

        if (move_uploaded_file($_FILES['datoteka']['tmp_name'], $absolute_path)) {
            if (file_exists($old_path)) unlink($old_path);

            $stmt = $conn->prepare("
                UPDATE oddaje_nalog
                SET naslov = ?, komentar_ucenca = ?, datoteka = ?, datum_oddaje = NOW()
                WHERE id_oddaje = ? AND id_ucenca = ?
            ");
            $stmt->bind_param("sssii", $naslov, $komentar, $relative_path, $id_oddaje, $id_ucenca);
        } else {
            $msg = "❌ Napaka pri nalaganju nove datoteke.";
        }
    } else {
        $stmt = $conn->prepare("
            UPDATE oddaje_nalog
            SET naslov = ?, komentar_ucenca = ?, datum_oddaje = NOW()
            WHERE id_oddaje = ? AND id_ucenca = ?
        ");
        $stmt->bind_param("ssii", $naslov, $komentar, $id_oddaje, $id_ucenca);
    }

    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
        $msg = "✅ Naloga uspešno posodobljena!";
           // ✅ Preusmeri učenca nazaj na glavno stran po uspešni posodobitvi
    header("Location: oddaja_nalog.php?posodobljeno=1");
    exit();

    }
}

// ✅ Učitelj oceni nalogo
if ($is_ucitelj && isset($_POST['oceni'])) {
    $id_oddaje = intval($_POST['id_oddaje']);
    $ocena = $_POST['ocena'];
    $komentar_ucitelja = $_POST['komentar_ucitelja'];

    $stmt = $conn->prepare("UPDATE oddaje_nalog SET ocena = ?, komentar_ucitelja = ? WHERE id_oddaje = ?");
    $stmt->bind_param("dsi", $ocena, $komentar_ucitelja, $id_oddaje);
    $stmt->execute();
    $stmt->close();
    $msg = "Ocena shranjena ✅";
}

// ✅ Izbran predmet iz URL-ja
$id_predmeta_iz_url = isset($_GET['id_predmeta']) ? intval($_GET['id_predmeta']) : 0;

// ✅ Podatki glede na vlogo
if ($is_ucenec) {
    $oddaje = $conn->query("
        SELECT o.*, p.ime_predmeta
        FROM oddaje_nalog o
        LEFT JOIN predmeti p ON o.id_predmeta = p.id_predmeta
        WHERE o.id_ucenca = $id_ucenca
        ORDER BY o.datum_oddaje DESC
    ");
} elseif ($is_ucitelj) {
    $oddaje = $conn->query("
        SELECT o.*, p.ime_predmeta, up.uporabnisko_ime
        FROM oddaje_nalog o
        JOIN ucenci u ON o.id_ucenca = u.id_ucenca
        JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika
        LEFT JOIN predmeti p ON o.id_predmeta = p.id_predmeta
        WHERE p.id_predmeta IN (
            SELECT id_predmeta FROM ucitelj_predmet WHERE id_ucitelja = $id_ucitelja
        )
        ORDER BY o.datum_oddaje DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Oddaja nalog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1><?= $is_ucitelj ? "Pregled in ocenjevanje nalog" : "Oddaja nalog" ?></h1>
    </header>

    <nav class="main-nav">
        <a href="gradiva.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'gradiva.php' ? 'active' : '' ?>">Gradiva</a>
        <a href="oddaja_nalog.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'oddaja_nalog.php' ? 'active' : '' ?>">Naloge</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ucenec'): ?>
            <a href="upravljanje_predmetov.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'upravljanje_predmetov.php' ? 'active' : '' ?>">Urejanje predmetov</a>
        <?php endif; ?>
    </nav>

    <main>
    <?php if ($is_ucenec && !$urejana_naloga): ?>
        <!-- 📤 Obrazec za oddajo nove naloge -->
        <div class="card">
            <h2>📤 Oddaj novo nalogo</h2>
            <?php if (isset($msg)) echo "<p><strong>$msg</strong></p>"; ?>
            <form method="POST" enctype="multipart/form-data" class="assignment-form">
                <div class="form-group">
                    <label for="id_predmeta">Predmet:</label>
                    <select name="id_predmeta" id="id_predmeta" class="form-select" required>
                        <option value="">Izberi predmet</option>
                        <?php 
                        $predmeti->data_seek(0);
                        while ($p = $predmeti->fetch_assoc()): ?>
                            <option value="<?= $p['id_predmeta'] ?>"><?= htmlspecialchars($p['ime_predmeta']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="naslov">Naslov naloge:</label>
                    <input type="text" id="naslov" name="naslov" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="datoteka">Datoteka:</label>
                    <input type="file" id="datoteka" name="datoteka" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="komentar">Komentar (neobvezno):</label>
                    <textarea id="komentar" name="komentar" class="form-input"></textarea>
                </div>

                <button type="submit" name="oddaj_nalogo" class="btn btn-success">📤 Oddaj nalogo</button>
            </form>
        </div>

    <?php elseif ($is_ucenec && $urejana_naloga): ?>
        <!-- ✏️ Obrazec za urejanje naloge -->
        <div class="card">
            <h2>✏️ Uredi nalogo</h2>
            <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Ali res želiš prepisati staro datoteko, če izbereš novo?');">
                <input type="hidden" name="id_oddaje" value="<?= $urejana_naloga['id_oddaje'] ?>">
                <div class="form-group">
                    <label>Naslov:</label>
                    <input type="text" name="naslov" class="form-input" value="<?= htmlspecialchars($urejana_naloga['naslov'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Trenutna datoteka:</label>
                    <a href="<?= htmlspecialchars($urejana_naloga['datoteka'] ?? '#') ?>" target="_blank">Odpri</a>
                </div>

                <div class="form-group">
                    <label>Nova datoteka (neobvezno):</label>
                    <input type="file" name="datoteka" class="form-input">
                </div>

                <div class="form-group">
                    <label>Komentar:</label>
                    <textarea name="komentar" class="form-input"><?= htmlspecialchars($urejana_naloga['komentar_ucenca'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="posodobi_nalogo" class="btn btn-primary">💾 Shrani spremembe</button>
                <a href="oddaja_nalog.php" class="btn btn-secondary">Prekliči</a>
            </form>
        </div>
    <?php endif; ?>

    <!-- 📚 Seznam oddanih nalog -->
    <div class="card">
        <h2><?= $is_ucitelj ? "Oddane naloge učencev" : "Zgodovina oddanih nalog" ?></h2>
        <?php if (isset($msg)) echo "<p><strong>$msg</strong></p>"; ?>

        <?php if ($oddaje->num_rows > 0): ?>
            <?php while ($o = $oddaje->fetch_assoc()): ?>
                <div class="subject-item">
                    <h3><?= htmlspecialchars($o['ime_predmeta'] ?: 'Neznan predmet') ?></h3>
                    <?php if ($is_ucitelj): ?>
                        <p><strong>Učenec:</strong> <?= htmlspecialchars($o['uporabnisko_ime']) ?></p>
                    <?php endif; ?>
                    <p><strong>Naslov:</strong> <?= htmlspecialchars($o['naslov']) ?></p>
                    <p><strong>Datoteka:</strong> <a href="<?= htmlspecialchars($o['datoteka']) ?>" target="_blank">Odpri</a></p>
                    <p><strong>Komentar učenca:</strong> <?= htmlspecialchars($o['komentar_ucenca']) ?></p>
                    <p><strong>Datum oddaje:</strong> <?= $o['datum_oddaje'] ?></p>

                    <?php if ($is_ucitelj): ?>
                        <form method="POST" style="margin-top:10px;">
                            <input type="hidden" name="id_oddaje" value="<?= $o['id_oddaje'] ?>">
                            <label>Ocena:</label>
                            <input type="number" min="1" max="5" name="ocena" step="1" value="<?= htmlspecialchars($o['ocena']) ?>" class="form-input" style="width:100px;">
                            <textarea name="komentar_ucitelja" class="form-input" placeholder="Komentar učitelja"><?= htmlspecialchars($o['komentar_ucitelja']) ?></textarea>
                            <button type="submit" name="oceni" class="btn btn-primary">💾 Shrani oceno</button>
                        </form>
                    <?php elseif (!empty($o['ocena'])): ?>
                        <p><strong>Ocena:</strong> <?= $o['ocena'] ?></p>
                        <p><strong>Komentar učitelja:</strong> <?= htmlspecialchars($o['komentar_ucitelja']) ?></p>
                    <?php else: ?>
                        <p><em>Še ni ocenjena.</em></p>
                        <form method="GET" action="oddaja_nalog.php">
                            <input type="hidden" name="uredi" value="<?= $o['id_oddaje'] ?>">
                            <button type="submit" class="btn btn-secondary">✏️ Uredi nalogo</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Ni oddanih nalog.</p>
        <?php endif; ?>
    </div>
</main>
    <footer>
        © 2025 eUčilnica – Vsa pravica pridržana.
    </footer>
</div>
</body>
</html>
