<?php

namespace App\Models;

use CodeIgniter\Model;

class CoMngModel extends Model
{
    protected $table            = 'CO_MNG';
    protected $primaryKey       = 'CO_SN';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Manually handling DEL_YN

    protected $allowedFields    = [
        'CO_NM', 'PIC_NM', 'CNPL', 'BGNG_YMD', 'END_YMD',
        'CO_MNGR_ID', 'CO_MNGR_PSWD',
        'REG_ID', 'MDFCN_ID', 'DEL_YN'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'date'; // DB columns are 'date'
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setUserIdOnInsert', 'setDefaultDelYn'];
    protected $beforeUpdate   = ['setUserIdOnUpdate'];

    // 기본 유효성 검사 규칙 (is_unique는 buildValidationRules에서 동적으로 추가)
    protected $baseValidationRules = [
        'CO_NM'     => 'required|max_length[100]', // is_unique 규칙은 여기서 제거하고 buildValidationRules에서 추가
        'PIC_NM'    => 'permit_empty|max_length[50]',
        'CNPL'      => 'permit_empty|max_length[50]',
        'BGNG_YMD'  => 'permit_empty|valid_date[Y-m-d]',
        'END_YMD'   => 'permit_empty|valid_date[Y-m-d]',
        'CO_MNGR_ID'   => 'required|max_length[50]',
        'CO_MNGR_PSWD' => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages   = [
        'CO_NM' => [
            'required'   => '회사명은 필수 입력 항목입니다.',
            'max_length' => '회사명은 최대 100자까지 입력 가능합니다.',
            'is_unique'  => '이미 등록된 회사명입니다. 다른 회사명을 입력해주세요.' // 중복 메시지 추가
        ],
        'PIC_NM' => [
            'max_length' => '담당자명은 최대 50자까지 입력 가능합니다.',
        ],
        'CNPL' => [
            'max_length' => '연락처는 최대 50자까지 입력 가능합니다.',
        ],
        'BGNG_YMD' => [
            'valid_date' => '검진시작일자가 유효한 날짜 형식이 아닙니다 (YYYY-MM-DD).',
        ],
        'END_YMD' => [
            'valid_date' => '검진종료일자가 유효한 날짜 형식이 아닙니다 (YYYY-MM-DD).',
        ],
        'CO_MNGR_ID' => [
            'required'   => '회사관리자 아이디는 필수 입력 항목입니다.',
            'max_length' => '회사관리자 아이디는 최대 50자까지 입력 가능합니다.',
        ],
        'CO_MNGR_PSWD' => [
            'max_length' => '회사관리자 비밀번호는 최대 255자까지 입력 가능합니다.',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * 유효성 검사 규칙을 동적으로 빌드합니다.
     * DEL_YN = 'N' 조건과 함께 CO_NM의 유일성을 검사합니다.
     */
    public function buildValidationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = $this->baseValidationRules;

        // CO_NM 유일성 검사 규칙 설정
        // is_unique[table.field,ignore_field,ignore_value,extra_where_field,extra_where_value,...]
        // 여기서는 DEL_YN = 'N' 조건을 추가합니다.
        $uniqueRule = "is_unique[{$this->table}.CO_NM";

        if ($isUpdate && $id !== null) {
            // 수정 시에는 현재 레코드를 제외하고 중복 검사
            $uniqueRule .= ",{$this->primaryKey},{$id}";
        }
        // DEL_YN이 'N'인 경우에만 중복 검사하도록 조건 추가
        $uniqueRule .= ",DEL_YN,N]"; // 추가된 부분: DEL_YN 필드가 'N'인 경우만 검사

        // 기존 CO_NM 규칙에 is_unique 규칙을 추가 (이미 required, max_length 등이 있을 수 있으므로 | 로 연결)
        $rules['CO_NM'] .= '|' . $uniqueRule;

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

    public function softDeleteCo($id)
    {
        // Callbacks and timestamps will handle MDFCN_ID and MDFCN_YMD
        return $this->update($id, ['DEL_YN' => 'Y']);
    }

    public function getCoDescBySn($coSn)
    {
        return $this->select('CO_NM')
                    ->where($this->primaryKey, $coSn)
                    ->where('DEL_YN', 'N')
                    ->first();
    }
}