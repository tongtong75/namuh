<?php
$mngrId = $mngr['MNGR_SN'];
$mngrName = esc($mngr['MNGR_NM']);
$mngrLoginId = esc($mngr['MNGR_ID']);
?>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-mngr-btn"
            data-id="<?= $mngrId ?>" data-bs-toggle="modal" data-bs-target="#mngrModal">수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-mngr-btn"
            data-id="<?= $mngrId ?>" data-name="<?= $mngrName ?> (<?= $mngrLoginId ?>)">삭제</button>
</div>