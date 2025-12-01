<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsExcelMngModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'CKUP_GDS_EXCEL_MNG';
    protected $primaryKey       = 'CKUP_GDS_EXCEL_MNG_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'HSPTL_SN', 'CKUP_YYYY', 'CKUP_GDS_NM', 'SPRT_SE', 'FAM_SPRT_SE', 
        'DEL_YN', 'REG_ID', 'MDFCN_ID', 'REG_YMD', 'MDFCN_YMD'
    ];

    protected $useTimestamps = false;
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setRegistrationInfo'];
    protected $beforeUpdate   = ['setModificationInfo'];

    protected $baseValidationRules = [
        'HSPTL_SN'    => 'required|numeric',
        'CKUP_YYYY'     => 'required|exact_length[4]|numeric',
        'CKUP_GDS_NM' => 'required|max_length[100]'
    ];

    protected $validationMessages = [
        'HSPTL_SN' => [
            'required' => '병원을 선택해주세요.',
            'numeric'  => '올바른 병원을 선택해주세요.'
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

    public function getDatatablesList(int $start, int $length, string $searchValue, int $orderColumn, string $orderDir, array $filters)
    {
        $builder = $this->db->table($this->table . ' gds');
        $builder->select('gds.*, hsptl.HSPTL_NM');
        $builder->join('HSPTL_MNG hsptl', 'gds.HSPTL_SN = hsptl.HSPTL_SN', 'left');

        if (!empty($filters['ckup_yyyy'])) {
            $builder->where('gds.CKUP_YYYY', $filters['ckup_yyyy']);
        }
        if (!empty($filters['hsptl_sn'])) {
            $builder->where('gds.HSPTL_SN', $filters['hsptl_sn']);
        }

        if (!empty($searchValue)) {
            $builder->groupStart();
            $builder->like('gds.CKUP_GDS_NM', $searchValue);
            $builder->orLike('hsptl.HSPTL_NM', $searchValue);
            $builder->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults();

        $columnMap = ['no', 'gds.CKUP_YYYY', 'hsptl.HSPTL_NM', 'gds.CKUP_GDS_NM', 'gds.SPRT_SE', 'gds.REG_YMD'];
        $orderableColumn = $columnMap[$orderColumn] ?? $columnMap[1];
        if ($orderableColumn !== 'no') {
            $builder->orderBy($orderableColumn, $orderDir);
        }

        $builder->limit($length, $start);

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'data'            => $data,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }

    protected function setRegistrationInfo(array $data): array
    {
        $userId = session()->get('user_id') ?? null;
        $data['data']['REG_ID'] = $userId;
        $data['data']['REG_YMD'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function setModificationInfo(array $data): array
    {
        $userId = session()->get('user_id') ?? null;
        $data['data']['MDFCN_ID'] = $userId;
        $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    public function getCkupGdsExcelWithDetail(int $id): ?array
    {
        $basicInfo = $this->find($id);
        if (!$basicInfo) {
            return null;
        }

        $basicItems = $this->db->table('CKUP_GDS_EXCEL_ARTCL')
            ->where('CKUP_GDS_EXCEL_MNG_SN', $id)
            ->get()->getResultArray();

        $choiceGroups = $this->db->table('CKUP_GDS_EXCEL_CHC_GROUP')
            ->where('CKUP_GDS_EXCEL_MNG_SN', $id)
            ->get()->getResultArray();

        foreach ($choiceGroups as &$group) {
            $group['items'] = $this->db->table('CKUP_GDS_EXCEL_CHC_ARTCL')
                ->where('CKUP_GDS_EXCEL_CHC_GROUP_SN', $group['CKUP_GDS_EXCEL_CHC_GROUP_SN'])
                ->get()->getResultArray();
        }
        unset($group);

        $addChoiceItems = $this->db->table('CKUP_GDS_EXCEL_ADD_CHC')
            ->where('CKUP_GDS_EXCEL_SN', $id) // Note: Field name is CKUP_GDS_EXCEL_SN in order.txt
            ->get()->getResultArray();

        return [
            'basicInfo'      => $basicInfo,
            'basicItems'     => $basicItems,
            'choiceGroups'   => $choiceGroups,
            'addChoiceItems' => $addChoiceItems,
        ];
    }
}
