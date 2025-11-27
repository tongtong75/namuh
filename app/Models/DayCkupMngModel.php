<?php

namespace App\Models;

use CodeIgniter\Model;

class DayCkupMngModel extends Model
{
    protected $table            = 'DAY_CKUP_MNG';
    protected $primaryKey       = 'DAY_CKUP_MNG_SN';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'HSPTL_SN',
        'CKUP_YMD',
        'DAY_CKUP_SE',
        'CKUP_SE',
        'MAX_CNT',
        'CKUP_SE_CHC_CNT',
        'REG_ID',
        'MDFCN_ID',
        'REG_YMD',
        'MDFCN_YMD'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    /**
     * 연도별/병원별/요일구분별로 그룹화된 요약 목록을 조회합니다.
     *
     * @param array $params
     * @return array
     */
    public function getSummaryList($params = [])
    {
        $builder = $this->db->table('DAY_CKUP_MNG AS dcm');
        $builder->select('
            YEAR(dcm.CKUP_YMD) AS ckup_year,
            dcm.HSPTL_SN,
            hm.HSPTL_NM,
            dcm.DAY_CKUP_SE,
            dcm.CKUP_SE,
            dcm.MAX_CNT,
            MAX(dcm.REG_YMD) AS reg_ymd
        ');
        $builder->join('HSPTL_MNG AS hm', 'hm.HSPTL_SN = dcm.HSPTL_SN', 'left');

        // 검색 조건
        if (!empty($params['hsptl_sn'])) {
            $builder->where('dcm.HSPTL_SN', $params['hsptl_sn']);
        }
        if (!empty($params['ckup_year'])) {
            $builder->where('YEAR(dcm.CKUP_YMD)', $params['ckup_year']);
        }

        $builder->groupBy('YEAR(dcm.CKUP_YMD), dcm.HSPTL_SN, hm.HSPTL_NM, dcm.DAY_CKUP_SE, dcm.CKUP_SE, dcm.MAX_CNT');
        $builder->orderBy('ckup_year', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * 특정 조건의 상세 설정(검사항목 및 최대 인원) 목록을 조회합니다.
     *
     * @param int $hsptlSn
     * @param int $year
     * @param string $daySe
     * @return array
     */
    public function getDetailList($hsptlSn, $year, $daySe)
    {
        $builder = $this->db->table('DAY_CKUP_MNG AS dcm');
        $builder->select("\n            dcm.CKUP_SE,\n            dcm.MAX_CNT\n        ");
        $builder->where('dcm.HSPTL_SN', $hsptlSn);
        $builder->where('YEAR(dcm.CKUP_YMD)', $year);
        $builder->where('dcm.DAY_CKUP_SE', $daySe);
        $builder->groupBy('dcm.CKUP_SE, dcm.MAX_CNT');
        $builder->orderBy('dcm.CKUP_SE', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * 조건에 해당하는 데이터를 일괄 삭제합니다.
     *
     * @param int $hsptlSn
     * @param int $year
     * @param string $daySe
     * @return bool
     */
    public function deleteByCondition($hsptlSn, $year, $daySe)
    {
        return $this->where('HSPTL_SN', $hsptlSn)
                    ->where('YEAR(CKUP_YMD)', $year)
                    ->where('DAY_CKUP_SE', $daySe)
                    ->delete();
    }

    /**
     * 데이터를 일괄 삽입합니다.
     *
     * @param array $data
     * @return bool|int
     */
    public function bulkInsert($data)
    {
        if (empty($data)) {
            return true;
        }
        return $this->insertBatch($data);
    }

    public function getCalendarEvents($hsptlSn, $year)
    {
        $builder = $this->db->table('DAY_CKUP_MNG as dcm');
        $builder->select('
            dcm.CKUP_YMD as start,
            dcm.MAX_CNT as person_count,
            dcm.CKUP_SE_CHC_CNT as chc_cnt,
            dcm.CKUP_SE as item_name
        ');
        $builder->where('dcm.HSPTL_SN', $hsptlSn);
        $builder->where('YEAR(dcm.CKUP_YMD)', $year);
        $builder->orderBy('dcm.CKUP_YMD', 'ASC');

        $results = $builder->get()->getResultArray();

        // Sort results to have TOTAL last for each day
        usort($results, function($a, $b) {
            // First, sort by date
            if ($a['start'] !== $b['start']) {
                return strcmp($a['start'], $b['start']);
            }

            // If dates are the same, then apply custom sort for items
            if ($a['item_name'] === 'TOTAL') {
                return 1; // a is TOTAL, should come after b
            }
            if ($b['item_name'] === 'TOTAL') {
                return -1; // b is TOTAL, should come after a
            }
            return strcmp($a['item_name'], $b['item_name']);
        });

        $finalEvents = [];
        
        $textMap = [
            'TOTAL' => '전체',
            'ET' => '기타',
            'CT' => 'CT',
            'GS' => '위내시경',
            'UT' => '초음파',
            'CS' => '대장내시경',
            'PU' => '골반초음파',
            'BU' => '유방초음파'
        ];

        $colorMap = [
            'TOTAL' => '#e9ecef',
            'GS'    => '#d6e4ff',
            'CS'    => '#f8d7da',
            'CT'    => '#fff3cd',
            'UT'    => '#d1e7dd',
            'PU'    => '#e2d9f3',
            'BU'    => '#f3d9e2',
            'ET'    => '#f8f9fa'
        ];

        foreach ($results as $row) {
            $itemName = $row['item_name'];
            $titleText = $textMap[$itemName] ?? $itemName;

            $finalEvents[] = [
                'start'     => $row['start'],
                'allDay'    => true,
                'title'     => $titleText . ': ' . $row['chc_cnt'] . '/' . $row['person_count'],
                'color'     => $colorMap[$itemName] ?? '#e9ecef',
                'textColor' => '#212529'
            ];
        }

        return $finalEvents;
    }

    public function getDetailForDate($hsptlSn, $date)
    {
        $builder = $this->db->table('DAY_CKUP_MNG AS dcm');
        $builder->select("\n            dcm.CKUP_SE,\n            dcm.MAX_CNT\n        ");
        $builder->where('dcm.HSPTL_SN', $hsptlSn);
        $builder->where('dcm.CKUP_YMD', $date);
        $builder->orderBy('dcm.CKUP_SE', 'ASC');

        return $builder->get()->getResultArray();
    }
}
