<?php
require_once __DIR__."/password.php";
require_once __DIR__."/../requestfunc.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = array('success' => check_password(false));
    header('Content-Type: application/json');
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        #cursor {
            display: inline-block;
            color: #0F0;
            font-family: monospace;
        }

        button[type="submit"] {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            color: #0F0;
            text-shadow: 0 0 10px rgba(0, 255, 0, 0.5),
                         0 0 20px rgba(0, 255, 0, 0.3),
                         0 0 30px rgba(0, 255, 0, 0.2);
            border-color: #0F0;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.3);
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(0, 255, 0, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        button[type="submit"]:hover::before {
            left: 100%;
        }
    </style>
    <script>
        // Matrix rain effect
        function setupMatrixRain() {
            const canvas = document.getElementById('matrix-rain');
            const ctx = canvas.getContext('2d');

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const characters = "01";
            const fontSize = 14;
            const columns = canvas.width / fontSize;
            const drops = [];

            for (let x = 0; x < columns; x++) {
                drops[x] = Math.random() * -100;
            }

            function draw() {
                ctx.fillStyle = 'rgba(27, 42, 71, 0.05)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.fillStyle = '#0F0';
                ctx.font = fontSize + 'px monospace';

                for (let i = 0; i < drops.length; i++) {
                    const text = characters.charAt(Math.floor(Math.random() * characters.length));
                    ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                    if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                        drops[i] = 0;
                    }
                    drops[i]++;
                }
            }

            setInterval(draw, 35);

            window.addEventListener('resize', () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupMatrixRain();
            const form = document.getElementById('login-form');
            const submitButton = form.querySelector('button[type="submit"]');
            const passwordInput = document.getElementById('password');
            const fakeInput = document.getElementById('fake-input');
            const cursor = document.getElementById('cursor');
            
            // Focus input on page load
            passwordInput.focus();

            // Handle cursor blinking
            let cursorVisible = true;
            setInterval(() => {
                cursorVisible = !cursorVisible;
                cursor.textContent = cursorVisible ? '█' : '';
            }, 530);

            // Handle password input
            passwordInput.addEventListener('input', function(e) {
                const value = this.value;
                fakeInput.textContent = 'X'.repeat(value.length);
            });

            // Keep focus on the real input
            document.addEventListener('click', () => passwordInput.focus());
            fakeInput.addEventListener('click', (e) => {
                e.preventDefault();
                passwordInput.focus();
            });

            // Handle form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                submitButton.disabled = true;
                submitButton.classList.add('loading');
                
                const password = passwordInput.value;
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
                    }
                } catch (error) {
                    alert('Error occurred during login');
                }
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
            });
        });
    </script>
</head>
<body>
    <canvas id="matrix-rain"></canvas>
    <div class="grid-overlay"></div>
    <div id="main">
        <div id="title">
            <img src="<?=get_cloaker_path()?>img/logobig.png" alt="Yellow Cloaker Logo" />
        </div>
        <div class="login-container">
            <form id="login-form">
                <h2>Welcome Back</h2>
                <div class="input-group">
                    <label for="password">Enter Admin Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required autocomplete="off"/>
                        <div class="fake-input-container">
                            <span id="fake-input"></span><span id="cursor">█</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="login-button">
                    <img src="<?=get_cloaker_path()?>img/loading.apng" class="loading-img" alt="Loading..." />
                    <span>Login to Dashboard</span>
                </button>
            </form>
            <div class="version-info">
                <?php include __DIR__."/version.php"; ?>
            </div>
        </div>
    </div>
</body>
</html>
