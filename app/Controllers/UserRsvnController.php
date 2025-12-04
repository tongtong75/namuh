<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CkupTrgtModel;
use App\Models\CoHsptlLnkngModel;
use App\Models\HsptlMngModel;

class UserRsvnController extends BaseController
{
    protected $ckupTrgtModel;
    protected $coHsptlLnkngModel;
    protected $hsptlMngModel;

    public function __construct()
    {
        $this->ckupTrgtModel = new CkupTrgtModel();
        $this->coHsptlLnkngModel = new CoHsptlLnkngModel();
        $this->hsptlMngModel = new HsptlMngModel();
    }

    public function index()
    {
        // 로그인 체크
        if (!session()->get('user_id')) {
            return redirect()->to('/user/login')->with('error', '로그인이 필요합니다.');
        }

        $ckupTrgtSn = $this->request->getGet('ckup_trgt_sn');

        if (!$ckupTrgtSn) {
            return redirect()->back()->with('error', '잘못된 접근입니다.');
        }

        // 검진 대상자 정보 조회
        $targetInfo = $this->ckupTrgtModel->find($ckupTrgtSn);

        if (!$targetInfo) {
            return redirect()->back()->with('error', '대상자 정보를 찾을 수 없습니다.');
        }

        // 회사 번호 (대상자의 회사 번호 사용)
        $coSn = $targetInfo['CO_SN'];

        // 연결된 병원 리스트 조회
        // CO_HSPTL_LNKNG 테이블과 HSPTL_MNG 테이블 조인
        $linkedHospitals = $this->coHsptlLnkngModel
            ->select('CO_HSPTL_LNKNG.*, HSPTL_MNG.HSPTL_NM, HSPTL_MNG.RGN, HSPTL_MNG.CNPL1 AS TEL')
            ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = CO_HSPTL_LNKNG.HSPTL_SN')
            ->where('CO_HSPTL_LNKNG.CO_SN', $coSn)
            // ->where('CO_HSPTL_LNKNG.USE_YN', 'Y') // USE_YN 컬럼이 없으므로 주석 처리
            ->findAll();

        // 만약 USE_YN 컬럼이 없다면 제외하고 조회해야 함. 일단 모델을 확인하지 않았으므로 일반적인 가정하에 작성.
        // 에러 발생 시 수정.

        $data = [
            'targetInfo' => $targetInfo,
            'linkedHospitals' => $linkedHospitals
        ];

        return view('user/rsvn/rsvn_sel', $data);
    }

    public function getCalendarEvents()
    {
        $hsptlSn = $this->request->getGet('hsptl_sn');
        $year = $this->request->getGet('year');

        if (!$hsptlSn || !$year) {
            return $this->response->setJSON([]);
        }

        $dayCkupMngModel = model('DayCkupMngModel');
        $events = $dayCkupMngModel->getCalendarEvents($hsptlSn, $year);

        return $this->response->setJSON($events);
    }

    public function getProducts()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $hsptlSn = $this->request->getGet('hsptl_sn');
        $ckupTrgtSn = $this->request->getGet('ckup_trgt_sn');

        if (!$hsptlSn || !$ckupTrgtSn) {
            return $this->response->setJSON(['success' => false, 'message' => '필수 파라미터가 없습니다.']);
        }

        // 대상자 정보 조회
        $targetInfo = $this->ckupTrgtModel->find($ckupTrgtSn);
        if (!$targetInfo) {
            return $this->response->setJSON(['success' => false, 'message' => '대상자 정보를 찾을 수 없습니다.']);
        }

        // 지원구분 결정 (본인: SUPPORT_FUND, 가족: FAMILY_SUPPORT_FUND)
        $sprtSe = ($targetInfo['RELATION'] == 'S') ? $targetInfo['SUPPORT_FUND'] : $targetInfo['FAMILY_SUPPORT_FUND'];

        // 디버깅 로그
        log_message('debug', '=== Product Query Debug ===');
        log_message('debug', 'HSPTL_SN: ' . $hsptlSn);
        log_message('debug', 'CKUP_YYYY: ' . $targetInfo['CKUP_YYYY']);
        log_message('debug', 'RELATION: ' . $targetInfo['RELATION']);
        log_message('debug', 'SUPPORT_FUND: ' . ($targetInfo['SUPPORT_FUND'] ?? 'NULL'));
        log_message('debug', 'FAMILY_SUPPORT_FUND: ' . ($targetInfo['FAMILY_SUPPORT_FUND'] ?? 'NULL'));
        log_message('debug', 'Selected SPRT_SE: ' . $sprtSe);

        // 검진상품 조회
        $ckupGdsExcelMngModel = model('CkupGdsExcelMngModel');
        $products = $ckupGdsExcelMngModel
            ->where('HSPTL_SN', $hsptlSn)
            ->where('CKUP_YYYY', $targetInfo['CKUP_YYYY'])
            ->where('DEL_YN', 'N');

        // 본인일 경우 SPRT_SE, 가족일 경우 FAM_SPRT_SE로 필터링
        if ($targetInfo['RELATION'] == 'S') {
            $products = $products->where('SPRT_SE', $sprtSe);
            log_message('debug', 'Filtering by SPRT_SE: ' . $sprtSe);
        } else {
            $products = $products->where('FAM_SPRT_SE', $sprtSe);
            log_message('debug', 'Filtering by FAM_SPRT_SE: ' . $sprtSe);
        }

        $productList = $products->findAll();
        
        // 디버그 정보 수집
        $debugInfo = [
            'hsptl_sn' => $hsptlSn,
            'ckup_yyyy' => $targetInfo['CKUP_YYYY'],
            'relation' => $targetInfo['RELATION'],
            'support_fund' => $targetInfo['SUPPORT_FUND'] ?? 'NULL',
            'family_support_fund' => $targetInfo['FAMILY_SUPPORT_FUND'] ?? 'NULL',
            'selected_sprt_se' => $sprtSe,
            'filter_column' => ($targetInfo['RELATION'] == 'S') ? 'SPRT_SE' : 'FAM_SPRT_SE',
            'sql_query' => $ckupGdsExcelMngModel->getLastQuery(),
            'products_count' => count($productList)
        ];
        
        log_message('debug', 'Products found: ' . count($productList));
        log_message('debug', 'SQL Query: ' . $ckupGdsExcelMngModel->getLastQuery());
        log_message('debug', '=========================');

        return $this->response->setJSON([
            'success' => true, 
            'products' => $productList,
            'debug' => $debugInfo
        ]);
    }

    public function getCheckupItems()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $ckupGdsSn = $this->request->getGet('ckup_gds_sn');

        if (!$ckupGdsSn) {
            return $this->response->setJSON(['success' => false, 'message' => '필수 파라미터가 없습니다.']);
        }

        // CKUP_GDS_EXCEL_ARTCL 테이블에서 검사항목 조회
        $db = db_connect();
        $builder = $db->table('CKUP_GDS_EXCEL_ARTCL');
        $items = $builder->select('CKUP_SE, CKUP_ARTCL, DSS')
                         ->where('CKUP_GDS_EXCEL_MNG_SN', $ckupGdsSn)
                         ->orderBy('CKUP_GDS_EXCEL_ARTCL_SN', 'ASC')
                         ->get()
                         ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'items' => $items
        ]);
    }

    public function getProductChoiceItems()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $ckupGdsSn = $this->request->getGet('ckup_gds_sn');

        if (!$ckupGdsSn) {
            return $this->response->setJSON(['success' => false, 'message' => '필수 파라미터가 없습니다.']);
        }

        $db = db_connect();

        // 1. 선택 그룹 조회
        $groupBuilder = $db->table('CKUP_GDS_EXCEL_CHC_GROUP');
        $groups = $groupBuilder->select('CKUP_GDS_EXCEL_CHC_GROUP_SN, GROUP_NM, CHC_ARTCL_CNT, CHC_ARTCL_CNT2')
                               ->where('CKUP_GDS_EXCEL_MNG_SN', $ckupGdsSn)
                               ->where('DEL_YN', 'N')
                               ->orderBy('CKUP_GDS_EXCEL_CHC_GROUP_SN', 'ASC')
                               ->get()
                               ->getResultArray();

        // 2. 각 그룹별 선택 항목 조회
        $result = [];
        foreach ($groups as $group) {
            $itemBuilder = $db->table('CKUP_GDS_EXCEL_CHC_ARTCL');
            $items = $itemBuilder->select('CKUP_GDS_EXCEL_CHC_ARTCL_SN, CKUP_TYPE, CKUP_ARTCL, GNDR_SE')
                                 ->where('CKUP_GDS_EXCEL_CHC_GROUP_SN', $group['CKUP_GDS_EXCEL_CHC_GROUP_SN'])
                                 ->where('DEL_YN', 'N')
                                 ->orderBy('CKUP_GDS_EXCEL_CHC_ARTCL_SN', 'ASC')
                                 ->get()
                                 ->getResultArray();
            
            // 그룹 정보에 항목 리스트 추가
            $group['items'] = $items;
            $result[] = $group;
        }

        return $this->response->setJSON([
            'success' => true,
            'groups' => $result
        ]);
    }

    public function getAdditionalCheckups()
    {
        $ckupGdsSn = $this->request->getGet('ckup_gds_sn');

        if (!$ckupGdsSn) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }

        $db = \Config\Database::connect();
        
        // CKUP_GDS_EXCEL_ADD_CHC 테이블에서 직접 조회
        // ckup_gds_sn은 실제로 CKUP_GDS_EXCEL_MNG_SN입니다
        $builder = $db->table('CKUP_GDS_EXCEL_ADD_CHC');
        $items = $builder->select('CKUP_GDS_EXCEL_ADD_CHC_SN, CKUP_ARTCL, GNDR_SE, CKUP_CST')
                         ->where('CKUP_GDS_EXCEL_SN', $ckupGdsSn)
                         ->where('DEL_YN', 'N')
                         ->orderBy('CKUP_GDS_EXCEL_ADD_CHC_SN', 'ASC')
                         ->get()
                         ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'items' => $items
        ]);
    }
}
