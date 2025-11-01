<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doma</title>
</head>
<body>
    <header>
           <?php if (isset($_SESSION['email'])): ?>
            <!-- Prikaz uporabniškega imena, če je uporabnik prijavljen -->
                    <a href="profilna stran.php"class ="vpis"><?= htmlspecialchars($_SESSION['uporabnisko_ime']) ?></a>
                    <p class="vpis črta_prijava">|</p>
                    <a href="odjava.php"class ="vpis">Odjava</a>
                    <?php else: ?>
            <!-- Povezava za vpis/registracijo, če ni uporabnik prijavljen -->
                    <a href="login stran.php" class="vpis">Vpiši se / Registracija</a>
                    <?php endif; ?>
    </header>
</body>
</html>