<?php
session_start();
$users = [
    'teacher1' => ['password' => 'ucitelj123', 'role' => 'teacher'],
    'teacher2' => ['password' => 'ucitelj456', 'role' => 'teacher']
];

if ($_POST['username'] && $_POST['password']) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (isset($users[$username]) && $users[$username]['password'] == $password) {
        $_SESSION['user_id'] = $username;
        $_SESSION['role'] = 'teacher';
        header("Location: teacher_dashboard.php");
    } else {
        echo "Napačno uporabniško ime ali geslo!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prijava za učitelje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Prijava za učitelje</h2>
        <form method="post">
            <div class="mb-3">
                <label>Uporabniško ime:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Geslo:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Prijava</button>
        </form>
        <hr>
        <a href="login_student.php">Prijava za učence</a>
    </div>
</body>
</html>