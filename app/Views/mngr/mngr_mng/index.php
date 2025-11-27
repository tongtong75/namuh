<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => '관리자 관리')); ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

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
                    <?php echo view('partials/page-title', array('pagetitle' => '관리자 설정', 'title' => '관리자 목록')); ?>
                    
                    <div id="ajax-message-placeholder"></div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <form id="filter-form" class="mt-2 filter-form" onsubmit="return false;">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <div class="col-md-2">
                                                <!--label for="search-keyword" class="form-label">검색어</label-->
                                                <input type="text" id="search-keyword" class="form-control" placeholder="검색어 입력">
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">초기화</button>
                                            </div>
                                            <div class="col d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" id="create-mngr-btn" data-bs-target="#mngrModal">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <table id="mngrListTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>병원명</th>
                                                <th>관리자명</th>
                                                <th>관리자 ID</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Modal -->
                    <div class="modal fade" id="mngrModal" tabindex="-1" aria-labelledby="mngrModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="mngrModalLabel">신규 관리자 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="mngr-form" autocomplete="off">
                                    <div class="modal-body">
                                        <input type="hidden" id="MNGR_SN_modal" name="MNGR_SN_modal">
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label for="HSPTL_SN_modal" class="form-label">병원 선택</label>
                                            <select class="form-select" id="HSPTL_SN_modal" name="HSPTL_SN" required>
                                                <option value="">병원을 선택하세요</option>
                                                <?php if (!empty($hospitals)): ?>
                                                    <?php foreach ($hospitals as $hsptl): ?>
                                                        <option value="<?= esc($hsptl['HSPTL_SN']) ?>"><?= esc($hsptl['HSPTL_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="invalid-feedback">병원을 선택해주세요.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="MNGR_NM_modal" class="form-label">관리자명</label>
                                            <input type="text" id="MNGR_NM_modal" name="MNGR_NM" class="form-control" placeholder="관리자명을 입력하세요" required />
                                            <div class="invalid-feedback">관리자명을 입력해주세요.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="MNGR_ID_modal" class="form-label">관리자 ID</label>
                                            <input type="text" id="MNGR_ID_modal" name="MNGR_ID" class="form-control" placeholder="관리자 ID를 입력하세요 (영문, 숫자)" required pattern="^[a-zA-Z0-9]+$" title="영문, 숫자만 입력 가능합니다." />
                                            <div class="invalid-feedback">관리자 ID는 영문과 숫자만 사용할 수 있습니다.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="MNGR_PSWD_modal" class="form-label">비밀번호</label>
                                            <input type="password" id="MNGR_PSWD_modal" name="MNGR_PSWD" class="form-control" placeholder="비밀번호를 입력하세요" />
                                            <div class="form-text">수정 시, 변경할 경우에만 입력하세요.</div>
                                            <div class="invalid-feedback">비밀번호는 최소 4자 이상이어야 합니다.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                        <button type="submit" class="btn btn-success" id="submit-mngr-btn">등록</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>';
        let mngrTable;

        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
        }

        function initializeMngrDataTable() {
            const dataTableLngOpt = {
                "decimal":        "",
                "emptyTable":     "표시할 데이터가 없습니다.",
                "info":           "총 _TOTAL_개 항목 중 _START_에서 _END_까지 표시",
                "infoEmpty":      "0개 항목 중 0에서 0까지 표시",
                "infoFiltered":   "(총 _MAX_개 항목에서 필터링됨)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "페이지당 _MENU_ 항목 표시", // 이 텍스트는 lengthChange:false 시 보이지 않음
                "loadingRecords": "로딩 중...",
                "processing":     "처리 중...",
                "zeroRecords":    "일치하는 레코드를 찾을 수 없습니다.",
                "paginate": {
                    "first":    "처음",
                    "last":     "마지막",
                    "next":     "다음",
                    "previous": "이전"
                },
                "aria": {
                    "sortAscending":  ": 오름차순으로 정렬",
                    "sortDescending": ": 내림차순으로 정렬"
                }
            };

            // 매니저 목록 테이블 초기화
            mngrTable = $('#mngrListTable').DataTable({
                ajax: {
                    url: BASE_URL + 'mngr/mngrMng/ajax_list',
                    type: 'POST',
                    data: function(d) {
                        d[CSRF_TOKEN_NAME] = CSRF_HASH;
                        d.search_keyword = $('#search-keyword').val();
                    },
                    dataSrc: function(json) {
                        updateCsrfTokenOnPage(json.csrf_hash);
                        return json.data; // 배열 반환
                    }
                },
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 }
                ],
                dom: "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: dataTableLngOpt,
                responsive: true,
                serverSide: false,   // 👈 서버사이드 비활성화
                processing: true,
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        orderable: false,
                        targets: 4
                    }
                ],
                lengthChange: false
            });
        }

        function clearMngrFormAndValidation() {
            $('#mngr-form')[0].reset();
            $('#mngr-form .form-control, #mngr-form .form-select').removeClass('is-invalid');
            $('#mngr-form .invalid-feedback').hide().text('');
            $('#MNGR_SN_modal').val('');
            $('#mngrModalLabel').text('신규 관리자 등록');
            $('#submit-mngr-btn').text('등록');
            $('#MNGR_PSWD_modal').attr('placeholder', '비밀번호를 입력하세요').prop('required', true); // 등록 시 비밀번호 필수
        }

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
            setTimeout(() => { placeholder.find('.alert').alert('close'); }, 5000);
        }

        $(document).ready(function() {
            initializeMngrDataTable();

            $('#btn-filter-search').on('click', function() {
                mngrTable.ajax.reload();
            });

            $('#btn-filter-reset').on('click', function() {
                $('#filter-form')[0].reset();
                mngrTable.ajax.reload();
            });

            $('#search-keyword').on('keyup', function(event) {
                if (event.key === 'Enter') {
                    mngrTable.ajax.reload();
                }
            });

            // 신규 등록 버튼 클릭
            $('#create-mngr-btn').on('click', function() {
                clearMngrFormAndValidation();
                // CSRF 토큰 최신화
                $('#mngr-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
            });

            $('#MNGR_ID_modal').on('input', function() {
                const MngrIdField = $(this);
                let MngrIdValue = MngrIdField.val();
                MngrIdValue = MngrIdValue.replace(/[^a-zA-Z0-9]/g, ''); // Replace non-alphanumeric
                MngrIdField.val(MngrIdValue);
            });

            // 수정 버튼 클릭
            $('#mngrListTable').on('click', '.edit-mngr-btn', function() {
                clearMngrFormAndValidation();
                const mngrId = $(this).data('id');
                $('#mngrModalLabel').text('관리자 정보 수정');
                $('#submit-mngr-btn').text('수정');
                $('#MNGR_PSWD_modal').attr('placeholder', '변경 시에만 입력').prop('required', false); // 수정 시 비밀번호 선택

                $.ajax({
                    url: BASE_URL + 'mngr/mngrMng/ajax_get_mngr/' + mngrId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            $('#MNGR_SN_modal').val(response.data.MNGR_SN);
                            $('#HSPTL_SN_modal').val(response.data.HSPTL_SN);
                            $('#MNGR_NM_modal').val(response.data.MNGR_NM);
                            $('#MNGR_ID_modal').val(response.data.MNGR_ID);
                            // 비밀번호는 채우지 않음
                            updateCsrfTokenOnPage(response.csrf_hash);
                            $('#mngr-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
                        } else {
                            showAjaxMessage(response.message || '정보를 불러오지 못했습니다.', 'danger');
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('정보 로딩 중 오류.', 'danger');
                        try { const errResp = JSON.parse(xhr.responseText); if (errResp && errResp.csrf_hash) updateCsrfTokenOnPage(errResp.csrf_hash); } catch(e){}
                    }
                });
            });

            // 폼 제출 (등록/수정)
            $('#mngr-form').on('submit', function(e) {
                e.preventDefault();
                $('#mngr-form .form-control, #mngr-form .form-select').removeClass('is-invalid');
                $('#mngr-form .invalid-feedback').hide().text('');

                const mngrSn = $('#MNGR_SN_modal').val();
                let url = mngrSn ? BASE_URL + 'mngr/mngrMng/ajax_update' : BASE_URL + 'mngr/mngrMng/ajax_create';
                let originalButtonText = mngrSn ? '수정' : '등록';
                
                let formDataObj = {
                    HSPTL_SN: $('#HSPTL_SN_modal').val(),
                    MNGR_NM: $('#MNGR_NM_modal').val(),
                    MNGR_ID: $('#MNGR_ID_modal').val(),
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                };
                if (mngrSn) { // 수정 시
                    formDataObj.MNGR_SN_modal = mngrSn; // 컨트롤러에서 MNGR_SN_modal로 받음
                }
                 // 비밀번호는 값이 있을 때만 전송
                const password = $('#MNGR_PSWD_modal').val();
                if (password) {
                    formDataObj.MNGR_PSWD = password;
                } else if (!mngrSn) { // 등록 시인데 비밀번호가 비어있으면 (required 속성으로 막히겠지만)
                     formDataObj.MNGR_PSWD = ""; // 빈 값이라도 보내서 유효성 검사에서 걸리도록
                }


                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formDataObj,
                    dataType: 'json',
                    beforeSend: function() { $('#submit-mngr-btn').prop('disabled', true).addClass('btn-loading').text(''); },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                         $('#mngr-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);

                        if (response.status === 'success') {
                            $('#mngrModal').modal('hide');
                            showAjaxMessage(response.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else if (response.status === 'fail') {
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    let fieldId = '#' + key + '_modal';
                                    $(fieldId).addClass('is-invalid');
                                    $(fieldId).siblings('.invalid-feedback').text(value).show();
                                });
                            }
                            showAjaxMessage(response.message || '입력값을 확인해주세요.', 'danger');
                        } else {
                            showAjaxMessage(response.message || '오류가 발생했습니다.', 'danger');
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('서버 통신 오류.', 'danger');
                        try { const errResp = JSON.parse(xhr.responseText); if (errResp && errResp.csrf_hash) updateCsrfTokenOnPage(errResp.csrf_hash); } catch(e){}
                    },
                    complete: function() { $('#submit-mngr-btn').prop('disabled', false).removeClass('btn-loading').text(originalButtonText); }
                });
            });

            // 삭제 버튼 클릭
            $('#mngrListTable').on('click', '.delete-mngr-btn', function() {
                const mngrId = $(this).data('id');
                const mngrName = $(this).data('name');
                const row = $(this).closest('tr');
                const $button = $(this);

                if (confirm(`'${mngrName}' 관리자 정보를 정말로 삭제하시겠습니까?`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/mngrMng/ajax_delete/' + mngrId,
                        type: 'POST', // 또는 'DELETE' (라우트 설정에 따라)
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        dataType: 'json',
                        beforeSend: function() { $button.prop('disabled', true).addClass('btn-loading').text(''); },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash);
                            if (response.status === 'success') {
                                showAjaxMessage(response.message, 'success');
                                mngrTable.row(row).remove().draw(false);
                            } else {
                                showAjaxMessage(response.message || '삭제 중 오류.', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessage('서버 통신 오류로 삭제 실패.', 'danger');
                            try { const errResp = JSON.parse(xhr.responseText); if (errResp && errResp.csrf_hash) updateCsrfTokenOnPage(errResp.csrf_hash); } catch(e){}
                        },
                        complete: function() { 
                            if ($button.length) { // 버튼이 여전히 존재하면 (삭제 실패 시)
                               $button.prop('disabled', false).removeClass('btn-loading').text('삭제');
                            }
                        }
                    });
                }
            });

            $('#mngrModal').on('hidden.bs.modal', function () {
                clearMngrFormAndValidation();
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>