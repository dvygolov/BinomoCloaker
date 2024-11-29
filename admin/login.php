<?php
require_once __DIR__."/password.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    $result = array('success' => check_password(false));
    echo json_encode($result);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Yellow Cloaker Login</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.classList.add('loading');
                
                const password = document.getElementById('password').value;
                const formData = new FormData();
                formData.append('password', password);
                
                try {
                    const response = await fetch('login.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        alert('Wrong password!');
                        // Re-enable button and hide loading state
                        submitButton.disabled = false;
                        submitButton.classList.remove('loading');
                    }
                } catch (error) {
                    alert('Error occurred during login');
                    // Re-enable button and hide loading state
                    submitButton.disabled = false;
                    submitButton.classList.remove('loading');
                }
            });
        });
    </script>
</head>
<body>
    <div id="main">
        <div id="title">
            <img src="img/logobig.png" />
        </div>
        <?php include __DIR__."/version.php"; ?>
        <form id="login-form">
            <label for="password">Enter Admin Password👇</label><br />
            <input type="password" id="password" name="password" required/><br />
            <button type="submit">
                <img src="img/loading.apng" class="loading-img" alt="Loading..." />
                <span>Login</span>
            </button>
        </form>
    </div>
</body>
</html>
