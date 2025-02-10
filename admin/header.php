<?php
require_once __DIR__.'/dates.php';
require_once __DIR__.'/../debug.php';

function get_bases_version(): string
{
    $updateFile = __DIR__ . "/../bases/update.txt";
    if (!file_exists($updateFile)) {
        return "Unknown";
    }
    return file_get_contents($updateFile);
}
?>
<div class="header-advance-area">
    <div class="header-top-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <div class="logo-pro">
                        <div class="logo-container">
                            <a href="index.php" class="logo-link">
                                <img class="main-logo" src="<?=get_cloaker_path()?>img/logo.png" alt="" />
                            </a>
                            <div class="geo-version">
                                GeoBases: <a href="#" id="updateBases" title="Update bases"><?= get_bases_version() ?></a>
                                <img style="width:30px; height:30px;display:none;" src="<?=get_cloaker_path()?>img/loading.apng" id="loadingAnimation" />
                                <?php if (DebugMethods::on()): ?>
                                <span style="color: red; margin-left: 10px;">Debug Mode</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                    <div class="header-right-info">
                        <ul class="nav navbar-nav mai-top-nav header-right-menu">
                            <li class="nav-item">
                                <a class="nav-link" href="#" id='litepicker'>
                                    <i class="bi bi-calendar"></i>
                                    <span>
                                        Date:&nbsp;&nbsp;<?= Dates::get_calend_date() ?>
                                    </span>
                                </a>
                                <a class="nav-link" href="" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    <span>Refresh</span>
                                </a>
                                <a class="nav-link" href="#" onclick="checkForUpdates(); return false;">
                                    <i class="bi bi-cloud-arrow-down"></i>
                                    <span>Update</span>
                                </a>
                                <a class="nav-link" href="logout.php">
                                    <i class="bi bi-door-closed"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="updateOverlay">
    <canvas id="matrix-rain"></canvas>
    <div class="grid-overlay"></div>
    <div class="updating-text">
        <span id="typing-text"></span><span class="cursor">█</span>
    </div>
</div>
<style>
.logo-container {
    display: flex;
    align-items: center;
    gap: 20px;
}
.logo-link {
    flex-shrink: 0;
}
.geo-version {
    font-size: 14px;
    color: #666;
    white-space: nowrap;
}
.geo-version a {
    color: #337ab7;
    text-decoration: none;
}
.geo-version a:hover {
    text-decoration: underline;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#matrix-rain {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.grid-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: linear-gradient(rgba(27, 42, 71, 0.3) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(27, 42, 71, 0.3) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
    z-index: -1;
}

.updating-text {
    font-family: monospace;
    font-size: 24px;
    color: #0F0;
    text-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
}

.cursor {
    animation: blink 1s infinite;
    opacity: 1;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.overlay.active {
    display: flex;
}
</style>
<script>
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

    let interval = setInterval(draw, 35);
    return () => clearInterval(interval);
}

function typeText(text, element, delay = 100) {
    let index = 0;
    return new Promise((resolve) => {
        function type() {
            if (index < text.length) {
                element.textContent += text.charAt(index);
                index++;
                setTimeout(type, delay);
            } else {
                setTimeout(() => {
                    element.textContent = '';
                    index = 0;
                    type();
                }, 1000);
            }
        }
        type();
    });
}

let matrixCleanup = null;

// Modified checkForUpdates function
async function checkForUpdates() {
    try {
        const response = await fetch('autoupdate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check'
        });
        
        const result = await response.json();
        
        if (!result.success) {
            alert('Error checking for updates: ' + result.message);
            return;
        }
        
        if (!result.hasUpdate) {
            alert('Your system is up to date!');
            return;
        }
        
        if (confirm(`An update to version ${result.version} is available. Would you like to update now?`)) {
            const overlay = document.getElementById('updateOverlay');
            const typingText = document.getElementById('typing-text');
            
            overlay.style.display = 'flex';
            matrixCleanup = setupMatrixRain();
            typeText('UPDATING SYSTEM...', typingText);
            
            const updateResponse = await fetch('autoupdate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update'
            });
            
            const updateResult = await updateResponse.json();
            
            if (matrixCleanup) {
                matrixCleanup();
                matrixCleanup = null;
            }
            overlay.style.display = 'none';
            
            if (updateResult.success) {
                alert('Update successful! The page will now reload.');
                location.reload();
            } else {
                alert('Update failed: ' + updateResult.message);
            }
        }
    } catch (error) {
        if (matrixCleanup) {
            matrixCleanup();
            matrixCleanup = null;
        }
        document.getElementById('updateOverlay').style.display = 'none';
        alert('Error checking for updates: ' + error.message);
    }
}

// Geo database update functionality
var updElement = document.getElementById("updateBases");
var loadingAnimation = document.getElementById("loadingAnimation");

updElement.onclick = async () => {
    // Show loading animation
    updElement.style.display = 'none';
    loadingAnimation.style.display = '';

    let res = await fetch("../bases/update.php", {
        method: "GET",
    });
    let js = await res.json();
    if (!js["error"]) {
        loadingAnimation.style.display = 'none';
        alert(js["result"]);
        window.location.reload();
    } else {
        loadingAnimation.style.display = 'none';
        updElement.style.display = '';
        alert(`An error occured: ${js["result"]}`);
    }
};

flatpickr("#litepicker", {
    dateFomat: "DD.MM.YY",
    mode: "range",
    onClose: function(selectedDates, dateStr, instance) {
        update_datepicker_dates(selectedDates);
    }
});

function update_datepicker_dates(selectedDates) {
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear()).slice(-2);
        return `${day}.${month}.${year}`;
    }
    let searchParams = new URLSearchParams(window.location.search);
    let d1 = formatDate(selectedDates[0]);
    let d2 = formatDate(selectedDates[1]);
    searchParams.set('startdate', d1);
    searchParams.set('enddate', d2);
    window.location.search = searchParams.toString();
}
</script>
