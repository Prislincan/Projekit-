<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vpis</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      display: flex;
      height: 100vh;
    }

    .left-side {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 80px;
      align-items: center;
    }

    .right-side {
      flex: 1;
      background: url('slike/slika_prijavna_stran.jpg') no-repeat center center/cover;
    }

    h2 {
      font-size: 28px;
      margin-bottom: 10px;
    }

    h4 {
      font-size: 16px;
      margin-bottom: 30px;
      color: #666;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-bottom: 20px;
    }

    input {
      padding: 15px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    button {
      padding: 15px;
      background-color: #5c4cbc;
      color: white;
      font-weight: 600;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color:black;
    }


    .footer-link {
      margin-top: 30px;
      text-align: center;
    }

    .footer-link a {
      color: #7e7ef8;
      text-decoration: none;
      font-weight: 500;
    }
    .notranja_širina
    {
       width: 60%;
    }
    .napaka {
      color: red;
      margin-bottom: 15px;
    }
    @media (max-width: 776px) {
  body {
    background: url('3d-rendering-abstract-black-white-background.jpg') no-repeat center center/cover;
    position: relative;
  }

  .left-side {
    flex: none;
    width: 100%;
    padding: 40px 20px;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.85); /* semi-transparent white overlay */
    z-index: 1;
  }

  .right-side {
    display: none;
  }

  .notranja_širina {
    width: 100%;
    max-width: 400px;
  }
}
  </style>
</head>
<body>

  <div class="left-side">
    <div class="notranja_širina">
    <h4>Vpiši svoje detajle</h4>
    <h2>Vpiši se v e-učilnico</h2>
    <form action="prijava_uporabnika.php" method="POST">
      <input type="email" name="email" placeholder="E-mail*" required>
      <input type="password" name="password" placeholder="Geslo*" required>
      <button type="submit">Vpiši se</button>
    </form>

    <div class="footer-link">
      <p style="margin-top: 20px;"><a href="začetna stran.php">Vrni se na začetno stran</a></p>
    </div>
    </div>
  </div>

  <div class="right-side"></div>

</body>
</html>