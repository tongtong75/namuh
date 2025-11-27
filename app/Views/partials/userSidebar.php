<?php
    $current_uri = "/" . uri_string();

    $goods_uris = ["/mngr/ckupArtclMng", "/mngr/chcArtclMng", "/mngr/ckupGdsMng"];
    $target_uris = ["/mngr/ckupTrgt"];
    $reservation_uris = ["#sidebarCalendar", "apps-chat.html"];

    $is_goods_active = in_array($current_uri, $goods_uris);
    $is_target_active = in_array($current_uri, $target_uris);
    $is_reservation_active = in_array($current_uri, $reservation_uris);
?>

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box" style="margin: 10px 0px;">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?= base_url('/public/assets/images/logo-sm.png') ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <span class="fs-4 fw-bold text-dark"><?= session()->get('co_nm') ?></span>
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?= base_url('/public/assets/images/logo-sm-namuh.png') ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <span class="fs-4 fw-bold text-white"><?= session()->get('co_nm') ?></span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <?php if (session()->get('user_type') == 'M') : ?>
                <li class="nav-item">
                      <a href="/user/ckupTrgt" class="nav-link" ><i class="ri-group-line"></i> <span >인사등록</span> </a>
                </li> 
                <li class="nav-item">
                      <a href="#" class="nav-link" ><i class="ri-calendar-todo-line"></i> <span >검진현황</span> </a>
                </li>
                <li class="nav-item">
                      <a href="/user/hsptl" class="nav-link" ><i class="ri-hospital-line"></i> <span >병원정보</span> </a>
                </li> 
                <?php endif; ?> 
                <?php if (session()->get('user_type') == 'U') : ?>
                <li class="nav-item">
                      <a href="#" class="nav-link" ><i class="ri-calendar-todo-line"></i> <span> 건강검진예약</span> </a>
                </li> 
                <?php endif; ?> 

                <!-- end  Menu -->
            </ul>
        </div>
       
    </div>
    <!-- Sidebar -->
</div>

<div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var currentUrl = window.location.pathname;
        var navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        navLinks.forEach(function(link) {
            if (link.getAttribute('href') === currentUrl) {
                link.classList.add('active');
                var parentCollapse = link.closest('.collapse');
                if (parentCollapse) {
                    parentCollapse.classList.add('show');
                    var parentMenuLink = document.querySelector('a[href="#' + parentCollapse.id + '"]');
                    if (parentMenuLink) {
                        parentMenuLink.classList.add('active');
                    }
                }
            }
        });
    });
</script>