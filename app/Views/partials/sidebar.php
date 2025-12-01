<?php
    $current_uri = "/" . uri_string();

    $goods_uris = ["/mngr/ckupArtclMng", "/mngr/chcArtclMng", "/mngr/ckupGdsMng", "/mngr/ckupGdsExcel"];
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
                <img src="<?= base_url('/public/assets/images/logo-dark.png') ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?= base_url('/public/assets/images/logo-sm-namuh.png') ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?= base_url('/public/assets/images/logo-light-namuh.png') ?>" alt="" width="187" height="50">
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
                <?php if (session()->get('user_type') !== 'H') : ?>
                <li class="nav-item">
                      <a href="/mngr/mngrMng" class="nav-link" ><i class="ri-shield-user-line"></i> <span >관리자관리</span> </a>
                </li> 
                <li class="nav-item">
                      <a href="/mngr/hsptlMng" class="nav-link" ><i class="ri-hospital-line"></i> <span >검진병원관리</span> </a>
                </li>
                <li class="nav-item">
                      <a href="/mngr/coMng" class="nav-link" ><i class="ri-building-line"></i> <span >검진회사관리</span> </a>
                </li> 
                <?php endif; ?> 
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $is_goods_active ? 'active' : '' ?>" href="#goods" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_goods_active ? 'true' : 'false' ?>" aria-controls="goods">
                        <i class="ri-first-aid-kit-line"></i> <span >검진상품관리</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $is_goods_active ? 'show' : '' ?>" id="goods" data-bs-parent="#navbar-nav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="/mngr/ckupArtclMng" class="nav-link <?= $current_uri == '/mngr/ckupArtclMng' ? 'active' : '' ?>">검진항목</a>
                            </li>
                            <!--li class="nav-item">
                                <a href="/mngr/chcArtclMng" class="nav-link <?= $current_uri == '/mngr/chcArtclMng' ? 'active' : '' ?>">선택항목</a>
                            </l-->
                            <li class="nav-item">
                                <a href="/mngr/ckupGdsMng" class="nav-link <?= $current_uri == '/mngr/ckupGdsMng' ? 'active' : '' ?>"> 검진상품 </a>
                            </li>
                            <li class="nav-item">
                                <a href="/mngr/ckupGdsExcel" class="nav-link <?= $current_uri == '/mngr/ckupGdsExcel' ? 'active' : '' ?>"> 검진상품엑셀 </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= $is_target_active ? 'active' : '' ?>" href="#target" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_target_active ? 'true' : 'false' ?>" aria-controls="target">
                        <i class="ri-group-line"></i> <span >검진대상관리</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $is_target_active ? 'show' : '' ?>" id="target" data-bs-parent="#navbar-nav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="/mngr/ckupTrgt" class="nav-link <?= $current_uri == '/mngr/ckupTrgt' ? 'active' : '' ?>" >검진대상</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" >예약확정대기자</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" >수검관리</a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $is_reservation_active ? 'active' : '' ?>" href="#reservation" data-bs-toggle="collapse" role="button" aria-expanded="<?= $is_reservation_active ? 'true' : 'false' ?>" aria-controls="reservation">
                        <i class="ri-calendar-todo-line"></i> <span >검진예약관리</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $is_reservation_active ? 'show' : '' ?>" id="reservation" data-bs-parent="#navbar-nav">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="/mngr/dayCkupMng" class="nav-link <?= $current_uri == '/mngr/dayCkupMng' ? 'active' : '' ?>" >요일별 설정</a>
                            </li>
                            <li class="nav-item">
                                <a href="/mngr/dayCkupMng/calendar" class="nav-link <?= $current_uri == '/mngr/dayCkupMng/calendar' ? 'active' : '' ?>" >일자별 현황</a>
                            </li>
                        </ul>
                    </div>
                </li> 
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