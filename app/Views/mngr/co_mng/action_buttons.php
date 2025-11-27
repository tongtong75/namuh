<div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary custom-toggle link-hsptl-btn" data-bs-toggle="modal" data-bs-target="#showLinkModal" data-id="<?= esc($item['CO_SN'], 'attr') ?>" data-name="<?= esc($item['CO_NM'], 'attr') ?>">
        <span class="icon-on"><i class="ri-links-line"></i> 병원연결</span>
    </button>
    
    <button type="button" class="btn btn-sm btn-info edit-item-btn" data-bs-toggle="modal" data-bs-target="#showModal" data-id="<?= esc($item['CO_SN'], 'attr') ?>">수정</button>
    <button type="button" class="btn btn-sm btn-danger delete-item-btn" data-id="<?= esc($item['CO_SN'], 'attr') ?>" data-name="<?= esc($item['CO_NM'], 'attr') ?>">삭제</button>
</div>