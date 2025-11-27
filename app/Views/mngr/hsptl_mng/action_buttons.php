<?php
$hsptlId = $hsptl['HSPTL_SN'];
$hsptlName = esc($hsptl['HSPTL_NM']);
?>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-hsptl-btn"
            data-id="<?= $hsptlId ?>" data-bs-toggle="modal" data-bs-target="#showModal">수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-hsptl-btn"
            data-id="<?= $hsptlId ?>" data-name="<?= $hsptlName ?>">삭제</button>
</div>