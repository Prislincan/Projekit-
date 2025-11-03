<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Samo admin lahko ureja
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_stran.php");
    exit();
}

// ✅ Preveri ID učenca
if (!isset($_GET['id_ucenca'])) {
    die("Napaka: manjka ID učenca.");
}

$id_ucenca = intval($_GET['id_ucenca']);

// ✅ Shrani izbrane predmete
if (isset($_POST['shrani'])) {
    $conn->query("DELETE FROM ucenec_predmet WHERE id_ucenca = $id_ucenca");

    if (!empty($_POST['predmeti'])) {
        $stmt = $conn->prepare("INSERT INTO ucenec_predmet (id_ucenca, id_predmeta) VALUES (?, ?)");
        foreach ($_POST['predmeti'] as $id_predmeta) {
            $stmt->bind_param("ii", $id_ucenca, $id_predmeta);
            $stmt->execute();
        }
        $stmt->close();
    }

    header("Location: ucenci.php");
    exit();
}

// ✅ Podatki o učencu
$sql = "SELECT * FROM ucenci WHERE id_ucenca = $id_ucenca";
$ucenec = $conn->query($sql)->fetch_assoc();

// ✅ Vsi predmeti
$predmeti = $conn->query("SELECT * FROM predmeti");

// ✅ Že dodeljeni predmeti
$dodeljeni = [];
$res = $conn->query("SELECT id_predmeta FROM ucenec_predmet WHERE id_ucenca = $id_ucenca");
while ($r = $res->fetch_assoc()) {
    $dodeljeni[] = $r['id_predmeta'];
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
<meta charset="UTF-8">
<title>Dodeli predmete učencu</title>
<link rel="stylesheet" href="style.css">
<style>
/* 💎 Dodatni stili za dodeljevanje predmetov */
.subject-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 15px;
}

.subject-item {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.subject-item:hover {
    transform: scale(1.01);
    background: #f9fafb;
}

.subject-item input[type="checkbox"] {
    margin-right: 12px;
    transform: scale(1.2);
    accent-color: #4f46e5;
}

.subject-item label {
    cursor: pointer;
    flex-grow: 1;
}

.subject-item i {
    color: #6b7280;
    font-size: 0.9rem;
}

.action-buttons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}
</style>
</head>
<body>

<div class="container">
    <header>
        <h1>🎓 Dodeli predmete učencu: 
            <span style="color:#4f46e5;">
                <?= htmlspecialchars($ucenec['ime'] . ' ' . $ucenec['priimek']) ?>
            </span>
        </h1>
    </header>

    <div class="card">
        <form method="POST" class="assignment-form">
            <div class="subject-list">
                <?php while ($p = $predmeti->fetch_assoc()): ?>
                    <div class="subject-item">
                        <input type="checkbox" id="predmet_<?= $p['id_predmeta'] ?>" name="predmeti[]" 
                               value="<?= $p['id_predmeta'] ?>"
                               <?= in_array($p['id_predmeta'], $dodeljeni) ? 'checked' : '' ?>>
                        <label for="predmet_<?= $p['id_predmeta'] ?>">
                            <strong><?= htmlspecialchars($p['ime_predmeta']) ?></strong>
                            <i> — <?= htmlspecialchars($p['opis']) ?></i>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="action-buttons">
                <button type="submit" name="shrani" class="btn btn-success">💾 Shrani spremembe</button>
                <a href="ucenci.php" class="btn btn-outline">⬅ Nazaj</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
