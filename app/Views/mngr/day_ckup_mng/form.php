<style>
    #dayCkupFormList1 td, #dayCkupFormList1 th,
    #dayCkupFormList2 td, #dayCkupFormList2 th {
        vertical-align: middle;
    }
    .label-lg {
        font-size: calc(0.875rem + 4pt);
        font-weight: 500;
    }
</style>
<form id="day-ckup-form" onsubmit="return false;">
    <?= csrf_field() ?>
    <input type="hidden" name="hsptl_sn" id="hsptl_sn_form" value="<?= esc($selected_hsptl_sn) ?>">
    <input type="hidden" name="ckup_year" id="ckup_year_form" value="<?= esc($selected_ckup_year) ?>">

    <div class="row">
        <!-- Left Column: Main Form -->
        <div class="col-md-12">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label for="hsptl_sn_select" class="col-form-label">검진병원</label>
                        </div>
                        <div class="col">
                            <select id="hsptl_sn_select" class="form-select" <?= $is_edit ? 'disabled' : '' ?>>
                                <?php if (session()->get('user_type') !== 'H'): ?>
                                    <option value="">병원을 선택하세요</option>
                                <?php endif; ?>
                                <?php foreach ($hospitals as $hospital): ?>
                                    <?php if (session()->get('user_type') === 'H' && $hospital['HSPTL_SN'] != session()->get('hsptl_sn')) continue; ?>
                                    <option value="<?= esc($hospital['HSPTL_SN']) ?>" <?= ($selected_hsptl_sn == $hospital['HSPTL_SN']) ? 'selected' : '' ?>><?= esc($hospital['HSPTL_NM']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label for="ckup_year_select" class="col-form-label">검진년도</label>
                        </div>
                        <div class="col">
                            <select id="ckup_year_select" class="form-select" <?= $is_edit ? 'disabled' : '' ?>>
                                <option value="">년도를 선택하세요</option>
                                <?php 
                                    $currentYear = date('Y');
                                    for ($i = $currentYear + 1; $i >= $currentYear - 5; $i--): 
                                ?>
                                    <option value="<?= $i ?>" <?= ($selected_ckup_year == $i) ? 'selected' : '' ?>><?= $i ?>년</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <label class="form-label mb-0 label-lg me-3">평일</label>
                        <select id="weekday_ckup_artcl_select" class="form-select form-select-sm me-1" style="width: 150px;">
                            <option value="">검사항목 선택</option>
                            <option value="CS">대장</option>
                            <option value="GS">위내시경</option>
                            <option value="PU">골반초음파</option>
                            <option value="BU">유방초음파</option>
                            <option value="UT">초음파</option>
                            <option value="CT">CT</option>
                        </select>
                        <button type="button" id="add_weekday_item_btn" class="btn btn-sm btn-primary">추가</button>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="weekday_total_personnel" class="col-form-label me-2">전체인원</label>
                        <input type="text" id="weekday_total_personnel" class="form-control form-control-sm me-1" style="width: 100px;"/>
                    </div>
                </div>
                <table id="dayCkupFormList1" class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>검사구분</th>
                            <th>인원</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <hr class="my-4">

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <label class="form-label mb-0 label-lg me-3" style="color: blue;">토요일</label>
                        <select id="saturday_ckup_artcl_select" class="form-select form-select-sm me-1" style="width: 150px;">
                            <option value="">검사항목 선택</option>
                            <option value="CS">대장</option>
                            <option value="GS">위내시경</option>
                            <option value="PU">골반초음파</option>
                            <option value="BU">유방초음파</option>
                            <option value="UT">초음파</option>
                            <option value="CT">CT</option>
                        </select>
                        <button type="button" id="add_saturday_item_btn" class="btn btn-sm btn-primary">추가</button>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="saturday_total_personnel" class="col-form-label me-2">전체인원</label>
                        <input type="text" id="saturday_total_personnel" class="form-control form-control-sm me-1" style="width: 100px;"/>
                    </div>
                </div>
                <table id="dayCkupFormList2" class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>검사구분</th>
                            <th>인원</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-warning mt-3" style="color: red; font-weight: bold;">※ 이 설정은 선택한 연도의 모든 평일/토요일에 일괄 적용되며, 기존 데이터는 덮어씌워집니다.</div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">닫기</button>
        <button type="submit" class="btn btn-primary" id="save-btn">저장</button>
    </div>
</form>
<script>
$(document).ready(function() {
    const weekdayDetails = <?= json_encode($weekday_details) ?>;
    const saturdayDetails = <?= json_encode($saturday_details) ?>;

    function getGenderText(gndrSe) {
        switch(gndrSe) {
            case 'M': return '남자';
            case 'F': return '여자';
            case 'C': return '남녀';
            default: return gndrSe;
        }
    }

    function getCheckupTypeText(ckupSe) {
        switch(ckupSe) {
            case 'ET': return '기타';
            case 'CT': return 'CT';
            case 'GS': return '위내시경';
            case 'UT': return '초음파';
            case 'CS': return '대장내시경';
            case 'PU': return '골반초음파';
            case 'BU': return '유방초음파';
            default: return ckupSe;
        }
    }

    function addArticleToTable(ckupSe, daySe, maxCnt = 1) {
        const tableBody = (daySe === 'WEEKDAY') ? $('#dayCkupFormList1 tbody') : $('#dayCkupFormList2 tbody');

        let isDuplicate = false;
        tableBody.find('tr').each(function() {
            if (String($(this).data('ckup-se')) === String(ckupSe)) {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            alert('이미 추가된 항목입니다.');
            return;
        }

        const rowCount = tableBody.find('tr').length + 1;
        const newRow = `
            <tr data-ckup-se="${ckupSe}">
                <td>${rowCount}</td>
                <td>${getCheckupTypeText(ckupSe)}</td>
                <td><input type="number" class="form-control form-control-sm" style="width: 80px;" value="${maxCnt}" min="1"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">삭제</button></td>
            </tr>`;
        tableBody.append(newRow);
    }

    function populateInitialData() {
        const weekdayTotalPersonnelRecord = weekdayDetails.find(item => item.CKUP_SE === 'TOTAL');
        if(weekdayTotalPersonnelRecord) {
            $('#weekday_total_personnel').val(weekdayTotalPersonnelRecord.MAX_CNT);
        }

        const saturdayTotalPersonnelRecord = saturdayDetails.find(item => item.CKUP_SE === 'TOTAL');
        if(saturdayTotalPersonnelRecord) {
            $('#saturday_total_personnel').val(saturdayTotalPersonnelRecord.MAX_CNT);
        }

        const weekdayExams = weekdayDetails.filter(item => item.CKUP_SE !== 'TOTAL');
        if (weekdayExams.length > 0) {
            weekdayExams.forEach(item => {
                addArticleToTable(item.CKUP_SE, 'WEEKDAY', item.MAX_CNT);
            });
        }
        const saturdayExams = saturdayDetails.filter(item => item.CKUP_SE !== 'TOTAL');
        if (saturdayExams.length > 0) {
            saturdayExams.forEach(item => {
                addArticleToTable(item.CKUP_SE, 'SAT', item.MAX_CNT);
            });
        }
    }

    // Initial Load
    populateInitialData();

    // Event Handlers
    $('#hsptl_sn_select').on('change', function() {
        $('#hsptl_sn_form').val($(this).val());
    });

    $('#ckup_year_select').on('change', function() {
        $('#ckup_year_form').val($(this).val());
    });

    $('#add_weekday_item_btn').on('click', function() {
        const selectedOption = $('#weekday_ckup_artcl_select option:selected');
        const ckupSe = selectedOption.val();
        if (ckupSe) {
            addArticleToTable(ckupSe, 'WEEKDAY', 1);
        }
    });

    $('#add_saturday_item_btn').on('click', function() {
        const selectedOption = $('#saturday_ckup_artcl_select option:selected');
        const ckupSe = selectedOption.val();
        if (ckupSe) {
            addArticleToTable(ckupSe, 'SAT', 1);
        }
    });

    $('#dayCkupFormList1, #dayCkupFormList2').on('click', '.remove-item-btn', function() {
        $(this).closest('tr').remove();
    });

    

    // Form Submission
    $('#day-ckup-form').on('submit', function(e) {
        e.preventDefault();
        const hsptlSn = $('#hsptl_sn_form').val();
        const ckupYear = $('#ckup_year_form').val();

        if (!hsptlSn || !ckupYear) {
            showAjaxMessage('설정 대상 검진병원과 검진년도를 모두 선택해야 합니다.', 'danger');
            return;
        }

        if (!confirm(ckupYear + '년 설정을 저장하시겠습니까? 기존 데이터는 모두 삭제되고 새로 생성됩니다.')) {
            return;
        }

        const weekday_items = [];
        $('#dayCkupFormList1 tbody tr').each(function() {
            weekday_items.push({
                ckup_se: $(this).data('ckup-se'),
                max_cnt: $(this).find('input[type="number"]').val()
            });
        });

        const saturday_items = [];
        $('#dayCkupFormList2 tbody tr').each(function() {
            saturday_items.push({
                ckup_se: $(this).data('ckup-se'),
                max_cnt: $(this).find('input[type="number"]').val()
            });
        });

        const formData = {
            hsptl_sn: hsptlSn,
            ckup_year: ckupYear,
            weekday_total_personnel: $('#weekday_total_personnel').val(),
            saturday_total_personnel: $('#saturday_total_personnel').val(),
            weekday_items: weekday_items,
            saturday_items: saturday_items,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        };

        $.ajax({
            url: BASE_URL + 'mngr/dayCkupMng/save',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#save-btn').prop('disabled', true).text('저장 중...');
            },
            success: function(response) {
                if (response.success) {
                    showAjaxMessage(response.message, 'success');
                    $('#showModal').modal('hide');
                    mainTable.ajax.reload();
                } else {
                    showAjaxMessage(response.message || '저장에 실패했습니다.', 'danger');
                }
            },
            error: function() {
                showAjaxMessage('서버 통신 오류가 발생했습니다.', 'danger');
            },
            complete: function() {
                $('#save-btn').prop('disabled', false).text('저장');
            }
        });
    });
});
</script>