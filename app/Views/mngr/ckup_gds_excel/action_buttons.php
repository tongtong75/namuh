<div class="d-flex gap-2">
    <a href="<?= base_url('mngr/ckupGdsExcel/edit/' . $ckupGds['CKUP_GDS_EXCEL_MNG_SN']) ?>" class="btn btn-sm btn-outline-primary">
        <i class="ri-edit-line align-bottom me-1"></i> 수정
    </a>
    <button type="button" class="btn btn-sm btn-outline-success copy-item-btn" data-id="<?= $ckupGds['CKUP_GDS_EXCEL_MNG_SN'] ?>">
        <i class="ri-file-copy-line align-bottom me-1"></i> 복사
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger delete-item-btn" data-id="<?= $ckupGds['CKUP_GDS_EXCEL_MNG_SN'] ?>">
        <i class="ri-delete-bin-line align-bottom me-1"></i> 삭제
    </button>
</div>
