<?php
// 이 파일은 controller의 ajax_list에서 view() 헬퍼로 호출됩니다.
$ckupGdsSn = esc($ckupGds['CKUP_GDS_SN']);
?>

<div class="d-flex gap-2">
    <!-- 수정 버튼 링크 변경 -->
    <a href="<?= site_url('mngr/ckupGdsMng/edit/' . $ckupGdsSn) ?>" class="btn btn-sm btn-warning">수정</a>
    
    <!-- 보기/삭제 버튼은 필요에 따라 유지하거나 제거합니다. -->
    <button type="button" class="btn btn-sm btn-primary view-item-btn"
        data-id="<?= $ckupGdsSn ?>">
        보기
    </button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn"
        data-id="<?= $ckupGdsSn ?>" >
        삭제
    </button>
</div>