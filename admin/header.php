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
</style>
