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
    <div class="loading-spinner"></div>
    <div class="loading-text">UPDATING SYSTEM...</div>
</div>
<script>
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

    // System update functionality
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
                const updateResponse = await fetch('autoupdate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=update'
                });
                
                const updateResult = await updateResponse.json();
                
                if (updateResult.success) {
                    alert('Update successful! The page will now reload.');
                    location.reload();
                } else {
                    alert('Update failed: ' + updateResult.message);
                }
            }
        } catch (error) {
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
</script>
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
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(3px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.overlay.active {
    display: flex;
}

.loading-spinner {
    width: 80px;
    height: 80px;
    border: 8px solid #f3f3f3;
    border-top: 8px solid #00ff00;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    position: absolute;
    margin-top: 100px;
    color: #00ff00;
    font-family: 'Courier New', monospace;
    font-size: 18px;
    text-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
}
</style>
