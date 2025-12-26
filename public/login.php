<?php
// public/login.php
require_once __DIR__ . '/../config/config.php';

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= APP_NAME ?> - Login</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>

  <header class="navbar">
    <div class="brand">
      <span class="logo">🐬</span>
      <span><?= APP_NAME ?></span>
    </div>
  </header>

  <main class="main">
    <section class="card">
      <h1>Login</h1>
      
      <div class="form">
        <?php if ($error === 'invalid'): ?>
          <div class="alert">Invalid email or password.</div>
        <?php elseif ($error === 'missing'): ?>
          <div class="alert">Please enter your email and password.</div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST" autocomplete="off">
          <div class="field">
            <input class="input" type="email" name="email" placeholder="Email address" required />
          </div>

          <div class="field">
            <input class="input" type="password" name="password" placeholder="Password" required />
          </div>

          <button class="btn" type="submit">
            <span class="lock">🔒</span>
            Login
          </button>
        </form>
      </div>
      <p class="copyright">Copyright © <?= date('Y') ?> <?= APP_NAME ?></p>
    </section>
  </main>

</body>
</html>
