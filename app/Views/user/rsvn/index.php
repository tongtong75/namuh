<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'검진예약')); ?>

    <?= $this->include('partials/head-css') ?>
    
    <!-- 예약 페이지 전용 CSS -->
    <link href="<?= base_url('public/assets/css/reservation.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/userMenu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= view('partials/page-title', ['pagetitle' => '검진예약', 'title' => '검진 예약 신청']) ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="ri-calendar-check-line"></i> 검진 예약</h5>
                                </div>
                                <div class="card-body">
                                    <!-- 기간 정보 -->
                                    <div class="period-info-box">
                                        <h6 class="mb-3"><i class="ri-information-line"></i> 기간</h6>
                                        <table class="period-info-table">
                                            <tr>
                                                <th>예약신청기간</th>
                                                <td>
                                                    <?php if ($companyInfo && $companyInfo['BGNG_YMD'] && $companyInfo['END_YMD']): ?>
                                                        <?= date('Y-m-d', strtotime($companyInfo['BGNG_YMD'])) ?> ~ <?= date('Y-m-d', strtotime($companyInfo['END_YMD'])) ?>
                                                    <?php else: ?>
                                                        기간 정보가 없습니다.
                                                    <?php endif; ?>
                                                </td>
                                                <th>검진가능기간</th>
                                                <td>
                                                    <?php if ($companyInfo && $companyInfo['BGNG_YMD'] && $companyInfo['END_YMD']): ?>
                                                        <?= date('Y-m-d', strtotime($companyInfo['BGNG_YMD'])) ?> ~ <?= date('Y-m-d', strtotime($companyInfo['END_YMD'])) ?>
                                                    <?php else: ?>
                                                        기간 정보가 없습니다.
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- 예약현황 -->
                                    <h6 class="mb-3"><i class="ri-file-list-3-line"></i> 예약현황</h6>
                                    <div class="table-responsive">
                                        <table class="reservation-table table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>이름</th>
                                                    <th>관계</th>
                                                    <th>검진병원</th>
                                                    <th>검진예약일</th>
                                                    <th>신청내역</th>
                                                    <th>본인부담금</th>
                                                    <th>예약관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $relationMap = [
                                                    'S' => '본인',
                                                    'W' => '배우자',
                                                    'C' => '자녀',
                                                    'O' => '기타',
                                                    'P' => '부모님'
                                                ];
                                                ?>
                                                <?php if (isset($familyMembers) && !empty($familyMembers)): ?>
                                                    <?php foreach ($familyMembers as $member): ?>
                                                        <tr>
                                                            <td><?= $member['NAME'] ?? 'N/A' ?></td>
                                                            <td><?= $relationMap[$member['RELATION']] ?? $member['RELATION'] ?></td>
                                                            <td><?= $member['HSPTL_NM'] ?? '-' ?></td>
                                                            <td>
                                                                <?php 
                                                                if (!empty($member['CKUP_RSVN_YMD'])) {
                                                                    echo date('Y-m-d', strtotime($member['CKUP_RSVN_YMD']));
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td>
                                                                <?php if (($member['RSVT_STTS'] ?? 'N') == 'Y'): ?>
                                                                    <button type="button" class="btn btn-danger btn-sm btnCancelReservation" 
                                                                            data-id="<?= $member['CKUP_TRGT_SN'] ?>" 
                                                                            data-name="<?= $member['NAME'] ?>">
                                                                        <i class="ri-close-circle-line"></i> 예약취소
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-primary btn-sm btnMakeReservation" 
                                                                            data-id="<?= $member['CKUP_TRGT_SN'] ?>" 
                                                                            data-name="<?= $member['NAME'] ?>">
                                                                        <i class="ri-calendar-check-line"></i> 예약하기
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7">등록된 검진 대상자가 없습니다.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 주의사항 -->
                                    <div class="notice-box">
                                        <ul>
                                            <li>검진예약은 예약신청기간 중에 예약자별로 1개 병원에 대해서만 예약신청이 가능합니다.</li>
                                            <li>회사지원금을 초과하는 검진비용은 본인이 부담하셔야 합니다. (검진당일 해당병원에 납부)</li>
                                            <li>병원담당자 정보는 병원명을 Click하시면 볼 수 있습니다.</li>
                                            <li class="red-text">병원사정에 따라 상품에 포함된 일부 장비검사가 원하시는 날짜에 불가능할 경우 개별적으로 연락을 드릴수 있습니다. ex)내시경, 초음파, CT 등</li>
                                            <li class="red-text">검사 불가 연락을 받으신경우 상품 또는 날짜 변경에 협조 부탁드리겠습니다.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->

                </div><!-- container-fluid -->
            </div>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>
    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 예약하기 버튼 (이벤트 위임)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnMakeReservation')) {
                    const btn = e.target.closest('.btnMakeReservation');
                    const ckupTrgtSn = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');

                    if (!confirm(`${name}님의 검진 예약을 하시겠습니까?`)) {
                        return;
                    }

                    fetch('<?= site_url('user/makeReservation') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'ckup_trgt_sn=' + encodeURIComponent(ckupTrgtSn)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload(); // 페이지 새로고침
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('오류가 발생했습니다. 다시 시도해주세요.');
                    });
                }
            });

            // 예약취소 버튼 (이벤트 위임)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnCancelReservation')) {
                    const btn = e.target.closest('.btnCancelReservation');
                    const ckupTrgtSn = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');

                    if (!confirm(`${name}님의 검진 예약을 취소하시겠습니까?`)) {
                        return;
                    }

                    fetch('<?= site_url('user/cancelReservation') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'ckup_trgt_sn=' + encodeURIComponent(ckupTrgtSn)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload(); // 페이지 새로고침
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('오류가 발생했습니다. 다시 시도해주세요.');
                    });
                }
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>
