<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Samo učenec ima dostop
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ucenec') {
    die("Dostop zavrnjen. Ta stran je namenjena samo učencem.");
}

$email = $_SESSION['email'];

// ✅ Pridobi ID učenca
$stmt = $conn->prepare("
    SELECT u.id_ucenca 
    FROM ucenci u
    JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika
    WHERE up.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$ucenec = $result->fetch_assoc();
$stmt->close();

$id_ucenca = $ucenec['id_ucenca'];

// ✅ Pridobi vse predmete
$predmeti_vsi = $conn->query("SELECT id_predmeta, ime_predmeta FROM predmeti ORDER BY ime_predmeta ASC");

// ✅ Pridobi predmete, v katere je učenec vpisan
$trenutni_predmeti = [];
$res = $conn->query("SELECT id_predmeta FROM ucenec_predmet WHERE id_ucenca = $id_ucenca");
while ($row = $res->fetch_assoc()) {
    $trenutni_predmeti[] = $row['id_predmeta'];
}

// ✅ Shrani spremembe
if (isset($_POST['shrani'])) {
    $izbrani_predmeti = isset($_POST['predmeti']) ? $_POST['predmeti'] : [];

    if (count($izbrani_predmeti) < 2) {
        $msg = "❌ Izbrati moraš vsaj 2 predmeta.";
    } else {
        // Najprej pobriši stare vpise
        $conn->query("DELETE FROM ucenec_predmet WHERE id_ucenca = $id_ucenca");

        // Nato dodaj nove
        $stmt = $conn->prepare("INSERT INTO ucenec_predmet (id_ucenca, id_predmeta) VALUES (?, ?)");
        foreach ($izbrani_predmeti as $id_predmeta) {
            $stmt->bind_param("ii", $id_ucenca, $id_predmeta);
            $stmt->execute();
        }
        $stmt->close();

        $msg = "✅ Predmeti uspešno posodobljeni!";
        // Osveži seznam trenutnih predmetov
        $trenutni_predmeti = $izbrani_predmeti;
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Upravljanje predmetov</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Upravljanje predmetov</h1>
    </header>

    <nav class="main-nav">
        <a href="gradiva.php" class="nav-link">Gradiva</a>
        <a href="oddaja_nalog.php" class="nav-link">Naloge</a>
        <a href="upravljanje_predmetov.php" class="nav-link active">Urejanje predmetov</a>
    </nav>

    <main>
        <div class="card">
            <h2>Izberi predmete, ki jih želiš obiskovati</h2>
            <?php if (isset($msg)) echo "<p><strong>$msg</strong></p>"; ?>

            <form method="POST">
                <div class="form-group" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <?php while ($p = $predmeti_vsi->fetch_assoc()): ?>
                        <?php $checked = in_array($p['id_predmeta'], $trenutni_predmeti) ? 'checked' : ''; ?>
                        <label style="background:#f8f9ff; padding:10px; border-radius:10px; display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="predmeti[]" value="<?= $p['id_predmeta'] ?>" <?= $checked ?>>
                            <?= htmlspecialchars($p['ime_predmeta']) ?>
                        </label>
                    <?php endwhile; ?>
                </div>

                <button type="submit" name="shrani" class="btn btn-primary" style="margin-top:15px;">💾 Shrani spremembe</button>
            </form>
        </div>
    </main>
        <footer>
        © 2025 eUčilnica – Vsa pravica pridržana.
    </footer>
</div>
</body>
</html>
