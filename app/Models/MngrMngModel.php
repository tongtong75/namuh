<?php

namespace App\Models;

use CodeIgniter\Model;

class MngrMngModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'MNGR_MNG';
    protected $primaryKey       = 'MNGR_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'MNGR_ID', 'MNGR_NM', 'MNGR_PSWD', 'HSPTL_SN'
    ];

    protected $useTimestamps = false;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPasswordIfNeeded'];
    protected $beforeUpdate   = ['hashPasswordIfNeeded'];

    // 기본 유효성 검사 규칙 (is_unique, 비밀번호 필수 여부 제외)
    protected $baseValidationRules = [
        'HSPTL_SN'  => 'required|numeric',
        'MNGR_NM'   => 'required|max_length[50]',
    ];
    protected $validationMessages   = [
        'MNGR_ID'   => [
            'is_unique' => '이미 사용 중인 관리자 ID입니다.'
        ],
        'MNGR_PSWD' => [
            'required' => '비밀번호는 필수 입력 항목입니다.',
            'min_length' => '비밀번호는 최소 4자 이상이어야 합니다.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;


    protected function hashPasswordIfNeeded(array $data): array
    {
        // 비밀번호 필드가 있고, 비어있지 않은 경우에만 해싱
        if (isset($data['data']['MNGR_PSWD']) && !empty($data['data']['MNGR_PSWD'])) {
            $data['data']['MNGR_PSWD'] = password_hash($data['data']['MNGR_PSWD'], PASSWORD_DEFAULT);
        } else {
            // 비밀번호가 없거나 비어있으면 데이터에서 제거 (업데이트 시 기존 비밀번호 유지 목적)
            // 등록 시에는 컨트롤러에서 유효성 검사를 통해 필수 입력 처리
            unset($data['data']['MNGR_PSWD']);
        }
        return $data;
    }

    public function getManagersWithHospitalDetails()
    {
        return $this->select('MNGR_MNG.*, HSPTL_MNG.HSPTL_NM')
                    //->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN', 'left')
                    ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN')
                    ->orderBy('MNGR_MNG.MNGR_SN', 'DESC')
                    ->findAll();
    }

    public function getManagerWithHospitalDetail($id)
    {
        return $this->select('MNGR_MNG.*, HSPTL_MNG.HSPTL_NM')
                    //->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN', 'left')
                    ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN')
                    ->find($id);
    }

    /**
     * 상황(등록/수정)에 맞는 유효성 검사 규칙을 생성하여 반환합니다.
     *
     * @param bool $isUpdate 수정 모드인지 여부
     * @param int|null $managerId 수정 시 현재 관리자의 MNGR_SN (is_unique 검사용)
     * @return array
     */
    public function buildValidationRules(bool $isUpdate = false, ?int $managerId = null): array
    {
        $rules = $this->baseValidationRules; // 기본 규칙 (HSPTL_SN, MNGR_NM)

        $uniqueRuleIgnoreSegment = ($isUpdate && $managerId !== null) ? ",{$this->primaryKey},{$managerId}" : "";
        $rules['MNGR_ID'] = [
            'label' => '관리자 ID',
            'rules' => "required|max_length[50]|is_unique[{$this->table}.MNGR_ID{$uniqueRuleIgnoreSegment}]",
            // validationMessages에서 'is_unique' 메시지 사용
        ];

        if ($isUpdate) {
            // 수정 시: 비밀번호가 입력된 경우에만 유효성 검사 (permit_empty)
            $rules['MNGR_PSWD'] = 'permit_empty|min_length[4]|max_length[255]';
        } else {
            // 등록 시: 비밀번호 필수
            $rules['MNGR_PSWD'] = 'required|min_length[4]|max_length[255]';
        }
        return $rules;
    }
}