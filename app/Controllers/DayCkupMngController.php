<?php

namespace App\Controllers;

use App\Models\DayCkupMngModel;
use App\Models\HsptlMngModel;
use App\Models\ChcArtclMngModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class DayCkupMngController extends BaseController
{
    /**
     * 연도별 검진 인원 설정 목록 페이지
     */
    public function index()
    {
        $hsptlMngModel = new HsptlMngModel();
        if (session()->get('user_type') === 'H') {
            $data['hospitals'] = $hsptlMngModel->where('HSPTL_SN', session()->get('hsptl_sn'))->findAll();
        } else {
            $data['hospitals'] = $hsptlMngModel->findAll();
        }
        $data['currentYear'] = date('Y');

        // 뷰에 데이터 전달
        return view('mngr/day_ckup_mng/list', $data);
    }

    /**
     * 등록/수정 폼을 로드합니다. (AJAX)
     */
    public function form()
    {
        $hsptlMngModel = new HsptlMngModel();
        $dayCkupMngModel = new DayCkupMngModel();

        if (session()->get('user_type') === 'H') {
            $data['hospitals'] = $hsptlMngModel->where('HSPTL_SN', session()->get('hsptl_sn'))->findAll();
        } else {
            $data['hospitals'] = $hsptlMngModel->findAll();
        }

        $hsptlSn = $this->request->getGet('hsptl_sn');
        $ckupYear = $this->request->getGet('ckup_year');

        $data['is_edit'] = !empty($hsptlSn) && !empty($ckupYear);
        $data['selected_hsptl_sn'] = $hsptlSn;
        $data['selected_ckup_year'] = $ckupYear;
        $data['weekday_details'] = [];
        $data['saturday_details'] = [];

        if ($data['is_edit']) {
            $data['weekday_details'] = $dayCkupMngModel->getDetailList($hsptlSn, $ckupYear, 'WEEKDAY');
            $data['saturday_details'] = $dayCkupMngModel->getDetailList($hsptlSn, $ckupYear, 'SAT');
        }

        return view('mngr/day_ckup_mng/form', $data);
    }

    /**
     * 목록 데이터 조회 (AJAX)
     */
    public function getList()
    {
        if ($this->request->isAJAX()) {
            $dayCkupMngModel = new DayCkupMngModel();
            $params = [
                'hsptl_sn' => $this->request->getGet('hsptl_sn'),
                'ckup_year' => $this->request->getGet('ckup_year'),
            ];
            
            if (session()->get('user_type') === 'H') {
                $params['hsptl_sn'] = session()->get('hsptl_sn');
            }

            $list = $dayCkupMngModel->getSummaryList($params);

            $summary = [];
            foreach ($list as $row) {
                $key = $row['ckup_year'] . '-' . $row['HSPTL_SN'] . '-' . $row['DAY_CKUP_SE'];
                if (!isset($summary[$key])) {
                    $summary[$key] = [
                        'ckup_year' => $row['ckup_year'],
                        'HSPTL_SN' => $row['HSPTL_SN'],
                        'HSPTL_NM' => $row['HSPTL_NM'],
                        'DAY_CKUP_SE' => $row['DAY_CKUP_SE'],
                        'reg_ymd' => $row['reg_ymd'],
                        'items' => []
                    ];
                }
                $summary[$key]['items'][] = [
                    'ckup_se' => $row['CKUP_SE'],
                    'max_cnt' => $row['MAX_CNT']
                ];
            }

            $result = [];
            foreach ($summary as $group) {
                $item_summary_parts = [];
                $total_item = null;
                $other_items = [];

                foreach ($group['items'] as $item) {
                    if ($item['ckup_se'] === 'TOTAL') {
                        $total_item = $item;
                    } else {
                        $other_items[] = $item;
                    }
                }

                if ($total_item) {
                    $item_summary_parts[] = '전체(' . $total_item['max_cnt'] . ')';
                }

                foreach ($other_items as $item) {
                    $item_summary_parts[] = $this->getCheckupTypeText($item['ckup_se']) . '(' . $item['max_cnt'] . ')';
                }
                
                $group['item_summary'] = implode(', ', $item_summary_parts);
                unset($group['items']);
                $result[] = $group;
            }

            return $this->response->setJSON(['success' => true, 'list' => array_values($result)]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    private function getCheckupTypeText($ckupSe)
    {
        switch($ckupSe) {
            case 'ET': return '기타';
            case 'CT': return 'CT';
            case 'GS': return '위내시경';
            case 'UT': return '초음파';
            case 'CS': return '대장';
            case 'PU': return '골반초음파';
            case 'BU': return '유방초음파';
            default: return $ckupSe;
        }
    }

    /**
     * 상세 설정 정보 조회 (AJAX)
     */
    public function getDetail()
    {
        if ($this->request->isAJAX()) {
            $dayCkupMngModel = new DayCkupMngModel();
            $hsptlSn = $this->request->getGet('hsptl_sn');
            
            if (session()->get('user_type') === 'H') {
                $hsptlSn = session()->get('hsptl_sn');
            }
            
            $year = $this->request->getGet('ckup_year');
            $daySe = $this->request->getGet('day_se');

            $detailList = $dayCkupMngModel->getDetailList($hsptlSn, $year, $daySe);

            return $this->response->setJSON(['success' => true, 'detail' => $detailList]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    

    /**
     * 데이터 저장 (신규/수정)
     */
    public function save()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $dayCkupMngModel = new DayCkupMngModel();

            $hsptlSn = $this->request->getPost('hsptl_sn');
            $year = $this->request->getPost('ckup_year');
            $weekdayTotalPersonnel = $this->request->getPost('weekday_total_personnel');
            $saturdayTotalPersonnel = $this->request->getPost('saturday_total_personnel');
            $weekdayItems = $this->request->getPost('weekday_items') ?? [];
            $saturdayItems = $this->request->getPost('saturday_items') ?? [];
            $regId = session()->get('mngr_id'); // 로그인 세션에서 ID 가져오기

            $db->transStart();

            try {
                // 1. 평일 데이터 처리
                $this->processDayTypeData($dayCkupMngModel, $hsptlSn, $year, 'WEEKDAY', $weekdayItems, $weekdayTotalPersonnel, $regId);

                // 2. 토요일 데이터 처리
                $this->processDayTypeData($dayCkupMngModel, $hsptlSn, $year, 'SAT', $saturdayItems, $saturdayTotalPersonnel, $regId);

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return $this->response->setJSON(['success' => false, 'message' => '저장에 실패했습니다. (Transaction Error)']);
                }

                return $this->response->setJSON(['success' => true, 'message' => '성공적으로 저장되었습니다.']);

            } catch (\Exception $e) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    /**
     * 요일 구분별 데이터 처리 (삭제 후 일괄 생성)
     */
    private function processDayTypeData($model, $hsptlSn, $year, $daySe, $items, $totalPersonnel, $regId)
    {
        // 기존 데이터 삭제
        $model->deleteByCondition($hsptlSn, $year, $daySe);

        // 삽입할 날짜 목록 생성
        $dates = $this->getDatesForYear($year, $daySe);
        $bulkData = [];

        foreach ($dates as $date) {
            // Add total personnel record
            if (!empty($totalPersonnel)) {
                $bulkData[] = [
                    'HSPTL_SN' => $hsptlSn,
                    'CKUP_YMD' => $date,
                    'DAY_CKUP_SE' => $daySe,
                    'CKUP_SE' => 'TOTAL', // Special SE for total count
                    'MAX_CNT' => $totalPersonnel,
                    'REG_ID' => $regId,
                ];
            }

            // Add individual article records
            foreach ($items as $item) {
                $bulkData[] = [
                    'HSPTL_SN' => $hsptlSn,
                    'CKUP_YMD' => $date,
                    'DAY_CKUP_SE' => $daySe,
                    'CKUP_SE' => $item['ckup_se'],
                    'MAX_CNT' => $item['max_cnt'],
                    'REG_ID' => $regId,
                ];
            }
        }

        // 데이터 일괄 삽입
        if (!empty($bulkData)) {
            $model->bulkInsert($bulkData);
        }
    }

    /**
     * 특정 연도의 평일/토요일 날짜 목록 생성
     */
    private function getDatesForYear($year, $dayType)
    {
        $dates = [];
        $startDate = new \DateTime("$year-01-01");
        $endDate = new \DateTime("$year-12-31");

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($dateRange as $date) {
            $dayOfWeek = $date->format('N'); // 1(월) ~ 7(일)

            if ($dayType === 'WEEKDAY' && $dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $dates[] = $date->format('Y-m-d');
            }
            if ($dayType === 'SAT' && $dayOfWeek == 6) {
                $dates[] = $date->format('Y-m-d');
            }
        }
        return $dates;
    }

    /**
     * 데이터 삭제
     */
    public function delete()
    {
        if ($this->request->isAJAX()) {
            $dayCkupMngModel = new DayCkupMngModel();
            $hsptlSn = $this->request->getPost('hsptl_sn');
            $year = $this->request->getPost('ckup_year');
            $daySe = $this->request->getPost('day_se');

            $result = $dayCkupMngModel->deleteByCondition($hsptlSn, $year, $daySe);

            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => '삭제되었습니다.']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => '삭제에 실패했습니다.']);
            }
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function calendar()
    {
        $hsptlMngModel = new HsptlMngModel();
        if (session()->get('user_type') === 'H') {
            $data['hospitals'] = $hsptlMngModel->where('HSPTL_SN', session()->get('hsptl_sn'))->findAll();
        } else {
            $data['hospitals'] = $hsptlMngModel->findAll();
        }
        $data['currentYear'] = date('Y');

        return view('mngr/day_ckup_mng/calendar', $data);
    }

    public function getCalendarEvents()
    {
        $dayCkupMngModel = new DayCkupMngModel();
        $hsptlSn = $this->request->getGet('hsptl_sn');
        
        if (session()->get('user_type') === 'H') {
            $hsptlSn = session()->get('hsptl_sn');
        }
        
        $year = $this->request->getGet('year');
        
        $events = $dayCkupMngModel->getCalendarEvents($hsptlSn, $year);
        
        return $this->response->setJSON($events);
    }

    public function getDailyDetail()
    {
        if ($this->request->isAJAX()) {
            $dayCkupMngModel = new DayCkupMngModel();
            $hsptlSn = $this->request->getGet('hsptl_sn');
            
            if (session()->get('user_type') === 'H') {
                $hsptlSn = session()->get('hsptl_sn');
            }
            
            $date = $this->request->getGet('date');
            
            $details = $dayCkupMngModel->getDetailForDate($hsptlSn, $date);
            
            return $this->response->setJSON(['success' => true, 'detail' => $details]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }

    public function saveDailyDetail()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $dayCkupMngModel = new DayCkupMngModel();

            $hsptlSn = $this->request->getPost('hsptl_sn');
            $date = $this->request->getPost('date');
            $totalPersonnel = $this->request->getPost('total_personnel');
            $items = $this->request->getPost('items') ?? [];
            $regId = session()->get('mngr_id');

            $db->transStart();

            try {
                // 1. 기존 데이터 삭제
                $dayCkupMngModel->where('HSPTL_SN', $hsptlSn)->where('CKUP_YMD', $date)->delete();

                // 2. 신규 데이터 추가
                $dayOfWeek = date('N', strtotime($date));
                $daySe = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'WEEKDAY' : (($dayOfWeek == 6) ? 'SAT' : 'SUN');

                $bulkData = [];
                if (!empty($totalPersonnel)) {
                    $bulkData[] = [
                        'HSPTL_SN' => $hsptlSn,
                        'CKUP_YMD' => $date,
                        'DAY_CKUP_SE' => $daySe,
                        'CKUP_SE' => 'TOTAL',
                        'MAX_CNT' => $totalPersonnel,
                        'REG_ID' => $regId,
                    ];
                }

                foreach ($items as $item) {
                    $bulkData[] = [
                        'HSPTL_SN' => $hsptlSn,
                        'CKUP_YMD' => $date,
                        'DAY_CKUP_SE' => $daySe,
                        'CKUP_SE' => $item['ckup_se'],
                        'MAX_CNT' => $item['max_cnt'],
                        'REG_ID' => $regId,
                    ];
                }

                if (!empty($bulkData)) {
                    $dayCkupMngModel->bulkInsert($bulkData);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return $this->response->setJSON(['success' => false, 'message' => '저장에 실패했습니다.']);
                }

                return $this->response->setJSON(['success' => true, 'message' => '성공적으로 저장되었습니다.']);

            } catch (\Exception $e) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
            }
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
    }
}