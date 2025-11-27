<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupArtclMngModel extends Model
{
    protected $table            = 'CKUP_ARTCL_MNG';
    protected $primaryKey       = 'CKUP_ARTCL_SN';
    protected $useSoftDeletes   = false; // 수동으로 DEL_YN 처리

    protected $allowedFields    = [
        'CKUP_SE', 'CKUP_ARTCL', 'ARTCL_CODE', 'DSS', 'HSPTL_SN', 'GNDR_SE',
        'CKUP_TYPE', 'CKUP_CST', 'RMRK', // Add new fields
        'REG_ID', 'MDFCN_ID', 'DEL_YN'
    ];

    // 타임스탬프 설정
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    // 콜백 설정
    protected $beforeInsert   = ['setUserData'];
    protected $beforeUpdate   = ['setUpdateUserData'];

    // 기본 유효성 검사 규칙
    protected $validationRules = [
        'CKUP_SE'    => 'required|max_length[50]',
        'CKUP_ARTCL' => 'required|max_length[100]',
        'ARTCL_CODE' => 'permit_empty|max_length[50]',
        'DSS'        => 'permit_empty|max_length[100]',
        'CKUP_TYPE'  => 'permit_empty|max_length[2]', // Optional based on schema
        'CKUP_CST'   => 'required|max_length[10]',
        'RMRK'       => 'permit_empty|max_length[200]',
    ];

    protected $validationMessages = [
        'CKUP_SE' => [
            'required'   => '검진구분은 필수 입력 항목입니다.',
            'max_length' => '검진구분은 최대 50자까지 입력 가능합니다.',
        ],
        'CKUP_ARTCL' => [
            'required'   => '검진항목명은 필수 입력 항목입니다.',
            'max_length' => '검진항목명은 최대 100자까지 입력 가능합니다.',
            'is_unique'  => '이미 등록된 검진항목명입니다.'
        ],
        'ARTCL_CODE' => [
            'max_length' => '항목코드는 최대 50자까지 입력 가능합니다.',
        ],
        'DSS' => [
            'max_length' => '질환명은 최대 100자까지 입력 가능합니다.',
        ],
        'CKUP_CST' => [
            'required'   => '검사비용은 필수 입력 항목입니다.',
            'max_length' => '검사비용은 최대 10자까지 입력 가능합니다.',
        ],
        'RMRK' => [
            'max_length' => '비고는 최대 200자까지 입력 가능합니다.',
        ],
    ];

    /**
     * 유니크 검증 규칙을 동적으로 설정
     */
    public function setUniqueValidation(bool $isUpdate = false, ?int $id = null): void
    {
        // 기존 규칙을 재설정하여 중복 규칙 방지
        $baseRules = [
            'CKUP_SE'    => 'required|max_length[50]',
            'CKUP_ARTCL' => 'required|max_length[100]',
            'ARTCL_CODE' => 'permit_empty|max_length[50]',
            'DSS'        => 'permit_empty|max_length[100]',
        ];
        
        $uniqueRule = "is_unique[{$this->table}.CKUP_ARTCL";
        if ($isUpdate && $id !== null) {
            $uniqueRule .= ",{$this->primaryKey},{$id}";
        }
        $uniqueRule .= "]";
        
        $baseRules['CKUP_ARTCL'] .= '|' . $uniqueRule;
        $this->validationRules = $baseRules;
    }

    /**
     * Insert 시 사용자 정보 설정
     */
    protected function setUserData(array $data): array
    {
        $currentUserId = $this->getCurrentUserId();
        
        $data['data']['REG_ID'] = $currentUserId;
        $data['data']['MDFCN_ID'] = $currentUserId;
        $data['data']['DEL_YN'] = $data['data']['DEL_YN'] ?? 'N';
        
        return $data;
    }

    /**
     * Update 시 사용자 정보 설정
     */
    protected function setUpdateUserData(array $data): array
    {
        $data['data']['MDFCN_ID'] = $this->getCurrentUserId();
        return $data;
    }

    /**
     * 현재 사용자 ID 가져오기
     */
    private function getCurrentUserId(): string
    {
        return session()->get('user_id') ?? 
               (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM');
    }

    /**
     * 소프트 삭제 처리
     */
    public function softDeleteCkupArtcl($id): bool
    {
        return $this->update($id, ['DEL_YN' => 'Y']);
    }

    /**
     * 검진항목명 조회
     */
    public function getCheckupItemNameBySn($ckupArtclSn): ?array
    {
        return $this->select('CKUP_ARTCL')
                    ->where('CKUP_ARTCL_SN', $ckupArtclSn)
                    ->where('DEL_YN', 'N')
                    ->first();
    }

    /**
     * 활성 상태인 검진항목들만 조회 (검진구분별 정렬 추가)
     */
    public function getActiveItems($hsptlSn = null, $searchKeyword = null, $ckupType = null): array
    {
        $builder = $this->select('CKUP_ARTCL_MNG.*, HSPTL_MNG.HSPTL_NM')
                        ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = CKUP_ARTCL_MNG.HSPTL_SN', 'left')
                        ->where('CKUP_ARTCL_MNG.DEL_YN', 'N');

        if ($hsptlSn) {
            $builder->where('CKUP_ARTCL_MNG.HSPTL_SN', $hsptlSn);
        }

        if ($searchKeyword) {
            $builder->groupStart()
                    ->like('CKUP_ARTCL_MNG.CKUP_ARTCL', $searchKeyword)
                    ->orLike('CKUP_ARTCL_MNG.DSS', $searchKeyword)
                    ->groupEnd();
        }

        if ($ckupType) {
            $builder->where('CKUP_ARTCL_MNG.CKUP_TYPE', $ckupType);
        }

        return $builder->orderBy('CKUP_ARTCL_MNG.CKUP_SE', 'ASC')
                      ->orderBy('CKUP_ARTCL_MNG.CKUP_ARTCL_SN', 'DESC')
                      ->findAll();
    }

    /**
     * 검진구분별 검진항목 조회
     */
    public function getItemsByCheckupType(string $ckupSe): array
    {
        return $this->where('DEL_YN', 'N')
                    ->where('CKUP_SE', $ckupSe)
                    ->orderBy('CKUP_ARTCL_SN', 'DESC')
                    ->findAll();
    }

    /**
     * 검진구분 목록 조회
     */
    public function getCheckupTypes(): array
    {
        return $this->select('CKUP_SE')
                    ->where('DEL_YN', 'N')
                    ->groupBy('CKUP_SE')
                    ->orderBy('CKUP_SE', 'ASC')
                    ->findAll();
    }
}