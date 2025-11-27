<?php
// 컨트롤러에서 전달된 $ckupGds 변수의 존재 여부로 수정/등록 모드를 결정합니다.
$isEditMode = isset($ckupGds) && !empty($ckupGds);

// 모드에 따라 페이지 제목과 버튼 텍스트를 동적으로 설정합니다.
$pageTitle = $isEditMode ? '검진상품 정보 수정' : '검진상품 신규 등록';
$buttonText = $isEditMode ? '전체수정' : '전체저장';
?>

<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle)); ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    
    <!-- Custom CSS -->
    <?= $this->include('partials/head-css') ?>
    <style>
        th.no-sort::before, th.no-sort::after { display: none !important; }
        
    </style>
</head>

<body>
    <div id="layout-wrapper">

        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => '검진상품 관리', 'title' => $pageTitle)); ?>

                    <div id="ajax-message-placeholder"></div>
                    <div>
                        <ul class="nav nav-tabs nav-border-top nav-border-top-primary d-flex align-items-center" style = "background-color:#ffffff" role="tablist">
                            <li class="nav-item waves-effect waves-light" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#baseInfo" role="tab" aria-selected="true">
                                    기본 및 항목 정보
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#selectInfo" role="tab" aria-selected="false" tabindex="-1">
                                    선택항목정보
                                </a>
                            </li>
                            <li class="nav-item waves-effect waves-light" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#addSelectInfo" role="tab" aria-selected="false" tabindex="-1">
                                    추가선택항목정보
                                </a>
                            </li>
                            <li class="ms-auto px-3">
                                <button type="button" id='save-all-btn' class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                                    <i class="ri-save-line align-bottom me-1"></i> <?= esc($buttonText) ?>
                                </button>
                                <a href="<?= site_url('mngr/ckupGdsMng') ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                                    <i class="ri-list-check align-bottom me-1"></i> 목록으로
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content text-muted">
	                    <div class="tab-pane active" id="baseInfo" role="tabpanel">
                            <!-- 기본정보 카드: 병원, 년도, 회사, 상품명 등 핵심 정보를 입력하는 영역입니다. -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <h4 class="card-title mb-0 me-2 text-primary fw-bold">
                                                    <?= esc($pageTitle) ?>
                                                </h4>
                                                <!--div class="ms-auto d-flex gap-2">
                                                    <button type="button" id='save-all-btn' class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                                                        <i class="ri-save-line align-bottom me-1"></i> <?= esc($buttonText) ?>
                                                    </button>
                                                    <a href="<?= site_url('mngr/ckupGdsMng') ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                                                        <i class="ri-list-check align-bottom me-1"></i> 목록으로
                                                    </a>
                                                </div-->
                                            </div>
                                        </div><!-- //card-header -->

                                        <div class="card-body">
                                            <input type="hidden" id="CKUP_GDS_SN_hidden" value="<?= $isEditMode ? esc($ckupGds['basicInfo']['CKUP_GDS_SN']) : '' ?>">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label for="HSPTL_SN_sel" class="form-label">검진병원<span class="text-danger">*</span></label>
                                                    <select id="HSPTL_SN_sel" name="HSPTL_SN" class="form-select">
                                                        <option value="">병원을 선택하세요</option>
                                                        <?php foreach ($hospitals as $hsptl): ?>
                                                            <option value="<?= esc($hsptl['HSPTL_SN']) ?>"><?= esc($hsptl['HSPTL_NM']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="CKUP_YYYY_sel" class="form-label">검진년도<span class="text-danger">*</span></label>
                                                    <select id="CKUP_YYYY_sel" name="CKUP_YYYY" class="form-select">
                                                        <option value="">년도를 선택하세요</option>
                                                        <?php foreach ($years as $year): ?>
                                                            <option value="<?= $year ?>"><?= $year ?>년</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="CO_SN_sel" class="form-label">회사<span class="text-danger">*</span></label>
                                                    <select id="CO_SN_sel" name="CO_SN" class="form-select" required>
                                                        <option value="">회사를 선택하세요</option>
                                                        <?php foreach ($companies as $company): ?>
                                                            <option value="<?= esc($company['CO_SN']) ?>"><?= esc($company['CO_NM']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label for="CKUP_GDS_NM_modal" class="form-label">상품명<span class="text-danger">*</span></label>
                                                    <input type="text" id="CKUP_GDS_NM_modal" name="CKUP_GDS_NM" class="form-control" required placeholder="상품명을 입력하세요">
                                                </div>
                                            </div>
                                        </div><!-- //card-body -->
                                    </div><!-- //card -->
                                </div><!-- //col -->
                            </div><!-- //row -->

                            <!-- 항목정보 (기본) 카드: 좌측 테이블에서 항목을 선택하여 우측 상품 구성 테이블로 추가/삭제합니다. -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0 me-2 text-primary fw-bold">항목정보</h4>
                                        </div><!-- //card-header -->
                                        <div class="card-body">
                                            <div class="row align-items-start">
                                                <div class="col-md-5 mb-3">
                                                    <table id="ckupArtclList1" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" style="width: 10px;" class="no-sort">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input fs-15" type="checkbox" id="checkAll1">
                                                                    </div>
                                                                </th>
                                                                <th>검사구분</th>
                                                                <th>검사항목</th>
                                                                <th>질환</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-2 mb-3 text-center mt-5">
                                                    <button type="button" class="btn btn-outline-primary mb-2 w-75" id="add-basic-item-btn"><i class="ri-add-line me-1"></i> 추가</button>
                                                    <button type="button" class="btn btn-outline-danger w-75" id="delete-basic-item-btn"><i class="ri-subtract-line align-bottom me-1"></i> 삭제</button>
                                                </div>
                                                <div class="col-md-5 mb-3">
                                                    <table id="ckupGdsArtclTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" style="width: 10px;" class="no-sort"><div class="form-check"><input class="form-check-input fs-15" type="checkbox" id="checkAll2"></div></th>
                                                                <th>검사구분</th>
                                                                <th>검사항목</th>
                                                                <th>질환</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div><!-- //card-body -->
                                    </div><!-- //card -->
                                </div><!-- //col -->
                            </div><!-- //row -->
                        </div>
	                    <div class="tab-pane" id="selectInfo" role="tabpanel">
                            <!-- 항목정보 (선택) 카드: '그룹 추가' 버튼으로 동적으로 선택 항목 그룹을 생성하고 관리합니다. -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h4 class="card-title mb-0 me-2 text-primary fw-bold">선택 항목정보</h4>
                                                <button type="button" class="btn btn-primary btn-sm" id="add-choice-group-btn">
                                                    <i class="ri-add-line align-bottom me-1"></i> 선택 항목 그룹 추가
                                                </button>
                                            </div>
                                        </div><!-- //card-header -->
                                        <div id="choice-sections-container">
                                            <!-- 선택 항목 그룹이 여기에 동적으로 추가됩니다. -->
                                        </div>
                                    </div><!-- //card -->
                                </div><!-- //col -->
                            </div><!-- //row -->
                        </div>
                        <div class="tab-pane" id="addSelectInfo" role="tabpanel">
                        
                            <!-- 추가선택 항목정보 : 좌측 테이블에서 항목을 선택하여 우측 상품 구성 테이블로 추가/삭제합니다. -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0 me-2 text-primary fw-bold">추가 선택 항목정보</h4>
                                        </div><!-- //card-header -->
                                        <div class="card-body">
                                            <div class="row align-items-start">
                                                <div class="col-md-5 mb-3">
                                                    <table id="addChcArtclList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" style="width: 10px;" class="no-sort">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input fs-15" type="checkbox" id="checkAll5">
                                                                    </div>
                                                                </th>
                                                                <th>검사항목</th>
                                                                <th>성별구분</th>
                                                                <th>검사비용</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-2 mb-3 text-center mt-5">
                                                    <button type="button" class="btn btn-outline-primary mb-2 w-75" id="add-addchc-item-btn"><i class="ri-add-line me-1"></i> 추가</button>
                                                    <button type="button" class="btn btn-outline-danger w-75" id="delete-addchc-item-btn"><i class="ri-subtract-line align-bottom me-1"></i> 삭제</button>
                                                </div>
                                                <div class="col-md-5 mb-3">
                                                    <table id="addChcArtclTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" style="width: 10px;" class="no-sort"><div class="form-check"><input class="form-check-input fs-15" type="checkbox" id="checkAll6"></div></th>
                                                                <th>검사항목</th>
                                                                <th>성별구분</th>
                                                                <th>검사비용</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div><!-- //card-body -->
                                    </div><!-- //card -->
                                </div><!-- //col -->
                            </div><!-- //row -->
                        </div>
                    </div>
            </div><!-- //tab-content -->
                </div><!-- //container-fluid -->
            </div><!-- //page-content -->
            <?= $this->include('partials/footer') ?>
        </div><!-- //main-content -->
    </div><!-- //layout-wrapper -->

    <!-- 복제를 위한 HTML 템플릿: JavaScript를 통해 이 템플릿을 복사하여 '선택 항목 그룹'을 동적으로 생성합니다. -->
    <template id="choice-section-template">
        <div class="card-body border-top choice-item-group" data-group-index="__INDEX__">
            
            <div class="row mb-3 align-items-center bg-light p-2">
                <div class="col-auto">
                    <label for="GROUP_NM___INDEX__" class="form-label mb-0">선택항목명<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-2">
                    <input type="text" id="GROUP_NM___INDEX__" name="GROUP_NM[]" class="form-control" required placeholder="선택항목명을 입력하세요">
                </div>
                <div class="col-auto">
                    <label for="CHC_ARTCL_CNT___INDEX__" class="form-label mb-0">선택갯수<span class="text-danger">*</span></label>
                </div>
                <div class="col-auto">
                    <div class="input-step">
                        <button type="button" class="minus material-shadow">–</button>
                        <input type="number" id="CHC_ARTCL_CNT___INDEX__" name="CHC_ARTCL_CNT[]" class="product-quantity" value="1" min="1" max="100" readonly>
                        <button type="button" class="plus material-shadow">+</button>
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-danger btn-sm delete-choice-group-btn">
                        <i class="ri-close-line me-1"></i> 이 그룹 삭제
                    </button>
                </div>
            </div>
            <div class="row align-items-start">
                <div class="col-md-5 mb-3">
                    <table id="chcArtclList__INDEX__" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 10px;" class="no-sort"><div class="form-check"><input class="form-check-input fs-15" type="checkbox" id="checkAll3__INDEX__"></div></th>
                                <th>검사항목</th>
                                <th>성별구분</th>
                                <th>검사비용</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="col-md-2 mb-3 text-center mt-5">
                    <button type="button" id="add-choice-item-btn__INDEX__" class="btn btn-outline-primary mb-2 w-75"><i class="ri-add-line me-1"></i> 추가</button>
                    <button type="button" id="delete-choice-item-btn__INDEX__" class="btn btn-outline-danger w-75"><i class="ri-subtract-line align-bottom me-1"></i> 삭제</button>
                </div>
                <div class="col-md-5 mb-3">
                    <table id="ckupGdsChcArtclTable__INDEX__" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 10px;" class="no-sort"><div class="form-check"><input class="form-check-input fs-15" type="checkbox" id="checkAll4__INDEX__"></div></th>
                                <th>검사항목</th>
                                <th>성별구분</th>
                                <th>검사비용</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    
    <script>
$(document).ready(function () {
    /**
     * 검진상품 등록/수정 페이지의 모든 JavaScript 로직을 관리하는 객체
     */
    const CkupGdsManager = {
        
        // =================================================================
        // 1. CONFIG & STATE
        // =================================================================
        
        config: {
            baseUrl: "<?= base_url() ?>",
            isEditMode: <?= $isEditMode ? 'true' : 'false' ?>,
            initialData: <?= $isEditMode ? json_encode($ckupGds) : 'null' ?>,
            
            csrf: {
                name: "<?= csrf_token() ?>",
                hash: "<?= csrf_hash() ?>"
            },
            
            datatableCommonOptions: {
                processing: true,
                lengthChange: false,
                info: false,
                searching: false,      // 검색 기능 활성화
                pageLength: 100,
                responsive: true,
                scrollY: "300px",
                scrollCollapse: true,
                paging: false,
                language: {
                    emptyTable: "데이터가 없습니다.",
                    paginate: {
                        next: ">",
                        previous: "<"
                    }
                },
                columnDefs: [{
                    orderable: false,
                    targets: 'no-sort'
                }]
            }
        },
        
        state: {
            choiceSectionCounter: 0,
            tables: {
                ckupArtclList: null,  // 기본항목 > 전체 목록 테이블
                ckupGdsArtcl: null,   // 기본항목 > 상품 구성 테이블
                addChcArtclList: null, // 추가선택항목 > 전체 목록 테이블
                addChcArtclTable: null // 추가선택항목 > 상품 구성 테이블
            }
        },

        // =================================================================
        // 2. INITIALIZATION
        // =================================================================
        
        /**
         * 페이지 로드 시 실행되는 메인 초기화 함수
         */
        init: function() {
            console.log('CkupGdsManager.init() called.');
            console.log('config.isEditMode:', this.config.isEditMode);
            console.log('config.initialData:', this.config.initialData); // 이 부분이 중요합니다.

            this.initBasicItemTables();
            this.bindGlobalEvents();
            this.initAddSelectTables();

            if (this.config.isEditMode && this.config.initialData) {
                this.populateFormForEditMode();
            } else {
                // 신규 등록 모드에서는 빈 선택 그룹을 하나 추가합니다.
                this.addChoiceSection();
            }
            console.log('addChcArtclTable after init:', this.state.tables.addChcArtclTable.rows().data().toArray());
        },

        initBasicItemTables: function() {
            // 테이블 그룹핑을 위한 drawCallback 함수
            const drawCallbackGrouping = (settings) => {
                const api = settings.oInstance.api();
                const rows = api.rows({ page: 'current' }).nodes();
                let last = null;
                const groupCol = 1;

                api.column(groupCol, { page: 'current' }).data().each((group, i) => {
                    const row = $(rows).eq(i);
                    const cell = row.find('td').eq(groupCol);

                    if (last !== group) {
                        cell.attr('rowspan', 1).css('vertical-align', 'middle').show();
                        last = group;
                    } else {
                        cell.hide();
                        for (let j = i - 1; j >= 0; j--) {
                            const prevRow = $(rows).eq(j);
                            const prevCell = prevRow.find('td').eq(groupCol);
                            if (prevCell.is(':visible')) {
                                prevCell.attr('rowspan', (parseInt(prevCell.attr('rowspan') || 1) + 1));
                                break;
                            }
                        }
                    }
                });
                
                // 테이블 높이 동기화
                this.helpers.syncTableHeights(
                    '#ckupArtclList1_wrapper .dataTables_scrollBody',
                    '#ckupGdsArtclTable_wrapper .dataTables_scrollBody'
                );
            };

            // ---[수정된 부분 시작]---

            // 기본항목 전체 목록 테이블 (왼쪽) - 검색, 페이징 활성화
            this.state.tables.ckupArtclList = $('#ckupArtclList1').DataTable({
                processing: true,
                responsive: true,
                pageLength: 15,       // 한 페이지에 10개씩 표시
                lengthChange: false,   // 페이지 당 표시 항목 수 변경 옵션 활성화
                searching: true,      // 검색 기능 활성화
                paging: true,         // 페이징 기능 활성화
                scrollY: false,       // 페이징을 사용하므로 내부 스크롤은 비활성화
                scrollCollapse: false,
                info: false,
                language: {
                    "search": "검색:",
                    "lengthMenu": "_MENU_ 개씩 보기",
                    "info": "총 _TOTAL_개 중 _START_에서 _END_까지",
                    "infoEmpty": "표시할 데이터가 없습니다.",
                    "infoFiltered": "(_MAX_개 항목에서 필터링됨)",
                    "zeroRecords": "일치하는 데이터가 없습니다.",
                    "paginate": { "next": ">", "previous": "<" },
                    "emptyTable": "데이터가 없습니다."
                },
                ajax: {
                    url: this.config.baseUrl + 'mngr/ckupArtclMng/ajax_list',
                    type: 'POST',
                    data: (d) => {
                        d[this.config.csrf.name] = this.config.csrf.hash;
                    },
                    dataSrc: (json) => {
                        if (json.csrf_hash) {
                            this.helpers.updateCsrfToken(json.csrf_hash);
                        }
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'CKUP_ARTCL_SN',
                        render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}" name="artcl_list_cb"></div>`,
                        orderable: false,
                        searchable: false
                    },
                    { data: 'CKUP_SE' },
                    { data: 'CKUP_ARTCL' },
                    { data: 'DSS' }
                ],
                order: [[1, 'asc']],
                drawCallback: drawCallbackGrouping,
                // 숨겨진 검색창과 길이 변경 메뉴를 다시 보이게 함
                initComplete: function() {
                    $('#ckupArtclList1_wrapper .row:first-child').show();
                }
            });

            // 기본항목 상품 구성 테이블 (오른쪽) - 기존 설정 유지 (페이징 없음)
            this.state.tables.ckupGdsArtcl = $('#ckupGdsArtclTable').DataTable({
                ...this.config.datatableCommonOptions,
                data: [],
                columns: [
                    {
                        data: 'CKUP_ARTCL_SN',
                        render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}" name="gds_artcl_cb"></div>`
                    },
                    { data: 'CKUP_SE' },
                    { data: 'CKUP_ARTCL' },
                    { data: 'DSS' }
                ],
                order: [[1, 'asc']],
                drawCallback: drawCallbackGrouping,
                language: {
                    ...this.config.datatableCommonOptions.language,
                    emptyTable: "추가된 항목이 없습니다."
                }
            });

            // ---[수정된 부분 끝]---
        },

        /**
         * 새로운 '선택 항목 그룹' 섹션을 초기화합니다.
         * @param {number} index - 그룹의 고유 인덱스
         */
        initializeNewChoiceSection: function(index) {
            const groupSelector = `.choice-item-group[data-group-index="${index}"]`;
            const leftTableId = `#chcArtclList${index}`;
            const rightTableId = `#ckupGdsChcArtclTable${index}`;
            const eventNamespace = `.group${index}`;

            // 테이블 컬럼 설정
            const columns = [
                {
                    data: 'CHC_ARTCL_SN',
                    render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}"></div>`
                },
                { data: 'CKUP_ARTCL' },
                {
                    data: 'GNDR_SE',
                    className: 'text-center',
                    render: (d) => d === 'M' ? '남자' : d === 'F' ? '여자' : '공통'
                },
                {
                    data: 'CKUP_CST',
                    className: 'text-end',
                    render: (d) => d ? parseFloat(String(d).replace(/,/g, '')).toLocaleString('ko-KR') : ''
                }
            ];
            
            // 테이블 높이 동기화 함수
            const drawCallback = () => {
                this.helpers.syncTableHeights(
                    `${leftTableId}_wrapper .dataTables_scrollBody`,
                    `${rightTableId}_wrapper .dataTables_scrollBody`
                );
            };

            // 선택항목 전체 목록 테이블 (왼쪽)
            const leftTable = $(leftTableId).DataTable({
                processing: true,
                responsive: true,
                pageLength: 15,       // 한 페이지에 10개씩 표시
                lengthChange: false,   // 페이지 당 표시 항목 수 변경 옵션 활성화
                searching: true,      // 검색 기능 활성화
                paging: true,         // 페이징 기능 활성화
                scrollY: false,       // 페이징을 사용하므로 내부 스크롤은 비활성화
                scrollCollapse: false,
                info: false,
                language: {
                    "search": "검색:",
                    "lengthMenu": "_MENU_ 개씩 보기",
                    "info": "총 _TOTAL_개 중 _START_에서 _END_까지",
                    "infoEmpty": "표시할 데이터가 없습니다.",
                    "infoFiltered": "(_MAX_개 항목에서 필터링됨)",
                    "zeroRecords": "일치하는 데이터가 없습니다.",
                    "paginate": { "next": ">", "previous": "<" },
                    "emptyTable": "데이터가 없습니다."
                },
                ajax: {
                    url: this.config.baseUrl + 'mngr/chcArtclMng/ajax_list',
                    type: 'POST',
                    data: (d) => {
                        d[this.config.csrf.name] = this.config.csrf.hash;
                    },
                    dataSrc: (json) => {
                        if (json.csrf_hash) {
                            this.helpers.updateCsrfToken(json.csrf_hash);
                        }
                        return json.data;
                    }
                },
                columns: columns,
                drawCallback: drawCallback
            });

            // 선택항목 상품 구성 테이블 (오른쪽)
            const rightTable = $(rightTableId).DataTable({
                ...this.config.datatableCommonOptions,
                data: [],
                columns: columns,
                language: {
                    ...this.config.datatableCommonOptions.language,
                    emptyTable: "추가된 항목이 없습니다."
                },
                drawCallback: drawCallback
            });
            
            // 테이블 체크박스 name 속성 설정
            leftTable.on('draw', () => {
                $(`${leftTableId} tbody input[type="checkbox"]`).attr('name', `chc_artcl_cb_${index}`);
            });
            
            rightTable.on('draw', () => {
                $(`${rightTableId} tbody input[type="checkbox"]`).attr('name', `gds_chc_artcl_cb_${index}`);
            });

            // 이벤트 바인딩 (네임스페이스 사용으로 메모리 누수 방지)
            $(document).on(`click${eventNamespace}`, `${groupSelector} #add-choice-item-btn${index}`, () => {
                this.helpers.handleAddItem(
                    leftTable,
                    rightTable,
                    'CHC_ARTCL_SN',
                    `chc_artcl_cb_${index}`,
                    `${groupSelector} #checkAll3${index}`
                );
            });
            
            $(document).on(`click${eventNamespace}`, `${groupSelector} #delete-choice-item-btn${index}`, () => {
                this.helpers.handleDeleteItem(
                    rightTable,
                    `gds_chc_artcl_cb_${index}`,
                    `${groupSelector} #checkAll4${index}`
                );
            });
            
            $(document).on(`click${eventNamespace}`, `${groupSelector} .delete-choice-group-btn`, function() {
                if (confirm('이 그룹을 삭제하시겠습니까?')) {
                    $(document).off(eventNamespace);
                    $(this).closest(groupSelector).remove();
                }
            });
            
            $(document).on(`click${eventNamespace}`, `${groupSelector} .input-step button`, function() {
                const input = $(this).siblings('input');
                let val = parseInt(input.val());
                
                if ($(this).hasClass('plus')) {
                    val++;
                } else if (val > 1) {
                    val--;
                }
                
                input.val(val);
            });
            
            $(document).on(`click${eventNamespace}`, `${groupSelector} #checkAll3${index}`, function() {
                $(`${leftTableId} input[type="checkbox"]`).prop('checked', this.checked);
            });
            
            $(document).on(`click${eventNamespace}`, `${groupSelector} #checkAll4${index}`, function() {
                $(`${rightTableId} input[type="checkbox"]`).prop('checked', this.checked);
            });
        },

        initAddSelectTables: function() {
            // 테이블 그룹핑을 위한 drawCallback 함수
            const drawCallbackGrouping = (settings) => {                
                // 테이블 높이 동기화
                this.helpers.syncTableHeights(
                    '#addChcArtclList_wrapper .dataTables_scrollBody',
                    '#addChcArtclTable_wrapper .dataTables_scrollBody'
                );
            };

            // ---[수정된 부분 시작]---

            // 기본항목 전체 목록 테이블 (왼쪽) - 검색, 페이징 활성화
            this.state.tables.addChcArtclList = $('#addChcArtclList').DataTable({
                processing: true,
                responsive: true,
                pageLength: 15,       // 한 페이지에 10개씩 표시
                lengthChange: false,   // 페이지 당 표시 항목 수 변경 옵션 활성화
                searching: true,      // 검색 기능 활성화
                paging: true,         // 페이징 기능 활성화
                scrollY: false,       // 페이징을 사용하므로 내부 스크롤은 비활성화
                scrollCollapse: false,
                info: false,
                language: {
                    "search": "검색:",
                    "lengthMenu": "_MENU_ 개씩 보기",
                    "info": "총 _TOTAL_개 중 _START_에서 _END_까지",
                    "infoEmpty": "표시할 데이터가 없습니다.",
                    "infoFiltered": "(_MAX_개 항목에서 필터링됨)",
                    "zeroRecords": "일치하는 데이터가 없습니다.",
                    "paginate": { "next": ">", "previous": "<" },
                    "emptyTable": "데이터가 없습니다."
                },
                ajax: {
                    url: this.config.baseUrl + 'mngr/chcArtclMng/ajax_list',
                    type: 'POST',
                    data: (d) => {
                        d[this.config.csrf.name] = this.config.csrf.hash;
                    },
                    dataSrc: (json) => {
                        if (json.csrf_hash) {
                            this.helpers.updateCsrfToken(json.csrf_hash);
                        }
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'CHC_ARTCL_SN',
                        render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}" name="add_chc_artcl_list_cb"></div>`,
                        orderable: false,
                        searchable: false
                    },
                    { data: 'CKUP_ARTCL' },
                    {
                        data: 'GNDR_SE',
                        className: 'text-center',
                        render: (d) => d === 'M' ? '남자' : d === 'F' ? '여자' : '공통'
                    },
                    {
                        data: 'CKUP_CST',
                        className: 'text-end',
                        render: (d) => d ? parseFloat(String(d).replace(/,/g, '')).toLocaleString('ko-KR') : ''
                    }
                ],
                order: [[1, 'asc']],
                drawCallback: drawCallbackGrouping,
                // 숨겨진 검색창과 길이 변경 메뉴를 다시 보이게 함
                initComplete: function() {
                    $('#addChcArtclList_wrapper .row:first-child').show();
                }
            });

            // 추가선택항목 상품 구성 테이블 (오른쪽) - 기존 설정 유지 (페이징 없음)
            this.state.tables.addChcArtclTable = $('#addChcArtclTable').DataTable({
                ...this.config.datatableCommonOptions,
                data: (this.config.isEditMode && this.config.initialData && this.config.initialData.addChoiceItems) ? this.config.initialData.addChoiceItems : [],
                columns: [
                    {
                        data: 'CHC_ARTCL_SN',
                        render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}" name="add_chc_artcl_cb"></div>`
                    },
                    { data: 'CKUP_ARTCL' },
                    {
                        data: 'GNDR_SE',
                        className: 'text-center',
                        render: (d) => d === 'M' ? '남자' : d === 'F' ? '여자' : '공통'
                    },
                    { data: 'CKUP_CST',
                        className: 'text-end',
                        render: (d) => d ? parseFloat(String(d).replace(/,/g, '')).toLocaleString('ko-KR') : ''
                    }
                ],
                order: [[1, 'asc']],
                drawCallback: drawCallbackGrouping,
                language: {
                    ...this.config.datatableCommonOptions.language,
                    emptyTable: "추가된 항목이 없습니다."
                }
            });
            console.log('addChcArtclTable initialized. Current data:', this.state.tables.addChcArtclTable.rows().data().toArray());

            // ---[수정된 부분 끝]---
        },
        // =================================================================
        // 3. EVENT BINDING & CORE LOGIC
        // =================================================================
        
        /**
         * 페이지 전역 이벤트 핸들러를 바인딩합니다.
         */
        bindGlobalEvents: function() {
            // 저장 버튼
            $('#save-all-btn').on('click', () => {
                this.saveAllData();
            });
            
            // 기본항목 추가/삭제 버튼
            $('#add-basic-item-btn').on('click', () => {
                this.helpers.handleAddItem(
                    this.state.tables.ckupArtclList,
                    this.state.tables.ckupGdsArtcl,
                    'CKUP_ARTCL_SN',
                    'artcl_list_cb',
                    '#checkAll1'
                );
            });
            
            $('#delete-basic-item-btn').on('click', () => {
                this.helpers.handleDeleteItem(
                    this.state.tables.ckupGdsArtcl,
                    'gds_artcl_cb',
                    '#checkAll2'
                );
            });
            
            // 전체 선택 체크박스
            $('#checkAll1').on('click', () => {
                $('input[name="artcl_list_cb"]', this.state.tables.ckupArtclList.rows({ search: 'applied' }).nodes())
                    .prop('checked', $('#checkAll1').is(':checked'));
            });
            
            $('#checkAll2').on('click', () => {
                $('input[name="gds_artcl_cb"]', this.state.tables.ckupGdsArtcl.rows({ search: 'applied' }).nodes())
                    .prop('checked', $('#checkAll2').is(':checked'));
            });
            
            // 선택 그룹 추가 버튼
            $('#add-choice-group-btn').on('click', () => {
                this.addChoiceSection();
            });

            // 추가선택항목 추가/삭제 버튼
            $('#add-addchc-item-btn').on('click', () => {
                this.helpers.handleAddItem(
                    this.state.tables.addChcArtclList,
                    this.state.tables.addChcArtclTable,
                    'CHC_ARTCL_SN',
                    'add_chc_artcl_list_cb',
                    '#checkAll5'
                );
            });

            $('#delete-addchc-item-btn').on('click', () => {
                this.helpers.handleDeleteItem(
                    this.state.tables.addChcArtclTable,
                    'add_chc_artcl_cb',
                    '#checkAll6'
                );
            });

            $('#checkAll5').on('click', () => {
                $('input[name="add_chc_artcl_list_cb"]', this.state.tables.addChcArtclList.rows({ search: 'applied' }).nodes())
                    .prop('checked', $('#checkAll5').is(':checked'));
            });

            $('#checkAll6').on('click', () => {
                $('input[name="add_chc_artcl_cb"]', this.state.tables.addChcArtclTable.rows({ search: 'applied' }).nodes())
                    .prop('checked', $('#checkAll6').is(':checked'));
            });

            // '선택항목정보' 탭이 표시될 때 DataTables 레이아웃을 재계산하고, 보류 중인 데이터를 로드합니다.
            $('a[data-bs-toggle="tab"][href="#selectInfo"]').on('shown.bs.tab', function () {
                // 1. 모든 보이는 테이블의 컬럼 너비를 조정합니다. (헤더 정렬 문제 해결)
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();

                // 2. 오른쪽 테이블에 보류된 데이터가 있으면 로드합니다.
                $('.choice-item-group').each(function() {
                    const groupIndex = $(this).data('group-index');
                    const rightTableElement = $(`#ckupGdsChcArtclTable${groupIndex}`);
                    const pendingData = rightTableElement.data('pending-data');

                    if (pendingData) {
                        const rightTable = rightTableElement.DataTable();
                        rightTable.rows.add(pendingData).draw();
                        // 데이터 로드 후 보류 상태 제거
                        rightTableElement.removeData('pending-data');
                    }
                });
            });

            // '추가선택항목정보' 탭이 표시될 때 DataTables 레이아웃을 재계산합니다.
            $('a[data-bs-toggle="tab"][href="#addSelectInfo"]').on('shown.bs.tab', function () {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                CkupGdsManager.state.tables.addChcArtclTable.draw(); // 테이블을 다시 그립니다.
            });
        },

        /**
         * 새로운 선택 항목 그룹 섹션을 추가합니다.
         */
        addChoiceSection: function() {
            let templateHtml = $('#choice-section-template').html();
            
            // 첫 번째 그룹인 경우 삭제 버튼 제거
            if (this.state.choiceSectionCounter === 0 && $('.choice-item-group').length === 0) {
                const tempDiv = $('<div>').html(templateHtml);
                tempDiv.find('.delete-choice-group-btn').parent().remove();
                templateHtml = tempDiv.html();
            }
            
            // 템플릿의 __INDEX__ 플레이스홀더를 실제 인덱스로 치환
            const newHtml = templateHtml.replace(/__INDEX__/g, this.state.choiceSectionCounter);
            $('#choice-sections-container').append(newHtml);
            
            // 새로 추가된 섹션 초기화
            this.initializeNewChoiceSection(this.state.choiceSectionCounter);
            this.state.choiceSectionCounter++;
        },

        /**
         * 수정 모드일 때 기존 데이터로 폼을 채웁니다.
         */
        populateFormForEditMode: function() {
            const data = this.config.initialData;
            console.log('populateFormForEditMode called with data:', data);
            console.log('addChcArtclTable data before populateFormForEditMode:', this.state.tables.addChcArtclTable.rows().data().toArray());

            // 기본 정보 설정
            if (data.basicInfo) {
                $('#HSPTL_SN_sel').val(data.basicInfo.HSPTL_SN);
                $('#CKUP_YYYY_sel').val(data.basicInfo.CKUP_YYYY);
                $('#CO_SN_sel').val(data.basicInfo.CO_SN);
                $('#CKUP_GDS_NM_modal').val(data.basicInfo.CKUP_GDS_NM);
            }
            
            // 기본 항목 설정
            if (data.basicItems && data.basicItems.length > 0) {
                this.state.tables.ckupGdsArtcl.rows.add(data.basicItems).draw();
            }
            
            // 추가선택항목 설정
            // 데이터는 initAddSelectTables에서 초기화 시 로드됩니다.

            if (data.choiceGroups && data.choiceGroups.length > 0) {
                data.choiceGroups.forEach(groupData => {
                    this.addChoiceSection();
                    const currentGroupIndex = this.state.choiceSectionCounter - 1;
                    const groupContainer = $(`.choice-item-group[data-group-index="${currentGroupIndex}"]`);
                    
                    // 그룹 정보 설정
                    groupContainer.find('input[name="GROUP_NM[]"]').val(groupData.GROUP_NM);
                    groupContainer.find('input[name="CHC_ARTCL_CNT[]"]').val(groupData.CHC_ARTCL_CNT);
                    
                    // 그룹 항목 데이터를 테이블에 바로 추가하지 않고, 'pending-data'로 저장해 둡니다.
                    // 탭이 표시될 때 데이터를 로드하여 렌더링 문제를 해결합니다.
                    if (groupData.items && groupData.items.length > 0) {
                        $(`#ckupGdsChcArtclTable${currentGroupIndex}`).data('pending-data', groupData.items);
                    }
                });
            } else {
                // 수정 모드이지만 선택 그룹 데이터가 없는 경우, 빈 그룹을 하나 추가합니다.
                this.addChoiceSection();
            }
        },

        /**
         * 모든 데이터를 서버에 저장합니다.
         */
        saveAllData: function() {
            // 유효성 검사
            const basicInfo = {
                CKUP_GDS_SN: $('#CKUP_GDS_SN_hidden').val(),
                HSPTL_SN: $('#HSPTL_SN_sel').val(),
                CKUP_YYYY: $('#CKUP_YYYY_sel').val(),
                CO_SN: $('#CO_SN_sel').val(),
                CKUP_GDS_NM: $('#CKUP_GDS_NM_modal').val().trim()
            };

            if (!basicInfo.HSPTL_SN) { alert('기본정보: 검진병원을 선택해주세요.'); return; }
            if (!basicInfo.CKUP_YYYY) { alert('기본정보: 검진년도를 선택해주세요.'); return; }
            if (!basicInfo.CO_SN) { alert('기본정보: 회사를 선택해주세요.'); return; }
            if (!basicInfo.CKUP_GDS_NM) { alert('기본정보: 상품명을 입력해주세요.'); return; }

            const basicItems = this.state.tables.ckupGdsArtcl.rows().data().toArray().map(item => item.CKUP_ARTCL_SN);
            if (basicItems.length === 0) {
                alert('항목정보: 기본 항목을 1개 이상 추가해주세요.');
                return;
            }

            const choiceGroups = [];
            let validationError = false;
            $('.choice-item-group').each(function() {
                const groupIndex = $(this).data('group-index');
                const groupName = $(this).find(`input[name="GROUP_NM[]"]`).val().trim();
                const choiceCount = $(this).find(`input[name="CHC_ARTCL_CNT[]"]`).val();
                
                if (!groupName) {
                    alert('선택항목정보: 그룹명을 입력해주세요.');
                    validationError = true;
                    return false;
                }
                
                const choiceItems = $(`#ckupGdsChcArtclTable${groupIndex}`).DataTable().rows().data().toArray().map(item => item.CHC_ARTCL_SN);
                if (choiceItems.length === 0) {
                    alert(`선택항목정보: '${groupName}' 그룹에 항목을 1개 이상 추가해주세요.`);
                    validationError = true;
                    return false;
                }
                
                choiceGroups.push({ GROUP_NM: groupName, CHC_ARTCL_CNT: choiceCount, items: choiceItems });
            });

            if (validationError) return;

            const addChoiceItems = this.state.tables.addChcArtclTable.rows().data().toArray().map(item => item.CHC_ARTCL_SN);
            if (addChoiceItems.length === 0) {
                alert('추가선택항목정보: 추가 선택 항목을 1개 이상 추가해주세요.');
                return;
            }

            // 서버 전송 데이터 구성

            const payload = {
                [this.config.csrf.name]: this.config.csrf.hash,
                basicInfo,
                basicItems,
                choiceGroups,
                addChoiceItems
            };

            // AJAX 요청
            $.ajax({
                url: this.config.baseUrl + 'mngr/ckupGdsMng/ckupGdsSave',
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                dataType: 'json',
                success: (response) => {
                    if (response.csrf_hash) {
                        this.helpers.updateCsrfToken(response.csrf_hash);
                    }
                    
                    if (response.success) {
                        alert(response.message);
                        window.location.href = this.config.baseUrl + 'mngr/ckupGdsMng';
                    } else {
                        alert('실패: ' + (response.message || '알 수 없는 오류가 발생했습니다.'));
                    }
                },
                error: (xhr) => {
                    alert('서버와 통신 중 오류가 발생했습니다. 콘솔을 확인해주세요.');
                    console.error(xhr.responseText);
                }
            });
        },

        // =================================================================
        // 4. HELPER FUNCTIONS
        // =================================================================
        
        helpers: {
            /**
             * CSRF 토큰을 업데이트합니다.
             * @param {string} newHash - 새로운 CSRF 해시값
             */
            updateCsrfToken: function(newHash) {
                CkupGdsManager.config.csrf.hash = newHash;
            },

            /**
             * 두 테이블의 높이를 동기화합니다.
             * @param {string} leftSelector - 왼쪽 테이블 셀렉터
             * @param {string} rightSelector - 오른쪽 테이블 셀렉터
             */
            syncTableHeights: function(leftSelector, rightSelector) {
                const leftWrapper = $(leftSelector);
                const rightWrapper = $(rightSelector);
                
                if (leftWrapper.length && rightWrapper.length) {
                    rightWrapper.css('max-height', leftWrapper.height() + 'px');
                }
            },

            /**
             * 선택된 항목을 소스 테이블에서 대상 테이블로 추가합니다.
             * @param {Object} sourceTable - 소스 DataTable 객체
             * @param {Object} destTable - 대상 DataTable 객체
             * @param {string} idKey - 고유 식별자 키
             * @param {string} checkboxName - 체크박스 name 속성
             * @param {string} checkAllSelector - 전체 선택 체크박스 셀렉터
             */
            handleAddItem: function(sourceTable, destTable, idKey, checkboxName, checkAllSelector) {
                const selectedRows = sourceTable.rows(
                    $(`tbody input[name^="${checkboxName}"]:checked`, sourceTable.table().container()).closest('tr')
                );
                const selectedData = selectedRows.data().toArray();
                
                if (selectedData.length === 0) {
                    alert('추가할 항목을 선택해주세요.');
                    return;
                }
                
                // 중복 체크
                const existingIds = destTable.rows().data().toArray().map(row => row[idKey]);
                let addedCount = 0;
                
                selectedData.forEach(newData => {
                    if (!existingIds.includes(newData[idKey])) {
                        destTable.row.add(newData);
                        addedCount++;
                    }
                });
                
                if (addedCount > 0) {
                    destTable.draw();
                    // 선택 해제
                    selectedRows.nodes().to$().find('input[type="checkbox"]').prop('checked', false);
                    $(checkAllSelector).prop('checked', false);
                } else {
                    alert('이미 추가된 항목입니다.');
                }
            },

            /**
             * 선택된 항목을 테이블에서 삭제합니다.
             * @param {Object} destTable - 대상 DataTable 객체
             * @param {string} checkboxName - 체크박스 name 속성
             * @param {string} checkAllSelector - 전체 선택 체크박스 셀렉터
             */
            handleDeleteItem: function(destTable, checkboxName, checkAllSelector) {
                const rowsToDelete = destTable.rows(
                    $(`tbody input[name^="${checkboxName}"]:checked`, destTable.table().container()).closest('tr')
                );
                
                if (rowsToDelete.count() === 0) {
                    alert('삭제할 항목을 선택해주세요.');
                    return;
                }
                
                rowsToDelete.remove().draw();
                $(checkAllSelector).prop('checked', false);
            }
        }
    };

    // 초기화 실행
    CkupGdsManager.init();
});
</script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>