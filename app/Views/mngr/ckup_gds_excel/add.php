<?php
$isEditMode = isset($ckupGds) && !empty($ckupGds);
$pageTitle = $isEditMode ? '검진상품 엑셀 정보 수정' : '검진상품 엑셀 신규 등록';
$buttonText = $isEditMode ? '수정' : '저장';
?>

<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => $pageTitle)); ?>
    <?= $this->include('partials/head-css') ?>
    <style>
        /* Paste UI Styles */
        .paste-wrapper {
            position: relative;
            height: 400px;
        }
        .paste-container {
            border: 2px dashed #a2aab7;
            border-radius: 8px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            background-color: #f8f9fa;
            transition: all 0.2s;
        }
        .paste-container:hover, .paste-container.dragover {
            border-color: #405189;
            background-color: #f0f4f8;
        }
        .paste-icon {
            font-size: 48px;
            color: #f19668;
            margin-bottom: 16px;
        }
        .paste-text {
            font-size: 18px;
            color: #495057;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .paste-subtext {
            font-size: 13px;
            color: #878a99;
            margin-bottom: 24px;
        }
        .paste-tags span {
            display: inline-block;
            padding: 6px 16px;
            background-color: #eef0f7;
            color: #405189;
            border-radius: 20px;
            font-size: 13px;
            margin: 0 4px;
            font-weight: 500;
        }
        .hidden-paste-input {
            position: absolute;
            opacity: 0;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
        }
        
        /* Preview UI Styles */
        .preview-container {
            display: none;
            border: 1px solid #e9ebec;
            border-radius: 8px;
            padding: 16px;
            background: #fff;
            height: 100%;
            flex-direction: column;
        }
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .preview-content {
            flex: 1;
            overflow: auto;
            border: 1px solid #e9ebec;
            border-radius: 4px;
        }
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .preview-table th {
            background-color: #405189;
            color: #fff;
            padding: 10px;
            position: sticky;
            top: 0;
            text-align: center;
        }
        .preview-table td {
            padding: 8px;
            border-bottom: 1px solid #e9ebec;
            text-align: center;
        }
        .preview-table tr:last-child td {
            border-bottom: none;
        }
        
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
                        <ul class="nav nav-tabs nav-border-top nav-border-top-primary d-flex align-items-center" style="background-color:#ffffff" role="tablist">
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
                                <a href="<?= site_url('mngr/ckupGdsExcel') ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                                    <i class="ri-list-check align-bottom me-1"></i> 목록으로
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content text-muted">
                        <!-- Tab 1: Basic & Item Info -->
                        <div class="tab-pane active" id="baseInfo" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0 text-primary fw-bold">기본 정보</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" id="CKUP_GDS_EXCEL_MNG_SN" value="<?= $isEditMode ? esc($ckupGds['basicInfo']['CKUP_GDS_EXCEL_MNG_SN']) : '' ?>">
                                            <div class="row">
                                                <div class="col-md-2 mb-3">
                                                    <label for="HSPTL_SN_sel" class="form-label">검진병원<span class="text-danger">*</span></label>
                                                    <select id="HSPTL_SN_sel" name="HSPTL_SN" class="form-select">
                                                        <?php if (session()->get('user_type') !== 'H'): ?>
                                                            <option value="">병원을 선택하세요</option>
                                                        <?php endif; ?>
                                                        <?php foreach ($hospitals as $hsptl): ?>
                                                            <?php if (session()->get('user_type') === 'H' && $hsptl['HSPTL_SN'] != session()->get('hsptl_sn')) continue; ?>
                                                            <option value="<?= esc($hsptl['HSPTL_SN']) ?>" <?= ($isEditMode && $ckupGds['basicInfo']['HSPTL_SN'] == $hsptl['HSPTL_SN']) ? 'selected' : '' ?>><?= esc($hsptl['HSPTL_NM']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label for="CKUP_YYYY_sel" class="form-label">검진년도<span class="text-danger">*</span></label>
                                                    <select id="CKUP_YYYY_sel" name="CKUP_YYYY" class="form-select">
                                                        <option value="">년도를 선택하세요</option>
                                                        <?php foreach ($years as $year): ?>
                                                            <option value="<?= $year ?>" <?= ($isEditMode && $ckupGds['basicInfo']['CKUP_YYYY'] == $year) ? 'selected' : '' ?>><?= $year ?>년</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label for="CKUP_GDS_NM" class="form-label">상품명<span class="text-danger">*</span></label>
                                                    <input type="text" id="CKUP_GDS_NM" name="CKUP_GDS_NM" class="form-control" required placeholder="상품명을 입력하세요" value="<?= $isEditMode ? esc($ckupGds['basicInfo']['CKUP_GDS_NM']) : '' ?>">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label for="SPRT_SE" class="form-label">지원구분</label>
                                                    <input type="text" id="SPRT_SE" name="SPRT_SE" class="form-control" placeholder="지원구분" value="<?= $isEditMode ? esc($ckupGds['basicInfo']['SPRT_SE']) : '' ?>">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label for="FAM_SPRT_SE" class="form-label">가족지원구분</label>
                                                    <input type="text" id="FAM_SPRT_SE" name="FAM_SPRT_SE" class="form-control" placeholder="가족지원구분" value="<?= $isEditMode ? esc($ckupGds['basicInfo']['FAM_SPRT_SE']) : '' ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h4 class="card-title mb-0 text-primary fw-bold">등록된 항목 정보</h4>
                                            <div>
                                                <button type="button" class="btn btn-danger btn-sm me-1" id="delete-selected-basic-btn">
                                                    <i class="ri-delete-bin-line align-bottom me-1"></i> 선택 삭제
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm" id="open-basic-excel-modal-btn">
                                                    <i class="ri-file-excel-2-line align-bottom me-1"></i> 엑셀 붙여넣기
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table id="basicItemsTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 10px;" class="no-sort">
                                                            <div class="form-check">
                                                                <input class="form-check-input fs-15" type="checkbox" id="checkAllBasic">
                                                            </div>
                                                        </th>
                                                        <th>검진구분</th>
                                                        <th>검진항목명</th>
                                                        <th>질환명</th>
                                                        <th>성별</th>
                                                        <th>비고</th>
                                                        <th>관리</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($isEditMode && !empty($ckupGds['basicItems'])): ?>
                                                        <?php foreach ($ckupGds['basicItems'] as $item): ?>
                                                            <tr data-id="<?= $item['CKUP_GDS_EXCEL_ARTCL_SN'] ?>">
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input fs-15 basic-item-checkbox" type="checkbox" value="<?= $item['CKUP_GDS_EXCEL_ARTCL_SN'] ?>">
                                                                    </div>
                                                                </td>
                                                                <td><?= esc($item['CKUP_SE']) ?></td>
                                                                <td><?= esc($item['CKUP_ARTCL']) ?></td>
                                                                <td><?= esc($item['DSS']) ?></td>
                                                                <td>
                                                                    <select class="form-select form-select-sm basic-gndr-select">
                                                                        <option value="C" <?= $item['GNDR_SE'] === 'C' ? 'selected' : '' ?>>공통</option>
                                                                        <option value="M" <?= $item['GNDR_SE'] === 'M' ? 'selected' : '' ?>>남자</option>
                                                                        <option value="F" <?= $item['GNDR_SE'] === 'F' ? 'selected' : '' ?>>여자</option>
                                                                    </select>
                                                                </td>
                                                                <td><?= esc($item['RMRK']) ?></td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button type="button" class="btn btn-primary save-basic-row-btn" data-id="<?= $item['CKUP_GDS_EXCEL_ARTCL_SN'] ?>">저장</button>
                                                                        <button type="button" class="btn btn-danger delete-row-btn" data-type="basic" data-id="<?= $item['CKUP_GDS_EXCEL_ARTCL_SN'] ?>">삭제</button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Choice Info -->
                        <div class="tab-pane" id="selectInfo" role="tabpanel">
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
                                        </div>
                                        <div id="choice-sections-container">
                                            <!-- Dynamic Sections -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Additional Choice Info -->
                        <div class="tab-pane" id="addSelectInfo" role="tabpanel">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h4 class="card-title mb-0 text-primary fw-bold">등록된 추가 선택 항목 정보</h4>
                                            <div>
                                                <button type="button" class="btn btn-danger btn-sm me-1" id="delete-selected-add-btn">
                                                    <i class="ri-delete-bin-line align-bottom me-1"></i> 선택 삭제
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm" id="open-add-excel-modal-btn">
                                                    <i class="ri-file-excel-2-line align-bottom me-1"></i> 엑셀 붙여넣기
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table id="addChoiceItemsTable" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 10px;" class="no-sort">
                                                            <div class="form-check">
                                                                <input class="form-check-input fs-15" type="checkbox" id="checkAllAdd">
                                                            </div>
                                                        </th>
                                                        <th>검사유형</th>
                                                        <th>검사구분</th>
                                                        <th>검사항목</th>
                                                        <th>비용</th>
                                                        <th>성별구분</th>
                                                        <th>관리</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($isEditMode && !empty($ckupGds['addChoiceItems'])): ?>
                                                        <?php foreach ($ckupGds['addChoiceItems'] as $item): ?>
                                                            <tr data-id="<?= $item['CKUP_GDS_EXCEL_ADD_CHC_SN'] ?>">
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input fs-15 add-item-checkbox" type="checkbox" value="<?= $item['CKUP_GDS_EXCEL_ADD_CHC_SN'] ?>">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <select class="form-select form-select-sm add-type-select">
                                                                        <option value="GS" <?= $item['CKUP_TYPE'] === 'GS' ? 'selected' : '' ?>>위내시경</option>
                                                                        <option value="CS" <?= $item['CKUP_TYPE'] === 'CS' ? 'selected' : '' ?>>대장내시경</option>
                                                                        <option value="CT" <?= $item['CKUP_TYPE'] === 'CT' ? 'selected' : '' ?>>CT</option>
                                                                        <option value="UT" <?= $item['CKUP_TYPE'] === 'UT' ? 'selected' : '' ?>>초음파</option>
                                                                        <option value="BU" <?= $item['CKUP_TYPE'] === 'BU' ? 'selected' : '' ?>>유방초음파</option>
                                                                        <option value="PU" <?= $item['CKUP_TYPE'] === 'PU' ? 'selected' : '' ?>>골반초음파</option>
                                                                        <option value="ET" <?= $item['CKUP_TYPE'] === 'ET' ? 'selected' : '' ?>>기타</option>
                                                                    </select>
                                                                </td>
                                                                <td><?= esc($item['CKUP_SE']) ?></td>
                                                                <td><?= esc($item['CKUP_ARTCL']) ?></td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm add-cost-input" value="<?= esc($item['CKUP_CST']) ?>">
                                                                </td>
                                                                <td>
                                                                    <select class="form-select form-select-sm add-gndr-select">
                                                                        <option value="C" <?= $item['GNDR_SE'] === 'C' ? 'selected' : '' ?>>공통</option>
                                                                        <option value="M" <?= $item['GNDR_SE'] === 'M' ? 'selected' : '' ?>>남성</option>
                                                                        <option value="F" <?= $item['GNDR_SE'] === 'F' ? 'selected' : '' ?>>여성</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <button type="button" class="btn btn-primary save-add-row-btn" data-id="<?= $item['CKUP_GDS_EXCEL_ADD_CHC_SN'] ?>">저장</button>
                                                                        <button type="button" class="btn btn-danger delete-row-btn" data-type="add" data-id="<?= $item['CKUP_GDS_EXCEL_ADD_CHC_SN'] ?>">삭제</button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
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

    <!-- Template for Choice Group -->
    <template id="choice-section-template">
        <div class="card-body border-top choice-item-group" data-group-index="__INDEX__">
            <div class="row mb-3 align-items-center bg-light p-2">
                <div class="col-auto">
                    <label class="form-label mb-0">선택항목명<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-3">
                    <input type="text" name="GROUP_NM[]" class="form-control" required placeholder="선택항목명을 입력하세요">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">선택갯수<span class="text-danger">*</span></label>
                </div>
                <div class="col-auto">
                    <input type="number" name="CHC_ARTCL_CNT[]" class="form-control" value="1" min="1" style="width: 80px;">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">또는 선택갯수2</label>
                </div>
                <div class="col-auto">
                    <input type="number" name="CHC_ARTCL_CNT2[]" class="form-control" value="0" min="0" style="width: 80px;">
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-warning btn-sm me-2 delete-selected-choice-btn">
                        <i class="ri-delete-bin-line me-1"></i> 선택 삭제
                    </button>
                    <button type="button" class="btn btn-success btn-sm me-2 open-excel-modal-btn">
                        <i class="ri-file-excel-2-line me-1"></i> 엑셀 붙여넣기
                    </button>
                    <button type="button" class="btn btn-danger btn-sm delete-choice-group-btn">
                        <i class="ri-close-line me-1"></i> 이 그룹 삭제
                    </button>
                </div>
            </div>
            <div class="row align-items-start">
                <div class="col-md-12 mb-3">
                    <!-- Target Table (Right) -->
                    <table id="ckupGdsChcArtclTable__INDEX__" class="table table-bordered dt-responsive table-striped align-middle" style="width:100%; word-wrap: break-word;">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 10px;" class="no-sort">
                                    <div class="form-check">
                                        <input class="form-check-input fs-15" type="checkbox" id="checkAll4__INDEX__">
                                    </div>
                                </th>
                                <th style="max-width: 120px;">검사유형</th>
                                <th style="max-width: 150px;">검사구분</th>
                                <th style="max-width: 200px;">검사항목</th>
                                <th style="max-width: 100px;">성별구분</th>
                                <th style="max-width: 150px;">비고</th>
                                <th style="width: 120px;">관리</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal for Choice Item Excel Paste -->
    <div class="modal fade" id="choiceItemExcelModal" tabindex="-1" aria-labelledby="choiceItemExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="choiceItemExcelModalLabel">선택항목 엑셀 붙여넣기</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="paste-wrapper" id="modalChoiceItemsPaste">
                        <div class="paste-container">
                            <i class="ri-clipboard-line paste-icon"></i>
                            <div class="paste-text">여기를 클릭한 후 붙여넣기 하세요</div>
                            <div class="paste-subtext">엑셀에서 셀을 선택하고 복사(Ctrl+C)한 후 여기에 붙여넣기(Ctrl+V)</div>
                            <div class="paste-tags">
                                <span>검사구분</span>
                                <span>검사항목</span>
                                <span>성별구분</span>
                            </div>
                            <textarea class="hidden-paste-input"></textarea>
                        </div>
                        <div class="preview-container">
                            <div class="preview-header">
                                <span class="fw-bold text-primary">총 <span class="preview-count">0</span>개 항목</span>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm me-1 btn-reset">초기화</button>
                                    <button type="button" class="btn btn-success btn-sm btn-register">등록하기</button>
                                </div>
                            </div>
                            <div class="preview-content">
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <th>검사구분</th>
                                            <th>검사항목</th>
                                            <th>성별구분</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Basic Item Excel Paste -->
    <div class="modal fade" id="basicItemExcelModal" tabindex="-1" aria-labelledby="basicItemExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="basicItemExcelModalLabel">항목 정보 엑셀 붙여넣기</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="paste-wrapper" id="modalBasicItemsPaste">
                        <div class="paste-container">
                            <i class="ri-clipboard-line paste-icon"></i>
                            <div class="paste-text">여기를 클릭한 후 붙여넣기 하세요</div>
                            <div class="paste-subtext">엑셀에서 셀을 선택하고 복사(Ctrl+C)한 후 여기에 붙여넣기(Ctrl+V)</div>
                            <div class="paste-tags">
                                <span>검진구분</span>
                                <span>검진항목명</span>
                                <span>질환명</span>
                                <span>성별구분</span>
                                <span>비고</span>
                            </div>
                            <textarea class="hidden-paste-input"></textarea>
                        </div>
                        <div class="preview-container">
                            <div class="preview-header">
                                <span class="fw-bold text-primary">총 <span class="preview-count">0</span>개 항목</span>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm me-1 btn-reset">초기화</button>
                                    <button type="button" class="btn btn-success btn-sm btn-register">등록하기</button>
                                </div>
                            </div>
                            <div class="preview-content">
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <th>검진구분</th>
                                            <th>검진항목명</th>
                                            <th>질환명</th>
                                            <th>성별구분</th>
                                            <th>비고</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Additional Choice Item Excel Paste -->
    <div class="modal fade" id="addChoiceItemExcelModal" tabindex="-1" aria-labelledby="addChoiceItemExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addChoiceItemExcelModalLabel">추가 선택 항목 엑셀 븥여넣기</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="paste-wrapper" id="modalAddChoiceItemsPaste">
                        <div class="paste-container">
                            <i class="ri-clipboard-line paste-icon"></i>
                            <div class="paste-text">여기를 클릭한 후 붙여넣기 하세요</div>
                            <div class="paste-subtext">엑셀에서 셀을 선택하고 복사(Ctrl+C)한 후 여기에 붙여넣기(Ctrl+V)</div>
                            <div class="paste-tags">
                                <span>검진구분</span>
                                <span>검진항목명</span>
                                <span>비용</span>
                                <span>성별구분</span>
                            </div>
                            <textarea class="hidden-paste-input"></textarea>
                        </div>
                        <div class="preview-container">
                            <div class="preview-header">
                                <span class="fw-bold text-primary">총 <span class="preview-count">0</span>개 항목</span>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm me-1 btn-reset">초기화</button>
                                    <button type="button" class="btn btn-success btn-sm btn-register">등록하기</button>
                                </div>
                            </div>
                            <div class="preview-content">
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <th>검진구분</th>
                                            <th>검진항목명</th>
                                            <th>비용</th>
                                            <th>성별구분</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const BASE_URL = '<?= rtrim(site_url(), '/') . '/' ?>';
        const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>';

        const CkupGdsManager = {
            config: {
                baseUrl: BASE_URL,
                isEditMode: <?= $isEditMode ? 'true' : 'false' ?>,
                initialData: <?= $isEditMode ? json_encode($ckupGds) : 'null' ?>,
                csrf: { name: CSRF_TOKEN_NAME, hash: CSRF_HASH },
                ckupTypeMap: { 'ET': '기타', 'CS': '대장내시경', 'GS': '위내시경', 'BU': '유방초음파', 'PU': '골반초음파', 'UT': '초음파', 'CT': 'CT' }
            },
            state: {
                choiceSectionCounter: 0,
                activeGroupIndex: null // Tracks which group opened the modal
            },
            init: function() {
                this.bindGlobalEvents();
                this.initPasteUIs();
                if (this.config.isEditMode && this.config.initialData) {
                    this.populateFormForEditMode();
                } else {
                    this.addChoiceSection();
                }
            },
            bindGlobalEvents: function() {
                $('#add-choice-group-btn').on('click', () => this.addChoiceSection());
                $('#save-all-btn').on('click', () => this.saveAllData());
                
                // Open Basic Excel Modal
                $('#open-basic-excel-modal-btn').on('click', () => {
                    const modalWrapper = $('#modalBasicItemsPaste');
                    modalWrapper.find('.btn-reset').click();
                    $('#basicItemExcelModal').modal('show');
                });

                // Open Additional Choice Excel Modal
                $('#open-add-excel-modal-btn').on('click', () => {
                    const modalWrapper = $('#modalAddChoiceItemsPaste');
                    modalWrapper.find('.btn-reset').click();
                    $('#addChoiceItemExcelModal').modal('show');
                });

                // Delete Selected Basic Items
                $('#delete-selected-basic-btn').on('click', () => {
                    const selectedCheckboxes = $('.basic-item-checkbox:checked');
                    if (selectedCheckboxes.length === 0) {
                        alert('삭제할 항목을 선택해주세요.');
                        return;
                    }

                    if (!confirm(`선택한 ${selectedCheckboxes.length}개 항목을 삭제하시겠습니까?`)) return;

                    const idsToDelete = [];
                    selectedCheckboxes.each(function() {
                        const val = $(this).val();
                        if (val && val !== 'on') { // Existing item
                            idsToDelete.push(val);
                        } else { // New item (DOM only)
                            $(this).closest('tr').remove();
                        }
                    });

                    if (idsToDelete.length > 0) {
                        $.ajax({
                            url: BASE_URL + 'mngr/ckupGdsExcel/deleteItems/basic',
                            type: 'POST',
                            data: { 
                                ids: idsToDelete,
                                [CSRF_TOKEN_NAME]: CSRF_HASH 
                            },
                            success: function(response) {
                                if (response.success) {
                                    idsToDelete.forEach(id => {
                                        $(`tr[data-id="${id}"]`).remove();
                                    });
                                } else {
                                    alert('삭제 실패: ' + response.message);
                                }
                                if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                            },
                            error: function() {
                                alert('서버 오류가 발생했습니다.');
                            }
                        });
                    }
                });

                // Save Basic Row
                $(document).on('click', '.save-basic-row-btn', function() {
                    const btn = $(this);
                    const tr = btn.closest('tr');
                    const id = btn.data('id');
                    
                    // Check if this is an existing item with valid SN
                    if (!id) {
                        alert('신규 항목은 "전체 저장" 버튼을 사용해주세요.');
                        return;
                    }

                    const gndrSe = tr.find('.basic-gndr-select').val();

                    const dataToUpdate = {
                        GNDR_SE: gndrSe,
                        [CSRF_TOKEN_NAME]: CSRF_HASH
                    };

                    $.ajax({
                        url: BASE_URL + 'mngr/ckupGdsExcel/updateBasicItem/' + id,
                        type: 'POST',
                        data: dataToUpdate,
                        success: function(response) {
                            if (response.success) {
                                alert('저장되었습니다.');
                            } else {
                                alert('저장 실패: ' + response.message);
                            }
                            if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                        },
                        error: function() {
                            alert('서버 오류가 발생했습니다.');
                        }
                    });
                });

                // Save Additional Choice Row
                $(document).on('click', '.save-add-row-btn', function() {
                    const btn = $(this);
                    const tr = btn.closest('tr');
                    const id = btn.data('id');
                    
                    if (!id) {
                        alert('신규 항목은 "전체 저장" 버튼을 사용해주세요.');
                        return;
                    }

                    const cost = tr.find('.add-cost-input').val();
                    const type = tr.find('.add-type-select').val();
                    const gndr = tr.find('.add-gndr-select').val();

                    const dataToUpdate = {
                        CKUP_CST: cost,
                        CKUP_TYPE: type,
                        GNDR_SE: gndr,
                        [CSRF_TOKEN_NAME]: CSRF_HASH
                    };

                    $.ajax({
                        url: BASE_URL + 'mngr/ckupGdsExcel/updateAddChoiceItem/' + id,
                        type: 'POST',
                        data: dataToUpdate,
                        success: function(response) {
                            if (response.success) {
                                alert('저장되었습니다.');
                            } else {
                                alert('저장 실패: ' + response.message);
                            }
                            if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                        },
                        error: function() {
                            alert('서버 오류가 발생했습니다.');
                        }
                    });
                });

                // Check All Add Items
                $('#checkAllAdd').on('change', function() {
                    $('.add-item-checkbox').prop('checked', $(this).prop('checked'));
                });

                // Delete Selected Add Items
                $('#delete-selected-add-btn').on('click', () => {
                    const selectedCheckboxes = $('.add-item-checkbox:checked');
                    if (selectedCheckboxes.length === 0) {
                        alert('삭제할 항목을 선택해주세요.');
                        return;
                    }

                    if (!confirm(`선택한 ${selectedCheckboxes.length}개 항목을 삭제하시겠습니까?`)) return;

                    const idsToDelete = [];
                    selectedCheckboxes.each(function() {
                        const val = $(this).val();
                        if (val && val !== 'on') { 
                            idsToDelete.push(val);
                        } else { 
                            $(this).closest('tr').remove();
                        }
                    });

                    if (idsToDelete.length > 0) {
                        $.ajax({
                            url: BASE_URL + 'mngr/ckupGdsExcel/deleteItems/add',
                            type: 'POST',
                            data: { 
                                ids: idsToDelete,
                                [CSRF_TOKEN_NAME]: CSRF_HASH 
                            },
                            success: function(response) {
                                if (response.success) {
                                    idsToDelete.forEach(id => {
                                        $(`tr[data-id="${id}"]`).remove();
                                    });
                                } else {
                                    alert('삭제 실패: ' + response.message);
                                }
                                if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                            },
                            error: function() {
                                alert('서버 오류가 발생했습니다.');
                            }
                        });
                    }
                });

                // Check All Basic Items
                $('#checkAllBasic').on('change', function() {
                    $('.basic-item-checkbox').prop('checked', $(this).prop('checked'));
                });

                $('a[data-bs-toggle="tab"][href="#selectInfo"]').on('shown.bs.tab', function () {
                    const tables = $.fn.dataTable.tables({ visible: true, api: true });
                    tables.columns.adjust();
                    // Only call responsive.recalc if responsive plugin is available
                    if (tables.responsive) {
                        tables.responsive.recalc();
                    }
                });
            },
            parseTSV: function(text, preserveNewlines = false) {
                // TSV parser that handles multi-line cells
                let processedText = text;
                processedText = processedText.replace(/"((?:[^"]|\n)*)"/g, function(match, content) {
                    return content.replace(/\n/g, preserveNewlines ? '___NEWLINE___' : ', ');
                });
                
                const lines = processedText.split('\n');
                if (lines.length === 0) return [];
                if (lines[lines.length - 1].trim() === '') lines.pop();
                if (lines.length === 0) return [];
                
                const firstRowCells = lines[0].split('\t');
                const expectedColumns = firstRowCells.length;
                
                const rows = [];
                let currentRow = null;
                
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i];
                    const cells = line.split('\t');
                    
                    if (cells.length === expectedColumns) {
                        if (currentRow !== null) rows.push(currentRow);
                        currentRow = cells;
                    } else if (cells.length < expectedColumns && currentRow !== null) {
                        const lastCellIndex = currentRow.length - 1;
                        currentRow[lastCellIndex] += (preserveNewlines ? '\n' : ', ') + line;
                    } else {
                        if (currentRow !== null) rows.push(currentRow);
                        currentRow = cells;
                    }
                }
                if (currentRow !== null) rows.push(currentRow);

                if (preserveNewlines) {
                    rows.forEach(row => {
                        for (let i = 0; i < row.length; i++) {
                            row[i] = row[i].replace(/___NEWLINE___/g, '\n');
                        }
                    });
                }

                return rows;
            },
            initPasteUIs: function() {
                // Basic Items Paste (Modal)
                this.setupPasteUI('#modalBasicItemsPaste', (data) => {
                    const tbody = $('#basicItemsTable tbody');
                    data.forEach(row => {
                        const gndrValue = row[3] || 'C'; // Default to 공통
                        const tr = `<tr class="new-item">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input fs-15 basic-item-checkbox" type="checkbox">
                                </div>
                            </td>
                            <td>${row[0] || ''}</td>
                            <td>${row[1] || ''}</td>
                            <td>${row[2] || ''}</td>
                            <td>
                                <select class="form-select form-select-sm basic-gndr-select">
                                    <option value="C" ${gndrValue === 'C' ? 'selected' : ''}>공통</option>
                                    <option value="M" ${gndrValue === 'M' ? 'selected' : ''}>남자</option>
                                    <option value="F" ${gndrValue === 'F' ? 'selected' : ''}>여자</option>
                                </select>
                            </td>
                            <td>${row[4] || ''}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary save-basic-row-btn">저장</button>
                                    <button type="button" class="btn btn-danger delete-row-btn">삭제</button>
                                </div>
                            </td>
                        </tr>`;
                        tbody.append(tr);
                    });
                    
                    // Close Modal
                    $('#basicItemExcelModal').modal('hide');
                }, ['검진구분', '검진항목명', '질환명', '성별구분', '비고'], [0]); // Fill down '검진구분'

                // Modal Choice Items Paste (Per Group)
                this.setupPasteUI('#modalChoiceItemsPaste', (data) => {
                    if (this.state.activeGroupIndex === null) return;
                    
                    const table = $(`#ckupGdsChcArtclTable${this.state.activeGroupIndex}`).DataTable();
                    data.forEach(row => {
                        const ckupArtcl = row[1] || '';
                        let ckupType = 'ET'; 
                        
                        if (ckupArtcl.includes('대장내시경')) ckupType = 'CS';
                        else if (ckupArtcl.includes('위내시경')) ckupType = 'GS';
                        else if (ckupArtcl.includes('CT')) ckupType = 'CT';
                        else if (ckupArtcl.includes('유방초음파')) ckupType = 'BU';
                        else if (ckupArtcl.includes('골반초음파')) ckupType = 'PU';
                        else if (ckupArtcl.includes('초음파')) ckupType = 'UT';
                        
                        let gndr = 'C';
                        const gndrInput = row[2] ? row[2].trim() : '';
                        if (gndrInput === '남성' || gndrInput === 'M') gndr = 'M';
                        else if (gndrInput === '여성' || gndrInput === 'F') gndr = 'F';

                        table.row.add({
                            CKUP_GDS_EXCEL_CHC_ARTCL_SN: null,
                            CKUP_TYPE: ckupType,
                            CKUP_SE: row[0],
                            CKUP_ARTCL: row[1],
                            GNDR_SE: gndr,
                            RMRK: '',
                            ARTCL_CODE: ''
                        });
                    });
                    table.draw();
                    $('#choiceItemExcelModal').modal('hide');
                }, ['검사구분', '검사항목', '성별구분'], [0], true); 

                // Additional Choice Items Paste
                this.setupPasteUI('#modalAddChoiceItemsPaste', (data) => {
                    const tbody = $('#addChoiceItemsTable tbody');
                    data.forEach(row => {
                        const ckupArtcl = row[1] || '';
                        let ckupType = 'ET'; 
                        
                        if (ckupArtcl.includes('대장내시경')) ckupType = 'CS';
                        else if (ckupArtcl.includes('위내시경')) ckupType = 'GS';
                        else if (ckupArtcl.includes('CT')) ckupType = 'CT';
                        else if (ckupArtcl.includes('유방초음파')) ckupType = 'BU';
                        else if (ckupArtcl.includes('골반초음파')) ckupType = 'PU';
                        else if (ckupArtcl.includes('초음파')) ckupType = 'UT';

                        const tr = `<tr class="new-item">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input fs-15 add-item-checkbox" type="checkbox">
                                </div>
                            </td>
                            <td>
                                <select class="form-select form-select-sm add-type-select">
                                    <option value="GS" ${ckupType === 'GS' ? 'selected' : ''}>위내시경</option>
                                    <option value="CS" ${ckupType === 'CS' ? 'selected' : ''}>대장내시경</option>
                                    <option value="CT" ${ckupType === 'CT' ? 'selected' : ''}>CT</option>
                                    <option value="UT" ${ckupType === 'UT' ? 'selected' : ''}>초음파</option>
                                    <option value="BU" ${ckupType === 'BU' ? 'selected' : ''}>유방초음파</option>
                                    <option value="PU" ${ckupType === 'PU' ? 'selected' : ''}>골반초음파</option>
                                    <option value="ET" ${ckupType === 'ET' ? 'selected' : ''}>기타</option>
                                </select>
                            </td>
                            <td>${row[0] || ''}</td>
                            <td>${row[1] || ''}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm add-cost-input" value="${row[2] || ''}">
                            </td>
                            <td>
                                <select class="form-select form-select-sm add-gndr-select">
                                    <option value="C" ${row[3] === 'C' || !row[3] ? 'selected' : ''}>공통</option>
                                    <option value="M" ${row[3] === 'M' ? 'selected' : ''}>남성</option>
                                    <option value="F" ${row[3] === 'F' ? 'selected' : ''}>여성</option>
                                </select>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary save-add-row-btn">저장</button>
                                    <button type="button" class="btn btn-danger delete-row-btn">삭제</button>
                                </div>
                            </td>
                        </tr>`;
                        const $tr = $(tr);
                        tbody.append($tr);
                    });
                    $('#addChoiceItemExcelModal').modal('hide');
                }, ['검진구분', '검진항목명', '비용', '성별구분'], [0]); 
            },
            setupPasteUI: function(selector, onRegister, headers, fillDownIndices = [], preserveNewlines = false) {
                const wrapper = $(selector);
                const container = wrapper.find('.paste-container');
                const input = wrapper.find('.hidden-paste-input');
                const preview = wrapper.find('.preview-container');
                const tbody = wrapper.find('.preview-table tbody');
                let parsedData = [];

                container.on('click', () => input.focus());
                
                input.on('paste', (e) => {
                    setTimeout(() => {
                        const text = input.val();
                        if (!text.trim()) return;
                        parsedData = this.parseTSV(text, preserveNewlines);
                        
                        if (fillDownIndices.length > 0) {
                            for (let i = 1; i < parsedData.length; i++) {
                                fillDownIndices.forEach(idx => {
                                    if (parsedData[i][idx] === undefined || parsedData[i][idx] === null || parsedData[i][idx].trim() === '') {
                                        if (parsedData[i-1][idx]) {
                                            parsedData[i][idx] = parsedData[i-1][idx];
                                        }
                                    }
                                });
                            }
                        }

                        tbody.empty();
                        parsedData.forEach(row => {
                            let tr = '<tr>';
                            row.slice(0, 5).forEach(cell => {
                                const displayCell = (cell || '').replace(/\n/g, '<br>');
                                tr += `<td>${displayCell}</td>`;
                            });
                            tr += '</tr>';
                            tbody.append(tr);
                        });
                        
                        wrapper.find('.preview-count').text(parsedData.length);
                        container.hide();
                        preview.css('display', 'flex');
                        input.val('');
                    }, 100);
                });

                wrapper.find('.btn-reset').on('click', () => {
                    parsedData = [];
                    tbody.empty();
                    preview.hide();
                    container.show();
                });

                wrapper.find('.btn-register').on('click', () => {
                    if (parsedData.length > 0) {
                        onRegister(parsedData);
                        parsedData = [];
                        tbody.empty();
                        preview.hide();
                        container.show();
                    }
                });
            },
            addChoiceSection: function() {
                let templateHtml = $('#choice-section-template').html();
                const newHtml = templateHtml.replace(/__INDEX__/g, this.state.choiceSectionCounter);
                $('#choice-sections-container').append(newHtml);
                this.initializeNewChoiceSection(this.state.choiceSectionCounter);
                this.state.choiceSectionCounter++;
            },
            initializeNewChoiceSection: function(index) {
                const groupSelector = `.choice-item-group[data-group-index="${index}"]`;
                const rightTableId = `#ckupGdsChcArtclTable${index}`;
                const eventNamespace = `.group${index}`;

                const columns = [
                    { data: 'CKUP_GDS_EXCEL_CHC_ARTCL_SN', render: (d) => `<div class="form-check"><input class="form-check-input fs-15" type="checkbox" value="${d}"></div>` },
                    { 
                        data: 'CKUP_TYPE', 
                        render: (data, type, row) => {
                            if (type === 'display') {
                                return `<select class="form-select form-select-sm ckup-type-select" data-row-id="${row.CKUP_GDS_EXCEL_CHC_ARTCL_SN}">
                                    <option value="GS" ${data === 'GS' ? 'selected' : ''}>위내시경</option>
                                    <option value="CS" ${data === 'CS' ? 'selected' : ''}>대장내시경</option>
                                    <option value="CT" ${data === 'CT' ? 'selected' : ''}>CT</option>
                                    <option value="UT" ${data === 'UT' ? 'selected' : ''}>초음파</option>
                                    <option value="BU" ${data === 'BU' ? 'selected' : ''}>유방초음파</option>
                                    <option value="PU" ${data === 'PU' ? 'selected' : ''}>골반초음파</option>
                                    <option value="ET" ${data === 'ET' ? 'selected' : ''}>기타</option>
                                </select>`;
                            }
                            return data;
                        }
                    },
                    { data: 'CKUP_SE' },
                    { data: 'CKUP_ARTCL' },
                    { 
                        data: 'GNDR_SE',
                        render: (data, type, row) => {
                            if (type === 'display') {
                                return `<select class="form-select form-select-sm gndr-se-select" data-row-id="${row.CKUP_GDS_EXCEL_CHC_ARTCL_SN}">
                                    <option value="C" ${data === 'C' ? 'selected' : ''}>공통</option>
                                    <option value="M" ${data === 'M' ? 'selected' : ''}>남자</option>
                                    <option value="F" ${data === 'F' ? 'selected' : ''}>여자</option>
                                </select>`;
                            }
                            return data;
                        }
                    },
                    { data: 'RMRK' },
                    { 
                        data: null, 
                        render: (data, type, row) => {
                            return `<div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-primary save-row-btn">저장</button>
                                <button type="button" class="btn btn-danger delete-item-btn">삭제</button>
                            </div>`;
                        }
                    }
                ];

                const rightTable = $(rightTableId).DataTable({
                    data: [],
                    columns: columns,
                    paging: false,           // 페이지 기능 제거
                    ordering: false,         // 정렬 기능 제거
                    lengthChange: false,
                    searching: false,
                    info: false,
                    //scrollX: true,           // 가로 스크롤 활성화
                    autoWidth: false,        // 자동 너비 비활성화
                    language: { emptyTable: "추가된 항목이 없습니다." }
                });

                // Select All Checkbox Logic
                $(document).on('change', `#checkAll4${index}`, function() {
                    const isChecked = $(this).prop('checked');
                    $(`${rightTableId} .form-check-input`).prop('checked', isChecked);
                });

                // Delete Selected Items in Group
                $(document).on(`click${eventNamespace}`, `${groupSelector} .delete-selected-choice-btn`, function() {
                    const selectedCheckboxes = $(`${rightTableId} .form-check-input:checked`);
                    if (selectedCheckboxes.length === 0) {
                        alert('삭제할 항목을 선택해주세요.');
                        return;
                    }

                    if (!confirm(`선택한 ${selectedCheckboxes.length}개 항목을 삭제하시겠습니까?`)) return;

                    const idsToDelete = [];
                    const rowsToRemove = [];

                    selectedCheckboxes.each(function() {
                        const val = $(this).val();
                        const row = rightTable.row($(this).closest('tr'));
                        if (val && val !== 'on' && val !== 'undefined') { // Existing item
                            idsToDelete.push(val);
                        }
                        rowsToRemove.push(row);
                    });

                    if (idsToDelete.length > 0) {
                        $.ajax({
                            url: BASE_URL + 'mngr/ckupGdsExcel/deleteItems/choice',
                            type: 'POST',
                            data: { 
                                ids: idsToDelete,
                                [CSRF_TOKEN_NAME]: CSRF_HASH 
                            },
                            success: function(response) {
                                if (response.success) {
                                    rowsToRemove.forEach(row => row.remove().draw());
                                } else {
                                    alert('삭제 실패: ' + response.message);
                                }
                                if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                            },
                            error: function() {
                                alert('서버 오류가 발생했습니다.');
                            }
                        });
                    } else {
                        // Only new items selected
                        rowsToRemove.forEach(row => row.remove().draw());
                    }
                });

                // Save Row
                $(document).on(`click${eventNamespace}`, `${groupSelector} .save-row-btn`, function() {
                    const tr = $(this).closest('tr');
                    const row = rightTable.row(tr);
                    const rowData = row.data();
                    
                    // Check if this is an existing item with valid SN
                    if (!rowData.CKUP_GDS_EXCEL_CHC_ARTCL_SN || rowData.CKUP_GDS_EXCEL_CHC_ARTCL_SN === null) {
                        alert('신규 항목은 "전체 저장" 버튼을 사용해주세요.');
                        return;
                    }

                    const dataToUpdate = {
                        CKUP_TYPE: rowData.CKUP_TYPE,
                        GNDR_SE: rowData.GNDR_SE,
                        [CSRF_TOKEN_NAME]: CSRF_HASH
                    };

                    $.ajax({
                        url: BASE_URL + 'mngr/ckupGdsExcel/updateChoiceItem/' + rowData.CKUP_GDS_EXCEL_CHC_ARTCL_SN,
                        type: 'POST',
                        data: dataToUpdate,
                        success: function(response) {
                            if (response.success) {
                                alert('저장되었습니다.');
                            } else {
                                alert('저장 실패: ' + response.message);
                            }
                            if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                        },
                        error: function() {
                            alert('서버 오류가 발생했습니다.');
                        }
                    });
                });

                $(document).on(`click${eventNamespace}`, `${groupSelector} .delete-item-btn`, function() {
                    rightTable.row($(this).closest('tr')).remove().draw();
                });

                $(document).on(`click${eventNamespace}`, `${groupSelector} .delete-choice-group-btn`, function() {
                    if (confirm('이 그룹을 삭제하시겠습니까?')) {
                        $(document).off(eventNamespace);
                        $(this).closest(groupSelector).remove();
                    }
                });
                
                $(document).on(`change${eventNamespace}`, `${groupSelector} .ckup-type-select`, function() {
                    const newValue = $(this).val();
                    const row = rightTable.row($(this).closest('tr'));
                    const rowData = row.data();
                    rowData.CKUP_TYPE = newValue;
                    row.data(rowData);
                });
                
                $(document).on(`change${eventNamespace}`, `${groupSelector} .gndr-se-select`, function() {
                    const newValue = $(this).val();
                    const row = rightTable.row($(this).closest('tr'));
                    const rowData = row.data();
                    rowData.GNDR_SE = newValue;
                    row.data(rowData);
                });
                
                $(document).on(`click${eventNamespace}`, `${groupSelector} .open-excel-modal-btn`, () => {
                    this.state.activeGroupIndex = index;
                    const modalWrapper = $('#modalChoiceItemsPaste');
                    modalWrapper.find('.btn-reset').click();
                    $('#choiceItemExcelModal').modal('show');
                });
            },
            populateFormForEditMode: function() {
                const data = this.config.initialData;
                if (data.choiceGroups && data.choiceGroups.length > 0) {
                    data.choiceGroups.forEach(groupData => {
                        this.addChoiceSection();
                        const currentGroupIndex = this.state.choiceSectionCounter - 1;
                        const groupContainer = $(`.choice-item-group[data-group-index="${currentGroupIndex}"]`);
                        
                        groupContainer.find('input[name="GROUP_NM[]"]').val(groupData.GROUP_NM);
                        groupContainer.find('input[name="CHC_ARTCL_CNT[]"]').val(groupData.CHC_ARTCL_CNT);
                        groupContainer.find('input[name="CHC_ARTCL_CNT2[]"]').val(groupData.CHC_ARTCL_CNT2 || 0);
                        
                        if (groupData.items && groupData.items.length > 0) {
                            $(`#ckupGdsChcArtclTable${currentGroupIndex}`).DataTable().rows.add(groupData.items).draw();
                        }
                    });
                } else {
                    this.addChoiceSection();
                }
            },
            saveAllData: function() {
                const basicInfo = {
                    CKUP_GDS_EXCEL_MNG_SN: $('#CKUP_GDS_EXCEL_MNG_SN').val(),
                    HSPTL_SN: $('#HSPTL_SN_sel').val(),
                    CKUP_YYYY: $('#CKUP_YYYY_sel').val(),
                    CKUP_GDS_NM: $('#CKUP_GDS_NM').val(),
                    SPRT_SE: $('#SPRT_SE').val(),
                    FAM_SPRT_SE: $('#FAM_SPRT_SE').val()
                };

                const basicItems = []; 
                const newBasicItems = []; 
                $('#basicItemsTable tbody tr').each(function() {
                    const id = $(this).data('id');
                    if (id) {
                        basicItems.push(id);
                    } else if ($(this).hasClass('new-item')) {
                        const cells = $(this).find('td');
                        newBasicItems.push({
                            CKUP_SE: cells.eq(1).text(), 
                            CKUP_ARTCL: cells.eq(2).text(),
                            DSS: cells.eq(3).text(),
                            GNDR_SE: cells.eq(4).find('select').val(), 
                            RMRK: cells.eq(5).text()
                        });
                    }
                });

                const choiceGroups = [];
                let isValid = true;
                $('.choice-item-group').each(function() {
                    const groupIndex = $(this).data('group-index');
                    const groupNameInput = $(this).find(`input[name="GROUP_NM[]"]`);
                    const choiceCountInput = $(this).find(`input[name="CHC_ARTCL_CNT[]"]`);
                    
                    const groupName = groupNameInput.val();
                    const choiceCount = choiceCountInput.val();
                    
                    if (!groupName || groupName.trim() === '') {
                        alert('선택항목명을 입력해주세요.');
                        groupNameInput.focus();
                        isValid = false;
                        return false; // Break loop
                    }
                    
                    if (!choiceCount || choiceCount.trim() === '') {
                        alert('선택갯수를 입력해주세요.');
                        choiceCountInput.focus();
                        isValid = false;
                        return false; // Break loop
                    }

                    const choiceCount2 = $(this).find(`input[name="CHC_ARTCL_CNT2[]"]`).val();
                    const tableData = $(`#ckupGdsChcArtclTable${groupIndex}`).DataTable().rows().data().toArray();
                    
                    const items = [];
                    const newItems = [];
                    
                    tableData.forEach(item => {
                        if (item.CKUP_GDS_EXCEL_CHC_ARTCL_SN) {
                            items.push(item.CKUP_GDS_EXCEL_CHC_ARTCL_SN);
                        } else {
                            newItems.push({
                                CKUP_SE: item.CKUP_SE,
                                CKUP_TYPE: item.CKUP_TYPE,
                                CKUP_ARTCL: item.CKUP_ARTCL,
                                GNDR_SE: item.GNDR_SE,
                                RMRK: item.RMRK
                            });
                        }
                    });

                    if (groupName) {
                        choiceGroups.push({ 
                            GROUP_NM: groupName, 
                            CHC_ARTCL_CNT: choiceCount, 
                            CHC_ARTCL_CNT2: choiceCount2, 
                            items: items,
                            newItems: newItems
                        });
                    }
                });

                if (!isValid) return;

                const addChoiceItems = [];
                const newAddChoiceItems = [];
                $('#addChoiceItemsTable tbody tr').each(function() {
                    const id = $(this).data('id');
                    if (id) {
                        addChoiceItems.push(id);
                    } else if ($(this).hasClass('new-item')) {
                        const cells = $(this).find('td');
                        newAddChoiceItems.push({
                            CKUP_TYPE: cells.eq(1).find('select').val(),
                            CKUP_SE: cells.eq(2).text(),
                            CKUP_ARTCL: cells.eq(3).text(),
                            CKUP_CST: cells.eq(4).find('input').val(),
                            GNDR_SE: cells.eq(5).find('select').val()
                        });
                    }
                });

                const data = {
                    basicInfo,
                    basicItems,
                    newBasicItems,
                    choiceGroups,
                    addChoiceItems,
                    newAddChoiceItems
                };

                $.ajax({
                    url: BASE_URL + 'mngr/ckupGdsExcel/save',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', [CSRF_TOKEN_NAME]: CSRF_HASH },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            if (response.ckup_gds_sn) window.location.href = BASE_URL + 'mngr/ckupGdsExcel/edit/' + response.ckup_gds_sn;
                        } else {
                            alert(response.message);
                        }
                        if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                    }
                });
            }
        };

        $(document).ready(function() {
            CkupGdsManager.init();
            
            // Global Delete Row Button (for static tables)
            $(document).on('click', '.delete-row-btn', function() {
                if (!confirm('이 항목을 삭제하시겠습니까?')) return;
                const btn = $(this);
                const type = btn.data('type');
                const id = btn.data('id');
                
                if (id) {
                    // Existing item, call server
                    $.ajax({
                        url: BASE_URL + 'mngr/ckupGdsExcel/deleteItem/' + type + '/' + id,
                        type: 'POST',
                        data: { [CSRF_TOKEN_NAME]: CSRF_HASH },
                        success: function(response) {
                            if (response.success) btn.closest('tr').remove();
                            else alert('삭제 실패');
                            if (response.csrf_hash) CSRF_HASH = response.csrf_hash;
                        }
                    });
                } else {
                    // New item (DOM only)
                    btn.closest('tr').remove();
                }
            });
        });
    </script>
</body>
</html>
