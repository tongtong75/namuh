<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-item-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-bs-toggle="modal" data-bs-target="#mainModal">대상수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-name="<?= esc($item['NAME']) ?>">대상삭제</button>
    <button type="button" class="btn btn-sm btn-secondary reset-password-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-name="<?= esc($item['NAME']) ?>">비밀번호 초기화</button>


    <button type="button" class="btn btn-sm btn-outline-info manage-memo-btn"
            data-id="<?= esc($item['CKUP_TRGT_SN']) ?>"
            data-name="<?= esc($item['NAME']) ?>"
            data-memo-sn="<?= esc($memo_sn ?? '') ?>"
            ><?= $has_memo ? '메모 수정/보기' : '메모 등록' ?>
    </button>
</div>