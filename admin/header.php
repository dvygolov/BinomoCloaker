<?php
require_once __DIR__.'/dates.php';
?>
<div class="header-advance-area">
    <div class="header-top-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <div class="logo-pro">
                        <a href="index.php">
                            <img class="main-logo" src="<?=get_cloaker_path()?>img/logo.png" alt="" />
                        </a>
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
</script>
<?php


?>
