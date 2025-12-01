<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => '검진상품 엑셀 관리')); ?>
    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>
        
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- 페이지 제목 -->
                    <?php echo view('partials/page-title', array(
                        'pagetitle' => '검진상품 관리', 
                        'title' => '검진상품 엑셀 목록'
                    )); ?>
                    
                    <!-- 메시지 플레이스홀더 -->
                    <div id="ajax-message-placeholder"></div>

                    <!-- 메인 컨텐츠 -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <!-- 검색 필터 영역 -->
                                <div class="card-header" style="background-color:aliceblue">
                                    <form id="filter-form" class="filter-form" onsubmit="return false;">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <!-- 검진년도 필터 -->
                                            <div class="col-md-2">
                                                <label for="ckup_yyyy_filter" class="form-label">검진년도</label>
                                                <select id="ckup_yyyy_filter" name="ckup_yyyy_filter" class="form-select">
                                                    <option value="">전체 년도</option>
                                                    <?php foreach ($years as $year): ?>
                                                        <option value="<?= $year ?>"><?= $year ?>년</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <!-- 검진병원 필터 -->
                                            <div class="col-md-2">
                                                <label for="hsptl_sn_filter" class="form-label">검진병원</label>
                                                <select id="hsptl_sn_filter" name="hsptl_sn_filter" class="form-select">
                                                    <?php if (session()->get('user_type') !== 'H'): ?>
                                                        <option value="">전체 병원</option>
                                                    <?php endif; ?>
                                                    <?php foreach ($hospitals as $hsptl): ?>
                                                        <option value="<?= esc($hsptl['HSPTL_SN']) ?>">
                                                            <?= esc($hsptl['HSPTL_NM']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            

                                            
                                            <!-- 검색/초기화 버튼 -->
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">
                                                    검색
                                                </button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">
                                                    초기화
                                                </button>
                                            </div>

                                            <!-- 신규 등록 버튼 -->
                                            <div class="col d-flex justify-content-end gap-2">
                                                <a href="<?= base_url('mngr/ckupGdsExcel/add') ?>" class="btn btn-outline-primary">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- 데이터 테이블 영역 -->
                                <div class="card-body">
                                    <table id="mngrListTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>검진년도</th>
                                                <th>검진병원</th>
                                                <th>검진상품</th>
                                                <th>지원구분</th>
                                                <th>가족지원금</th>
                                                <th>등록일자</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- 데이터는 서버사이드에서 채워집니다. -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?= $this->include('partials/footer') ?>
        </div>
    </div>

    <!-- 스크립트 영역 -->
    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>
    
    <!-- jQuery 및 DataTables 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

    <script>
        // 전역 변수 설정
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>';

        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
        }

        $(document).ready(function() {
            // DataTables 초기화
            const mngrTable = $('#mngrListTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: BASE_URL + 'mngr/ckupGdsExcel/ajax_list',
                    type: 'POST',
                    data: function(d) {
                        d[CSRF_TOKEN_NAME] = CSRF_HASH;
                        d.ckup_yyyy = $('#ckup_yyyy_filter').val();
                        d.hsptl_sn = $('#hsptl_sn_filter').val();

                    },
                    dataSrc: function(json) {
                        updateCsrfTokenOnPage(json.csrf_hash);
                        return json.data;
                    }
                },
                columns: [
                    { data: 'no' },
                    { data: 'ckup_yyyy' },
                    { data: 'hsptl_nm' },
                    { data: 'ckup_gds_nm' },
                    { data: 'sprt_se' },
                    { data: 'fam_sprt_se' },
                    { data: 'reg_ymd' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                language: {
                    "emptyTable": "데이터가 없습니다.",
                    "lengthMenu": "페이지당 _MENU_ 개 보기",
                    "info": "현재 _START_ - _END_ / _TOTAL_건",
                    "infoEmpty": "데이터 없음",
                    "infoFiltered": "( _MAX_건의 데이터에서 필터링됨 )",
                    "search": "검색: ",
                    "zeroRecords": "일치하는 데이터가 없습니다.",
                    "loadingRecords": "로딩 중...",
                    "processing": "처리 중...",
                    "paginate": {
                        "next": "다음",
                        "previous": "이전"
                    }
                },
                searching: false,
                responsive: true,
                lengthChange: false,
                pageLength: 10
            });

            $('#btn-filter-search').on('click', function() {
                mngrTable.draw();
            });

            $('#btn-filter-reset').on('click', function() {
                $('#filter-form')[0].reset();
                mngrTable.draw();
            });

            $(document).on('click', '.delete-item-btn', function() {
                const id = $(this).data('id');
                
                if (confirm('정말로 이 상품을 삭제하시겠습니까? 관련 데이터가 모두 삭제됩니다.')) {
                    deleteCheckupProduct(id);
                }
            });
        });

        function deleteCheckupProduct(id) {
            $.ajax({
                url: BASE_URL + 'mngr/ckupGdsExcel/delete/' + id,
                type: 'POST',
                data: {
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('삭제가 완료되었습니다.');
                        $('#mngrListTable').DataTable().draw();
                    } else {
                        alert('삭제 중 오류가 발생했습니다: ' + response.message);
                    }
                    if (response.csrf_hash) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                    }
                },
                error: function() {
                    alert('서버 오류가 발생했습니다.');
                }
            });
        }
    </script>
    
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>
</body>
</html>
