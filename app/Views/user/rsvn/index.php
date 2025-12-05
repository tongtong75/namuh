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
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="mb-0"><i class="ri-file-list-3-line"></i> 예약현황</h6>
                                        <?php
                                            $selfInfo = null;
                                            if (isset($familyMembers) && !empty($familyMembers)) {
                                                foreach ($familyMembers as $member) {
                                                    if ($member['RELATION'] === 'S') {
                                                        $selfInfo = $member;
                                                        break;
                                                    }
                                                }
                                            }
                                        ?>
                                        <?php if ($selfInfo): ?>
                                            <button type="button" class="btn btn-sm btn-success add-family-btn"
                                                data-co-sn="<?= $selfInfo['CO_SN'] ?>"
                                                data-ckup-yyyy="<?= $selfInfo['CKUP_YYYY'] ?>"
                                                data-business-num="<?= $selfInfo['BUSINESS_NUM'] ?>"
                                                data-name="<?= $selfInfo['NAME'] ?>">
                                                <i class="ri-add-line align-bottom me-1"></i> 가족추가
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
                                                            <td><?= $member['CKUP_NAME'] ?? '' ?></td>
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
                                                            <td>
                                                                <?php if (($member['CKUP_GDS_NM'] ?? '-') !== '-'): ?>
                                                                    <button type="button" class="btn btn-outline-primary btn-sm btnViewReservationDetails" 
                                                                       data-id="<?= $member['CKUP_TRGT_SN'] ?>" 
                                                                       data-product-name="<?= $member['CKUP_GDS_NM'] ?>">
                                                                        보기
                                                                    </button>
                                                                <?php else: ?>
                                                                    <?= $member['CKUP_GDS_NM'] ?? '-' ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= number_format($member['TOTAL_COST']) ?>원</td>
                                                            <td>
                                                                <?php 
                                                                $rsvtStts = $member['RSVT_STTS'] ?? 'N';
                                                                if ($rsvtStts == 'C'): ?>
                                                                    <span class="text-danger fw-bold fs-6">예약확정</span>
                                                                <?php elseif ($rsvtStts == 'Y'): ?>
                                                                    <span class="text-success fw-bold fs-6">예약완료</span>
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

    <!-- Family Registration Modal -->
    <div class="modal fade" id="familyModal" tabindex="-1" aria-labelledby="familyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="familyModalLabel">가족 등록</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="family-item-form" class="needs-validation">
                    <div class="modal-body">
                        <input type="hidden" id="CKUP_TRGT_SN_modal_family" name="CKUP_TRGT_SN">
                        <input type="hidden" id="CO_SN_modal_family" name="CO_SN">
                        <input type="hidden" id="CKUP_YYYY_modal_family" name="CKUP_YYYY">
                        <input type="hidden" id="BUSINESS_NUM_modal_family" name="BUSINESS_NUM">
                        <input type="hidden" id="NAME_modal_family" name="NAME">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="CKUP_NAME_modal_family" class="form-label">수검자명 <span class="text-danger">*</span></label>
                                <input type="text" id="CKUP_NAME_modal_family" name="CKUP_NAME" class="form-control" placeholder="수검자명" required>
                                <div class="invalid-feedback">수검자명을 입력해주세요.</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="BIRTHDAY_modal_family" class="form-label">생년월일 <span class="text-danger">*</span></label>
                                <input type="text" id="BIRTHDAY_modal_family" name="BIRTHDAY" class="form-control" placeholder="YYMMDD" required>
                                <div class="invalid-feedback">생년월일을 입력해주세요.</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">관계 <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 mt-2">
                                    <!--div class="form-check">
                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_W_family" value="W" required>
                                        <label class="form-check-label" for="RELATION_W_family">배우자</label>
                                    </div-->
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_C_family" value="C">
                                        <label class="form-check-label" for="RELATION_C_family">자녀</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_P_family" value="P">
                                        <label class="form-check-label" for="RELATION_P_family">부모</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_O_family" value="O">
                                        <label class="form-check-label" for="RELATION_O_family">기타</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">성별 <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="SEX" id="SEX_M_family" value="M" required>
                                        <label class="form-check-label" for="SEX_M_family">남</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="SEX" id="SEX_F_family" value="F">
                                        <label class="form-check-label" for="SEX_F_family">여</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="HANDPHONE_modal_family" class="form-label">핸드폰번호 <span class="text-danger">*</span></label>
                                <input type="text" id="HANDPHONE_modal_family" name="HANDPHONE" class="form-control" placeholder="010-1234-5678" required>
                                <div class="invalid-feedback">핸드폰번호를 입력해주세요.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                        <button type="submit" class="btn btn-primary" id="family-add-btn">등록</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reservation Details Modal -->
    <div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">예약 상세 내역</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="fs-15 mb-3 fw-bold">신청 상품</h6>
                        <div class="p-3 bg-light rounded border">
                            <span id="modalProductName" class="fs-16 fw-bold text-primary"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fs-15 mb-3 fw-bold">선택 항목</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>검사구분</th>
                                        <th>검사항목</th>
                                    </tr>
                                </thead>
                                <tbody id="modalChoiceItems">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h6 class="fs-15 mb-3 fw-bold">추가 검사 항목</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>검사항목</th>
                                        <th>비용</th>
                                    </tr>
                                </thead>
                                <tbody id="modalAddItems">
                                    <!-- Items will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?= $this->include('partials/vendor-scripts') ?>
    
    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>';

        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
        }

        function showAjaxMessageGlobal(message, type = 'success') {
            alert(message); // Simple alert for now, or implement a custom toast
        }

        function handleAjaxError(xhr) {
            console.error(xhr);
            alert('오류가 발생했습니다.');
        }

        function clearFormAndValidation(formId) {
            const formElement = $('#' + formId);
            formElement.trigger('reset');
            formElement.find('.form-control, .form-select').removeClass('is-invalid');
            formElement.find('.invalid-feedback').hide().text('');
            formElement.find('input[type="radio"]').prop('checked', false);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 예약 상세 내역 보기 (상품명 클릭)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnViewReservationDetails')) {
                    e.preventDefault();
                    const btn = e.target.closest('.btnViewReservationDetails');
                    const ckupTrgtSn = btn.getAttribute('data-id');
                    const productName = btn.getAttribute('data-product-name');

                    // Set product name
                    document.getElementById('modalProductName').textContent = productName;
                    
                    // Clear previous data
                    document.getElementById('modalChoiceItems').innerHTML = '<tr><td colspan="3" class="text-center py-3">로딩중...</td></tr>';
                    document.getElementById('modalAddItems').innerHTML = '<tr><td colspan="2" class="text-center py-3">로딩중...</td></tr>';

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('reservationDetailsModal'));
                    modal.show();

                    // Fetch details
                    fetch(`<?= site_url('user/rsvn/getReservationDetails') ?>?ckup_trgt_sn=${ckupTrgtSn}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Render Choice Items
                            const choiceTbody = document.getElementById('modalChoiceItems');
                            if (data.choiceItems && data.choiceItems.length > 0) {
                                let html = '';
                                data.choiceItems.forEach(item => {
                                    html += `<tr>
                                        <td class="text-center">${item.CKUP_SE || '-'}</td>
                                        <td class="text-center">${item.CKUP_ARTCL}</td>
                                    </tr>`;
                                });
                                choiceTbody.innerHTML = html;
                            } else {
                                choiceTbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">선택된 항목이 없습니다.</td></tr>';
                            }

                            // Render Additional Items
                            const addTbody = document.getElementById('modalAddItems');
                            if (data.addItems && data.addItems.length > 0) {
                                let html = '';
                                data.addItems.forEach(item => {
                                    const cost = parseInt(item.CKUP_CST).toLocaleString();
                                    html += `<tr>
                                        <td class="text-center">${item.CKUP_ARTCL}</td>
                                        <td class="text-center">${cost}원</td>
                                    </tr>`;
                                });
                                addTbody.innerHTML = html;
                            } else {
                                addTbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">선택된 추가 항목이 없습니다.</td></tr>';
                            }
                        } else {
                            alert(data.message || '데이터를 불러오는데 실패했습니다.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('modalChoiceItems').innerHTML = '<tr><td colspan="3" class="text-center text-danger">오류가 발생했습니다.</td></tr>';
                        document.getElementById('modalAddItems').innerHTML = '<tr><td colspan="2" class="text-center text-danger">오류가 발생했습니다.</td></tr>';
                    });
                }
            });

            // 예약하기 버튼 (이벤트 위임)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnMakeReservation')) {
                    const btn = e.target.closest('.btnMakeReservation');
                    const ckupTrgtSn = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');

                    // 예약 페이지로 이동
                    window.location.href = '<?= site_url('user/rsvnSel') ?>?ckup_trgt_sn=' + ckupTrgtSn;
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

            // 가족 추가 버튼 클릭
            $(document).on('click', '.add-family-btn', function() {
                clearFormAndValidation('family-item-form');
                $('#family-add-btn').text('등록');
                $('#CKUP_TRGT_SN_modal_family').val(''); 
                
                // 부모 데이터 가져오기
                const coSn = $(this).data('co-sn');
                const ckupYyyy = $(this).data('ckup-yyyy');
                const businessNum = $(this).data('business-num');
                const name = $(this).data('name');

                // 필드 값 설정
                $('#CO_SN_modal_family').val(coSn);
                $('#CKUP_YYYY_modal_family').val(ckupYyyy);
                $('#BUSINESS_NUM_modal_family').val(businessNum);
                $('#NAME_modal_family').val(name);

                // 모달 표시
                $('#familyModal').modal('show');
            });

            // 가족 등록 폼 제출
            $('#family-item-form').on('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);

                $.ajax({
                    url: BASE_URL + 'user/ckupTrgt/ajax_create',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#family-add-btn').prop('disabled', true).text('처리중...');
                    },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                        if (response.status === 'success') {
                            alert(response.message);
                            $('#familyModal').modal('hide');
                            location.reload(); // Reload to show new family member
                        } else {
                            if (response.errors) {
                                let errorMsg = '';
                                $.each(response.errors, function(key, value) {
                                    errorMsg += value + '\n';
                                    $('#family-item-form #' + key + '_modal_family').addClass('is-invalid');
                                    $('#family-item-form #' + key + '_modal_family').next('.invalid-feedback').text(value).show();
                                });
                                alert(errorMsg);
                            } else {
                                alert(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        alert('서버 오류 발생.');
                        handleAjaxError(xhr);
                    },
                    complete: function() {
                        $('#family-add-btn').prop('disabled', false).text('등록');
                    }
                });
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>
