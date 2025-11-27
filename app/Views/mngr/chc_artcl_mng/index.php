<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'선택항목관리')); ?>

    <?= $this->include('partials/head-css') ?>

    <style>
        .invalid-feedback { display: none; width: 100%; margin-top: .25rem; font-size: .875em; color: #dc3545; }
        .is-invalid ~ .invalid-feedback { display: block; }
        .btn-loading { position: relative; pointer-events: none; opacity: 0.7; }
        .btn-loading::after { content: ""; position: absolute; top: 50%; left: 50%; width: 1rem; height: 1rem; margin-top: -0.5rem; margin-left: -0.5rem; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'선택항목관리', 'title'=>'선택항목 목록')); ?>

                    <?php if (session()->getFlashdata('message')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('message') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div id="ajax-message-placeholder"></div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <!--h5 class="card-title mb-0">검색 필터</h5-->
                                    <form id="filter-form" class="mt-2 filter-form" onsubmit="return false;">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <div class="col-md-2">
                                                <label for="hospital-filter" class="form-label">검진병원</label>
                                                <select id="hospital-filter" name="hsptl_sn_filter" class="form-select">
                                                    <?php if (session()->get('user_type') !== 'H'): ?>
                                                        <option value="">전체병원</option>
                                                    <?php endif; ?>
                                                    <?php foreach ($hospitals as $hospital): ?>
                                                        <option value="<?= esc($hospital['HSPTL_SN']) ?>"><?= esc($hospital['HSPTL_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="search-keyword" class="form-label">검색어</label>
                                                <input type="text" id="search-keyword" class="form-control" placeholder="검색어 입력">
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">초기화</button>
                                            </div>
                                            <div class="col d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-success" id="custom-excel-btn">
                                                    <i class="ri-file-excel-2-line align-bottom me-1"></i> 엑셀 다운로드
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" id="create-item-btn" data-bs-target="#showModal">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <table id="chcArtclList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>검사항목</th>
                                                <th>항목코드</th>
                                                <th>검진병원</th>
                                                <th>검사구분</th>
                                                <th>성별구분</th>
                                                <th>검사비용</th>
                                                <th>동의서제출</th>
                                                <th>비고</th>
                                                <th>등록일</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->

                    <!--start add/edit modal-->
                    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel">신규 선택항목 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                               
                                <form id="item-form" class="tablelist-form" method="post" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="CHC_ARTCL_SN_modal" name="CHC_ARTCL_SN"> 
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label for="CKUP_ARTCL_modal" class="form-label">검사항목</label>
                                            <input type="text" id="CKUP_ARTCL_modal" name="CKUP_ARTCL" class="form-control" placeholder="검사항목을 입력하세요" required />
                                            <div class="invalid-feedback">검사항목을 입력해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ARTCL_CODE_modal" class="form-label">항목코드</label>
                                            <input type="text" id="ARTCL_CODE_modal" name="ARTCL_CODE" class="form-control" placeholder="항목코드를 입력하세요" />
                                            <div class="invalid-feedback">항목코드를 입력해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="HSPTL_SN_modal" class="form-label">검진병원</label>
                                            <select id="HSPTL_SN_modal" name="HSPTL_SN" class="form-select" required>
                                                <?php if (session()->get('user_type') !== 'H'): ?>
                                                    <option value="">병원을 선택하세요</option>
                                                <?php endif; ?>
                                                <?php foreach ($hospitals as $hospital): ?>
                                                    <?php if (session()->get('user_type') === 'H' && $hospital['HSPTL_SN'] != session()->get('hsptl_sn')) continue; ?>
                                                    <option value="<?= esc($hospital['HSPTL_SN']) ?>"><?= esc($hospital['HSPTL_NM']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">검진병원을 선택해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CKUP_SE_modal" class="form-label">검사구분</label>
                                            <select class="form-select" id="CKUP_SE_modal" name="CKUP_SE" required>
                                                <option value="ET">기타</option>
                                                <option value="CS">대장내시경</option>
                                                <option value="GS">위내시경</option>
                                                <option value="BU">유방초음파</option>
                                                <option value="PU">골반초음파</option>
                                                <option value="UT">초음파</option>
                                                <option value="CT">CT</option>
                                            </select>
                                            <div class="invalid-feedback">검사구분을 확인해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">성별구분</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="GNDR_SE" id="GNDR_SE_C" value="C" checked>
                                                    <label class="form-check-label" for="GNDR_SE_C">공통</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="GNDR_SE" id="GNDR_SE_M" value="M">
                                                    <label class="form-check-label" for="GNDR_SE_M">남자</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="GNDR_SE" id="GNDR_SE_F" value="F">
                                                    <label class="form-check-label" for="GNDR_SE_F">여자</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CKUP_CST_modal" class="form-label">검사비용</label>
                                            <input type="text" id="CKUP_CST_modal" name="CKUP_CST" class="form-control" placeholder="검사비용을 입력하세요" />
                                            <div class="invalid-feedback">비고를 확인해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">동의서제출여부</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="AGREE_SUBMIT_YN" id="AGREE_SUBMIT_YN_N" value="N" checked>
                                                    <label class="form-check-label" for="AGREE_SUBMIT_YN_N">미제출</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="AGREE_SUBMIT_YN" id="AGREE_SUBMIT_YN_Y" value="Y">
                                                    <label class="form-check-label" for="AGREE_SUBMIT_YN_Y">제출</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="RMRK_modal" class="form-label">비고</label>
                                            <input type="text" id="RMRK_modal" name="RMRK" class="form-control" placeholder="비고를 입력하세요 (선택)" />
                                            <div class="invalid-feedback">비고를 확인해 주세요.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                            <button type="submit" class="btn btn-success" id="add-btn">등록</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div><!-- container-fluid -->
            </div>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>
    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>'; 
        let mainTable; // Changed from chcArtclList for consistency with original script

        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
        }

        function initializeMainDataTable() {
            if (!$.fn.dataTable.isDataTable('#chcArtclList')) { // Check correct table ID
                mainTable = new DataTable('#chcArtclList', { // Use correct table ID
                    serverSide:false, // As per original example for ckupArtclList
                    processing: true,
                    ajax: {
                        url: BASE_URL + 'mngr/chcArtclMng/ajax_list', 
                        type: 'POST',
                        data: function (d) {
                            d[CSRF_TOKEN_NAME] = CSRF_HASH;
                            d.hsptl_sn = $('#hospital-filter').val();
                            d.search_keyword = $('#search-keyword').val();
                        },
                        dataSrc: function (json) {
                            updateCsrfTokenOnPage(json.csrf_hash);
                            return json.data;
                        },
                        error: function (xhr) {
                            console.error('서버 에러:', xhr.responseText);
                            showAjaxMessage('데이터 로딩 중 오류가 발생했습니다.', 'danger');
                        }
                    },
                    columns: [ 
                        { data: 'no' },
                        { data: 'CKUP_ARTCL' },
                        { data: 'ARTCL_CODE' },
                        { data: 'HSPTL_NM' },
                        {
                            data: 'CKUP_SE',
                            className: 'text-center', // 가운데 정렬
                            render: function(data, type, row, meta) { // 'meta' 파라미터 추가
                                
                                if (type === 'display') { // 화면에 표시될 때 스타일 적용
                                    if (data === 'ET') {
                                        return '기타';
                                    } else if (data === 'CS') {
                                        return '<span style="font-weight: bold; color: red;">대장내시경</span>';
                                    } else if (data === 'GS') {
                                        return '<span style="font-weight: bold; color: red;">위내시경</span>';
                                    } else if (data === 'CT') {
                                        return 'CT';
                                    } else if (data === 'UT') {
                                        return '초음파';
                                    } else if (data === 'PU') {
                                        return '<span style="font-weight: bold; color: red;">골반초음파</span>';
                                    } else if (data === 'BU') {
                                        return '<span style="font-weight: bold; color: red;">유방초음파</span>';
                                    }
                                    return data; // 'M', 'F', 'C'가 아닌 경우 원본 데이터 표시 (예: null 또는 예상치 못한 값)
                                } else if (type === 'filter' || type === 'sort' || type === 'type') {
                                    // 필터링, 정렬, 타입 감지를 위해서는 한글 텍스트 또는 원본 코드를 사용합니다.
                                    if (data === 'ET') return '기타';
                                    if (data === 'CS') return '대장내시경';
                                    if (data === 'GS') return '위내시경';
                                    if (data === 'CT') return 'CT';
                                    if (data === 'UT') return '초음파';
                                    if (data === 'PU') return '골반초음파';
                                    if (data === 'BU') return '유방초음파';
                                    return data;
                                }
                                return data; // 다른 모든 타입에 대한 기본 반환값
                            }
                        },
                        {
                            data: 'GNDR_SE',
                            className: 'text-center', // 가운데 정렬
                            render: function(data, type, row, meta) { // 'meta' 파라미터 추가
                                
                                if (type === 'display') { // 화면에 표시될 때 스타일 적용
                                    if (data === 'M') {
                                        return '<span style="font-weight: bold; color: blue;">남자</span>';
                                    } else if (data === 'F') {
                                        return '<span style="font-weight: bold; color: red;">여자</span>';
                                    } else if (data === 'C') {
                                        return '공통';
                                    }
                                    return data; // 'M', 'F', 'C'가 아닌 경우 원본 데이터 표시 (예: null 또는 예상치 못한 값)
                                } else if (type === 'filter' || type === 'sort' || type === 'type') {
                                    // 필터링, 정렬, 타입 감지를 위해서는 한글 텍스트 또는 원본 코드를 사용합니다.
                                    if (data === 'M') return '남자';
                                    if (data === 'F') return '여자';
                                    if (data === 'C') return '공통';
                                    return data;
                                }
                                return data; // 다른 모든 타입에 대한 기본 반환값
                            }
                        },
                        {
                            data: 'CKUP_CST',
                            className: 'text-end', // <<< ADDED for right alignment
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    // Check if data is a number
                                    if (data === null || data === undefined || data === '') return '';
                                    const number = parseFloat(data.toString().replace(/,/g, ''));
                                    if (isNaN(number)) return data; // Return original if not a valid number
                                    return number.toLocaleString('ko-KR'); // Format with commas for Korean locale
                                }
                                return data;
                            }
                        },
                        {
                            data: 'AGREE_SUBMIT_YN',
                            className: 'text-center',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return data === 'Y' ? '<span class="badge bg-success">제출</span>' : '<span class="badge bg-secondary">미제출</span>';
                                }
                                return data;
                            }
                        },
                        { data: 'RMRK' },
                        { data: 'REG_YMD' },
                        { data: 'action', orderable: false, searchable: false }
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        { extend: 'excelHtml5', text: '<i class="ri-download-line align-bottom"></i> Excel', className: 'buttons-excel' }
                    ],
                    language: {
                        "emptyTable": "표시할 데이터가 없습니다.",
                        "info": "총 _TOTAL_개 항목 중 _START_에서 _END_까지 표시",
                        "infoEmpty": "0개 항목 중 0에서 0까지 표시",
                        "infoFiltered": "(총 _MAX_개 항목에서 필터링됨)",
                        "lengthMenu": "페이지당 _MENU_ 항목 표시",
                        "loadingRecords": "로딩 중...",
                        "processing": "처리 중...",
                        "search": "검색:",
                        "zeroRecords": "일치하는 레코드를 찾을 수 없습니다.",
                        "paginate": {
                            "first": "처음", "last": "마지막", "next": "다음", "previous": "이전"
                        }
                    },
                    order: [[0, 'desc']], // Order by 'No.' column descending
                    columnDefs: [
                        { orderable: false, targets: 6 } // '관리' column
                    ],
                    responsive: true
                });
            }
        }

        function clearFormAndValidation() {
            $('#item-form').trigger('reset');
            $('#item-form .form-control').removeClass('is-invalid');
            $('#item-form .invalid-feedback').hide().text('');
            $('#item-form .invalid-feedback').hide().text('');
            $('#CHC_ARTCL_SN_modal').val('');
            // Reset radio buttons to default
            $('#GNDR_SE_C').prop('checked', true);
            $('#AGREE_SUBMIT_YN_N').prop('checked', true);
        }

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
            setTimeout(() => { placeholder.find('.alert').alert('close'); }, 5000);
        }

        $(document).ready(function() {
            initializeMainDataTable();

            $('#custom-excel-btn').on('click', function() {
                mainTable.button('.buttons-excel').trigger();
            });

            $('#btn-filter-search').on('click', function() {
                mainTable.ajax.reload();
            });

            $('#btn-filter-reset').on('click', function() {
                $('#filter-form')[0].reset();
                mainTable.ajax.reload();
            });

            $('#search-keyword').on('keyup', function(event) {
                if (event.key === 'Enter') {
                    mainTable.ajax.reload();
                }
            });

            $('#create-item-btn').on('click', function() {
                clearFormAndValidation();
                $('#exampleModalLabel').text('신규 선택항목 등록');
                $('#add-btn').text('등록');
                $('#CHC_ARTCL_SN_modal').val('');
                $('#HSPTL_SN_modal').val('');
                $('#item-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
            });

            // Use the correct table ID for event delegation
            $('#chcArtclList').on('click', '.edit-item-btn', function() { // <<<< CHANGED TABLE ID
                clearFormAndValidation();
                const itemId = $(this).data('id');

                $('#exampleModalLabel').text('선택항목 정보 수정'); // <<<< CHANGED TEXT
                $('#add-btn').text('수정');

                $.ajax({
                    url: BASE_URL + 'mngr/chcArtclMng/ajax_get_chc_artcl/' + itemId, // <<<< CHANGED URL
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            $('#CHC_ARTCL_SN_modal').val(response.data.CHC_ARTCL_SN); 
                            $('#CKUP_ARTCL_modal').val(response.data.CKUP_ARTCL);
                            $('#ARTCL_CODE_modal').val(response.data.ARTCL_CODE);   
                            $('#HSPTL_SN_modal').val(response.data.HSPTL_SN); // Set hospital selection
                            
                            // Set Radio Buttons
                            $(`input[name="GNDR_SE"][value="${response.data.GNDR_SE}"]`).prop('checked', true);
                            
                            $('#CKUP_SE_modal').val(response.data.CKUP_SE);     
                            $('#CKUP_CST_modal').val(response.data.CKUP_CST);   
                            
                            // Set Radio Buttons
                            let agreeSubmitYn = response.data.AGREE_SUBMIT_YN || 'N';
                            $(`input[name="AGREE_SUBMIT_YN"][value="${agreeSubmitYn}"]`).prop('checked', true);
                            
                            $('#RMRK_modal').val(response.data.RMRK);             
                            updateCsrfTokenOnPage(response.csrf_hash);
                        } else {
                            showAjaxMessage(response.message || '선택항목 정보를 불러오는데 실패했습니다.', 'danger'); // <<<< CHANGED TEXT
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('선택항목 정보 로딩 중 오류 발생.', 'danger'); // <<<< CHANGED TEXT
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.csrf_hash)
                                updateCsrfTokenOnPage(errorResponse.csrf_hash);
                        } catch (e) {}
                    }
                });
            });

            $('#item-form').on('submit', function(e) {
                e.preventDefault();
                $('#item-form .form-control').removeClass('is-invalid');
                $('#item-form .invalid-feedback').hide().text('');

                const itemId = $('#CHC_ARTCL_SN_modal').val(); // <<<< CHANGED ID
                let url;
                let originalButtonText = itemId ? '수정' : '등록';

                const formData = { // <<<< CHANGED FORM DATA FIELDS
                    CKUP_ARTCL: $('#CKUP_ARTCL_modal').val(),
                    ARTCL_CODE: $('#ARTCL_CODE_modal').val(),
                    GNDR_SE: $('input[name="GNDR_SE"]:checked').val(),
                    CKUP_SE: $('#CKUP_SE_modal').val(),
                    CKUP_CST: $('#CKUP_CST_modal').val(),
                    AGREE_SUBMIT_YN: $('input[name="AGREE_SUBMIT_YN"]:checked').val(),
                    RMRK: $('#RMRK_modal').val(),
                    HSPTL_SN: $('#HSPTL_SN_modal').val(), // Add this line
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                };

                if (itemId) {
                    url = BASE_URL + 'mngr/chcArtclMng/ajax_update'; // <<<< CHANGED URL
                    formData.CHC_ARTCL_SN = itemId; // <<<< CHANGED ID FIELD
                } else {
                    url = BASE_URL + 'mngr/chcArtclMng/ajax_create'; // <<<< CHANGED URL
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#add-btn').prop('disabled', true).addClass('btn-loading').text('');
                    },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);

                        if (response.status === 'success') {
                            $('#showModal').modal('hide');
                            showAjaxMessage(response.message, 'success');
                            setTimeout(function() { location.reload(); }, 1000); // Reload to see changes
                        } else if (response.status === 'fail') {
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    // Map model validation keys to form field IDs
                                    let fieldId = '#' + key + '_modal'; 
                                    // Special handling if key doesn't directly map (e.g. if CHC_ARTCL_SN was a form field)
                                    // if (key === 'CHC_ARTCL_SN') fieldId = '#CHC_ARTCL_SN_modal';
                                    
                                    if ($(fieldId).length) {
                                        $(fieldId).addClass('is-invalid');
                                        $(fieldId).siblings('.invalid-feedback').text(value).show();
                                    } else {
                                        // Fallback for general errors not tied to a specific field in the modal
                                        console.warn("Validation error for unmapped field or general message: ", key, value);
                                        // showAjaxMessage(value, 'danger'); // Or display it somewhere else
                                    }
                                });
                            }
                            // Show general message if available, or a default one
                             showAjaxMessage(response.message || '입력값을 확인해주세요.', 'danger');
                        } else {
                            showAjaxMessage(response.message || '오류가 발생했습니다.', 'danger');
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('서버 통신 중 오류가 발생했습니다.', 'danger');
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.csrf_hash)
                                updateCsrfTokenOnPage(errorResponse.csrf_hash);
                        } catch (e) {}
                    },
                    complete: function() {
                        $('#add-btn').prop('disabled', false).removeClass('btn-loading').text(originalButtonText);
                    }
                });
            });

            // Use the correct table ID for event delegation
            $('#chcArtclList').on('click', '.delete-item-btn', function() { // <<<< CHANGED TABLE ID
                const itemId = $(this).data('id');
                const itemName = $(this).data('name'); // This will use the CKUP_ARTCL field from action_buttons.php
                const $rowElement = $(this).closest('tr');
                const $button = $(this);

                if (confirm(`'${itemName}' 선택항목 정보를 정말로 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) { // <<<< CHANGED TEXT
                    $.ajax({
                        url: BASE_URL + 'mngr/chcArtclMng/ajax_delete/' + itemId, // <<<< CHANGED URL
                        type: 'POST', // Or 'DELETE' if your server/routes are configured for it with _method
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        dataType: 'json',
                        beforeSend: function() {
                            $button.prop('disabled', true).addClass('btn-loading').text('');
                        },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash);
                            if (response.status === 'success') {
                                showAjaxMessage(response.message, 'success');
                                const tableRow = mainTable.row($rowElement);
                                if (tableRow.length) {
                                    tableRow.remove().draw(false);
                                }
                            } else {
                                showAjaxMessage(response.message || '삭제 중 오류가 발생했습니다.', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessage('서버 통신 오류로 삭제에 실패했습니다.', 'danger');
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse && errorResponse.csrf_hash)
                                    updateCsrfTokenOnPage(errorResponse.csrf_hash);
                            } catch (e) {}
                        },
                        complete: function() {
                            $button.prop('disabled', false).removeClass('btn-loading').text('삭제');
                        }
                    });
                }
            });

            $('#showModal').on('hidden.bs.modal', function () {
                clearFormAndValidation();
                $('#exampleModalLabel').text('신규 선택항목 등록'); // <<<< CHANGED TEXT
                $('#add-btn').text('등록').prop('disabled', false).removeClass('btn-loading');
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>