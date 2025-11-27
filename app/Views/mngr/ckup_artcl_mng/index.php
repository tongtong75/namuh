<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'검진항목관리')); ?>
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

                    <?php echo view('partials/page-title', array('pagetitle'=>'검진항목관리', 'title'=>'검진항목 목록')); ?>

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
                                            <div class="col-md-auto">
                                                <label for="ckup-type-filter" class="form-label">검사유형</label>
                                                <select id="ckup-type-filter" name="ckup_type_filter" class="form-select">
                                                    <option value="">전체</option>
                                                    <option value="ET">기타</option>
                                                    <option value="CS">대장내시경</option>
                                                    <option value="GS">위내시경</option>
                                                    <option value="BU">유방초음파</option>
                                                    <option value="PU">골반초음파</option>
                                                    <option value="UT">초음파</option>
                                                    <option value="CT">CT</option>
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
                                    <table id="ckupArtclList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>검진구분</th>
                                                <th>검진항목</th>
                                                <th>항목코드</th>
                                                <th>검진병원</th>
                                                <th>검사유형</th>
                                                <th>검사비용</th>
                                                <th>성별</th>
                                                <th>질환</th>
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
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel">신규 항목 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                               
                                <form id="item-form" class="tablelist-form" method="post" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="CKUP_ARTCL_SN_modal" name="CKUP_ARTCL_SN"> 
                                        <?= csrf_field() ?>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="CKUP_SE_modal" class="form-label">검진구분</label>
                                                <input type="text" id="CKUP_SE_modal" name="CKUP_SE" class="form-control" placeholder="검진구분을 입력하세요" required />
                                                <div class="invalid-feedback">검진구분을 입력해 주세요.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="CKUP_ARTCL_modal" class="form-label">검진항목</label>
                                                <input type="text" id="CKUP_ARTCL_modal" name="CKUP_ARTCL" class="form-control" placeholder="검진항목명을 입력하세요" required />
                                                <div class="invalid-feedback">검진항목명을 입력해 주세요.</div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ARTCL_CODE_modal" class="form-label">항목코드</label>
                                                <input type="text" id="ARTCL_CODE_modal" name="ARTCL_CODE" class="form-control" placeholder="항목코드를 입력하세요" />
                                                <div class="invalid-feedback">항목코드를 입력해 주세요.</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
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
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="CKUP_TYPE_modal" class="form-label">검사유형</label>
                                                <select id="CKUP_TYPE_modal" name="CKUP_TYPE" class="form-select">
                                                    <option value="">선택하세요</option>
                                                    <option value="ET">기타</option>
                                                    <option value="CS">대장내시경</option>
                                                    <option value="GS">위내시경</option>
                                                    <option value="BU">유방초음파</option>
                                                    <option value="PU">골반초음파</option>
                                                    <option value="UT">초음파</option>
                                                    <option value="CT">CT</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="CKUP_CST_modal" class="form-label">검사비용</label>
                                                <input type="text" id="CKUP_CST_modal" name="CKUP_CST" class="form-control" placeholder="비용을 입력하세요" required />
                                                <div class="invalid-feedback">검사비용을 입력해 주세요.</div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">성별구분</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="GNDR_SE" id="gender_c" value="C" checked>
                                                        <label class="form-check-label" for="gender_c">공통</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="GNDR_SE" id="gender_m" value="M">
                                                        <label class="form-check-label" for="gender_m">남성</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="GNDR_SE" id="gender_f" value="F">
                                                        <label class="form-check-label" for="gender_f">여성</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="DSS_modal" class="form-label">질환</label>
                                                <input type="text" id="DSS_modal" name="DSS" class="form-control" placeholder="관련 질환명을 입력하세요 (선택)" />
                                                <div class="invalid-feedback">질환명을 확인해 주세요.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="RMRK_modal" class="form-label">비고</label>
                                            <textarea id="RMRK_modal" name="RMRK" class="form-control" rows="2" placeholder="비고를 입력하세요"></textarea>
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
        let mainTable;

        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
        }

        function initializeMainDataTable() {
            if (!$.fn.dataTable.isDataTable('#ckupArtclList')) {
                mainTable = new DataTable('#ckupArtclList', {
                    serverSide: false, // 클라이언트 사이드 처리
                    processing: true,
                    ajax: {
                        url: BASE_URL + 'mngr/ckupArtclMng/ajax_list',
                        type: 'POST',
                        data: function (d) {
                            d[CSRF_TOKEN_NAME] = CSRF_HASH;
                            d.hsptl_sn = $('#hospital-filter').val();
                            d.ckup_type = $('#ckup-type-filter').val(); // Add this line
                            d.search_keyword = $('#search-keyword').val();
                        },
                        dataSrc: function (json) {
                            updateCsrfTokenOnPage(json.csrf_hash); // 갱신
                            return json.data;
                        },
                        error: function (xhr) {
                            console.error('서버 에러:', xhr.responseText);
                        }
                    },
                    columns: [
                        { data: 'no' },
                        { data: 'CKUP_SE' }, // 병합 대상 열 (인덱스 1)
                        { data: 'CKUP_ARTCL' },
                        { data: 'ARTCL_CODE', className: 'text-center' },
                        { data: 'HSPTL_NM', className: 'text-center' },
                        { 
                            data: 'CKUP_TYPE',
                            className: 'text-center',
                            render: function(data, type, row) {
                                const types = {
                                    'ET': '기타', 'CS': '대장내시경', 'GS': '위내시경',
                                    'BU': '유방초음파', 'PU': '골반초음파', 'UT': '초음파', 'CT': 'CT'
                                };
                                return types[data] || data || '-';
                            }
                        },
                        { data: 'CKUP_CST', className: 'text-end' },
                        { 
                            data: 'GNDR_SE',
                            className: 'text-center',
                            render: function(data, type, row) {
                                if (data === 'M') return '남성';
                                if (data === 'F') return '여성';
                                return '공통';
                            }
                        },
                        { data: 'DSS' },
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
                    // '검진구분' 열(인덱스 1)을 기준으로 오름차순 정렬, 그 다음 'No.' 열(인덱스 0) 기준으로 내림차순 정렬
                    ordering: false, // 정렬 비활성화
                    columnDefs: [
                        { targets: 2, width: '150px' }, // 검진항목 열 너비 고정
                        { targets: 8, width: '150px' }  // 질환 열 너비 고정
                    ],
                    responsive: false, // 반응형 비활성화
                    scrollX: true,     // 가로 스크롤 활성화
                    autoWidth: false,
                    drawCallback: function (settings) {
                        var api = this.api();
                        var rows = api.rows({ page: 'current' }).nodes();
                        var last = null;
                        var groupColumnIndex = 1; // 'CKUP_SE' (검진구분) 열의 인덱스

                        api.column(groupColumnIndex, { page: 'current' })
                            .data()
                            .each(function (group, i) {
                                var currentRow = $(rows).eq(i);
                                var currentCell = currentRow.find('td').eq(groupColumnIndex);

                                if (last !== group) {
                                    // 새로운 그룹 시작
                                    currentCell.attr('rowspan', 1)
                                               .css('vertical-align', 'middle') // 세로 중앙 정렬 (선택 사항)
                                               .show();
                                    last = group;
                                } else {
                                    // 이전 그룹과 동일한 경우, 현재 행의 셀 숨기기
                                    currentCell.hide();

                                    // 그룹의 첫 번째 셀을 찾아 rowspan 증가
                                    // 현재 행(i)부터 위로 올라가면서 보이는 셀(그룹의 첫 번째 셀)을 찾습니다.
                                    for (let j = i - 1; j >= 0; j--) {
                                        let $previousRowVisibleCell = $(rows).eq(j).find('td').eq(groupColumnIndex);
                                        if ($previousRowVisibleCell.is(':visible')) {
                                            // ✅ 수정된 부분
                                            let currentRowspan = parseInt($previousRowVisibleCell.attr('rowspan') || 1);
                                            $previousRowVisibleCell.attr('rowspan', currentRowspan + 1);
                                            break; // 찾았으면 반복 중단
                                        }
                                    }
                                }
                            });
                    }
                });
            }
        }

        function clearFormAndValidation() {
            $('#item-form').trigger('reset');
            $('#item-form .form-control').removeClass('is-invalid');
            $('#item-form .invalid-feedback').hide().text('');
            $('#CKUP_ARTCL_SN_modal').val('');
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
                $('#exampleModalLabel').text('신규 항목 등록');
                $('#add-btn').text('등록');
                $('#CKUP_ARTCL_SN_modal').val('');
                $('input[name="GNDR_SE"][value="C"]').prop('checked', true);
                $('#item-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
            });

            $('#ckupArtclList').on('click', '.edit-item-btn', function() {
                clearFormAndValidation();
                const itemId = $(this).data('id');

                $('#exampleModalLabel').text('항목 정보 수정');
                $('#add-btn').text('수정');

                $.ajax({
                    url: BASE_URL + 'mngr/ckupArtclMng/ajax_get_ckup_artcl/' + itemId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data) {
                            $('#CKUP_ARTCL_SN_modal').val(response.data.CKUP_ARTCL_SN);
                            $('#CKUP_SE_modal').val(response.data.CKUP_SE);
                            $('#ARTCL_CODE_modal').val(response.data.ARTCL_CODE);
                            $('#CKUP_ARTCL_modal').val(response.data.CKUP_ARTCL);
                            $('#HSPTL_SN_modal').val(response.data.HSPTL_SN);
                            $('#DSS_modal').val(response.data.DSS);
                            $('#CKUP_TYPE_modal').val(response.data.CKUP_TYPE);
                            $('#CKUP_CST_modal').val(response.data.CKUP_CST);
                            $('#RMRK_modal').val(response.data.RMRK);
                            
                            // 성별 라디오 버튼 설정
                            const gender = response.data.GNDR_SE || 'C';
                            $(`input[name="GNDR_SE"][value="${gender}"]`).prop('checked', true);

                            updateCsrfTokenOnPage(response.csrf_hash);
                        } else {
                            showAjaxMessage(response.message || '항목 정보를 불러오는데 실패했습니다.', 'danger');
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessage('항목 정보 로딩 중 오류 발생.', 'danger');
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

                const itemId = $('#CKUP_ARTCL_SN_modal').val();
                let url;
                let originalButtonText = itemId ? '수정' : '등록';

                const formData = {
                    CKUP_SE: $('#CKUP_SE_modal').val(),
                    ARTCL_CODE: $('#ARTCL_CODE_modal').val(),
                    CKUP_ARTCL: $('#CKUP_ARTCL_modal').val(),
                    HSPTL_SN: $('#HSPTL_SN_modal').val(),
                    DSS: $('#DSS_modal').val(),
                    CKUP_TYPE: $('#CKUP_TYPE_modal').val(),
                    CKUP_CST: $('#CKUP_CST_modal').val(),
                    RMRK: $('#RMRK_modal').val(),
                    GNDR_SE: $('input[name="GNDR_SE"]:checked').val(),
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                };

                if (itemId) {
                    url = BASE_URL + 'mngr/ckupArtclMng/ajax_update';
                    formData.CKUP_ARTCL_SN = itemId;
                } else {
                    url = BASE_URL + 'mngr/ckupArtclMng/ajax_create';
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
                            setTimeout(function() { location.reload(); }, 1000);
                        } else if (response.status === 'fail') {
                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    let fieldId = '#' + key + '_modal';
                                    if ($(fieldId).length) {
                                        $(fieldId).addClass('is-invalid');
                                        $(fieldId).siblings('.invalid-feedback').text(value).show();
                                    } else {
                                        console.warn("Validation error: ", key, value);
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

            $('#ckupArtclList').on('click', '.delete-item-btn', function() {
                const itemId = $(this).data('id');
                const itemName = $(this).data('name');
                const $rowElement = $(this).closest('tr');
                const $button = $(this);

                if (confirm(`'${itemName}' 항목 정보를 정말로 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupArtclMng/ajax_delete/' + itemId,
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

                                // ✅ 안전하게 삭제: DataTables row 객체를 찾아서 삭제
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
                $('#exampleModalLabel').text('신규 항목 등록'); 
                $('#add-btn').text('등록').prop('disabled', false).removeClass('btn-loading');
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>