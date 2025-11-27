<?php

namespace App\Models;

use CodeIgniter\Model;

class ChcArtclMngModel extends Model
{
    protected $table            = 'CHC_ARTCL_MNG';
    protected $primaryKey       = 'CHC_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // 수동으로 DEL_YN 처리

    protected $allowedFields = [
        'CKUP_ARTCL', 'ARTCL_CODE', 'GNDR_SE', 'CKUP_SE', 'CKUP_CST', 'AGREE_SUBMIT_YN', 'RMRK', 'HSPTL_SN',
        'REG_ID', 'MDFCN_ID', 'DEL_YN'
    ];

    // 타임스탬프 설정
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    // 콜백 설정
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setUserIdOnInsert', 'setDefaultDelYn'];
    protected $beforeUpdate   = ['setUserIdOnUpdate'];

    // 기본 유효성 검사 규칙
    protected $baseValidationRules = [
        'CKUP_ARTCL' => 'required|max_length[100]',
        'ARTCL_CODE' => 'permit_empty|max_length[10]',
        'CKUP_CST'   => 'required|max_length[10]',
        'CKUP_SE'    => 'permit_empty|max_length[2]',
        'GNDR_SE'    => 'permit_empty|max_length[1]',
        'AGREE_SUBMIT_YN' => 'permit_empty|max_length[1]|in_list[Y,N]',
        'RMRK'       => 'permit_empty|max_length[200]',
    ];

    protected $validationMessages = [
        'CKUP_ARTCL' => [
            'required'   => '검사항목은 필수 입력 항목입니다.',
            'max_length' => '검사항목은 최대 100자까지 입력 가능합니다.',
        ],
        'ARTCL_CODE' => [
            'max_length' => '항목코드는 최대 10자까지 입력 가능합니다.',
        ],
        'CKUP_CST' => [
            'required'   => '검사비용은 필수 입력 항목입니다.',
            'max_length' => '검사비용은 최대 10자까지 입력 가능합니다.',
        ],
        'GNDR_SE' => [
            'max_length' => '성별구분은 최대 1자까지 입력 가능합니다. (M, F, C)',
        ],
        'AGREE_SUBMIT_YN' => [
            'max_length' => '동의서제출여부는 최대 1자까지 입력 가능합니다.',
            'in_list'    => '동의서제출여부는 Y 또는 N이어야 합니다.',
        ],
        'CKUP_SE' => [
            'max_length' => '검사구분은 최대 2자까지 입력 가능합니다.',
        ],
        'RMRK' => [
            'max_length' => '비고는 최대 200자까지 입력 가능합니다.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * 유효성 검사 규칙 빌드
     */
    public function buildValidationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = $this->baseValidationRules;
        
        // 필요시 고유성 검사 규칙 추가
        // if ($isUpdate && $id !== null) {
        //     $rules['CKUP_ARTCL'] .= "|is_unique[{$this->table}.CKUP_ARTCL,{$this->primaryKey},{$id}]";
        // } else {
        //     $rules['CKUP_ARTCL'] .= "|is_unique[{$this->table}.CKUP_ARTCL]";
        // }
        
        return $rules;
    }

    /**
     * 생성 시 사용자 ID 설정
     */
    protected function setUserIdOnInsert(array $data): array
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM');
        
        if (isset($data['data'])) {
            $data['data']['REG_ID'] = $currentUserId;
            if (!isset($data['data']['MDFCN_ID'])) {
                $data['data']['MDFCN_ID'] = $currentUserId;
            }
        } else {
            $data['REG_ID'] = $currentUserId;
            if (!isset($data['MDFCN_ID'])) {
                $data['MDFCN_ID'] = $currentUserId;
            }
        }
        
        return $data;
    }

    /**
     * 수정 시 사용자 ID 설정
     */
    protected function setUserIdOnUpdate(array $data): array
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM');
        
        if (isset($data['data'])) {
            $data['data']['MDFCN_ID'] = $currentUserId;
        } else {
            $data['MDFCN_ID'] = $currentUserId;
        }
        
        return $data;
    }
    
    /**
     * 기본 삭제 여부 설정
     */
    protected function setDefaultDelYn(array $data): array
    {
        if (isset($data['data'])) {
            if (!isset($data['data']['DEL_YN'])) {
                $data['data']['DEL_YN'] = 'N';
            }
        } else {
            if (!isset($data['DEL_YN'])) {
                $data['DEL_YN'] = 'N';
            }
        }
        
        return $data;
    }

    /**
     * 소프트 삭제 실행
     */
    public function softDeleteChcArtcl($id)
    {
        return $this->update($id, ['DEL_YN' => 'Y']);
    }

    /**
     * SN으로 선택항목 설명 조회
     */
    public function getSelectionItemDescBySn($chcArtclSn)
    {
        return $this->select('CKUP_ARTCL')
                    ->where($this->primaryKey, $chcArtclSn)
                    ->where('DEL_YN', 'N')
                    ->first();
    }

    public function getFilteredItems($hsptlSn = null, $searchKeyword = null): array
    {
        $builder = $this->select('CHC_ARTCL_MNG.*, HSPTL_MNG.HSPTL_NM')
                        ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = CHC_ARTCL_MNG.HSPTL_SN', 'left')
                        ->where('CHC_ARTCL_MNG.DEL_YN', 'N');

        if ($hsptlSn) {
            $builder->where('CHC_ARTCL_MNG.HSPTL_SN', $hsptlSn);
        }

        if ($searchKeyword) {
            $builder->groupStart()
                    ->like('CHC_ARTCL_MNG.CKUP_ARTCL', $searchKeyword)
                    ->orLike('CHC_ARTCL_MNG.RMRK', $searchKeyword)
                    ->groupEnd();
        }

        return $builder->orderBy('CHC_ARTCL_MNG.CKUP_ARTCL', 'ASC')
                      ->findAll();
    }
}