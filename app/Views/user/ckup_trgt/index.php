<?= $this->include('partials/main') ?>

<head>

    <?php echo view('partials/title-meta', array('title'=>'인사등록')); ?>


    <?= $this->include('partials/head-css') ?>
    <style>
        /* 기존 스타일 유지 */
        .invalid-feedback { display: none; width: 100%; margin-top: .25rem; font-size: .875em; color: #dc3545; }
        .is-invalid ~ .invalid-feedback { display: block; }
        .btn-loading { position: relative; pointer-events: none; opacity: 0.7; }
        .btn-loading::after { content: ""; position: absolute; top: 50%; left: 50%; width: 1rem; height: 1rem; margin-top: -0.5rem; margin-left: -0.5rem; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .name-col-width { min-width: 120px; } /* 이름 컬럼 너비 */
        .action-col-width { min-width: 180px; } /* 관리 컬럼 너비 */

        /* 메모 팝오버용 */
        .popover-header { font-weight: bold; }
        .popover-body { white-space: pre-wrap; word-break: break-all; max-height: 200px; overflow-y: auto; }
        .table-responsive { overflow-x: auto; }

        /* 필터 영역 스타일 */
        .filter-form .row > div { margin-bottom: 0.5rem; }
        .filter-form .form-label { margin-bottom: 0.25rem; font-size: 0.875rem;}
        .filter-form .form-control, .filter-form .form-select { font-size: 0.875rem; padding: 0.375rem 0.75rem;}

        /* Custom Alert Container Style */
        #ajax-message-placeholder {
            position: fixed;
            top: 70px; /* Adjust based on your header height */
            right: 20px;
            z-index: 1060; /* Higher than modals (Bootstrap modal z-index is 1050-1055) */
            width: 350px;
            max-width: 90%;
        }
        #ajax-message-placeholder .alert {
            margin-bottom: 10px;
        }

    </style>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/userMenu') ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= view('partials/page-title', ['pagetitle' => '인사등록', 'title' => '검진대상자 목록']) ?>

                    <div id="ajax-message-placeholder"></div> <!-- AJAX 메시지 표시 위치 -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <form id="filter-form" class="mt-2 filter-form">
                                        <div class="row gx-2 gy-2 align-items-end">
                                            <!-- 필터 영역 -->
                                            <div class="col-md-1">
                                                <label for="co_sn_filter" class="form-label">회사</label>
                                                <select id="co_sn_filter" name="co_sn_filter" class="form-select" <?= !empty($userCoSn) ? 'disabled' : '' ?>>
                                                    <option value="">전체 회사</option>
                                                    <?php foreach ($companies as $company): ?>
                                                        <option value="<?= esc($company['CO_SN']) ?>" <?= (!empty($userCoSn) && $userCoSn == $company['CO_SN']) ? 'selected' : '' ?>><?= esc($company['CO_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="ckup_yyyy_filter" class="form-label">검진년도</label>
                                                <select id="ckup_yyyy_filter" name="ckup_yyyy_filter" class="form-select">
                                                    <option value="">전체 년도</option>
                                                    <?php foreach ($years as $year): ?>
                                                        <option value="<?= $year ?>"><?= $year ?>년</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="relation_filter" class="form-label">관계</label>
                                                <select id="relation_filter" name="relation_filter" class="form-select">
                                                    <option value="">전체</option>
                                                    <option value="S">본인</option>
                                                    <option value="W">배우자</option>
                                                    <option value="C">자녀</option>
                                                    <option value="P">부모</option>
                                                    <option value="O">기타</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="ckup_name_filter" class="form-label">수검자명</label>
                                                <input type="text" id="ckup_name_filter" name="ckup_name_filter" class="form-control" placeholder="이름 검색">
                                            </div>
                                            <div class="col-md-1">
                                                <label for="name_filter" class="form-label">직원명</label>
                                                <input type="text" id="name_filter" name="name_filter" class="form-control" placeholder="이름 검색">
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-search" class="btn btn-primary w-100">검색</button>
                                            </div>
                                            <div class="col-md-auto">
                                                <button type="button" id="btn-filter-reset" class="btn btn-secondary w-100">초기화</button>
                                            </div>

                                            <!-- 오른쪽 끝 버튼 묶음 -->
                                            <div class="col d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" id="create-item-btn" data-bs-target="#mainModal">
                                                    <i class="ri-add-line align-bottom me-1"></i> 신규 등록
                                                </button>
                                            </div>
                                        </div>
                                        
                                    </form>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="ckupTrgtList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>회사명</th>
                                                    <th>검진년도</th>
                                                    <th>사번</th>
                                                    <th>관계</th>
                                                    <th>수검자명</th>
                                                    <th class="name-col-width">직원명</th>
                                                    <th>성별</th>
                                                    <th>생년월일</th>
                                                    <th>핸드폰</th>
                                                    <th>메모</th>
                                                    <th class="action-col-width">관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->

                    <!-- 대상자 등록/수정 모달 -->
                    <div class="modal fade" id="mainModal" tabindex="-1" aria-labelledby="mainModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="mainModalLabel">신규 대상자 등록</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-main-modal"></button>
                                </div>
                                <form id="main-item-form" class="tablelist-form" method="post" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="CKUP_TRGT_SN_modal" name="CKUP_TRGT_SN">
                                        <?= csrf_field() ?>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="CO_SN_modal" class="form-label">회사 <span class="text-danger">*</span></label>
                                                <select id="CO_SN_modal" name="CO_SN" class="form-select" required <?= !empty($userCoSn) ? 'disabled' : '' ?>>
                                                    <option value="">회사를 선택하세요</option>
                                                    <?php foreach ($companies as $company): ?>
                                                        <option value="<?= esc($company['CO_SN']) ?>" <?= (!empty($userCoSn) && $userCoSn == $company['CO_SN']) ? 'selected' : '' ?>><?= esc($company['CO_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">회사를 선택해주세요.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="CKUP_YYYY_modal" class="form-label">검진년도 <span class="text-danger">*</span></label>
                                                <input type="number" id="CKUP_YYYY_modal" name="CKUP_YYYY" class="form-control" placeholder="YYYY" required min="1900" max="2100" value="<?= date('Y') ?>">
                                                <div class="invalid-feedback">검진년도를 입력해주세요.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="BUSINESS_NUM_modal" class="form-label">사번 <span class="text-danger">*</span></label>
                                                <input type="text" id="BUSINESS_NUM_modal" name="BUSINESS_NUM" class="form-control" required placeholder="사번을 입력하세요">
                                                <div class="invalid-feedback">사번을 입력해주세요.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="NAME_modal" class="form-label">직원명 <span class="text-danger">*</span></label>
                                                <input type="text" id="NAME_modal" name="NAME" class="form-control" placeholder="성명을 입력하세요" required>
                                                <div class="invalid-feedback">성명을 입력해주세요.</div>
                                            </div>
                                            <!-- Password field removed -->
                                            
                                        </div>
                                        <div class="row">
                                            
                                            <div class="col-md-3 mb-3">
                                                <label for="CKUP_NAME_modal" class="form-label">수검자명 <span class="text-danger">*</span></label>
                                                <input type="text" id="CKUP_NAME_modal" name="CKUP_NAME" class="form-control" placeholder="수검자명을 입력하세요" required>
                                                <div class="invalid-feedback">수검자명을 입력해주세요.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="BIRTHDAY_modal" class="form-label">생년월일 <span class="text-danger">*</span></label>
                                                <input type="text" id="BIRTHDAY_modal" name="BIRTHDAY" class="form-control" placeholder="YYMMDD 형식 (예: 800101)" required>
                                                <div class="invalid-feedback">생년월일을 YYMMDD 형식으로 입력해주세요.</div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="HANDPHONE_modal" class="form-label">핸드폰번호 <span class="text-danger">*</span></label>
                                                <input type="text" id="HANDPHONE_modal" name="HANDPHONE" class="form-control" placeholder="010-1234-5678" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">성별 <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3 mt-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="SEX" id="SEX_M" value="M">
                                                        <label class="form-check-label" for="SEX_M">남</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="SEX" id="SEX_F" value="F">
                                                        <label class="form-check-label" for="SEX_F">여</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">관계 <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3 mt-1">
                                                    <div class="form-check" id="relation-self-wrapper">
                                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_S" value="S">
                                                        <label class="form-check-label" for="RELATION_S">본인</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_W" value="W">
                                                        <label class="form-check-label" for="RELATION_W">배우자</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_C" value="C">
                                                        <label class="form-check-label" for="RELATION_C">자녀</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_P" value="P">
                                                        <label class="form-check-label" for="RELATION_P">부모</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="RELATION" id="RELATION_O" value="O">
                                                        <label class="form-check-label" for="RELATION_O">기타</label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                        </div>
                                        <!-- Checkup Target, Reservation Status, Checkup Status removed -->
                                        <!-- Employment Status, Job Title, Department removed -->
                                        

                                        <div class="mb-3">
                                            <label for="MEMO_modal_main" class="form-label">메모</label>
                                            <textarea id="MEMO_modal_main" name="MEMO_modal_main" class="form-control" rows="3" placeholder="대상자에 대한 메모를 입력하세요. (등록/수정 시 함께 저장)"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                        <button type="submit" class="btn btn-primary" id="main-add-btn">등록</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /대상자 등록/수정 모달 -->

                    <!-- 메모 관리 모달 -->
                    <div class="modal fade" id="memoModal" tabindex="-1" aria-labelledby="memoModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="memoModalLabel">메모 관리</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-memo-modal"></button>
                                </div>
                                <form id="memo-form" method="post" onsubmit="return false;">
                                    <div class="modal-body">
                                        <input type="hidden" id="CKUP_TRGT_SN_memo" name="CKUP_TRGT_SN_memo">
                                        <input type="hidden" id="CKUP_TRGT_MEMO_SN_memo" name="CKUP_TRGT_MEMO_SN_memo">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label for="MEMO_modal" class="form-label">메모 내용</label>
                                            <textarea class="form-control" id="MEMO_modal" name="MEMO_modal" rows="5" placeholder="메모를 입력하세요."></textarea>
                                            <div class="invalid-feedback">메모 내용을 입력해주세요.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                            <button type="button" class="btn btn-danger me-auto" id="delete-memo-btn" style="display:none;">메모 삭제</button>
                                            <button type="submit" class="btn btn-success" id="save-memo-btn">저장</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /메모 관리 모달 -->
                    <!-- 파일 업로드 관리 모달 -->
                    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="fileModalLabel">엑셀업로드</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-memo-modal"></button>
                                </div>
                                <form id="file-form" method="post" enctype="multipart/form-data" onsubmit="return false;">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <select id="fileModal_co_sn" name="fileModal_co_sn" class="form-select">
                                                    <option value="">전체 회사</option>
                                                    <?php foreach ($companies as $company): ?>
                                                        <option value="<?= esc($company['CO_SN']) ?>"><?= esc($company['CO_NM']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>   
                                            <div class="col-md-6 mb-3">
                                                <select id="fileModal_ckup_yyyy" name="fileModal_ckup_yyyy" class="form-select">
                                                    <option value="">전체 년도</option>
                                                    <?php foreach ($years as $year): ?>
                                                        <option value="<?= $year ?>"><?= $year ?>년</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div> 
                                        </div>   
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <input type="file" class="filepond filepond-input-multiple" multiple id="excel_file" name="excel_file" data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3">
                                            </div> 
                                        </div>         
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
                                            <button type="submit" class="btn btn-success" id="save-file-btn">저장</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /파일 업로드 모달 -->

                </div><!-- container-fluid -->
            </div>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>

<!-- Family Registration Modal -->
<div class="modal fade" id="familyModal" tabindex="-1" aria-labelledby="familyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm"> <!-- Use modal-lg for vertical layout -->
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="familyModalLabel">가족 등록</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="family-item-form" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="CKUP_TRGT_SN_modal_family" name="CKUP_TRGT_SN">
                    <input type="hidden" id="CO_SN_modal_family" name="CO_SN">
                    <input type="hidden" id="CKUP_YYYY_modal_family" name="CKUP_YYYY">
                    <input type="hidden" id="BUSINESS_NUM_modal_family" name="BUSINESS_NUM">
                    <input type="hidden" id="NAME_modal_family" name="NAME">
                    <?= csrf_field() ?>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="CKUP_NAME_modal_family" class="form-label">수검자명 <span class="text-danger">*</span></label>
                            <input type="text" id="CKUP_NAME_modal_family" name="CKUP_NAME" class="form-control" placeholder="수검자명" required>
                            <div class="invalid-feedback">수검자명을 입력해주세요.</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="BIRTHDAY_modal_family" class="form-label">생년월일 <span class="text-danger">*</span></label>
                            <input type="text" id="BIRTHDAY_modal_family" name="BIRTHDAY" class="form-control" placeholder="YYMMDD" required>
                            <div class="invalid-feedback">생년월일을 입력해주세요.</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">관계 <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <!-- Self option hidden for family modal -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="RELATION" id="RELATION_W_family" value="W" required>
                                    <label class="form-check-label" for="RELATION_W_family">배우자</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="RELATION" id="RELATION_C_family" value="C">
                                    <label class="form-check-label" for="RELATION_C_family">자녀</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="RELATION" id="RELATION_P_family" value="P">
                                    <label class="form-check-label" for="RELATION_P_family">부모</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="RELATION" id="RELATION_O_family" value="O">
                                    <label class="form-check-label" for="RELATION_O_family">기타</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">성별 <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="SEX" id="SEX_M_family" value="M" required>
                                    <label class="form-check-label" for="SEX_M_family">남</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="SEX" id="SEX_F_family" value="F">
                                    <label class="form-check-label" for="SEX_F_family">여</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="HANDPHONE_modal_family" class="form-label">핸드폰번호 <span class="text-danger">*</span></label>
                            <input type="text" id="HANDPHONE_modal_family" name="HANDPHONE" class="form-control" placeholder="010-1234-5678" required>
                            <div class="invalid-feedback">핸드폰번호를 입력해주세요.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-primary" id="family-add-btn">등록</button>
                </div>
            </form>
        </div>
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
        let mainDataTable;

        // Custom Alert Function
        function showCustomAlert(message, type = 'success', duration = 5000) {
            const alertContainer = $('#ajax-message-placeholder');
            if (!alertContainer.length) {
                console.error('Custom alert container (#ajax-message-placeholder) not found.');
                // Fallback to a simple browser alert if container is missing
                window.alert((type.toUpperCase() + ": " + message));
                return;
            }

            let alertClass = '';
            switch (type) {
                case 'error':
                    alertClass = 'alert-danger';
                    break;
                case 'warning':
                    alertClass = 'alert-warning';
                    break;
                case 'info':
                    alertClass = 'alert-info';
                    break;
                case 'success':
                default:
                    alertClass = 'alert-success';
                    break;
            }

            const alertId = 'custom-alert-' + new Date().getTime();
            const alertHtml = `
                <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            alertContainer.append(alertHtml);

            const $newAlert = $('#' + alertId);
            if (duration > 0) { // Only set timeout if duration is positive
                setTimeout(() => {
                    $newAlert.fadeOut(500, function() { $(this).remove(); });
                }, duration);
            }
        }


        function updateCsrfTokenOnPage(newHash) {
            CSRF_HASH = newHash;
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(newHash);
        }

        function clearFormAndValidation(formId) {
            const formElement = $('#' + formId);
            formElement.trigger('reset'); // Resets form fields to their initial values
            formElement.find('.form-control, .form-select').removeClass('is-invalid');
            formElement.find('.invalid-feedback').hide().text('');
            // Ensure selects are reset to their first option (often the placeholder with value="")
            formElement.find('select.form-select').each(function() {
                 $(this).val($(this).find('option:first').val());
            });
            // Reset radio buttons
            formElement.find('input[type="radio"]').prop('checked', false);
        }

        function showAjaxMessageGlobal(message, type = 'success') {
            showCustomAlert(message, type);
        }

        // 팝오버 초기화 (DataTables drawCallback 등에서 호출)
        function initializePopovers() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                var existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
                if (existingPopover) {
                    existingPopover.dispose();
                }
                return new bootstrap.Popover(popoverTriggerEl, {
                    trigger: 'hover focus',
                    html: true,
                    sanitize: false,
                    delay: { "show": 200, "hide": 100 }
                });
            });
        }


        $(document).ready(function() {
            // Select2 is removed, standard select elements will be used.

            mainDataTable = $('#ckupTrgtList').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: BASE_URL + 'mngr/ckupTrgt/ajax_list',
                    type: 'POST',
                    data: function (d) {
                        d[CSRF_TOKEN_NAME] = CSRF_HASH;
                        // 필터 값 추가
                        d.co_sn_filter = $('#co_sn_filter').val();
                        d.ckup_yyyy_filter = $('#ckup_yyyy_filter').val();
                        d.relation_filter = $('#relation_filter').val();
                        d.name_filter = $('#name_filter').val();
                         d.ckup_name_filter = $('#ckup_name_filter').val();
                    },
                    dataSrc: function (json) {
                        updateCsrfTokenOnPage(json.csrf_hash);
                        return json.data;
                    },
                    error: function(xhr, error, code){
                        console.error("DataTables AJAX Error: ", xhr.responseText);
                        showAjaxMessageGlobal('목록을 불러오는데 실패했습니다.('+code+')', 'error');
                    }
                },
                columns: [
                    { data: 'no', orderable: false, searchable: false },
                    { data: 'CO_NM' },
                    { data: 'CKUP_YYYY' },
                    { data: 'BUSINESS_NUM' },
                    { data: 'RELATION' },
                    { data: 'CKUP_NAME' },
                    { data: 'NAME', className: 'name-col-width' },
                    { data: 'SEX' },
                    { data: 'BIRTHDAY' },
                    { data: 'HANDPHONE' },
                    { data: 'memo_status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'action-col-width' }
                ],
                dom: "<'row'<'col-sm-12 col-md-6 mb-2'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    {
                        text: '<i class="ri-download-line align-bottom"></i> Excel 다운로드',
                        //className: 'btn btn-primary btn-sm ms-1',
                        action: function ( e, dt, node, config ) {
                            // 현재 필터 값들을 가져옵니다.
                            let co_sn_filter = $('#co_sn_filter').val();
                            let ckup_yyyy_filter = $('#ckup_yyyy_filter').val();
                            if (!co_sn_filter || co_sn_filter.trim() === '') {
                                alert('검색필터의 회사를 선택해주세요.');
                                return;
                            }
                            if (!ckup_yyyy_filter || ckup_yyyy_filter.trim() === '') {
                                alert('검색필터의 검진년도를 선택해주세요.');
                                return;
                            }
                            // 서버의 전체 엑셀 다운로드 URL 생성
                            // CSRF 토큰은 GET 요청에서는 일반적으로 URL 파라미터로 보내지 않지만,
                            // 만약 서버에서 GET 요청에도 CSRF 검증을 한다면 추가해야 합니다.
                            // 여기서는 간단하게 필터 값만 전달합니다.
                            let exportUrl = BASE_URL + 'mngr/ckupTrgt/excel_download?';
                            exportUrl += 'co_sn_filter=' + encodeURIComponent(co_sn_filter);
                            exportUrl += '&ckup_yyyy_filter=' + encodeURIComponent(ckup_yyyy_filter);

                            // 새로운 창이나 현재 창에서 URL을 열어 다운로드를 시작합니다.
                            window.location.href = exportUrl;
                        }
                    },
                    {
                        text: '<i class="ri-upload-line align-bottom me-1"></i>Excel 업로드',
                        //className: 'btn btn-warning btn-lg ms-1',
                        action: function (e, dt, node, config) {
                            
                            // 업로드 모달을 띄우는 코드 예시
                            $('#fileModal').modal('show');
                        }
                    }
                ],
                language: {
                    "emptyTable": "표시할 데이터가 없습니다.",
                    "info": "총 _TOTAL_개 항목 중 _START_에서 _END_까지 표시",
                    "infoEmpty": "0개 항목 중 0에서 0까지 표시",
                    "infoFiltered": "(총 _MAX_개 항목에서 필터링됨)",
                    "lengthMenu": "페이지당 _MENU_ 항목 표시",
                    "loadingRecords": "로딩 중...",
                    "processing": "<div class='spinner-border spinner-border-sm' role='status'><span class='visually-hidden'>처리 중...</span></div>",
                    "search": "전체 검색:",
                    "zeroRecords": "일치하는 레코드를 찾을 수 없습니다.",
                    "paginate": { "first": "처음", "last": "마지막", "next": "다음", "previous": "이전" }
                },
                order: [[2, 'desc'], [3, 'asc']],
                responsive: false,
                autoWidth: false,
                drawCallback: function(settings) {
                    initializePopovers(); // Re-initialize popovers on each draw
                },
                initComplete: function () {
                    // Bootstrap 버튼 스타일 수동 적용
                    $('button.dt-button:contains("Excel 다운로드")')
                        .removeClass('dt-button') // DataTables 기본 클래스 제거
                        .addClass('btn btn-outline-success ms-1'); // 원하는 Bootstrap 클래스 적용

                    $('button.dt-button:contains("Excel 업로드")')
                        .removeClass('dt-button') // DataTables 기본 클래스 제거
                        .addClass('btn btn-outline-danger ms-1'); // 원하는 Bootstrap 클래스 적용
                }
            });
           // mainDataTable.buttons().container().appendTo('#datatables-buttons-placeholder');


            // 필터 검색 버튼 클릭
            $('#btn-filter-search').on('click', function() {
                mainDataTable.ajax.reload();
            });

            // 필터 초기화 버튼 클릭
            $('#btn-filter-reset').on('click', function() {
                $('#filter-form').trigger('reset'); // This will reset inputs and selects to their initial state
                mainDataTable.ajax.reload();
            });


            // 신규 등록 버튼 클릭
            $('#create-item-btn').on('click', function() {
                clearFormAndValidation('main-item-form');
                $('#mainModalLabel').text('신규 대상자 등록');
                $('#main-add-btn').text('등록');
                $('#CKUP_TRGT_SN_modal').val('');
                $('#main-item-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);
                $('#MEMO_modal_main').val('');

                // 필드 상태 초기화
                $('#CKUP_YYYY_modal').val('<?= date('Y') ?>');
                $('#BUSINESS_NUM_modal').val('');
                $('#NAME_modal').val('');
                
                // 회사 정보가 고정된 경우 다시 설정
                <?php if (!empty($userCoSn)): ?>
                    $('#CO_SN_modal').val('<?= $userCoSn ?>');
                <?php endif; ?>
            });

            // 대상자 정보 수정 버튼 클릭 (이벤트 위임)
            $('#ckupTrgtList').on('click', '.edit-item-btn', function() {
                clearFormAndValidation('main-item-form');
                const itemId = $(this).data('id');
                $('#mainModalLabel').text('대상자 정보 수정');
                $('#main-add-btn').text('수정');
                $('#PSWD_modal').attr('placeholder', '변경 시에만 입력');

                $.ajax({
                    url: BASE_URL + 'mngr/ckupTrgt/ajax_get_ckup_trgt/' + itemId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                       
                        if (response.status === 'success' && response.data) {
                            const d = response.data;
                            $('#CKUP_TRGT_SN_modal').val(d.CKUP_TRGT_SN);
                            
                            $('#CO_SN_modal').val(d.CO_SN); 
                            
                            $('#CKUP_YYYY_modal').val(d.CKUP_YYYY);
                            $('#NAME_modal').val(d.NAME);
                            $('#CKUP_NAME_modal').val(d.CKUP_NAME);
                            $('#BUSINESS_NUM_modal').val(d.BUSINESS_NUM);
                            $('#BUSINESS_NUM_modal').val(d.BUSINESS_NUM);
                            $('#BIRTHDAY_modal').val(d.BIRTHDAY);
                            
                            // Radio buttons for SEX
                            $('#main-item-form input[name="SEX"][value="' + d.SEX + '"]').prop('checked', true);
                            
                            $('#HANDPHONE_modal').val(d.HANDPHONE);
                            
                            // Radio buttons for RELATION
                            $('#main-item-form input[name="RELATION"][value="' + d.RELATION + '"]').prop('checked', true);
                            
                            // Password field removed

                            $.ajax({
                                url: BASE_URL + 'mngr/ckupTrgt/ajax_get_memo/' + itemId,
                                type: 'GET',
                                dataType: 'json',
                                success: function(memoResponse) {
                                    if (memoResponse.status === 'success' && memoResponse.data) {
                                        $('#MEMO_modal_main').val(memoResponse.data.MEMO);
                                    } else {
                                        $('#MEMO_modal_main').val('');
                                    }
                                    updateCsrfTokenOnPage(memoResponse.csrf_hash || response.csrf_hash);
                                },
                                error: function() { $('#MEMO_modal_main').val(''); }
                            });
                            updateCsrfTokenOnPage(response.csrf_hash);
                        } else {
                            showAjaxMessageGlobal(response.message || '정보를 불러오는데 실패했습니다.', 'error');
                            if(response.csrf_hash) updateCsrfTokenOnPage(response.csrf_hash);
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessageGlobal('정보 로딩 중 오류 발생.', 'error');
                        handleAjaxError(xhr);
                    }
                });
            });

            // 대상자 정보 등록/수정 폼 제출
            $('#main-item-form').on('submit', function(e) {
                e.preventDefault(); // 폼 기본 제출 동작 방지
                // 유효성 검사 클래스 및 메시지 초기화
                $('#main-item-form .form-control, #main-item-form .form-select').removeClass('is-invalid');
                $('#main-item-form .invalid-feedback').hide().text('');

                const itemId = $('#CKUP_TRGT_SN_modal').val(); // 숨겨진 필드에서 대상자 ID 가져오기
                let url;
                let originalButtonText;

                // itemId의 존재 여부에 따라 URL 및 버튼 텍스트 설정
                if (itemId) {
                    // 수정 작업 시: ID는 URL에 포함되지 않고, 폼 데이터로 전송됨
                    url = BASE_URL + 'mngr/ckupTrgt/ajax_update'; // 대상자 ID(itemId)가 URL 경로에 포함되지 않음
                    originalButtonText = '수정';
                } else {
                    // 신규 등록 작업 시
                    url = BASE_URL + 'mngr/ckupTrgt/ajax_create';
                    originalButtonText = '등록';
                }
                const $submitButton = $('#main-add-btn'); // 제출 버튼 jQuery 객체

                // 폼 데이터를 직렬화하여 객체로 변환
                // CKUP_TRGT_SN (itemId)는 이 과정에서 formData에 포함됨
                const formData = $(this).serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
                formData[CSRF_TOKEN_NAME] = CSRF_HASH; // CSRF 토큰 추가

                // AJAX 요청 시작
                $.ajax({
                    url: url, // 요청을 보낼 URL
                    type: 'POST', // HTTP 요청 방식
                    data: formData, // 서버로 전송할 데이터 (폼 데이터 + CSRF 토큰)
                    dataType: 'json', // 서버로부터 받을 응답 데이터 타입
                    beforeSend: function() {
                        // 요청 보내기 전: 버튼 비활성화 및 로딩 표시
                        $submitButton.prop('disabled', true).addClass('btn-loading').html('');
                    },
                    success: function(response) {
                        // 요청 성공 시
                        updateCsrfTokenOnPage(response.csrf_hash); // CSRF 토큰 갱신

                        if (response.status === 'success') {
                            // 서버에서 성공 응답을 받은 경우
                            $('#mainModal').modal('hide'); // 모달 닫기
                            showAjaxMessageGlobal(response.message, 'success'); // 성공 메시지 표시
                            mainDataTable.ajax.reload(null, false); // 데이터 테이블 새로고침 (페이징 유지)
                        } else if (response.status === 'fail') {
                            // 서버에서 유효성 검사 실패 등의 'fail' 응답을 받은 경우
                            if (response.errors) {
                                // 유효성 검사 오류 메시지가 있는 경우 각 필드에 표시
                                $.each(response.errors, function(key, value) {
                                    let fieldId = '#' + key.toUpperCase() + '_modal';
                                    if (!$(fieldId).length) fieldId = '#' + key + '_modal'; // 필드 ID 대소문자 처리

                                    if ($(fieldId).length) {
                                        $(fieldId).addClass('is-invalid'); // 유효하지 않음 클래스 추가
                                        $(fieldId).siblings('.invalid-feedback').text(value).show(); // 오류 메시지 표시
                                    } else {
                                        // 매핑되는 필드가 없는 경우 콘솔 경고 및 전역 메시지 표시
                                        console.warn("Validation error for unmapped field: ", key, value);
                                        showAjaxMessageGlobal(value, 'error');
                                    }
                                });
                            }
                            // 'fail' 상태에 대한 일반 메시지 표시
                            showAjaxMessageGlobal(response.message || '입력값을 확인해주세요.', 'error');
                        } else {
                            // 기타 서버 응답 (예: 'error' 상태)
                            showAjaxMessageGlobal(response.message || '오류가 발생했습니다.', 'error');
                        }
                    },
                    error: function(xhr) {
                        // AJAX 통신 자체에 오류가 발생한 경우 (네트워크 오류, 서버 다운 등)
                        showAjaxMessageGlobal('서버 통신 중 오류가 발생했습니다.', 'error');
                        handleAjaxError(xhr); // 공통 에러 핸들러 호출
                    },
                    complete: function() {
                        // 요청 완료 시 (성공/실패 여부와 관계없이 실행)
                        // 버튼 활성화 및 로딩 표시 제거
                        $submitButton.prop('disabled', false).removeClass('btn-loading').text(originalButtonText);
                    }
                });
            });


            // 대상자 정보 삭제 버튼 클릭
            $('#ckupTrgtList').on('click', '.delete-item-btn', function() {
                const itemId = $(this).data('id');
                const itemName = $(this).data('name');
                const $rowElement = $(this).closest('tr');
                const $button = $(this);

                if (confirm(`'${itemName}' 대상자 정보를 정말로 삭제하시겠습니까? 관련 메모도 함께 삭제됩니다.`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupTrgt/ajax_delete/' + itemId,
                        type: 'POST',
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        dataType: 'json',
                        beforeSend: function() { $button.prop('disabled', true).addClass('btn-loading').html(''); },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash);
                            if (response.status === 'success') {
                                showAjaxMessageGlobal(response.message, 'success');
                                mainDataTable.row($rowElement).remove().draw(false);
                            } else {
                                showAjaxMessageGlobal(response.message || '삭제 중 오류.', 'error');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessageGlobal('서버 오류로 삭제 실패.', 'error');
                            handleAjaxError(xhr);
                        },
                        complete: function() { $button.prop('disabled', false).removeClass('btn-loading').text('삭제'); }
                    });
                }
            });

            // 가족 추가 버튼 클릭
            $('#ckupTrgtList').on('click', '.add-family-btn', function() {
                clearFormAndValidation('family-item-form');
                // $('#familyModalLabel').text('가족 등록'); // Already set in HTML
                $('#family-add-btn').text('등록');
                $('#CKUP_TRGT_SN_modal_family').val(''); 
                
                // 부모 데이터 가져오기
                const coSn = $(this).data('co-sn');
                const ckupYyyy = $(this).data('ckup-yyyy');
                const businessNum = $(this).data('business-num');
                const name = $(this).data('name');

                // 필드 값 설정 (Hidden inputs in family modal)
                $('#CO_SN_modal_family').val(coSn);
                $('#CKUP_YYYY_modal_family').val(ckupYyyy);
                $('#BUSINESS_NUM_modal_family').val(businessNum);
                $('#NAME_modal_family').val(name);

                // 나머지 필드 초기화
                $('#CKUP_NAME_modal_family').val('');
                $('#BIRTHDAY_modal_family').val('');
                $('input[name="SEX"]').prop('checked', false); // Targets all sex radios, ok since family modal ones are unique IDs but name shared? No, name is SEX. 
                // Wait, if name is SEX in both forms, checking one might affect other if in same DOM? 
                // Radio buttons with same name in different forms are treated as same group by browser if forms are not isolated?
                // Actually, HTML5 spec says radios with same name are in same group unless they are in different forms. 
                // But here they are in different form elements, so it should be fine.
                // However, jquery selector `input[name="SEX"]` selects ALL.
                // Better to scope to form.
                $('#family-item-form input[name="SEX"]').prop('checked', false);
                $('#family-item-form input[name="RELATION"]').prop('checked', false);
                $('#HANDPHONE_modal_family').val('');

                // 모달 표시
                $('#familyModal').modal('show');
            });

            // 가족 등록 폼 제출
            $('#family-add-btn').on('click', function() {
                const form = $('#family-item-form')[0];
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(form);
                // CSRF token is already in the form via csrf_field()

                $.ajax({
                    url: BASE_URL + 'mngr/ckupTrgt/ajax_add', // Reusing the same add endpoint
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#family-add-btn').prop('disabled', true).addClass('btn-loading').text('처리중...');
                    },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                        if (response.status === 'success') {
                            showAjaxMessageGlobal(response.message, 'success');
                            $('#familyModal').modal('hide');
                            mainDataTable.ajax.reload(null, false);
                        } else {
                            if (response.errors) {
                                let errorMsg = '';
                                $.each(response.errors, function(key, value) {
                                    errorMsg += value + '<br>';
                                    $('#family-item-form #' + key + '_modal_family').addClass('is-invalid');
                                    $('#family-item-form #' + key + '_modal_family').next('.invalid-feedback').text(value).show();
                                });
                                showAjaxMessageGlobal(errorMsg, 'error');
                            } else {
                                showAjaxMessageGlobal(response.message, 'error');
                            }
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessageGlobal('서버 오류 발생.', 'error');
                        handleAjaxError(xhr);
                    },
                    complete: function() {
                        $('#family-add-btn').prop('disabled', false).removeClass('btn-loading').text('등록');
                    }
                });
            });

            // 메모 관리 버튼 클릭
            $('#ckupTrgtList').on('click', '.manage-memo-btn', function() {
                clearFormAndValidation('memo-form');
                const targetSn = $(this).data('id');
                const targetName = $(this).data('name');
                const memoSn = $(this).data('memo-sn');

                $('#memoModalLabel').text(`'${targetName}' 대상자 메모 관리`);
                $('#CKUP_TRGT_SN_memo').val(targetSn);
                $('#CKUP_TRGT_MEMO_SN_memo').val(memoSn || '');

                $('#memo-form input[name="' + CSRF_TOKEN_NAME + '"]').val(CSRF_HASH);

                if (memoSn) {
                    $('#delete-memo-btn').show().data('memo-sn', memoSn);
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupTrgt/ajax_get_memo/' + targetSn,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success' && response.data) {
                                $('#MEMO_modal').val(response.data.MEMO);
                                $('#CKUP_TRGT_MEMO_SN_memo').val(response.data.CKUP_TRGT_MEMO_SN);
                                $('#delete-memo-btn').data('memo-sn', response.data.CKUP_TRGT_MEMO_SN);
                            } else {
                                $('#MEMO_modal').val('');
                                $('#delete-memo-btn').hide();
                            }
                            updateCsrfTokenOnPage(response.csrf_hash);
                        },
                        error: function(xhr) {
                            showAjaxMessageGlobal('메모 로딩 중 오류.', 'error');
                            $('#MEMO_modal').val('');
                            $('#delete-memo-btn').hide();
                            handleAjaxError(xhr);
                        }
                    });
                } else {
                    $('#MEMO_modal').val('');
                    $('#delete-memo-btn').hide();
                }
                $('#memoModal').modal('show');
            });


            // 메모 저장 폼 제출
            $('#memo-form').on('submit', function(e) {
                e.preventDefault();
                $('#MEMO_modal').removeClass('is-invalid');
                $('#MEMO_modal').siblings('.invalid-feedback').hide().text('');

                const formData = $(this).serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
                formData[CSRF_TOKEN_NAME] = CSRF_HASH;

                const $submitButton = $('#save-memo-btn');
                const originalButtonText = '저장';

                $.ajax({
                    url: BASE_URL + 'mngr/ckupTrgt/ajax_save_memo',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() { $submitButton.prop('disabled', true).addClass('btn-loading').html(''); },
                    success: function(response) {
                        updateCsrfTokenOnPage(response.csrf_hash);
                        if (response.status === 'success') {
                            $('#memoModal').modal('hide');
                            showAjaxMessageGlobal(response.message, 'success');
                            mainDataTable.ajax.reload(null, false);
                        } else {
                            if (response.errors && response.errors.MEMO_modal) {
                                $('#MEMO_modal').addClass('is-invalid');
                                $('#MEMO_modal').siblings('.invalid-feedback').text(response.errors.MEMO_modal).show();
                            }
                            showAjaxMessageGlobal(response.message || '메모 저장 실패.', 'error');
                        }
                    },
                    error: function(xhr) {
                        showAjaxMessageGlobal('서버 통신 오류 (메모 저장).', 'error');
                        handleAjaxError(xhr);
                    },
                    complete: function() { $submitButton.prop('disabled', false).removeClass('btn-loading').text(originalButtonText); }
                });
            });

            // 메모 삭제 버튼 클릭
            $('#delete-memo-btn').on('click', function() {
                const memoSnToDelete = $(this).data('memo-sn') || $('#CKUP_TRGT_MEMO_SN_memo').val();

                if (!memoSnToDelete) {
                    showAjaxMessageGlobal('삭제할 메모 정보가 없습니다.', 'warning');
                    return;
                }

                if (confirm('이 메모를 정말 삭제하시겠습니까?')) {
                    const $button = $(this);
                    const originalButtonText = $button.text();
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupTrgt/ajax_delete_memo/' + memoSnToDelete,
                        type: 'POST',
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        dataType: 'json',
                        beforeSend: function() { $button.prop('disabled', true).addClass('btn-loading').html(''); },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash);
                            if (response.status === 'success') {
                                $('#memoModal').modal('hide');
                                showAjaxMessageGlobal(response.message, 'success');
                                mainDataTable.ajax.reload(null, false);
                            } else {
                                showAjaxMessageGlobal(response.message || '메모 삭제 실패.', 'error');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessageGlobal('서버 통신 오류 (메모 삭제).', 'error');
                            handleAjaxError(xhr);
                        },
                        complete: function() { $button.prop('disabled', false).removeClass('btn-loading').text(originalButtonText); }
                    });
                }
            });


            // 모달 닫힐 때 폼 초기화
            $('#mainModal').on('hidden.bs.modal', function () {
                clearFormAndValidation('main-item-form');
                $('#mainModalLabel').text('신규 대상자 등록');
                $('#main-add-btn').text('등록').prop('disabled', false).removeClass('btn-loading');
            });
            $('#memoModal').on('hidden.bs.modal', function () {
                clearFormAndValidation('memo-form');
                $('#delete-memo-btn').hide().removeData('memo-sn');
                $('#save-memo-btn').prop('disabled', false).removeClass('btn-loading').text('저장');
            });

            // 공통 AJAX 에러 핸들러
            function handleAjaxError(xhr) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse && errorResponse.csrf_hash) {
                        updateCsrfTokenOnPage(errorResponse.csrf_hash);
                    }
                    if (errorResponse && errorResponse.message) {
                         console.error("Server Error: ", errorResponse.message);
                    }
                } catch (e) {
                    console.error("AJAX Error: ", xhr.status, xhr.statusText, xhr.responseText);
                }
            }

            $('#file-form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                let fileModal_co_sn = $('#fileModal_co_sn').val();
                let fileModal_ckup_yyyy = $('#fileModal_ckup_yyyy').val();
                if (!fileModal_co_sn || fileModal_co_sn.trim() === '') {
                    alert('업로드 회사를 선택해주세요.');
                    return;
                }
                if (!fileModal_ckup_yyyy || fileModal_ckup_yyyy.trim() === '') {
                    alert('업로드 검진년도를 선택해주세요.');
                    return;
                }
               
                $.ajax({
                    url: '/mngr/ckupTrgt/excel_upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            showAjaxMessageGlobal(response.message, 'success');
                            $('#excel_file').val('');
                        } else {
                            showAjaxMessageGlobal(response.message || '등록 실패.', 'error');
                        }
                    },
                    error: function (err) {
                        showAjaxMessageGlobal('등록 실패.', 'error');
                    }
                });
            });

            // 비밀번호 초기화 버튼 클릭
            $('#ckupTrgtList').on('click', '.reset-password-btn', function() {
                const itemId = $(this).data('id');
                const itemName = $(this).data('name');
                const $button = $(this);

                if (confirm(`'${itemName}' 님의 비밀번호를 생년월일로 초기화하시겠습니까?`)) {
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupTrgt/ajax_reset_password/' + itemId,
                        type: 'POST',
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        dataType: 'json',
                        beforeSend: function() { $button.prop('disabled', true).addClass('btn-loading').html(''); },
                        success: function(response) {
                            updateCsrfTokenOnPage(response.csrf_hash);
                            if (response.status === 'success') {
                                showAjaxMessageGlobal(response.message, 'success');
                            } else {
                                showAjaxMessageGlobal(response.message || '초기화 중 오류가 발생했습니다.', 'error');
                            }
                        },
                        error: function(xhr) {
                            showAjaxMessageGlobal('서버 오류로 초기화에 실패했습니다.', 'error');
                            handleAjaxError(xhr);
                        },
                        complete: function() { $button.prop('disabled', false).removeClass('btn-loading').text('비밀번호 초기화'); }
                    });
                }
            });
        }); // end document ready
    </script>
    <script src="<?= base_url('public/assets/js/app.js') ?>"></script>

    <!--form id="excelUploadForm" enctype="multipart/form-data">
        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
        <button type="submit">업로드</button>
    </form-->
</body>
</html>