<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'검짐병원관리')); ?>

    <?= $this->include('partials/head-css') ?>

    <style>
        /* To make sure invalid-feedback is visible */
        .invalid-feedback {
            display: none; /* Hide by default */
            width: 100%;
            margin-top: .25rem;
            font-size: .875em;
            color: #dc3545;
        }
        .is-invalid ~ .invalid-feedback {
            display: block; /* Show when input is invalid */
        }
        /* 로딩 스피너를 위한 스타일 (선택 사항) */
        .btn-loading {
            position: relative;
            pointer-events: none; /* 클릭 방지 */
            opacity: 0.7;
        }
        .btn-loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 1rem;
            height: 1rem;
            margin-top: -0.5rem;
            margin-left: -0.5rem;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?= $this->include('partials/menu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'검짐병원관리', 'title'=>'검진병원 목록')); ?>

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
                     <!-- Placeholder for AJAX messages -->
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
                                                <!--<button type="button" class="btn btn-success" id="custom-excel-btn">
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
                                    <table id="hsptlList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>지역</th>
                                                <th>병원명</th>
                                                <th>담당자</th>
                                                <th>연락처</th>
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
                                    <h5 class="modal-title" id="exampleModalLabel">신규 병원 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form id="hsptl-form" class="tablelist-form" autocomplete="off">
                                    <div class="modal-body">
                                        <input type="hidden" id="HSPTL_SN_modal" name="HSPTL_SN_modal">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label for="RGN_modal" class="form-label">지역</label>
                                            <input type="text" id="RGN_modal" name="RGN" class="form-control" placeholder="지역을 입력하세요" required />
                                            <div class="invalid-feedback">지역을 입력해 주세요!</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="HSPTL_NM_modal" class="form-label">병원명</label>
                                            <input type="text" id="HSPTL_NM_modal" name="HSPTL_NM" class="form-control" placeholder="병원명을 입력하세요" required />
                                            <div class="invalid-feedback">병원명을 입력해 주세요!</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="PIC_NM_modal" class="form-label">담당자명</label>
                                            <input type="text" id="PIC_NM_modal" name="PIC_NM" class="form-control" placeholder="담당자명을 입력하세요" required />
                                            <div class="invalid-feedback">담당자명을 입력해 주세요!</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="CNPL1_modal" class="form-label">연락처</label>
                                            <input type="text" id="CNPL1_modal" name="CNPL1" class="form-control" placeholder="연락처를 입력하세요" required />
                                            <div class="invalid-feedback">연락처를 입력해 주세요!</div>
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
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/customizer') ?>

    <?= $this->include('partials/vendor-scripts') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
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
        let CSRF_HASH = '<?= csrf_hash() ?>'; // 페이지 로드 시 초기 CSRF 해시값
        let mainTable;

        // CSRF 토큰 값을 페이지 내 모든 CSRF 필드에 업데이트하는 함수
        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
            // AJAX 요청 시 헤더에 CSRF 토큰을 포함시킨다면 해당 부분도 업데이트 필요
            // $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': newHash } });
        }


        function initializeMainDataTable() {
            mainTable = $('#hsptlList').DataTable({
                ajax: {
                    url: BASE_URL + 'mngr/hsptlMng/ajax_list',
                    type: 'GET',
                    data: function(d) {
                        d.search_keyword = $('#search-keyword').val();
                    },
                    dataSrc: 'data',
                    dataFilter: function(data) {
                        const json = JSON.parse(data);
                        if (json.csrf_hash) updateCsrfTokenOnPage(json.csrf_hash);
                        return JSON.stringify({ data: json.data });
                    }
                },
                columns: [
                    { title: "No." },
                    { title: "지역" },
                    { title: "병원명" },
                    { title: "담당자" },
                    { title: "연락처" },
                    { title: "등록일" },
                    { title: "관리", orderable: false }
                ],
                dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-success btn-sm', exportOptions: { columns: ':visible' } }
                ],
                language: {
                    "emptyTable": "표시할 데이터가 없습니다.",
                    "info": "총 _TOTAL_개 항목 중 _START_에서 _END_까지 표시",
                    "lengthMenu": "페이지당 _MENU_ 항목 표시",
                    "search": "검색:",
                    "paginate": {
                        "first": "처음",
                        "last": "마지막",
                        "next": "다음",
                        "previous": "이전"
                    }
                },
                responsive: true,
                order: [[0, 'asc']]
            });
        }

        function clearFormAndValidation() {
            $('#hsptl-form')[0].reset();
            $('#hsptl-form .form-control').removeClass('is-invalid');
            $('#hsptl-form .invalid-feedback').hide().text('');
            $('#HSPTL_SN_modal').val(''); 
        }

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            placeholder.html(alertHtml);
            setTimeout(() => {
                placeholder.find('.alert').alert('close');
            }, 5000);
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
            // 신규 병원 등록 모달 준비
            $('#create-hsptl-btn').on('click', function() {
                clearFormAndValidation();
                $('#exampleModalLabel').text('신규 병원 등록');
                $('#add-btn').text('등록');
                $('#HSPTL_SN_modal').val('');
                // 모달 내 CSRF 필드 값 업데이트 (이미 updateCsrfTokenOnPage 에서 처리될 수 있지만, 명시적으로도 가능)
                $('#hsptl-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
            });

            // 병원 정보 수정 모달 준비
            $('#hsptlList').on('click', '.edit-hsptl-btn', function() {
                clearFormAndValidation();
                const hsptlId = $(this).data('id');
                
                $('#exampleModalLabel').text('병원 정보 수정');
                $('#add-btn').text('수정');

                $.ajax({
                    url: BASE_URL + 'mngr/hsptlMng/ajax_get_hsptl/' + hsptlId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            $('#RGN_modal').val(response.data.RGN);
                            $('#HSPTL_SN_modal').val(response.data.HSPTL_SN);
                            $('#HSPTL_NM_modal').val(response.data.HSPTL_NM);
                            $('#PIC_NM_modal').val(response.data.PIC_NM);
                            $('#CNPL1_modal').val(response.data.CNPL1);
                            
                            updateCsrfTokenOnPage(response.csrf_hash); // CSRF 업데이트
                        } else {
                            showAjaxMessage(response.message || '병원 정보를 불러오는데 실패했습니다.', 'danger');
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('병원 정보 로딩 중 오류 발생.', 'danger');
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.csrf_hash) updateCsrfTokenOnPage(errorResponse.csrf_hash);
                        } catch (e) { /* 파싱 실패 무시 */ }
                    }
                });
            });


            // 병원 등록 및 수정 폼 제출 처리
            $('#hsptl-form').on('submit', function(e) {
                e.preventDefault();
                $('#hsptl-form .form-control').removeClass('is-invalid');
                $('#hsptl-form .invalid-feedback').hide().text('');

                const hsptlId = $('#HSPTL_SN_modal').val();
                let url;
                let originalButtonText = hsptlId ? '수정' : '등록'; // 버튼 원래 텍스트
                
                const formData = {
                    RGN: $('#RGN_modal').val(),
                    HSPTL_NM: $('#HSPTL_NM_modal').val(),
                    PIC_NM: $('#PIC_NM_modal').val(),
                    CNPL1: $('#CNPL1_modal').val(),
                    [CSRF_TOKEN_NAME]: CSRF_HASH // 현재 CSRF 해시값 사용
                };

                if (hsptlId) { 
                    url = BASE_URL + 'mngr/hsptlMng/ajax_update';
                    formData.HSPTL_SN = hsptlId;
                } else { 
                    url = BASE_URL + 'mngr/hsptlMng/ajax_create';
                }
                
                $.ajax({
                    url: url,
                    type: 'POST', 
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#add-btn').prop('disabled', true).addClass('btn-loading').text(''); // 로딩 표시
                    },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash); // 응답으로 받은 새 CSRF 토큰으로 업데이트

                        if (response.status === 'success') {
                            $('#showModal').modal('hide');
                            showAjaxMessage(response.message, 'success');
                            setTimeout(function() {
                                location.reload(); // 성공 후 페이지 새로고침 (DataTables를 다시 로드하기 위함)
                            }, 1000);
                        } else if (response.status === 'fail') {
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    let fieldId = '#' + key + '_modal';
                                    if(key.toUpperCase() === 'HSPTL_SN' && !$(fieldId).length) {
                                         // HSPTL_SN 필드 에러지만 표시할 곳이 없을 경우 콘솔에 기록 또는 일반 메시지
                                    } else if ($(fieldId).length) {
                                        $(fieldId).addClass('is-invalid');
                                        $(fieldId).siblings('.invalid-feedback').text(value).show();
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
                            if (errorResponse && errorResponse.csrf_hash) updateCsrfTokenOnPage(errorResponse.csrf_hash);
                        } catch (e) { /* 파싱 실패 무시 */ }
                    },
                    complete: function() {
                        $('#add-btn').prop('disabled', false).removeClass('btn-loading').text(originalButtonText); // 로딩 해제
                    }
                });
            });

            // AJAX 삭제 처리
            $('#hsptlList').on('click', '.delete-hsptl-btn', function() {
                const hsptlId = $(this).data('id');
                const hsptlName = $(this).data('name'); // 확인 메시지에 병원명 사용
                const row = $(this).closest('tr'); // 삭제 후 테이블에서 행을 제거하기 위함
                const $button = $(this); // 버튼 참조

                if (confirm(`'${hsptlName}' 병원 정보를 정말로 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/hsptlMng/ajax_delete/' + hsptlId,
                        type: 'POST', // CodeIgniter는 _method 필드를 사용하지 않으면 POST로 처리, DELETE도 가능
                        data: {
                            [CSRF_TOKEN_NAME]: CSRF_HASH
                            // '_method': 'DELETE' // 명시적으로 DELETE 메소드를 사용하고 싶다면 추가 (라우팅 설정 필요)
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            $button.prop('disabled', true).addClass('btn-loading').text(''); // 로딩 표시
                        },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash); // CSRF 업데이트

                            if (response.status === 'success') {
                                showAjaxMessage(response.message, 'success');
                                mainTable.row(row).remove().draw(false); // DataTables에서 행 제거 (false는 페이징 유지)
                            } else {
                                showAjaxMessage(response.message || '삭제 중 오류가 발생했습니다.', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessage('서버 통신 오류로 삭제에 실패했습니다.', 'danger');
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse && errorResponse.csrf_hash) updateCsrfTokenOnPage(errorResponse.csrf_hash);
                            } catch (e) { /* 파싱 실패 무시 */ }
                        },
                        complete: function() {
                            // 버튼 로딩 상태 해제 (버튼이 사라지므로 필요 없을 수 있지만, 오류 시를 대비)
                            if ($button.length) { // 버튼이 여전히 존재하는 경우 (예: 삭제 실패)
                                $button.prop('disabled', false).removeClass('btn-loading').text('삭제');
                            }
                        }
                    });
                }
            });


            // 모달이 닫힐 때 폼 초기화
            $('#showModal').on('hidden.bs.modal', function () {
                clearFormAndValidation();
                $('#exampleModalLabel').text('신규 병원 등록'); 
                $('#add-btn').text('등록').prop('disabled', false).removeClass('btn-loading'); // 버튼 상태 초기화
            });

        });
    </script>

    <!-- App js -->
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>

</html>