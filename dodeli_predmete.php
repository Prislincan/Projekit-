<?php
session_start();
include 'povezavaPHP.php';
include 'header.php';

// ✅ Dovoljen samo admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_stran.php");
    exit();
}

// ✅ Preveri, ali je podan id učitelja
if (!isset($_GET['id_ucitelja'])) {
    die("Napaka: manjka ID učitelja.");
}

$id_ucitelja = intval($_GET['id_ucitelja']);

// ✅ Če admin klikne 'Shrani', posodobi povezave
if (isset($_POST['shrani'])) {
    $conn->query("DELETE FROM ucitelj_predmet WHERE id_ucitelja = $id_ucitelja");

    if (!empty($_POST['predmeti'])) {
        $stmt = $conn->prepare("INSERT INTO ucitelj_predmet (id_ucitelja, id_predmeta) VALUES (?, ?)");
        foreach ($_POST['predmeti'] as $id_predmeta) {
            $stmt->bind_param("ii", $id_ucitelja, $id_predmeta);
            $stmt->execute();
        }
        $stmt->close();
    }

    header("Location: ucitelji.php");
    exit();
}

// ✅ Pridobi podatke o učitelju
$sql_ucitelj = "SELECT u.id_ucitelja, up.uporabnisko_ime 
                FROM ucitelji u 
                JOIN uporabniki up ON u.uporabnik_id = up.id_uporabnika
                WHERE u.id_ucitelja = $id_ucitelja";
$ucitelj = $conn->query($sql_ucitelj)->fetch_assoc();

// ✅ Pridobi vse predmete
$predmeti = $conn->query("SELECT * FROM predmeti");

// ✅ Pridobi že dodeljene predmete temu učitelju
$dodeljeni_predmeti = [];
$res = $conn->query("SELECT id_predmeta FROM ucitelj_predmet WHERE id_ucitelja = $id_ucitelja");
while ($r = $res->fetch_assoc()) {
    $dodeljeni_predmeti[] = $r['id_predmeta'];
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
<meta charset="UTF-8">
<title>Dodeli predmete učitelju</title>
<link rel="stylesheet" href="style.css">
<style>
/* 💎 Dodatni stili za to stran */
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
        <h1>📚 Dodeli predmete učitelju: <span style="color:#4f46e5;"><?= htmlspecialchars($ucitelj['uporabnisko_ime']) ?></span></h1>
    </header>

    <div class="card">
        <form method="POST" class="assignment-form">
            <div class="subject-list">
                <?php while ($p = $predmeti->fetch_assoc()): ?>
                    <div class="subject-item">
                        <input type="checkbox" id="predmet_<?= $p['id_predmeta'] ?>" name="predmeti[]" 
                               value="<?= $p['id_predmeta'] ?>"
                               <?= in_array($p['id_predmeta'], $dodeljeni_predmeti) ? 'checked' : '' ?>>
                        <label for="predmet_<?= $p['id_predmeta'] ?>">
                            <strong><?= htmlspecialchars($p['ime_predmeta']) ?></strong>
                            <i> — <?= htmlspecialchars($p['opis']) ?></i>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="action-buttons">
                <button type="submit" name="shrani" class="btn btn-success">💾 Shrani spremembe</button>
                <a href="ucitelji.php" class="btn btn-outline">⬅ Nazaj</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
