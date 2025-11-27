<?php

namespace App\Models;

use CodeIgniter\Model;

class HsptlMngModel extends Model
{
    protected $table            = 'HSPTL_MNG';
    protected $primaryKey       = 'HSPTL_SN';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'HSPTL_NM', 'PIC_NM', 'CNPL1', 'RGN',
        'REG_ID', 'MDFCN_ID', 'DEL_YN'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setUserIdOnInsert', 'setDefaultDelYn'];
    protected $beforeUpdate   = ['setUserIdOnUpdate'];

    // 기본 유효성 검사 규칙 (is_unique 제외)
    protected $baseValidationRules = [
        'HSPTL_NM' => 'required|max_length[100]',
        'PIC_NM'   => 'permit_empty|max_length[50]',
        'CNPL1'    => 'permit_empty|max_length[50]',
        'RGN'    => 'permit_empty|max_length[10]',
    ];

    protected $validationMessages   = [
        'HSPTL_NM' => [
            'required'   => '병원명은 필수 입력 항목입니다.',
            'max_length' => '병원명은 최대 100자까지 입력 가능합니다.',
            'is_unique'  => '이미 등록된 병원명입니다.' // is_unique 메시지는 build... Rules에서 추가될 때 사용됨
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;


    /**
     * 상황(등록/수정)에 맞는 유효성 검사 규칙을 생성하여 반환합니다.
     * BaseModel의 getValidationRules와 충돌하지 않도록 다른 이름으로 정의합니다.
     *
     * @param bool $isUpdate 수정 모드인지 여부
     * @param int|null $id 수정 시 현재 병원의 HSPTL_SN (is_unique 검사용)
     * @return array
     */
    public function buildValidationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = $this->baseValidationRules; // 기본 규칙 가져오기
        $activeRecordCondition = "DEL_YN = 'N'";
        if ($isUpdate && $id !== null) {
            // 수정 시: HSPTL_NM에 is_unique 규칙 추가 (현재 레코드는 제외)
            $rules['HSPTL_NM'] = "required|max_length[100]|is_unique[{$this->table}.HSPTL_NM,{$this->primaryKey},{$id},{$activeRecordCondition}]";
        } else {
            // 등록 시: HSPTL_NM에 is_unique 규칙 추가 (모든 레코드 대상)
            $rules['HSPTL_NM'] = "required|max_length[100]|is_unique[{$this->table}.HSPTL_NM,{$this->primaryKey},0,{$activeRecordCondition}]";
            
        }
        // 필요하다면 다른 필드에 대한 규칙도 여기에 동적으로 추가/변경할 수 있습니다.
        return $rules;
    }


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

    public function softDeleteHsptl($id)
    {
        // setUserIdOnUpdate 콜백이 MDFCN_ID를 처리하고,
        // useTimestamps가 MDFCN_YMD를 처리하므로 DEL_YN만 설정해도 됨.
        // 만약 콜백이나 useTimestamps가 원하는 대로 동작하지 않으면 여기서 직접 설정.
        // $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM');
        return $this->update($id, [
            'DEL_YN' => 'Y',
            // 'MDFCN_ID' => $currentUserId, // setUserIdOnUpdate 콜백이 처리
        ]);
    }

    public function getHospitalNameBySn($hsptlSn)
    {
        return $this->select('HSPTL_NM')
                    ->where('HSPTL_SN', $hsptlSn)
                    ->where('DEL_YN', 'N')
                    ->first();
    }
    
    public function getAllActiveHsptls()
    {
        return $this->where('DEL_YN', 'N')
                    ->orderBy('HSPTL_NM', 'ASC')
                    ->findAll();
    }

    public function getActiveHospitals(): array
    {
        return $this->where('DEL_YN', 'N')
                    ->orderBy('HSPTL_NM', 'ASC')
                    ->findAll();
    }
}