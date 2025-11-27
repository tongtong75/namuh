<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsMngModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ckup_gds_mng';
    protected $primaryKey       = 'CKUP_GDS_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'HSPTL_SN', 'CO_SN', 'CKUP_YYYY', 'CKUP_GDS_NM', 'DEL_YN', 'REG_ID', 'MDFCN_ID'
    ];

    protected $useTimestamps = false;
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setRegistrationInfo'];
    protected $beforeUpdate   = ['setModificationInfo'];

    // 기본 유효성 검사 규칙
    protected $baseValidationRules = [
        'HSPTL_SN'    => 'required|numeric',
        'CO_SN'       => 'required|numeric',
        'CKUP_YYYY'     => 'required|exact_length[4]|numeric',
        'CKUP_GDS_NM' => 'required|max_length[100]'
    ];

    protected $validationMessages = [
        'HSPTL_SN' => [
            'required' => '병원을 선택해주세요.',
            'numeric'  => '올바른 병원을 선택해주세요.'
        ],
        'CO_SN' => [
            'required' => '회사를 선택해주세요.',
            'numeric'  => '올바른 회사를 선택해주세요.'
        ],
        'CKUP_YYYY' => [
            'required'      => '검진년도는 필수 입력 항목입니다.',
            'exact_length'  => '검진년도는 4자리 숫자여야 합니다.',
            'numeric'       => '검진년도는 숫자만 입력 가능합니다.'
        ],
        'CKUP_GDS_NM' => [
            'required'   => '검진상품명은 필수 입력 항목입니다.',
            'max_length' => '검진상품명은 100자 이하로 입력해주세요.'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;


    /**
     * 검진상품 목록을 병원명, 회사명과 함께 조회
     */
    public function getDatatablesList(int $start, int $length, string $searchValue, int $orderColumn, string $orderDir, array $filters)
    {
        // 1. 쿼리 빌더 시작 및 기본 조인 설정
        $builder = $this->db->table($this->table . ' gds');
        $builder->select('gds.*, hsptl.HSPTL_NM, co.CO_NM');
        $builder->join('hsptl_mng hsptl', 'gds.HSPTL_SN = hsptl.HSPTL_SN', 'left');
        $builder->join('co_mng co', 'gds.CO_SN = co.CO_SN', 'left');

        // 2. 커스텀 필터링 (WHERE 조건 추가)
        if (!empty($filters['ckup_yyyy'])) {
            $builder->where('gds.CKUP_YYYY', $filters['ckup_yyyy']);
        }
        if (!empty($filters['hsptl_sn'])) {
            $builder->where('gds.HSPTL_SN', $filters['hsptl_sn']);
        }
        if (!empty($filters['co_sn'])) {
            $builder->where('gds.CO_SN', $filters['co_sn']);
        }

        // 3. DataTables의 전체 검색 기능 (LIKE 검색)
        if (!empty($searchValue)) {
            $builder->groupStart(); // ( A LIKE '...' OR B LIKE '...' ) 와 같이 괄호로 묶어주기 위함
            $builder->like('gds.CKUP_GDS_NM', $searchValue);
            $builder->orLike('hsptl.HSPTL_NM', $searchValue);
            $builder->orLike('co.CO_NM', $searchValue);
            $builder->groupEnd();
        }

        // 4. 필터링된 결과의 총 개수 구하기 (페이징 전)
        // recordsFiltered 값을 위해, LIMIT을 적용하기 전에 총 개수를 미리 계산합니다.
        // 기존 쿼리 빌더를 복제하여 사용해야 원래 쿼리에 영향을 주지 않습니다.
        $recordsFiltered = (clone $builder)->countAllResults();

        // 5. 정렬 (ORDER BY)
        $columnMap = ['no', 'gds.CKUP_YYYY', 'hsptl.HSPTL_NM', 'co.CO_NM', 'gds.CKUP_GDS_NM', 'gds.REG_YMD'];
        $orderableColumn = $columnMap[$orderColumn] ?? $columnMap[1]; // 정렬할 수 없는 컬럼이 요청되면 기본값으로 정렬
        if ($orderableColumn !== 'no') { // 'no' 컬럼은 DB에 없으므로 정렬에서 제외
            $builder->orderBy($orderableColumn, $orderDir);
        }

        // 6. 페이징 (LIMIT, OFFSET)
        $builder->limit($length, $start);

        // 7. 최종 데이터 조회
        $data = $builder->get()->getResultArray();

        // 8. 필터링 전 전체 데이터 수 구하기
        // recordsTotal 값을 위해, 모든 필터 조건을 무시하고 전체 개수를 계산합니다.
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        // 9. 결과 반환
        return [
            'data'            => $data,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }

    /**
     * 상황(등록/수정)에 맞는 유효성 검사 규칙을 생성하여 반환
     *
     * @param bool $isUpdate 수정 모드인지 여부
     * @param int|null $ckupGdsId 수정 시 현재 검진상품의 CKUP_GDS_SN
     * @return array
     */
    public function buildValidationRules(bool $isUpdate = false, ?int $ckupGdsId = null): array
    {
        $rules = $this->baseValidationRules;

        // 필요시 추가 규칙 설정 (예: 중복 체크 등)
        // 예시: 동일 병원, 회사, 년도의 상품명 중복 체크
        if (!$isUpdate) {
            // 등록 시 중복 체크 규칙 추가 가능
        } else {
            // 수정 시 현재 레코드 제외한 중복 체크 규칙 추가 가능
        }

        return $rules;
    }

    /**
     * INSERT 전에 등록자 정보와 등록일자를 설정합니다.
     */
    protected function setRegistrationInfo(array $data): array
    {
        // 세션에서 현재 로그인한 사용자 ID를 가져온다고 가정
        $userId = session()->get('user_id') ?? null;
        
        $data['data']['REG_ID'] = $userId;
        $data['data']['REG_YMD'] = date('Y-m-d');

        return $data;
    }
    /**
     * UPDATE 전에 수정자 정보와 수정일자를 설정합니다.
     */
    protected function setModificationInfo(array $data): array
    {
        // 세션에서 현재 로그인한 사용자 ID를 가져온다고 가정
        $userId = session()->get('user_id') ?? null;
        
        $data['data']['MDFCN_ID'] = $userId;
        $data['data']['MDFCN_YMD'] = date('Y-m-d'); // 수정일자는 현재 날짜로 설정

        return $data;
    }



    public function getCkupGdsWithDetail(int $id): ?array
    {
        // 1. 기본 정보 조회
        $basicInfo = $this->find($id);
        if (!$basicInfo) {
            return null;
        }

        // 2. 기본 항목 목록 조회
        $basicItems = $this->db->table('ckup_gds_artcl a')
            ->join('ckup_artcl_mng b', 'a.CKUP_ARTCL_SN = b.CKUP_ARTCL_SN')
            ->where('a.CKUP_GDS_SN', $id)
            ->select('b.*') // 필요한 컬럼만 선택하는 것이 더 효율적입니다.
            ->get()->getResultArray();

        // 3. 선택 그룹 및 항목 조회
        $choiceGroups = $this->db->table('ckup_gds_chc_group')
            ->where('CKUP_GDS_SN', $id)
            ->get()->getResultArray();

        foreach ($choiceGroups as &$group) {
            $group['items'] = $this->db->table('ckup_gds_chc_artcl a')
                ->join('chc_artcl_mng b', 'a.CHC_ARTCL_SN = b.CHC_ARTCL_SN')
                ->where('a.CKUP_GDS_CHC_GROUP_SN', $group['CKUP_GDS_CHC_GROUP_SN'])
                ->select('b.*') // 필요한 컬럼만 선택
                ->get()->getResultArray();
        }
        unset($group);

        return [
            'basicInfo'    => $basicInfo,
            'basicItems'   => $basicItems,
            'choiceGroups' => $choiceGroups,
        ];
    }

}