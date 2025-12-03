<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'검진예약')); ?>
    <?= $this->include('partials/head-css') ?>
    <!-- FullCalendar CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <!-- 예약 페이지 전용 CSS (필요시 추가) -->
    <style>
        .rsvn-info-table th { background-color: #f8f9fa; width: 15%; }
        .rsvn-info-table td { width: 35%; }
        .nav-tabs-custom .nav-item .nav-link.active { color: #0ab39c; background-color: #f3f6f9; }
        .nav-tabs-custom .nav-item .nav-link { color: #495057; }
        .search-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #eff2f7; }
        
        /* FullCalendar 커스텀 스타일 */
        .fc-daygrid-day-number {
            padding: 4px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            white-space: nowrap !important;
        }
        
        .fc-daygrid-day-top {
            text-align: right !important;
            padding: 2px !important;
        }
        
        .fc-event {
            font-size: 12px !important;
            padding: 3px 2px !important;
            margin-bottom: 1px !important;
            white-space: normal !important;
            line-height: 1.2 !important;
        }
        
        .fc-event-title {
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: normal !important;
        }
        
        .fc-daygrid-event {
            white-space: normal !important;
        }
        
        .fc-daygrid-day-frame {
            min-height: 100px;
        }
    </style>
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
                                    <h5 class="card-title mb-0"><i class="ri-file-user-line"></i> 예약정보</h5>
                                </div>
                                <div class="card-body">
                                    <!-- 예약정보 테이블 -->
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered rsvn-info-table">
                                            <tbody>
                                                <tr>
                                                    <th>검진자명</th>
                                                    <td><?= $targetInfo['NAME'] ?></td>
                                                    <th>생년월일(성별)</th>
                                                    <td>
                                                        <?= $targetInfo['BIRTHDAY'] ?>
                                                        (<?= $targetInfo['SEX'] == 'M' ? '남자' : ($targetInfo['SEX'] == 'F' ? '여자' : '') ?>)
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>검진기관</th>
                                                    <td><!-- 선택된 병원 표시 --></td>
                                                    <th>검진희망일</th>
                                                    <td><!-- 선택된 날짜 표시 --></td>
                                                </tr>
                                                <tr>
                                                    <th>검진상품</th>
                                                    <td><!-- 선택된 상품 표시 --></td>
                                                    <th>추가검사</th>
                                                    <td><!-- 선택된 추가검사 표시 --></td>
                                                </tr>
                                                <tr>
                                                    <th>본인부담금</th>
                                                    <td>0원</td>
                                                    <th>관계</th>
                                                    <td>
                                                        <?php
                                                            $relationMap = ['S'=>'본인', 'W'=>'배우자', 'C'=>'자녀', 'O'=>'기타', 'P'=>'부모님'];
                                                            echo $relationMap[$targetInfo['RELATION']] ?? $targetInfo['RELATION'];
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>본인지원금</th>
                                                    <td><?= $targetInfo['SUPPORT_FUND'] ?? '-' ?></td>
                                                    <th>가족지원금</th>
                                                    <td><?= $targetInfo['FAMILY_SUPPORT_FUND'] ?? '-' ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 탭 메뉴 -->
                                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#step1" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-hospital"></i></span>
                                                <span class="d-none d-sm-block">검진병원 선택</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#step2" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-calendar-alt"></i></span>
                                                <span class="d-none d-sm-block">검진일/검진상품 선택</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#step3" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-plus-square"></i></span>
                                                <span class="d-none d-sm-block">추가검사 선택</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#step4" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-user-check"></i></span>
                                                <span class="d-none d-sm-block">예약자 정보 확인</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- 탭 컨텐츠 -->
                                    <div class="tab-content p-3 text-muted">
                                        <!-- 검진병원 선택 탭 -->
                                        <div class="tab-pane active" id="step1" role="tabpanel">
                                            <h6 class="mb-3"><i class="ri-list-check"></i> 검진가능병원</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover text-center align-middle" id="hospitalTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>No</th>
                                                            <th>지역</th>
                                                            <th>검진병원</th>
                                                            <th>예약 문의</th>
                                                            <th>병원선택</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($linkedHospitals)): ?>
                                                            <?php foreach ($linkedHospitals as $index => $hospital): ?>
                                                                <tr class="hospital-row" data-region="<?= $hospital['RGN'] ?>" data-name="<?= $hospital['HSPTL_NM'] ?>">
                                                                    <td><?= $index + 1 ?></td>
                                                                    <td><?= $hospital['RGN'] ?></td>
                                                                    <td class="text-start ps-4"><?= $hospital['HSPTL_NM'] ?></td>
                                                                    <td><?= $hospital['TEL'] ?></td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-outline-primary btn-sm btnSelectHospital" 
                                                                                data-id="<?= $hospital['HSPTL_SN'] ?>" 
                                                                                data-name="<?= $hospital['HSPTL_NM'] ?>">
                                                                            선택
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="5">검진 가능한 병원이 없습니다.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 검진일/검진상품 선택 탭 -->
                                        <div class="tab-pane" id="step2" role="tabpanel">
                                            <div class="row">
                                                <!-- 왼쪽: 달력 -->
                                                <div class="col-md-7">
                                                    <h6 class="mb-3"><i class="ri-calendar-line"></i> 검진일 선택</h6>
                                                    <div id="calendar"></div>
                                                </div>
                                                <!-- 오른쪽: 검진상품 -->
                                                <div class="col-md-5">
                                                    <h6 class="mb-3"><i class="ri-shopping-bag-line"></i> 검진상품</h6>
                                                    <div id="product-list" class="table-responsive">
                                                        <p class="text-muted">병원을 먼저 선택해주세요.</p>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button type="button" class="btn btn-primary" onclick="alert('준비중입니다.')">
                                                            <i class="ri-check-line"></i> 선택완료
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="step3" role="tabpanel">
                                            <p class="mb-0">추가검사 선택 화면 (준비중)</p>
                                        </div>
                                        <div class="tab-pane" id="step4" role="tabpanel">
                                            <p class="mb-0">예약자 정보 확인 화면 (준비중)</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- 검사항목 모달 -->
    <div class="modal fade" id="checkupItemsModal" tabindex="-1" aria-labelledby="checkupItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkupItemsModalLabel"><i class="ri-file-list-3-line"></i> 검사항목 상세</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="checkupItemsContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 상품선택 모달 -->
    <div class="modal fade" id="productChoiceModal" tabindex="-1" aria-labelledby="productChoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productChoiceModalLabel"><i class="ri-shopping-cart-2-line"></i> 상품 선택</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="ri-information-line me-1"></i> 선택한 검진상품 내에 포함된 기본선택항목입니다.
                    </div>
                    <div id="productChoiceContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmSelection">선택완료</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ckupTrgtSn = new URLSearchParams(window.location.search).get('ckup_trgt_sn');
            let selectedHospitalSn = null;
            let calendar = null;

            // 병원 선택 버튼
            document.querySelectorAll('.btnSelectHospital').forEach(btn => {
                btn.addEventListener('click', function() {
                    const hospitalSn = this.getAttribute('data-id');
                    const hospitalName = this.getAttribute('data-name');
                    
                    selectedHospitalSn = hospitalSn;
                    
                    // 병원 선택 표시
                    document.querySelectorAll('.btnSelectHospital').forEach(b => {
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-outline-primary');
                    });
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');
                    
                    // 달력 초기화 및 로드
                    loadCalendar(hospitalSn);
                    
                    // 상품 목록 로드
                    loadProducts(hospitalSn, ckupTrgtSn);
                    
                    // 다음 탭으로 이동
                    const step2Tab = new bootstrap.Tab(document.querySelector('[href="#step2"]'));
                    step2Tab.show();
                });
            });

            function loadCalendar(hsptlSn) {
                const year = <?= $targetInfo['CKUP_YYYY'] ?>;
                const calendarEl = document.getElementById('calendar');
                
                if (calendar) {
                    calendar.destroy();
                }
                
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'ko',
                    height: 'auto',
                    eventOrder: 'order,title',
                    headerToolbar: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },
                    titleFormat: function(info) {
                        return `${info.date.year}년 ${info.date.month + 1}월`;
                    },
                    initialDate: year + '-01-01',
                    validRange: {
                        start: year + '-01-01',
                        end: year + '-12-31'
                    },
                    events: {
                        url: '<?= site_url('user/rsvnSel/getCalendarEvents') ?>',
                        extraParams: {
                            hsptl_sn: hsptlSn,
                            year: year
                        },
                        failure: function() {
                            alert('달력 데이터 로드에 실패했습니다.');
                        }
                    },
                    dateClick: function(info) {
                        alert('준비중입니다.');
                    }
                });
                calendar.render();
                
                // 탭이 표시될 때 달력 크기 업데이트
                const step2TabEl = document.querySelector('[href="#step2"]');
                if (step2TabEl) {
                    step2TabEl.addEventListener('shown.bs.tab', function() {
                        if (calendar) {
                            calendar.updateSize();
                        }
                    });
                }
            }

            function loadProducts(hsptlSn, ckupTrgtSn) {
                fetch(`<?= site_url('user/rsvnSel/getProducts') ?>?hsptl_sn=${hsptlSn}&ckup_trgt_sn=${ckupTrgtSn}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // 디버그 정보 출력
                        if (data.debug) {
                            console.log('=== 검진상품 조회 디버그 정보 ===');
                            console.log('병원 일련번호:', data.debug.hsptl_sn);
                            console.log('검진년도:', data.debug.ckup_yyyy);
                            console.log('관계:', data.debug.relation);
                            console.log('본인지원금:', data.debug.support_fund);
                            console.log('가족지원금:', data.debug.family_support_fund);
                            console.log('선택된 지원구분:', data.debug.selected_sprt_se);
                            console.log('필터 컬럼:', data.debug.filter_column);
                            console.log('SQL 쿼리:', data.debug.sql_query);
                            console.log('조회된 상품 개수:', data.debug.products_count);
                            console.log('================================');
                        }
                        
                        const productList = document.getElementById('product-list');
                        
                        if (data.success && data.products.length > 0) {
                            let html = '<table class="table table-bordered table-hover">';
                            html += '<thead class="table-light"><tr><th>No</th><th>검진상품명</th><th>지원구분</th><th>검사항목보기</th><th>선택</th></tr></thead>';
                            html += '<tbody>';
                            
                            
                            data.products.forEach((product, index) => {
                                console.log('Product:', product); // 디버깅
                                html += `<tr>`;
                                html += `<td>${index + 1}</td>`;
                                html += `<td>${product.CKUP_GDS_NM}</td>`;
                                html += `<td>${product.SPRT_SE || '-'}</td>`;
                                html += `<td><button type="button" class="btn btn-outline-primary btn-sm btnViewItems" data-ckup-gds-sn="${product.CKUP_GDS_EXCEL_MNG_SN}"><i class="ri-eye-line"></i> 검사항목보기</button></td>`;
                                html += `<td><button type="button" class="btn btn-outline-primary btn-sm btnSelectProduct" data-ckup-gds-sn="${product.CKUP_GDS_EXCEL_MNG_SN}"><i class="ri-check-line"></i>상품선택</button></td>`;
                                html += `</tr>`;
                            });
                            
                            html += '</tbody></table>';
                            productList.innerHTML = html;
                        } else {
                            productList.innerHTML = '<p class="text-muted">해당 병원의 검진상품이 없습니다.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('product-list').innerHTML = '<p class="text-danger">상품 목록 로드에 실패했습니다.</p>';
                    });
            }

            // 검사항목보기 버튼 클릭 이벤트 (이벤트 위임)
            document.getElementById('product-list').addEventListener('click', function(e) {
                const btn = e.target.closest('.btnViewItems');
                if (btn) {
                    const ckupGdsSn = btn.getAttribute('data-ckup-gds-sn');
                    if (ckupGdsSn) {
                        showCheckupItems(ckupGdsSn);
                    }
                }
            });

            function showCheckupItems(ckupGdsSn) {
                const modal = new bootstrap.Modal(document.getElementById('checkupItemsModal'));
                const contentDiv = document.getElementById('checkupItemsContent');
                
                // 로딩 표시
                contentDiv.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                modal.show();
                
                // AJAX로 검사항목 데이터 가져오기
                fetch(`<?= site_url('user/rsvnSel/getCheckupItems') ?>?ckup_gds_sn=${ckupGdsSn}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items && data.items.length > 0) {
                        let html = '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-hover align-middle text-center">';
                        html += '<thead class="table-light"><tr><th>No</th><th>검진구분</th><th>검진항목</th><th>질환</th></tr></thead>';
                        html += '<tbody>';
                        
                        // 검진구분별로 그룹화하여 rowspan 계산
                        const ckupSeGroups = {};
                        data.items.forEach(item => {
                            const ckupSe = item.CKUP_SE || '-';
                            if (!ckupSeGroups[ckupSe]) {
                                ckupSeGroups[ckupSe] = 0;
                            }
                            ckupSeGroups[ckupSe]++;
                        });
                        
                        let currentCkupSe = null;
                        data.items.forEach((item, index) => {
                            const ckupSe = item.CKUP_SE || '-';
                            const isFirstInGroup = currentCkupSe !== ckupSe;
                            
                            html += `<tr>`;
                            html += `<td>${index + 1}</td>`;
                            
                            // 검진구분: 같은 값이면 rowspan 적용
                            if (isFirstInGroup) {
                                currentCkupSe = ckupSe;
                                const rowspan = ckupSeGroups[ckupSe];
                                html += `<td rowspan="${rowspan}" class="align-middle text-center">${ckupSe}</td>`;
                            }
                            
                            html += `<td class="text-start ps-3">${item.CKUP_ARTCL || '-'}</td>`;
                            html += `<td>${item.DSS || '-'}</td>`;
                            html += `</tr>`;
                        });
                        
                        html += '</tbody></table></div>';
                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML = '<p class="text-muted text-center">검사항목이 없습니다.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = '<p class="text-danger text-center">검사항목 로드에 실패했습니다.</p>';
                });
            }

            // 상품선택 버튼 클릭 이벤트 (이벤트 위임)
            document.getElementById('product-list').addEventListener('click', function(e) {
                const btn = e.target.closest('.btnSelectProduct');
                if (btn) {
                    const ckupGdsSn = btn.getAttribute('data-ckup-gds-sn');
                    if (ckupGdsSn) {
                        showProductChoice(ckupGdsSn);
                    }
                }
            });

            function showProductChoice(ckupGdsSn) {
                const modal = new bootstrap.Modal(document.getElementById('productChoiceModal'));
                const contentDiv = document.getElementById('productChoiceContent');
                
                // 로딩 표시
                contentDiv.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                modal.show();
                
                // AJAX로 상품선택 항목 데이터 가져오기
                fetch(`<?= site_url('user/rsvnSel/getProductChoiceItems') ?>?ckup_gds_sn=${ckupGdsSn}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.groups && data.groups.length > 0) {
                        renderProductChoiceTable(data.groups, contentDiv);
                    } else {
                        contentDiv.innerHTML = '<p class="text-muted text-center">선택 가능한 항목이 없습니다.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = '<p class="text-danger text-center">데이터 로드에 실패했습니다.</p>';
                });
            }

            function renderProductChoiceTable(groups, container) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover align-middle text-center">';
                html += '<thead class="table-light"><tr><th>번호</th><th>검사항목</th><th>남녀구분</th><th>선택</th><th>선택갯수</th><th>비고</th></tr></thead>';
                html += '<tbody>';
                
                groups.forEach((group, groupIndex) => {
                    const items = group.items;
                    const rowspan = items.length;
                    
                    // 선택갯수 표시 텍스트 생성
                    let countText = group.CHC_ARTCL_CNT;
                    if (group.CHC_ARTCL_CNT2) {
                        countText += ` 또는 ${group.CHC_ARTCL_CNT2}`;
                    }
                    
                    items.forEach((item, itemIndex) => {
                        html += `<tr>`;
                        
                        // 첫 번째 항목일 때 그룹 정보 표시 (Rowspan)
                        if (itemIndex === 0) {
                            html += `<td rowspan="${rowspan}" class="fw-bold">${group.GROUP_NM}</td>`;
                        }
                        
                        html += `<td class="text-start ps-3">${item.CKUP_ARTCL}</td>`;
                        
                        // 남녀구분 변환
                        let genderText = '공통';
                        if (item.GNDR_SE === 'M') genderText = '남성';
                        else if (item.GNDR_SE === 'F') genderText = '여성';
                        html += `<td>${genderText}</td>`;
                        
                        // 선택 체크박스
                        html += `<td>
                                    <input type="checkbox" class="form-check-input choice-item-checkbox" 
                                           name="choice_item_${group.CKUP_GDS_EXCEL_CHC_GROUP_SN}[]" 
                                           value="${item.CKUP_GDS_EXCEL_CHC_ARTCL_SN}"
                                           data-group-id="${group.CKUP_GDS_EXCEL_CHC_GROUP_SN}"
                                           data-max-count="${group.CHC_ARTCL_CNT}"
                                           data-max-count2="${group.CHC_ARTCL_CNT2 || ''}">
                                 </td>`;
                        
                        // 첫 번째 항목일 때 선택갯수 및 비고 표시 (Rowspan)
                        if (itemIndex === 0) {
                            html += `<td rowspan="${rowspan}">${countText}</td>`;
                            html += `<td rowspan="${rowspan}"></td>`; // 비고란 공란
                        }
                        
                        html += `</tr>`;
                    });
                });
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
                
                // 체크박스 이벤트 리스너 추가 (선택 제한 로직)
                addCheckboxListeners(container);
            }

            function addCheckboxListeners(container) {
                const checkboxes = container.querySelectorAll('.choice-item-checkbox');
                
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const groupId = this.getAttribute('data-group-id');
                        const maxCount = parseInt(this.getAttribute('data-max-count'));
                        const maxCount2 = this.getAttribute('data-max-count2') ? parseInt(this.getAttribute('data-max-count2')) : null;
                        
                        // 해당 그룹의 체크된 항목 수 계산
                        const groupCheckboxes = container.querySelectorAll(`.choice-item-checkbox[data-group-id="${groupId}"]:checked`);
                        const checkedCount = groupCheckboxes.length;
                        
                        // 최대 선택 가능 수 결정 로직
                        // CNT2가 있으면 더 큰 값을 기준으로 일단 허용하되, 최종 확인 시 검증하거나
                        // 여기서는 단순하게 CNT 또는 CNT2 중 큰 값까지만 선택 가능하게 하고, 
                        // 정확한 갯수 매칭은 "선택완료" 시점에 검증하는 것이 사용자 경험상 좋을 수 있음.
                        // 하지만 요구사항은 "선택갯수 만큼만 선택가능"이므로, 
                        // CNT2가 있는 경우 로직이 복잡해질 수 있음 (예: 1개 또는 3개 선택 가능).
                        // 일단 더 큰 값(maxLimit)을 기준으로 체크를 막고, 완료 시점에 정확한 갯수인지 확인하도록 구현.
                        
                        let maxLimit = maxCount;
                        if (maxCount2 && maxCount2 > maxLimit) {
                            maxLimit = maxCount2;
                        }
                        
                        if (checkedCount > maxLimit) {
                            alert(`이 그룹은 최대 ${maxLimit}개까지만 선택할 수 있습니다.`);
                            this.checked = false;
                        }
                    });
                });
            }

            // 선택완료 버튼 클릭 이벤트
            document.getElementById('btnConfirmSelection').addEventListener('click', function() {
                // 선택된 항목 검증 및 처리 로직 (추후 구현)
                alert('선택이 완료되었습니다. (기능 준비중)');
                const modal = bootstrap.Modal.getInstance(document.getElementById('productChoiceModal'));
                modal.hide();
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>
