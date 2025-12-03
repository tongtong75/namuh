<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-warning edit-item-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-bs-toggle="modal" data-bs-target="#mainModal">대상수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-name="<?= esc($item['NAME']) ?>">대상삭제</button>

    <?php
        // 비밀번호 초기화 및 가족추가 버튼은 관계가 본인(S)이고, 직원명과 수검자명이 동일할 경우에만 표시
        $canAddFamily = ($item['RELATION'] === 'S' && $item['NAME'] === $item['CKUP_NAME']);
    ?>
    
    <?php if ($canAddFamily): ?>
    <button type="button" class="btn btn-sm btn-secondary reset-password-btn"
        data-id="<?= esc($item['CKUP_TRGT_SN']) ?>" data-name="<?= esc($item['NAME']) ?>">비밀번호 초기화</button>
    <?php endif; ?>

    <?php if ($canAddFamily): ?>
    <button type="button" class="btn btn-sm btn-info add-family-btn"
            data-co-sn="<?= esc($item['CO_SN'] ?? '') ?>"
            data-ckup-yyyy="<?= esc($item['CKUP_YYYY']) ?>"
            data-business-num="<?= esc($item['BUSINESS_NUM']) ?>"
            data-name="<?= esc($item['NAME']) ?>">가족추가U</button>
    <?php endif; ?>

    <button type="button" class="btn btn-sm btn-outline-info manage-memo-btn"
            data-id="<?= esc($item['CKUP_TRGT_SN']) ?>"
            data-name="<?= esc($item['NAME']) ?>"
            data-memo-sn="<?= esc($memo_sn ?? '') ?>"
            ><?= $has_memo ? '메모 수정/보기' : '메모 등록' ?>
    </button>
</div>