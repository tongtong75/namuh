<?php
// App/Views/mngr/chc_artcl_mng/action_buttons.php

// $item is passed from the controller, containing a row from CHC_ARTCL_MNG table
$chcArtclId = esc($item['CHC_ARTCL_SN']);       // Primary Key for CHC_ARTCL_MNG
$chcArtclName = esc($item['CKUP_ARTCL']);   // Descriptive name field (검사항목) from CHC_ARTCL_MNG
?>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-item-btn"
        data-id="<?= $chcArtclId ?>" 
        data-bs-toggle="modal" 
        data-bs-target="#showModal">
        수정
    </button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn"
        data-id="<?= $chcArtclId ?>" 
        data-name="<?= $chcArtclName ?>">
        삭제
    </button>
</div>