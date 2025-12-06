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

        // 지원금 정보가 없을 경우, 본인의 지원금 정보를 가져옴
        if (empty($targetInfo['SUPPORT_FUND'])) {
            $employeeInfo = $this->ckupTrgtModel
                ->where('BUSINESS_NUM', $targetInfo['BUSINESS_NUM'])
                ->where('CO_SN', $targetInfo['CO_SN'])
                ->where('CKUP_YYYY', $targetInfo['CKUP_YYYY'])
                ->where('RELATION', 'S')
                ->first();

            if ($employeeInfo) {
                $targetInfo['SUPPORT_FUND'] = $employeeInfo['SUPPORT_FUND'];
                $targetInfo['FAMILY_SUPPORT_FUND'] = $employeeInfo['FAMILY_SUPPORT_FUND'];
            }
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

        // 지원금 정보가 없을 경우, 본인의 지원금 정보를 가져옴
        if (empty($targetInfo['SUPPORT_FUND'])) {
            $employeeInfo = $this->ckupTrgtModel
                ->where('BUSINESS_NUM', $targetInfo['BUSINESS_NUM'])
                ->where('CO_SN', $targetInfo['CO_SN'])
                ->where('CKUP_YYYY', $targetInfo['CKUP_YYYY'])
                ->where('RELATION', 'S')
                ->first();

            if ($employeeInfo) {
                $targetInfo['SUPPORT_FUND'] = $employeeInfo['SUPPORT_FUND'];
                $targetInfo['FAMILY_SUPPORT_FUND'] = $employeeInfo['FAMILY_SUPPORT_FUND'];
            }
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
        $hsptlSn = $this->request->getGet('hsptl_sn');

        $db = \Config\Database::connect();
        
        // hsptl_sn이 제공된 경우 (병원 선택 시)
        if ($hsptlSn) {
            // CKUP_GDS_EXCEL_MNG와 JOIN하여 해당 병원의 모든 추가검사 항목 조회
            $builder = $db->table('CKUP_GDS_EXCEL_ADD_CHC a');
            $items = $builder->select('a.CKUP_GDS_EXCEL_ADD_CHC_SN, a.CKUP_ARTCL, a.GNDR_SE, a.CKUP_CST')
                             ->join('CKUP_GDS_EXCEL_MNG m', 'a.CKUP_GDS_EXCEL_SN = m.CKUP_GDS_EXCEL_MNG_SN')
                             ->where('m.HSPTL_SN', $hsptlSn)
                             ->where('a.DEL_YN', 'N')
                             ->where('m.DEL_YN', 'N')
                             ->orderBy('a.CKUP_GDS_EXCEL_ADD_CHC_SN', 'ASC')
                             ->get()
                             ->getResultArray();
        }
        // ckup_gds_sn이 제공된 경우 (상품 선택 시 - 이전 방식 유지)
        else if ($ckupGdsSn) {
            $builder = $db->table('CKUP_GDS_EXCEL_ADD_CHC');
            $items = $builder->select('CKUP_GDS_EXCEL_ADD_CHC_SN, CKUP_ARTCL, GNDR_SE, CKUP_CST')
                             ->where('CKUP_GDS_EXCEL_SN', $ckupGdsSn)
                             ->where('DEL_YN', 'N')
                             ->orderBy('CKUP_GDS_EXCEL_ADD_CHC_SN', 'ASC')
                             ->get()
                             ->getResultArray();
        }
        else {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }

        return $this->response->setJSON([
            'success' => true,
            'items' => $items
        ]);
    }

    public function completeReservation()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $postData = $this->request->getPost();
        
        // 필수 데이터 검증
        $ckupTrgtSn = $postData['ckup_trgt_sn'] ?? null;
        $hsptlSn = $postData['hsptl_sn'] ?? null;
        $ckupDate = $postData['ckup_date'] ?? null;
        $ckupGdsSn = $postData['ckup_gds_sn'] ?? null;
        $handphone = $postData['handphone'] ?? null;
        $tel = $postData['tel'] ?? null;
        $zipCode = $postData['zip_code'] ?? null;
        $addr = $postData['addr'] ?? null;
        $addr2 = $postData['addr2'] ?? null;
        
        if (!$ckupTrgtSn || !$hsptlSn || !$ckupDate || !$ckupGdsSn || !$handphone || !$zipCode || !$addr) {
            return $this->response->setJSON(['success' => false, 'message' => '필수 정보가 누락되었습니다.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. CKUP_TRGT 업데이트
            $this->ckupTrgtModel->update($ckupTrgtSn, [
                'CKUP_HSPTL_SN' => $hsptlSn,
                'CKUP_RSVN_YMD' => $ckupDate,
                'CKUP_GDS_SN' => $ckupGdsSn,
                'TEL' => $tel,
                'HANDPHONE' => $handphone,
                'RSVT_STTS' => 'Y', // 예약상태: 예약
                'MDFCN_ID' => session()->get('user_id'),
                'MDFCN_YMD' => date('Y-m-d H:i:s')
            ]);

            // 2. RSVN_CKUP_TRGT_ADDR 등록/수정
            $rsvnAddrModel = model('RsvnCkupTrgtAddrModel');
            $existingAddr = $rsvnAddrModel->where('CKUP_TRGT_SN', $ckupTrgtSn)->first();
            
            $addrData = [
                'CKUP_TRGT_SN' => $ckupTrgtSn,
                'ZIP_CODE' => $zipCode,
                'ADDR' => $addr,
                'ADDR2' => $addr2
            ];

            if ($existingAddr) {
                $rsvnAddrModel->update($existingAddr['RSVN_CKUP_TRGT_ADDR_SN'], $addrData);
            } else {
                $rsvnAddrModel->insert($addrData);
            }

            // 3. RSVN_CKUP_GDS_CHC_ARTCL (선택항목) 등록
            // 기존 데이터 삭제 (재예약 시)
            $rsvnChcModel = model('RsvnCkupGdsChcArtclModel');
            $rsvnChcModel->where('CKUP_TRGT_SN', $ckupTrgtSn)->delete();

            if (!empty($postData['choice_items'])) {
                foreach ($postData['choice_items'] as $itemSn) {
                    $rsvnChcModel->insert([
                        'CKUP_GDS_EXCEL_CHC_ARTCL_SN' => $itemSn,
                        'CKUP_TRGT_SN' => $ckupTrgtSn
                    ]);
                }
            }

            // 4. RSVN_CKUP_GDS_ADD_CHC (추가검사) 등록
            // 기존 데이터 삭제
            $rsvnAddModel = model('RsvnCkupGdsAddChcModel');
            $rsvnAddModel->where('CKUP_TRGT_SN', $ckupTrgtSn)->delete();

            if (!empty($postData['additional_items'])) {
                foreach ($postData['additional_items'] as $itemSn) {
                    $rsvnAddModel->insert([
                        'CKUP_GDS_EXCEL_ADD_ARTCL_SN' => $itemSn,
                        'CKUP_TRGT_SN' => $ckupTrgtSn
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['success' => false, 'message' => '예약 처리 중 오류가 발생했습니다.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => '예약이 완료되었습니다.']);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Reservation Error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => '시스템 오류가 발생했습니다.']);
        }
    }
    public function getReservationDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $ckupTrgtSn = $this->request->getGet('ckup_trgt_sn');

        if (!$ckupTrgtSn) {
            return $this->response->setJSON(['success' => false, 'message' => '필수 파라미터가 없습니다.']);
        }

        $db = \Config\Database::connect();

        // 1. 선택 항목 조회 (RSVN_CKUP_GDS_CHC_ARTCL + CKUP_GDS_EXCEL_CHC_ARTCL)
        $choiceBuilder = $db->table('RSVN_CKUP_GDS_CHC_ARTCL a');
        $choiceItems = $choiceBuilder->select('b.CKUP_GDS_EXCEL_CHC_ARTCL_SN, b.CKUP_ARTCL, b.CKUP_TYPE, b.CKUP_SE')
            ->join('CKUP_GDS_EXCEL_CHC_ARTCL b', 'a.CKUP_GDS_EXCEL_CHC_ARTCL_SN = b.CKUP_GDS_EXCEL_CHC_ARTCL_SN')
            ->where('a.CKUP_TRGT_SN', $ckupTrgtSn)
            ->where('a.DEL_YN', 'N')
            ->get()
            ->getResultArray();

        // 2. 추가 검사 항목 조회 (RSVN_CKUP_GDS_ADD_CHC + CKUP_GDS_EXCEL_ADD_CHC)
        $addBuilder = $db->table('RSVN_CKUP_GDS_ADD_CHC a');
        $addItems = $addBuilder->select('b.CKUP_GDS_EXCEL_ADD_CHC_SN, b.CKUP_ARTCL, b.CKUP_CST')
            ->join('CKUP_GDS_EXCEL_ADD_CHC b', 'a.CKUP_GDS_EXCEL_ADD_ARTCL_SN = b.CKUP_GDS_EXCEL_ADD_CHC_SN')
            ->where('a.CKUP_TRGT_SN', $ckupTrgtSn)
            ->where('a.DEL_YN', 'N')
            ->get()
            ->getResultArray();

        // 3. 연락처 정보 조회 (CKUP_TRGT)
        $targetInfo = $db->table('CKUP_TRGT')
            ->select('TEL, HANDPHONE')
            ->where('CKUP_TRGT_SN', $ckupTrgtSn)
            ->get()
            ->getRowArray();

        // 4. 주소 정보 조회 (RSVN_CKUP_TRGT_ADDR)
        $addrInfo = $db->table('RSVN_CKUP_TRGT_ADDR')
            ->select('ZIP_CODE, ADDR, ADDR2')
            ->where('CKUP_TRGT_SN', $ckupTrgtSn)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'choiceItems' => $choiceItems,
            'addItems' => $addItems,
            'targetInfo' => $targetInfo,
            'addrInfo' => $addrInfo
        ]);
    }
}
