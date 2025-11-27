<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'요일별 검진 인원 관리')); ?>

    <?= $this->include('partials/head-css') ?>

</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php echo view('partials/page-title', array('pagetitle'=>'검진 관리', 'title'=>'요일별 검진 인원 관리')); ?>

                    <div id="ajax-message-placeholder"></div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <form id="filter-form" class="mt-2 filter-form" onsubmit="return false;">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <div class="col-md-2">
                                                <label for="hospital-filter" class="form-label">검진병원</label>
                                                <select id="hospital-filter" name="hsptl_sn" class="form-select">
                                                    <?php if (session()->get('user_type') !== 'H'): ?>
                                                        <option value="">전체병원</option>
                                                    <?php endif; ?>
                                                    <?php foreach ($hospitals as $hospital): ?>
                                                        <option value="<?= esc($hospital['HSPTL_SN']) ?>"><?= esc($hospital['HSPTL_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="year-filter" class="form-label">검진년도</label>
                                                <select id="year-filter" name="ckup_year" class="form-select">
                                                    <option value="">전체년도</option>
                                                    <?php 
                                                        $currentYear = date('Y');
                                                        for ($i = $currentYear + 1; $i >= $currentYear - 5; $i--): 
                                                    ?>
                                                        <option value="<?= $i ?>"><?= $i ?>년</option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">초기화</button>
                                            </div>
                                            <div class="col d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-primary" id="create-item-btn">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <table id="dayCkupMngList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>검진년도</th>
                                                <th>병원명</th>
                                                <th>요일 구분</th>
                                                <th>설정 항목</th>
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
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <div class="modal-body" id="modal-form-content">
                                    <!-- Form content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="modal fade" id="showDetailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel">설정 상세</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>검사구분</th>
                                                <th>요일</th>
                                                <th>인원수</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detail-table-body">
                                            <!-- Details will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        let mainTable;

        function showAjaxMessage(message, type = 'success') {
            const placeholder = $('#ajax-message-placeholder');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
            placeholder.html(alertHtml);
            setTimeout(() => { placeholder.find('.alert').alert('close'); }, 5000);
        }

        function initializeMainDataTable() {
            mainTable = new DataTable('#dayCkupMngList', {
                processing: true,
                serverSide: false, // Client-side processing
                ajax: {
                    url: BASE_URL + 'mngr/dayCkupMng/getList',
                    type: 'GET',
                    data: function (d) {
                        d.hsptl_sn = $('#hospital-filter').val();
                        d.ckup_year = $('#year-filter').val();
                    },
                    dataSrc: function (json) {
                        if(json.success) {
                            // Add a sequential number to each row
                            json.list.forEach((item, index) => {
                                item.no = index + 1;
                            });
                            return json.list;
                        } else {
                            showAjaxMessage('데이터를 불러오는데 실패했습니다.', 'danger');
                            return [];
                        }
                    }
                },
                columns: [
                    { data: 'no' },
                    { data: 'ckup_year' },
                    { data: 'HSPTL_NM' },
                    { 
                        data: 'DAY_CKUP_SE',
                        render: function(data) {
                            return data === 'WEEKDAY' ? '평일' : '토요일';
                        }
                    },
                    {
                        data: 'item_summary'
                    },
                    {
                        data: 'reg_ymd',
                        render: function(data) {
                            return data ? data.substring(0, 10) : '-';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `<button class="btn btn-sm btn-outline-secondary edit-item-btn" data-year="${row.ckup_year}" data-hsptl="${row.HSPTL_SN}" data-dayse="${row.DAY_CKUP_SE}">수정</button>
                                    <button class="btn btn-sm btn-outline-danger delete-item-btn" data-year="${row.ckup_year}" data-hsptl="${row.HSPTL_SN}" data-dayse="${row.DAY_CKUP_SE}">삭제</button>`;
                        }
                    }
                ],
                language: {
                    emptyTable: "표시할 데이터가 없습니다.",
                    info: "총 _TOTAL_개 항목",
                    infoEmpty: "",
                    processing: "처리 중...",
                    zeroRecords: "일치하는 레코드를 찾을 수 없습니다.",
                    paginate: { next: "다음", previous: "이전" }
                },
                order: [[1, 'desc'], [2, 'asc']]
            });
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

            

            function getCheckupTypeText(ckupSe) {
                switch(ckupSe) {
                    case 'ET': return '기타';
                    case 'CT': return 'CT';
                    case 'GS': return '위내시경';
                    case 'UT': return '초음파';
                    case 'CS': return '대장내시경';
                    default: return ckupSe;
                }
            }

            

            // 신규 등록 버튼 클릭
            $('#create-item-btn').on('click', function() {
                $('#exampleModalLabel').text('요일별 인원 신규 등록');
                $('#modal-form-content').load(BASE_URL + 'mngr/dayCkupMng/form', function() {
                    $('#showModal').modal('show');
                });
            });

            // 수정 버튼 클릭
            $('#dayCkupMngList').on('click', '.edit-item-btn', function() {
                const hsptl_sn = $(this).data('hsptl');
                const ckup_year = $(this).data('year');
                
                $('#exampleModalLabel').text(ckup_year + '년 설정 수정');
                
                const url = BASE_URL + `mngr/dayCkupMng/form?hsptl_sn=${hsptl_sn}&ckup_year=${ckup_year}`;

                $('#modal-form-content').load(url, function(response, status, xhr) {
                    if (status == "error") {
                        showAjaxMessage("양식을 불러오는데 실패했습니다.", "danger");
                    } else {
                        $('#showModal').modal('show');
                    }
                });
            });

            // 삭제 버튼 클릭
            $('#dayCkupMngList').on('click', '.delete-item-btn', function() {
                const hsptl_sn = $(this).data('hsptl');
                const ckup_year = $(this).data('year');
                const day_se = $(this).data('dayse');
                const day_se_kor = day_se === 'WEEKDAY' ? '평일' : '토요일';

                if (confirm(`${ckup_year}년, ${day_se_kor} 설정을 모두 삭제하시겠습니까?`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/dayCkupMng/delete',
                        type: 'POST',
                        data: {
                            hsptl_sn: hsptl_sn,
                            ckup_year: ckup_year,
                            day_se: day_se,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAjaxMessage(response.message, 'success');
                                mainTable.ajax.reload();
                            } else {
                                showAjaxMessage(response.message || '삭제에 실패했습니다.', 'danger');
                            }
                        },
                        error: function() {
                            showAjaxMessage('서버 통신 중 오류가 발생했습니다.', 'danger');
                        }
                    });
                }
            });
        });
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>