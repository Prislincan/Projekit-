<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header style="background: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="zacetna.php" style="color: white; text-decoration: none; font-size: 20px; font-weight: bold;">📘 eUčilnica</a>
    </div>

    <nav>
        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="predmeti.php" style="color: white; margin-right: 15px; text-decoration: none;">Predmeti</a>
                <a href="ucitelji.php" style="color: white; margin-right: 15px; text-decoration: none;">Učitelji</a>
                <a href="ucenci.php" style="color: white; margin-right: 15px; text-decoration: none;">Učenci</a>

            <?php elseif ($_SESSION['role'] === 'ucitelj'): ?>
                <a href="predmeti.php" style="color: white; margin-right: 15px; text-decoration: none;">Predmeti</a>

            <?php elseif ($_SESSION['role'] === 'ucenec'): ?>
                <a href="predmeti.php" style="color: white; margin-right: 15px; text-decoration: none;">Moji predmeti</a>
            <?php endif; ?>

            <span style="color: #bdc3c7; margin-right: 10px;">|</span>
            <a href="profilna stran.php" style="color: #f1c40f; text-decoration: none; margin-right: 15px;">
                <?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?>
            </a>
            <a href="odjava.php" style="color: #e74c3c; text-decoration: none;">Odjava</a>

        <?php else: ?>
            <a href="login stran.php" style="color: white; text-decoration: none;">Prijava / Registracija</a>
        <?php endif; ?>
    </nav>
</header>