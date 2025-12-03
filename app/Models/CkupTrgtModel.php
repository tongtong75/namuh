<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupTrgtModel extends Model
{
    protected $table            = 'CKUP_TRGT';
    protected $primaryKey       = 'CKUP_TRGT_SN';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // DEL_YN을 직접 처리

    // CKUP_TRGT 테이블의 모든 컬럼 (REG_YMD제외 - 콜백에서 처리)
    protected $allowedFields    = [
        'CO_SN', 'CKUP_YYYY', 'CKUP_NAME','NAME', 'BUSINESS_NUM', 'BIRTHDAY', 'PSWD','AGREE_YN',
        'SEX', 'HANDPHONE', 'SUPPORT_FUND', 'FAMILY_SUPPORT_FUND', 'EMAIL',
        'WORK_STATUS', 'ASSIGN_CODE', 'JOB', 'RELATION',
        'CHECKUP_TARGET_YN', 'RSVT_STTS',  'CKUP_YN',
        'REG_ID', 'MDFCN_ID','DEL_YN'
    ];

    // Dates - DB 스키마에 따라 useTimestamps 사용 여부 결정
    // REG_YMD: VARCHAR(8), MDFCN_YMD: DATE 이므로 useTimestamps=false로 하고 콜백에서 직접 처리
    protected $useTimestamps = false;
    // protected $createdField  = 'REG_YMD'; // 사용 안함
    // protected $updatedField  = 'MDFCN_YMD'; // 사용 안함

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setInsertDefaults'];
    protected $beforeUpdate   = ['setUpdateDefaults'];

    // 유효성 검사 규칙 (필요에 따라 상세화)
    protected $baseValidationRules = [
        'CO_SN'        => 'permit_empty|is_natural_no_zero', // 회사 선택 시
        'CKUP_YYYY'    => 'required|exact_length[4]|is_natural',
        'NAME'         => 'required|max_length[50]',
        'CKUP_NAME'    => 'required|max_length[50]',
        'BUSINESS_NUM' => 'permit_empty|max_length[30]',
        'BIRTHDAY'     => 'permit_empty|exact_length[6]', // YYMMDD 형식 가정
        'PSWD'         => 'permit_empty|max_length[50]', // 실제로는 암호화된 길이 고려
        'SEX'          => 'permit_empty|in_list[M,F]',
        'HANDPHONE'    => 'permit_empty|max_length[13]',
        'EMAIL'        => 'permit_empty|valid_email|max_length[50]',
        // ... 기타 필드 규칙
    ];
    // 유효성 검사 메시지 (baseValidationRules에 맞춰 작성)
    protected $validationMessages   = [
        'CKUP_YYYY' => [
            'required' => '검진년도는 필수입니다.',
            'exact_length' => '검진년도는 4자리여야 합니다.',
        ],
        'NAME' => [
            'required' => '직원명은 필수입니다.',
        ],
        'CKUP_NAME' => [
            'required' => '수검자명은 필수입니다.',
        ]
        // ... 기타 메시지
    ];


    public function buildValidationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = $this->baseValidationRules;
        // 수정 시 특정 필드 unique 검사 제외 등 로직 추가 가능
        // 예: $rules['BUSINESS_NUM'] .= "|is_unique[{$this->table}.BUSINESS_NUM,{$this->primaryKey},{$id}]";
        return $rules;
    }


    protected function setInsertDefaults(array $data): array
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM_USER');
        if (isset($data['data'])) {
            $data['data']['REG_ID'] = $currentUserId;
            $data['data']['MDFCN_ID'] = $currentUserId;
            $data['data']['REG_YMD'] = date('Ymd'); // VARCHAR(8)
            $data['data']['MDFCN_YMD'] = date('Y-m-d'); // DATE

            if (!isset($data['data']['DEL_YN'])) {
                $data['data']['DEL_YN'] = 'N';
            }
            // CKUP_YYYY 기본값 (DB DEFAULT YEAR(CURDATE()) 권장)
            if (!isset($data['data']['CKUP_YYYY'])) {
                 // $data['data']['CKUP_YYYY'] = date('Y');
                 // DB에서 처리하도록 하거나, 폼에서 반드시 받도록 유도
            }
            $ynFields = ['CHECKUP_TARGET_YN', 'RSVT_STTS', 'CKUP_YN'];
            foreach ($ynFields as $field) {
                if (!isset($data['data'][$field])) {
                    $data['data'][$field] = 'N';
                }
            }
        }
        return $data;
    }

    protected function setUpdateDefaults(array $data): array
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM_USER');
        if (isset($data['data'])) {
            $data['data']['MDFCN_ID'] = $currentUserId;
            $data['data']['MDFCN_YMD'] = date('Y-m-d'); // DATE
        }
        return $data;
    }

    public function softDeleteCkupTrgt($id)
    {
        return $this->update($id, ['DEL_YN' => 'Y']); // beforeUpdate 콜백으로 MDFCN_ID, MDFCN_YMD 자동 처리
    }

    // DataTables 서버 사이드 처리를 위한 메소드 (예시)
    public function getDataTableList($requestData, $extraWhere = [])
    {
        $builder = $this->db->table($this->table . ' CT'); // Alias 'CT' for CKUP_TRGT

        // Select fields (회사명 등 JOIN 필요시 추가)
        $builder->select('CT.*, CM.CO_NM'); // 예시: 회사명 가져오기
        $builder->join('CO_MNG CM', 'CM.CO_SN = CT.CO_SN', 'left'); // 예시: 회사 테이블 JOIN

        $builder->where('CT.DEL_YN', 'N');

        if ($extraWhere) {
            $builder->where($extraWhere);
        }

        // 검색 처리
        if (!empty($requestData['search']['value'])) {
            $searchValue = $requestData['search']['value'];
            $builder->groupStart();
            $builder->like('CT.NAME', $searchValue);
            $builder->orLike('CT.CKUP_NAME', $searchValue);
            $builder->orLike('CT.BUSINESS_NUM', $searchValue);
            $builder->orLike('CT.HANDPHONE', $searchValue);
            $builder->orLike('CM.CO_NM', $searchValue); // JOIN된 컬럼 검색
            // ... 다른 검색 필드
            $builder->groupEnd();
        }

        // 전체 레코드 수 (필터링 전)
        $totalRecords = $builder->countAllResults(false); // false to not reset query

        // 정렬 처리
        if (isset($requestData['order'])) {
            $order = $requestData['order'][0];
            $orderColumnIndex = $order['column'];
            $orderColumnName = $requestData['columns'][$orderColumnIndex]['data']; // JS에서 정의한 data 이름 사용
            $orderDir = $order['dir'];

            // 실제 DB 컬럼명으로 매핑 (JOIN 시 테이블명 명시)
            $columnMap = [
                'CO_NM' => 'CM.CO_NM',
                'CKUP_YYYY' => 'CT.CKUP_YYYY',
                'NAME' => 'CT.NAME',
                'CKUP_NAME' => 'CT.CKUP_NAME',
                'SUPPORT_FUND' => 'CT.SUPPORT_FUND',
                'FAMILY_SUPPORT_FUND' => 'CT.FAMILY_SUPPORT_FUND',
                'BUSINESS_NUM' => 'CT.BUSINESS_NUM',
                'BIRTHDAY' => 'CT.BIRTHDAY',
                'SEX' => 'CT.SEX',
                'HANDPHONE' => 'CT.HANDPHONE',
                'CKUP_YN' => 'CT.CKUP_YN',
                // 'no'는 실제 DB 컬럼이 아님
            ];
            if (array_key_exists($orderColumnName, $columnMap)) {
                 $builder->orderBy($columnMap[$orderColumnName], strtoupper($orderDir));
                 
                 // [FIX] 사번 정렬 시 본인(S) 우선 정렬 추가
                 if ($orderColumnName === 'BUSINESS_NUM') {
                     $builder->orderBy("CASE WHEN CT.RELATION = 'S' THEN 0 ELSE 1 END", 'ASC');
                 }
            } else if ($orderColumnName !== 'no' && $orderColumnName !== 'action') { // 'no', 'action' 등은 정렬 대상 아님
                $builder->orderBy('CT.' . $orderColumnName, strtoupper($orderDir)); // 기본적으로 CT 테이블의 컬럼으로 가정
            } else {
                 $builder->orderBy('CT.CKUP_TRGT_SN', 'DESC'); // 기본 정렬
            }
        } else {
            $builder->orderBy('CT.CKUP_TRGT_SN', 'DESC'); // 기본 정렬
        }
        // 페이징 처리
        if (isset($requestData['start']) && $requestData['length'] != -1) {
            $builder->limit($requestData['length'], $requestData['start']);
        }

        $query = $builder->get();
        $result = $query->getResultArray();

        // 필터링된 레코드 수
        // countAllResults()는 limit을 무시하므로, 위에서 검색/필터 조건만 적용된 builder를 다시 써야 함.
        // 간단히 하기 위해, 전체 레코드 수를 필터링된 수로 우선 사용 (정확하려면 별도 카운트 쿼리)
        // $recordsFiltered = $totalRecords;
        // 위에서 $totalRecords를 countAllResults(false)로 계산했으므로, 이것이 필터링된 카운트가 됨.
        // 정확한 필터링 전 전체 개수는 $this->where('DEL_YN', 'N')->countAllResults();

        return [
            "data" => $result,
            "recordsTotal" => $this->where('DEL_YN', 'N')->countAllResults(), // 필터링 전 전체
            "recordsFiltered" => $totalRecords // 필터링 후 전체
        ];
    }
    public function getExcelData($ckupYYYY, $coSn)
    {
        return $this->select('CKUP_YYYY, CO_SN, BUSINESS_NUM, CKUP_NAME, NAME, RELATION, SEX, TEL_NO, HANDPHONE, ADDR, RSVT_STTS, CKUP_YN')
            ->where('CKUP_YYYY', $ckupYYYY)
            ->where('CO_SN', $coSn)
            ->findAll();
    }
}