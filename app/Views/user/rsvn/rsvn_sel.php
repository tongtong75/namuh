<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'검진예약')); ?>
    <?= $this->include('partials/head-css') ?>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        /* Calendar event cursor */
        .fc-event {
            cursor: pointer !important;
        }
        
        .fc-daygrid-day {
            cursor: pointer !important;
        }
        
        /* Sticky cost display */
        .sticky-cost-display {
            position: sticky;
            top: 70px; /* Adjust based on your header height */
            z-index: 100;
            background-color: #f0fff0; /* Light green background */
            transition: box-shadow 0.3s ease;
        }
        
        .sticky-cost-display.stuck {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
                                                    <td><?= $targetInfo['CKUP_NAME'] ?></td>
                                                    <th>생년월일(성별)</th>
                                                    <td>
                                                        <?= $targetInfo['BIRTHDAY'] ?>
                                                        (<?= $targetInfo['SEX'] == 'M' ? '남자' : ($targetInfo['SEX'] == 'F' ? '여자' : '') ?>)
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>검진병원</th>
                                                    <td id="selectedHospitalDisplay"><!-- 선택된 병원 표시 --></td>
                                                    <th>검진희망일</th>
                                                    <td id="selectedDateDisplay"><!-- 선택된 날짜 표시 --></td>
                                                </tr>
                                                <tr>
                                                    <th>검진상품</th>
                                                    <td id="selectedProductDisplay"><!-- 선택된 상품 표시 --></td>
                                                    <th>추가검사</th>
                                                    <td><!-- 선택된 추가검사 표시 --></td>
                                                </tr>
                                                <tr>
                                                    <th>본인부담금</th>
                                                    <td id="selectedAdditionalCostDisplay"></td>
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
                                                    
                                                    <!-- 상품선택 섹션 (인라인) -->
                                                    <div id="productChoiceSection" class="mt-4" style="display: none;">
                                                        <div class="card border">
                                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                                <h6 class="card-title mb-0"><i class="ri-shopping-cart-2-line"></i> 상품 선택</h6>
                                                            </div>
                                                            <div class="card-body">
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
                                                            <div class="card-footer text-end">
                                                                 <button type="button" class="btn btn-light border me-1" id="btnPrevToHospital">이전</button>
                                                                 <button type="button" class="btn btn-primary" id="btnConfirmSelection">다음 <i class="ri-arrow-right-line"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="step3" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="alert alert-info" role="alert">
                                                        선택한 검진상품 외에 추가로 받고 싶은 항목이 있을 경우 선택해 주세요. 단, 회사지원금이 초과될 경우 본인이 부담하셔야 합니다.
                                                        <br/><br/>
                                                        * 특수검사(대장내시경, 수면위내시경 등)는 병원사정에 따라 검사일자가 별도로 지정될 수 있습니다.
                                                    </div>
                                                    <h5 class="card-title mb-3"><i class="ri-shopping-bag-line"></i>추가검사 항목</h5>
                                                    
                                                    <!-- 본인부담 발생금액 표시 (sticky) -->
                                                    <div class="sticky-cost-display alert alert-success border-1 material-shadow"  role="alert">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="mb-0">본인부담 발생금액(누적)</h6>
                                                            </div>
                                                            <div class="col-md-6 text-end">
                                                                <h5 class="mb-0 text-primary">
                                                                    <strong><span id="totalAdditionalCost">0</span>원</strong>
                                                                </h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="additionalCheckupList">
                                                        <!-- 추가검사 리스트가 여기에 로드됩니다 -->
                                                    </div>
                                                    
                                                    <div class="mt-4 text-end">
                                                        <button type="button" class="btn btn-light border me-1" id="btnPrevToProduct">이전</button>
                                                        <button type="button" class="btn btn-primary" id="btnConfirmAdditional">
                                                            다음 <i class="ri-arrow-right-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="step4" role="tabpanel">
                                            <h6 class="mb-3"><i class="ri-file-user-line"></i> 예약자 정보 확인</h6>
                                            <div class="card border">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered mb-0 align-middle">
                                                            <colgroup>
                                                                <col style="width: 15%; background-color: #f8f9fa;">
                                                                <col style="width: 35%;">
                                                                <col style="width: 15%; background-color: #f8f9fa;">
                                                                <col style="width: 35%;">
                                                            </colgroup>
                                                            <tbody>
                                                                <tr>
                                                                    <th class="text-center">검진자</th>
                                                                    <td class="ps-3"><?= $targetInfo['CKUP_NAME'] ?></td>
                                                                    <th class="text-center">생년월일(성별)</th>
                                                                    <td class="ps-3"><?= $targetInfo['BIRTHDAY'] ?>(<?= $targetInfo['SEX'] == 'M' ? '남자' : ($targetInfo['SEX'] == 'F' ? '여자' : '') ?>)</td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-center">일반전화</th>
                                                                    <td class="p-2">
                                                                        <input type="text" class="form-control" id="tel" placeholder="예: 02-3468-0314">
                                                                    </td>
                                                                    <th class="text-center">핸드폰<span class="text-danger">*</span></th>
                                                                    <td class="p-2">
                                                                        <input type="text" class="form-control" id="handphone" placeholder="예: 010-3468-0314">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="text-center align-middle">주소<span class="text-danger">*</span></th>
                                                                    <td colspan="3" class="p-2">
                                                                        <div class="d-flex gap-2 mb-2" style="max-width: 400px;">
                                                                            <input type="text" class="form-control" id="zipCode" placeholder="우편번호" readonly>
                                                                            <button type="button" class="btn btn-primary text-nowrap" id="btnFindZip">우편번호 찾기</button>
                                                                        </div>
                                                                        <div class="d-flex gap-2">
                                                                            <input type="text" class="form-control" id="address1" placeholder="기본주소" readonly>
                                                                            <input type="text" class="form-control" id="address2" placeholder="상세주소">
                                                                        </div>
                                                                        <div class="mt-2 text-warning small">
                                                                            * 검진 준비물품 배송 등에 이용되므로 정확히 입력하여 주시기 바랍니다.
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 text-end">
                                                <button type="button" class="btn btn-primary" id="btnCompleteReservation">완료</button>
                                                <button type="button" class="btn btn-light border" id="btnCancelReservation">취소</button>
                                            </div>
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



    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ckupTrgtSn = new URLSearchParams(window.location.search).get('ckup_trgt_sn');
            const userGender = '<?= $targetInfo['SEX'] ?>'; // Get user's gender from PHP
            let selectedHospitalSn = null;
            let selectedDate = null;
            let calendar = null;

            // 병원 선택 버튼
            document.querySelectorAll('.btnSelectHospital').forEach(btn => {
                btn.addEventListener('click', function() {
                    const hospitalSn = this.getAttribute('data-id');
                    const hospitalName = this.getAttribute('data-name');
                    const self = this; // Store reference to button

                    Swal.fire({
                        title: hospitalName + " 병원을 선택하셨습니다.",
                        text: "다음 단계로 이동하시겠습니까?",
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '예',
                        cancelButtonText: '아니오'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            selectedHospitalSn = hospitalSn;
                            
                            // 병원 선택 표시 (상단 테이블)
                            document.getElementById('selectedHospitalDisplay').textContent = hospitalName;

                            // 병원 선택 버튼 스타일 업데이트
                            document.querySelectorAll('.btnSelectHospital').forEach(b => {
                                b.classList.remove('btn-primary');
                                b.classList.add('btn-outline-primary');
                            });
                            self.classList.remove('btn-outline-primary');
                            self.classList.add('btn-primary');
                            
                            // 달력 초기화 및 로드
                            loadCalendar(hospitalSn);
                            
                            // 상품 목록 로드
                            loadProducts(hospitalSn, ckupTrgtSn);
                            
                            // 추가검사 항목 로드
                            loadAdditionalCheckups(hospitalSn);
                            
                            // 다음 탭으로 이동
                            const step2Tab = new bootstrap.Tab(document.querySelector('[href="#step2"]'));
                            step2Tab.show();
                        }
                    });
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
                        handleDateSelection(info.dateStr, info.dayEl);
                    },
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        const dateStr = info.event.startStr;
                        const eventTitle = info.event.title;
                        
                        // Check if this specific event is full
                        /*const capacityMatch = eventTitle.match(/(\d+)\/(\d+)/);
                        if (capacityMatch) {
                            const current = parseInt(capacityMatch[1]);
                            const total = parseInt(capacityMatch[2]);
                            if (current >= total) {
                                Swal.fire({
                                    title: '검진마감',
                                    text: '해당 검진유형은 마감되었습니다.',
                                    icon: 'warning',
                                    confirmButtonText: '확인'
                                });
                                return;
                            }
                        }*/
                        
                        handleDateSelection(dateStr, info.el);
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

            function handleDateSelection(dateStr, element) {
                // Get all events for this date
                const events = calendar.getEvents().filter(event => {
                    return event.startStr === dateStr;
                });
                
                // Check if there are any events for this date
                if (events.length === 0) {
                    Swal.fire({
                        title: '선택 불가',
                        text: '해당 날짜는 검진 일정이 없습니다.',
                        icon: 'info',
                        confirmButtonText: '확인'
                    });
                    return;
                }
                
                // Check if any "전체" event is full
                let isFull = false;
                for (const event of events) {
                    if (event.title.includes('전체')) {
                        const capacityMatch = event.title.match(/(\d+)\/(\d+)/);
                        if (capacityMatch) {
                            const current = parseInt(capacityMatch[1]);
                            const total = parseInt(capacityMatch[2]);
                            if (current >= total) {
                                isFull = true;
                                break;
                            }
                        }
                    }
                }
                
                if (isFull) {
                    Swal.fire({
                        title: '검진마감',
                        text: '해당 날짜는 검진 인원이 마감되었습니다.',
                        icon: 'warning',
                        confirmButtonText: '확인'
                    });
                    return;
                }
                
                // Format and display the selected date
                const date = new Date(dateStr);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;
                
                selectedDate = dateStr;
                document.getElementById('selectedDateDisplay').textContent = formattedDate;
                
                // Show confirmation alert
                Swal.fire({
                    title: `${year}년 ${month}월 ${day}일을 선택하셨습니다.`,
                    icon: 'success',
                    confirmButtonText: '확인',
                    timer: 2000
                });
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
            // 상품선택 버튼 클릭 이벤트 (이벤트 위임)
            document.getElementById('product-list').addEventListener('click', function(e) {
                const btn = e.target.closest('.btnSelectProduct');
                if (btn) {
                    // Check if date is selected first
                    if (!selectedDate) {
                        Swal.fire({
                            title: '검진희망일 미선택',
                            text: '검진희망일을 먼저 선택해주세요.',
                            icon: 'warning',
                            confirmButtonText: '확인'
                        });
                        return;
                    }
                    
                    const ckupGdsSn = btn.getAttribute('data-ckup-gds-sn');
                    if (ckupGdsSn) {
                        // 버튼 스타일 토글
                        document.querySelectorAll('.btnSelectProduct').forEach(b => {
                            b.classList.remove('btn-primary');
                            b.classList.add('btn-outline-primary');
                        });
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-primary');

                        showProductChoice(ckupGdsSn);
                    }
                }
            });

            function showProductChoice(ckupGdsSn) {
                const section = document.getElementById('productChoiceSection');
                const contentDiv = document.getElementById('productChoiceContent');
                
                // 로딩 표시
                contentDiv.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                section.style.display = 'block';
                section.scrollIntoView({ behavior: 'smooth' });
                
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
                html += '<thead class="table-light"><tr><th>번호</th><th>검사유형</th><th>검사항목</th><th>남녀구분</th><th>선택</th><th>선택갯수</th><th>비고</th></tr></thead>';
                html += '<tbody>';
                
                groups.forEach((group, groupIndex) => {
                    // Filter items based on user's gender
                    const filteredItems = group.items.filter(item => {
                        // Show items that are common (C) or match user's gender
                        return item.GNDR_SE === 'C' || item.GNDR_SE === userGender;
                    });
                    
                    // Skip this group if no items match the gender filter
                    if (filteredItems.length === 0) {
                        return;
                    }
                    
                    const rowspan = filteredItems.length;
                    
                    // 선택갯수 표시 텍스트 생성
                    let countText = group.CHC_ARTCL_CNT;
                    if (group.CHC_ARTCL_CNT2 && group.CHC_ARTCL_CNT2 > 0) {
                        countText += ` 또는 ${group.CHC_ARTCL_CNT2}`;
                    }
                    
                    filteredItems.forEach((item, itemIndex) => {
                        html += `<tr>`;
                        
                        // 첫 번째 항목일 때 그룹 정보 표시 (Rowspan)
                        if (itemIndex === 0) {
                            html += `<td rowspan="${rowspan}" class="fw-bold">${group.GROUP_NM}</td>`;
                        }
                        
                        // 검사유형 표시
                        const ckupTypeMap = {
                            'GS': '위내시경',
                            'CS': '대장내시경',
                            'CT': 'CT',
                            'UT': '초음파',
                            'BU': '유방초음파',
                            'PU': '골반초음파',
                            'ET': '일반'
                        };
                        const ckupTypeText = ckupTypeMap[item.CKUP_TYPE] || item.CKUP_TYPE || '-';
                        html += `<td>${ckupTypeText}</td>`;
                        
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
                                           data-max-count2="${group.CHC_ARTCL_CNT2 || ''}"
                                           data-ckup-type="${item.CKUP_TYPE}">
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
                        if (this.checked) {
                            // 1. Check capacity for this checkup type on the selected date
                            const ckupType = this.getAttribute('data-ckup-type');
                            
                            // Map CKUP_TYPE code to the title used in calendar
                            const ckupTypeMap = {
                                'GS': '위내시경',
                                'CS': '대장내시경',
                                'CT': 'CT',
                                'UT': '초음파',
                                'BU': '유방초음파',
                                'PU': '골반초음파',
                                'ET': '일반'
                            };
                            const ckupTypeName = ckupTypeMap[ckupType];

                            if (ckupTypeName && selectedDate && calendar) {
                                const events = calendar.getEvents().filter(event => {
                                    return event.startStr === selectedDate && event.title.startsWith(ckupTypeName);
                                });

                                if (events.length > 0) {
                                    const event = events[0];
                                    const capacityMatch = event.title.match(/(\d+)\/(\d+)/);
                                    if (capacityMatch) {
                                        const current = parseInt(capacityMatch[1]);
                                        const total = parseInt(capacityMatch[2]);
                                        if (current >= total) {
                                            Swal.fire({
                                                title: '마감 안내',
                                                text: `검진희망일에 ${ckupTypeName} 검사가 마감되었습니다.`,
                                                icon: 'warning',
                                                confirmButtonText: '확인'
                                            });
                                            this.checked = false;
                                            return; // Stop further processing
                                        }
                                    }
                                }
                            }
                        }

                        // 2. Cross-Group Selection Validation Logic
                        // Collect all groups and their current states
                        const allGroups = {};
                        const groupElements = container.querySelectorAll('.choice-item-checkbox');
                        
                        groupElements.forEach(cb => {
                            const gId = cb.getAttribute('data-group-id');
                            if (!allGroups[gId]) {
                                allGroups[gId] = {
                                    id: gId,
                                    maxCount1: parseInt(cb.getAttribute('data-max-count')),
                                    maxCount2: cb.getAttribute('data-max-count2') ? parseInt(cb.getAttribute('data-max-count2')) : null,
                                    checkedCount: 0
                                };
                            }
                            if (cb.checked) {
                                allGroups[gId].checkedCount++;
                            }
                        });

                        // Check if current state is valid for EITHER Option 1 OR Option 2
                        // Option 1: All groups satisfy maxCount1
                        let isValidOption1 = true;
                        for (const gId in allGroups) {
                            if (allGroups[gId].checkedCount > allGroups[gId].maxCount1) {
                                isValidOption1 = false;
                                break;
                            }
                        }

                        // Option 2: All groups satisfy maxCount2 (if maxCount2 exists, otherwise use maxCount1)
                        // Note: If any group doesn't have maxCount2, we assume it stays with maxCount1 for this option set too?
                        // Or does the requirement imply that IF maxCount2 exists, we check that set?
                        // Based on "Group 1: 3 or 2, Group 2: 1 or 3", it implies coupled sets:
                        // Set A: G1=3, G2=1 (using CNT1 of G1 and CNT1 of G2?) -> Wait, the example is tricky.
                        // G1: 3 or 2. G2: 1 or 3.
                        // If G1 selects 3 (CNT1), G2 must be 1 (CNT1).
                        // If G1 selects 2 (CNT2), G2 must be 3 (CNT2).
                        // So we check if (All <= CNT1) OR (All <= CNT2).
                        
                        let isValidOption2 = true;
                        let hasOption2 = false; // Check if any group actually has a second option
                        
                        for (const gId in allGroups) {
                            const limit = allGroups[gId].maxCount2 !== null ? allGroups[gId].maxCount2 : allGroups[gId].maxCount1;
                            if (allGroups[gId].maxCount2 !== null) hasOption2 = true;
                            
                            if (allGroups[gId].checkedCount > limit) {
                                isValidOption2 = false;
                                break;
                            }
                        }
                        
                        if (!hasOption2) isValidOption2 = false; // If no group has option 2, this path is invalid/irrelevant

                        // If neither option set is valid, revert the change
                        if (!isValidOption1 && !isValidOption2) {
                            // Determine which limit was violated to show a helpful message
                            // This is complex because it depends on which "mode" the user was implicitly in.
                            // We can simply say "Selection limit exceeded for the current combination."
                            
                            Swal.fire({
                                title: '선택 불가',
                                text: '선택 가능한 갯수 조합을 초과했습니다.',
                                icon: 'warning',
                                confirmButtonText: '확인'
                            });
                            this.checked = false;
                        }
                    });
                });
            }

            // 선택완료 버튼 클릭 이벤트
            document.getElementById('btnConfirmSelection').addEventListener('click', function() {
                const container = document.getElementById('productChoiceContent');
                const checkboxes = container.querySelectorAll('.choice-item-checkbox');
                
                // Collect all groups and their current states
                const allGroups = {};
                checkboxes.forEach(cb => {
                    const gId = cb.getAttribute('data-group-id');
                    if (!allGroups[gId]) {
                        allGroups[gId] = {
                            id: gId,
                            maxCount1: parseInt(cb.getAttribute('data-max-count')),
                            maxCount2: cb.getAttribute('data-max-count2') ? parseInt(cb.getAttribute('data-max-count2')) : null,
                            checkedCount: 0
                        };
                    }
                    if (cb.checked) {
                        allGroups[gId].checkedCount++;
                    }
                });

                // Validate selections
                // Logic: For each group, checkedCount MUST be equal to maxCount1 OR maxCount2
                // (Assuming user must select exactly the allowed number, or up to? 
                // Requirement says "선택갯수에 맞게 선택했는지 확인". Usually implies exact match or valid option match.
                // Based on "Group 1: 3 or 2", if they select 1, is it valid? Probably not if they need to fill the quota.
                // Let's assume they must match one of the options exactly.)
                
                // Wait, the cross-group logic was:
                // Option 1: All groups <= CNT1
                // Option 2: All groups <= CNT2
                // But "Selection Complete" usually enforces the *target* count.
                // Let's check if the current state matches Option 1 (All == CNT1) OR Option 2 (All == CNT2).
                // Or maybe just "Is Valid" based on the previous logic (<=) but also "Is Complete" (>=)?
                // Let's enforce Exact Match for one of the options.
                
                let isOption1Complete = true;
                for (const gId in allGroups) {
                    if (allGroups[gId].checkedCount !== allGroups[gId].maxCount1) {
                        isOption1Complete = false;
                        break;
                    }
                }
                
                let isOption2Complete = true;
                let hasOption2 = false;
                for (const gId in allGroups) {
                    if (allGroups[gId].maxCount2 !== null) hasOption2 = true;
                    const target = allGroups[gId].maxCount2 !== null ? allGroups[gId].maxCount2 : allGroups[gId].maxCount1;
                    if (allGroups[gId].checkedCount !== target) {
                        isOption2Complete = false;
                        break;
                    }
                }
                if (!hasOption2) isOption2Complete = false;

                if (!isOption1Complete && !isOption2Complete) {
                    Swal.fire({
                        title: '선택 확인',
                        text: '선택갯수에 맞게 선택해주세요.',
                        icon: 'warning',
                        confirmButtonText: '확인'
                    });
                    return;
                }

                // Valid selection
                // 1. Display selected product name
                const selectedProductBtn = document.querySelector('.btnSelectProduct.btn-primary');
                if (selectedProductBtn) {
                    const productName = selectedProductBtn.getAttribute('data-product-name'); // Need to ensure this attr exists or get text
                    // Actually, the button text might contain the name. Let's use the button's text content or add data-name.
                    // The button HTML: <button ...>${product.CKUP_GDS_NM}</button>
                    document.getElementById('selectedProductDisplay').textContent = selectedProductBtn.textContent.trim();
                }

                // 2. Switch to Additional Checkup Tab
                const step3Tab = new bootstrap.Tab(document.querySelector('[href="#step3"]'));
                step3Tab.show();
            });
            
            function loadAdditionalCheckups(hsptlSn) {
                const container = document.getElementById('additionalCheckupList');
                container.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                
                fetch(`<?= site_url('user/rsvnSel/getAdditionalCheckups') ?>?hsptl_sn=${hsptlSn}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items) {
                        renderAdditionalCheckupTable(data.items, container);
                    } else {
                        container.innerHTML = '<p class="text-muted text-center">추가 선택 가능한 항목이 없습니다.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<p class="text-danger text-center">데이터 로드에 실패했습니다.</p>';
                });
            }
            
            function renderAdditionalCheckupTable(items, container) {
                let html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover align-middle text-center">';
                html += '<thead class="table-light"><tr><th>No</th><th>검사항목</th><th>남녀구분</th><th>검사비용(원)</th><th>선택</th></tr></thead>';
                html += '<tbody>';
                
                // Filter by gender
                const filteredItems = items.filter(item => {
                    return item.GNDR_SE === 'C' || item.GNDR_SE === userGender;
                });
                
                if (filteredItems.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">선택 가능한 추가 항목이 없습니다.</p>';
                    return;
                }
                
                filteredItems.forEach((item, index) => {
                    html += `<tr>`;
                    html += `<td>${index + 1}</td>`;
                    html += `<td class="text-start ps-3">${item.CKUP_ARTCL}</td>`;
                    
                    let genderText = '공통';
                    if (item.GNDR_SE === 'M') genderText = '남성';
                    else if (item.GNDR_SE === 'F') genderText = '여성';
                    html += `<td>${genderText}</td>`;
                    
                    // Format cost with comma
                    const cost = parseInt(item.CKUP_CST).toLocaleString();
                    html += `<td class="text-end pe-3">${cost}</td>`;
                    
                    html += `<td>
                                <input type="checkbox" class="form-check-input additional-item-checkbox" 
                                       value="${item.CKUP_GDS_EXCEL_ADD_CHC_SN}"
                                       data-cost="${item.CKUP_CST}">
                             </td>`;
                    html += `</tr>`;
                });
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
                
                // Add event listeners for cost calculation
                addAdditionalCheckupListeners();
            }
            
            function addAdditionalCheckupListeners() {
                const checkboxes = document.querySelectorAll('.additional-item-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateTotalAdditionalCost);
                });
            }
            
            function updateTotalAdditionalCost() {
                const checkboxes = document.querySelectorAll('.additional-item-checkbox:checked');
                let total = 0;
                
                checkboxes.forEach(checkbox => {
                    const cost = parseInt(checkbox.getAttribute('data-cost'));
                    total += cost;
                });
                
                // Update display with comma formatting
                document.getElementById('totalAdditionalCost').textContent = total.toLocaleString();
                
                // 예약정보 테이블의 본인부담금도 함께 업데이트
                document.getElementById('selectedAdditionalCostDisplay').textContent = total.toLocaleString() + '원';
            }

            // 추가검사 선택완료(다음 단계) 버튼 클릭 이벤트
            document.getElementById('btnConfirmAdditional').addEventListener('click', function() {
                // 1. 현재 누적 금액 가져오기
                const totalCost = document.getElementById('totalAdditionalCost').textContent;
                
                // 2. 예약정보 테이블에 표시 (원 단위 추가)
                document.getElementById('selectedAdditionalCostDisplay').textContent = totalCost + '원';
                
                // 3. 다음 탭(예약자 정보 확인)으로 이동
                const step4Tab = new bootstrap.Tab(document.querySelector('[href="#step4"]'));
                step4Tab.show();
            });

            // 이전 버튼 (상품선택 -> 병원선택)
            document.getElementById('btnPrevToHospital').addEventListener('click', function() {
                const step1Tab = new bootstrap.Tab(document.querySelector('[href="#step1"]'));
                step1Tab.show();
            });

            // 이전 버튼 (추가검사 -> 검진일/상품선택)
            document.getElementById('btnPrevToProduct').addEventListener('click', function() {
                const step2Tab = new bootstrap.Tab(document.querySelector('[href="#step2"]'));
                step2Tab.show();
            });

            // 예약 취소 버튼 클릭 이벤트
            document.getElementById('btnCancelReservation').addEventListener('click', function() {
                Swal.fire({
                    title: '준비중',
                    text: '준비중입니다.',
                    icon: 'info',
                    confirmButtonText: '확인'
                });
            });
            
            // 완료 버튼 클릭 이벤트
            document.getElementById('btnCompleteReservation').addEventListener('click', function() {
                // 필수 값 검증
                const handphone = document.getElementById('handphone').value;
                const tel = document.getElementById('tel').value;
                const zipCode = document.getElementById('zipCode').value;
                const address1 = document.getElementById('address1').value;
                const address2 = document.getElementById('address2').value;

                if (!selectedHospitalSn) {
                    Swal.fire('알림', '병원을 선택해주세요.', 'warning');
                    return;
                }
                if (!selectedDate) {
                     Swal.fire('알림', '검진일을 선택해주세요.', 'warning');
                     return;
                }
                if (!handphone) {
                    Swal.fire('알림', '핸드폰 번호를 입력해주세요.', 'warning');
                    return;
                }
                if (!zipCode || !address1) {
                    Swal.fire('알림', '주소를 입력해주세요.', 'warning');
                    return;
                }

                // 데이터 수집
                const formData = new FormData();
                formData.append('ckup_trgt_sn', ckupTrgtSn);
                formData.append('hsptl_sn', selectedHospitalSn);
                formData.append('ckup_date', selectedDate);
                // 상품 선택 (단일 선택 가정, 여러 개일 경우 로직 수정 필요)
                const selectedProductBtn = document.querySelector('.btnSelectProduct.btn-primary');
                if (selectedProductBtn) {
                    formData.append('ckup_gds_sn', selectedProductBtn.getAttribute('data-ckup-gds-sn'));
                }
                
                formData.append('tel', tel);
                formData.append('handphone', handphone);
                formData.append('zip_code', zipCode);
                formData.append('addr', address1);
                formData.append('addr2', address2);

                // 선택 항목 수집
                document.querySelectorAll('.choice-item-checkbox:checked').forEach(cb => {
                    formData.append('choice_items[]', cb.value);
                });

                // 추가 검사 항목 수집
                document.querySelectorAll('.additional-item-checkbox:checked').forEach(cb => {
                    formData.append('additional_items[]', cb.value);
                });
                
                // 서버 전송
                fetch('<?= site_url('user/rsvnSel/complete') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '예약 완료',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: '확인'
                        }).then(() => {
                            window.location.href = '<?= site_url('user/rsvn') ?>'; // 예약 내역 페이지로 이동
                        });
                    } else {
                        Swal.fire('오류', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('오류', '서버 통신 중 오류가 발생했습니다.', 'error');
                });
            });

            // 우편번호 찾기 버튼 클릭 이벤트
            document.getElementById('btnFindZip').addEventListener('click', execDaumPostcode);
        });

        // Daum 우편번호 서비스
        function execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function(data) {
                    // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

                    // 도로명 주소의 노출 규칙에 따라 주소를 표시한다.
                    // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
                    var roadAddr = data.roadAddress; // 도로명 주소 변수
                    var extraRoadAddr = ''; // 참고 항목 변수

                    // 법정동명이 있을 경우 추가한다. (법정리는 제외)
                    // 법정동의 경우 마지막 문자가 "동/로/가"로 끝난다.
                    if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                        extraRoadAddr += data.bname;
                    }
                    // 건물명이 있고, 공동주택일 경우 추가한다.
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                       extraRoadAddr += (extraRoadAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    // 표시할 참고항목이 있을 경우, 괄호까지 추가한 최종 문자열을 만든다.
                    if(extraRoadAddr !== ''){
                        extraRoadAddr = ' (' + extraRoadAddr + ')';
                    }

                    // 우편번호와 주소 정보를 해당 필드에 넣는다.
                    document.getElementById('zipCode').value = data.zonecode;
                    document.getElementById("address1").value = roadAddr;
                    
                    // 상세주소 필드로 커서 이동
                    document.getElementById("address2").focus();
                }
            }).open();
        }
    </script>
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>
