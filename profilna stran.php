<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header>
           <?php if (isset($_SESSION['email'])): ?>
                    <a href="profilna stran.php"class ="vpis"><?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?></a>
                    <p class="vpis črta_prijava">|</p>
                    <a href="odjava.php"class ="vpis">Odjava</a>
                    <?php else: ?>
                        <a class="a_padding" href="x">xx</a>
                    <?php endif; ?>
    </header>
    <h2 >Pozdravljen, <?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?>!</span></h2>
</body>
</html>