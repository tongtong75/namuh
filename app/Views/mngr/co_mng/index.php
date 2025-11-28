<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'회사관리')); ?>

    <?= $this->include('partials/head-css') ?>

    <style>
        .invalid-feedback { display: none; width: 100%; margin-top: .25rem; font-size: .875em; color: #dc3545; }
        .is-invalid ~ .invalid-feedback { display: block; }
        .btn-loading { position: relative; pointer-events: none; opacity: 0.7; }
        .btn-loading::after { content: ""; position: absolute; top: 50%; left: 50%; width: 1rem; height: 1rem; margin-top: -0.5rem; margin-left: -0.5rem; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* For date inputs */
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
    </style>

</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'회사관리', 'title'=>'회사 목록')); ?>

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
                                    <form id="filter-form" class="mt-2 filter-form" onsubmit="return false;">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <div class="col-md-2">
                                                <input type="text" id="search-keyword" class="form-control" placeholder="검색어 입력">
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">초기화</button>
                                            </div>
                                            <div class="col d-flex justify-content-end gap-2">
                                                <!--button type="button" class="btn btn-success" id="custom-excel-btn">
                                                    <i class="ri-file-excel-2-line align-bottom me-1"></i> 엑셀 다운로드
                                                </button>-->
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" id="create-item-btn" data-bs-target="#showModal">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <table id="coList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>회사명</th>
                                                <th>회사관리자 아이디</th>
                                                <th>담당자명</th>
                                                <th>연락처</th>
                                                <th>검진시작일</th>
                                                <th>검진종료일</th>
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
                                    <h5 class="modal-title" id="exampleModalLabel">신규 회사 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                               
                                <form id="item-form" class="tablelist-form" method="post" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="CO_SN_modal" name="CO_SN"> 
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label for="CO_NM_modal" class="form-label">회사명</label>
                                            <input type="text" id="CO_NM_modal" name="CO_NM" class="form-control" placeholder="회사명을 입력하세요" required />
                                            <div class="invalid-feedback">회사명을 입력해 주세요.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="PIC_NM_modal" class="form-label">담당자명</label>
                                            <input type="text" id="PIC_NM_modal" name="PIC_NM" class="form-control" placeholder="담당자명을 입력하세요 (선택)" />
                                            <div class="invalid-feedback">담당자명을 확인해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CNPL_modal" class="form-label">연락처</label>
                                            <input type="text" id="CNPL_modal" name="CNPL" class="form-control" placeholder="연락처를 입력하세요 (선택)" />
                                            <div class="invalid-feedback">연락처를 확인해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CO_MNGR_ID_modal" class="form-label">회사관리자 아이디</label>
                                            <input type="text" id="CO_MNGR_ID_modal" name="CO_MNGR_ID" class="form-control" placeholder="회사관리자 아이디를 입력하세요" required />
                                            <div class="invalid-feedback">회사관리자 아이디를 입력해 주세요.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CO_MNGR_PSWD_modal" class="form-label">회사관리자 비밀번호</label>
                                            <input type="password" id="CO_MNGR_PSWD_modal" name="CO_MNGR_PSWD" class="form-control" placeholder="변경 시에만 입력" />
                                            <div class="form-text text-muted">비밀번호 변경할 경우에만 입력하세요.</div>
                                            <div class="invalid-feedback">회사관리자 비밀번호를 확인해 주세요.</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="BGNG_YMD_modal" class="form-label">검진시작일</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-2-line"></i></span>
                                                    <input type="text" id="BGNG_YMD_modal" name="BGNG_YMD" class="form-control flatpickr-input" data-provider="flatpickr" data-date-format="Y-m-d"  placeholder="Select date"  aria-describedby="basic-addon1" readonly="readonly">
                                                </div>
                                                <div class="invalid-feedback">검진시작일을 확인해 주세요.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="END_YMD_modal" class="form-label">검진종료일</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="basic-addon1"><i class="ri-calendar-2-line"></i></span>
                                                    <input type="text" id="END_YMD_modal" name="END_YMD" class="form-control flatpickr-input" data-provider="flatpickr" data-date-format="Y-m-d"  placeholder="Select date"  aria-describedby="basic-addon1" readonly="readonly">
                                                </div>
                                                <div class="invalid-feedback">검진종료일을 확인해 주세요.</div>
                                            </div>
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
                    <!--병원연결  modal-->
                    <div class="modal fade" id="showLinkModal" tabindex="-1" aria-labelledby="linkModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Made modal larger -->
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="linkModalLabel">검진 병원 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            
                                <form id="hsptl-link-form" class="tablelist-form" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="link_CO_SN_modal" name="CO_SN">
                                        <?= csrf_field() ?> 

                                        <table id="hsptlLnkListTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">
                                                        <div class="form-check">
                                                            <input class="form-check-input fs-15" type="checkbox" id="checkAllHsptlLinks" value="option">
                                                        </div>
                                                    </th>
                                                    <th>병원명</th>
                                                    <th>담당자명</th>
                                                    <th>연락처</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be populated by DataTables -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                            <button type="submit" class="btn btn-success" id="save-link-btn">저장</button>
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
        let mainTable;
        let hsptlLnkTable;

        function updateCsrfTokenOnPage(newHash) {
            /*CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);*/
            if (newHash) { // newHash가 유효한 경우에만 업데이트
                CSRF_HASH = newHash;
                $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash); // 페이지 내 모든 CSRF input 필드 업데이트
                console.log('CSRF Token Updated:', CSRF_HASH);
            }
        }
        

        function initializeMainDataTable() {
            if (!$.fn.dataTable.isDataTable('#coList')) {
                mainTable = new DataTable('#coList', {
                    serverSide:false, 
                    processing: true,
                    ajax: {
                        url: BASE_URL + 'mngr/coMng/ajax_list',
                        type: 'POST',
                        data: function (d) {
                            d[CSRF_TOKEN_NAME] = CSRF_HASH;
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
                        { data: 'CO_NM' },
                        { data: 'CO_MNGR_ID' },
                        { data: 'PIC_NM' },
                        { data: 'CNPL' },
                        { data: 'BGNG_YMD' },
                        { data: 'END_YMD' },
                        { data: 'REG_YMD' },
                        { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    dom: "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        { extend: 'excelHtml5', text: 'Excel', className: 'buttons-excel' }
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
                    order: [[0, 'desc']], 
                    columnDefs: [
                        { orderable: false, targets: 8 } // '관리' column
                    ],
                    responsive: true,
                    pageLength: 20
                });
            }
        }

        function initializeHsptlLnkDataTable(coSn) {
            if ($.fn.dataTable.isDataTable('#hsptlLnkListTable')) {
                hsptlLnkTable.ajax.url(BASE_URL + 'mngr/coMng/ajax_get_hsptls_for_linking/' + coSn).load(function(json){
                    updateCsrfTokenOnPage(json.csrf_hash); // Update CSRF from this call too
                });
            } else {
                hsptlLnkTable = $('#hsptlLnkListTable').DataTable({
                    processing: true,
                    serverSide: false, // Data is fully loaded, client-side processing for this small table
                    ajax: {
                        url: BASE_URL + 'mngr/coMng/ajax_get_hsptls_for_linking/' + coSn,
                        type: 'GET', // Or POST if you prefer, adjust controller and route
                        dataSrc: function (json) {
                            updateCsrfTokenOnPage(json.csrf_hash);
                            return json.data;
                        },
                        error: function (xhr) {
                            console.error('Error loading hospitals for linking:', xhr.responseText);
                            showAjaxMessage('병원 목록 로딩 중 오류.', 'danger');
                        }
                    },
                    columns: [
                        {
                            data: 'HSPTL_SN',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                const isChecked = row.is_linked ? 'checked' : '';
                                return `<div class="form-check">
                                            <input class="form-check-input hsptl-link-checkbox fs-15" type="checkbox" value="${data}" ${isChecked}>
                                        </div>`;
                            }
                        },
                        { data: 'HSPTL_NM' },
                        { data: 'PIC_NM' },
                        { data: 'CNPL1' }
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'f>>" + // Simple search
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    pageLength: 10,
                    language: { /* ... your existing language settings ... */
                        "emptyTable": "연결할 병원이 없습니다.",
                        "info": "총 _TOTAL_개 병원 중 _START_에서 _END_까지 표시",
                        // ... other settings as needed
                    },
                    drawCallback: function(settings) {
                        // Uncheck "Check All" initially or after redraw
                        $('#checkAllHsptlLinks').prop('checked', false);
                         // If all currently visible checkboxes are checked, then check "Check All"
                        let allChecked = true;
                        $('.hsptl-link-checkbox', this.api().table().body()).each(function() {
                            if (!$(this).prop('checked')) {
                                allChecked = false;
                                return false; // break
                            }
                        });
                        if ($('.hsptl-link-checkbox', this.api().table().body()).length > 0 && allChecked) {
                             $('#checkAllHsptlLinks').prop('checked', true);
                        }
                    }
                });
            }
        }

        function clearFormAndValidation() {
            $('#item-form').trigger('reset');
            $('#item-form .form-control').removeClass('is-invalid');
            $('#item-form .invalid-feedback').hide().text('');
            $('#CO_SN_modal').val('');
        }

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
            // Auto-close message after 5 seconds
            setTimeout(() => { placeholder.find('.alert').alert('close'); }, 5000);
        }

        $(document).ready(function() {
            initializeMainDataTable();

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

            $('#custom-excel-btn').on('click', function() {
                mainTable.button('.buttons-excel').trigger();
            });

            $('#create-item-btn').on('click', function() {
                clearFormAndValidation();
                $('#exampleModalLabel').text('신규 회사 등록');
                $('#add-btn').text('등록');
                $('#CO_SN_modal').val('');
                $('#item-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
            });

            $('#coList').on('click', '.edit-item-btn', function() {
                clearFormAndValidation();
                const itemId = $(this).data('id');

                $('#exampleModalLabel').text('회사 정보 수정');
                $('#add-btn').text('수정');

                $.ajax({
                    url: BASE_URL + 'mngr/coMng/ajax_get_co/' + itemId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            $('#CO_SN_modal').val(response.data.CO_SN);
                            $('#CO_NM_modal').val(response.data.CO_NM);
                            $('#PIC_NM_modal').val(response.data.PIC_NM);
                            $('#CNPL_modal').val(response.data.CNPL);
                            $('#CO_MNGR_ID_modal').val(response.data.CO_MNGR_ID);
                            $('#CO_MNGR_PSWD_modal').val(''); // Password should be empty
                            $('#BGNG_YMD_modal').val(response.data.BGNG_YMD); // Assuming YYYY-MM-DD from controller
                            $('#END_YMD_modal').val(response.data.END_YMD);   // Assuming YYYY-MM-DD from controller
                            updateCsrfTokenOnPage(response.csrf_hash);
                        } else {
                            showAjaxMessage(response.message || '회사 정보를 불러오는데 실패했습니다.', 'danger');
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('회사 정보 로딩 중 오류 발생.', 'danger');
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

                const itemId = $('#CO_SN_modal').val();
                let url;
                let originalButtonText = itemId ? '수정' : '등록';

                const formData = {
                    CO_NM: $('#CO_NM_modal').val(),
                    PIC_NM: $('#PIC_NM_modal').val(),
                    CNPL: $('#CNPL_modal').val(),
                    CO_MNGR_ID: $('#CO_MNGR_ID_modal').val(),
                    CO_MNGR_PSWD: $('#CO_MNGR_PSWD_modal').val(),
                    BGNG_YMD: $('#BGNG_YMD_modal').val(),
                    END_YMD: $('#END_YMD_modal').val(),
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                };

                if (itemId) {
                    url = BASE_URL + 'mngr/coMng/ajax_update';
                    formData.CO_SN = itemId;
                } else {
                    url = BASE_URL + 'mngr/coMng/ajax_create';
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
                            // Use DataTable API to redraw or reload, or full page reload
                            if(mainTable) mainTable.ajax.reload(null, false); // Reload DataTable data
                            // setTimeout(function() { location.reload(); }, 1000); // Alternative: Full page reload
                        } else if (response.status === 'fail') {
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    let fieldId = '#' + key + '_modal'; 
                                    if ($(fieldId).length) {
                                        $(fieldId).addClass('is-invalid');
                                        $(fieldId).siblings('.invalid-feedback').text(value).show();
                                    } else {
                                        // For errors not directly tied to a field like BGNG_YMD or END_YMD (if key is BGNG_YMD)
                                        // try to find the specific field if the key is just the field name
                                        let specificFieldId = '#' + key.toUpperCase() + '_modal';
                                        if ($(specificFieldId).length) {
                                            $(specificFieldId).addClass('is-invalid');
                                            $(specificFieldId).siblings('.invalid-feedback').text(value).show();
                                        } else {
                                            console.warn("Validation error for unmapped field: ", key, value);
                                        }
                                    }
                                });
                            }
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

            $('#coList').on('click', '.delete-item-btn', function() {
                const itemId = $(this).data('id');
                const itemName = $(this).data('name'); // CO_NM from action_buttons.php
                const $rowElement = $(this).closest('tr');
                const $button = $(this);

                if (confirm(`'${itemName}' 회사 정보를 정말로 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/coMng/ajax_delete/' + itemId,
                        type: 'POST', 
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
                $('#exampleModalLabel').text('신규 회사 등록');
                $('#add-btn').text('등록').prop('disabled', false).removeClass('btn-loading');
            });
            
            $('#coList').on('click', '.link-hsptl-btn', function() {
                const coId = $(this).data('id'); // Changed from 'co-id' to 'id'
                const coNm = $(this).data('name'); // Changed from 'co-nm' to 'name'

                console.log('Clicked Link Hospital Button. CO_ID:', coId, 'CO_NM:', coNm);
                console.log('Button HTML:', $(this)[0].outerHTML);

                if (typeof coId === 'undefined' || coId === null || String(coId).trim() === '' || String(coId) === 'undefined') {
                    console.error('Error: Company ID (coId) is undefined or empty.');
                    alert('회사 ID를 가져올 수 없습니다. 버튼의 data-id 속성을 확인해주세요.');
                    return;
                }

                $('#linkModalLabel').text(coNm + ' - 검진 병원 등록');
                $('#link_CO_SN_modal').val(coId);
                initializeHsptlLnkDataTable(coId);
                $('#checkAllHsptlLinks').prop('checked', false);
            });

            

            // "Check All" functionality for hospital links
            $('#checkAllHsptlLinks').on('change', function() {
                const isChecked = $(this).prop('checked');
                // Selects checkboxes only in the current view of DataTable
                // For selecting ALL checkboxes across ALL pages (if serverSide=true or many items):
                // You'd need a more complex logic, or simply post all items if "check all" is true
                // and handle server-side if it means "all available hospitals".
                // For client-side DataTable as configured, this works for visible items.
                // To affect all items in client-side table:
                hsptlLnkTable.rows().nodes().to$().find('.hsptl-link-checkbox').prop('checked', isChecked);
            });

            // Handle individual checkbox changes to update "Check All" status
            $('#hsptlLnkListTable tbody').on('change', '.hsptl-link-checkbox', function() {
                if (!$(this).prop('checked')) {
                    $('#checkAllHsptlLinks').prop('checked', false);
                } else {
                    // Check if all checkboxes in the current view are checked
                    let allCurrentPageChecked = true;
                    hsptlLnkTable.rows({ page: 'current' }).nodes().to$().find('.hsptl-link-checkbox').each(function() {
                        if (!$(this).prop('checked')) {
                            allCurrentPageChecked = false;
                            return false;
                        }
                    });
                    if (allCurrentPageChecked) {
                         // This logic might be tricky if you want "Check All" to reflect ALL pages
                         // For simplicity, let's keep "Check All" for the current view or all client-side data.
                         // The `drawCallback` handles the initial state well.
                    }
                }
            });


            // Save hospital links
            $('#hsptl-link-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $submitButton = $('#save-link-btn');
                const originalButtonText = $submitButton.text();

                const coSn = $('#link_CO_SN_modal').val();
                let selectedHsptls = [];
                // Important: Get all checkboxes from DataTable, not just visible ones
                hsptlLnkTable.rows().nodes().to$().find('.hsptl-link-checkbox:checked').each(function() {
                    selectedHsptls.push($(this).val());
                });

                const formData = {
                    CO_SN: coSn,
                    selected_hsptls: selectedHsptls,
                    [CSRF_TOKEN_NAME]: CSRF_HASH // Get current CSRF hash
                };

                $.ajax({
                    url: BASE_URL + 'mngr/coMng/ajax_save_hsptl_links',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $submitButton.prop('disabled', true).addClass('btn-loading').text('');
                    },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                        if (response.status === 'success') {
                            $('#showLinkModal').modal('hide');
                            showAjaxMessage(response.message, 'success');
                            // Optionally, you might want to refresh something on the main page
                            // if it displays linked hospital counts, etc.
                        } else {
                            showAjaxMessage(response.message || '저장 중 오류가 발생했습니다.', 'danger');
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('서버 통신 오류.', 'danger');
                         try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.csrf_hash)
                                updateCsrfTokenOnPage(errorResponse.csrf_hash);
                        } catch (e) {}
                    },
                    complete: function() {
                        $submitButton.prop('disabled', false).removeClass('btn-loading').text(originalButtonText);
                    }
                });
            });

            $('#showLinkModal').on('hidden.bs.modal', function () {
                // Optional: Clear or destroy DataTable if modal is closed without saving to free resources
                // if (hsptlLnkTable) {
                //     hsptlLnkTable.clear().destroy();
                //     $('#hsptlLnkListTable tbody').empty(); // Ensure tbody is empty
                // }
                $('#checkAllHsptlLinks').prop('checked', false); // Reset "check all"
                $('#save-link-btn').prop('disabled', false).removeClass('btn-loading').text('저장');
            });

        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script> <!-- Ensure this path is correct -->
</body>
</html>