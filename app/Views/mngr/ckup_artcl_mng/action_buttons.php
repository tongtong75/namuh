<?php
$artclId = $item['CKUP_ARTCL_SN'];
$artclName = esc($item['CKUP_ARTCL']);
?>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-item-btn"
        data-id="<?= $artclId ?>" data-bs-toggle="modal" data-bs-target="#showModal">수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn"
        data-id="<?= $artclId ?>" data-name="<?= $artclName ?>">삭제</button>
</div>