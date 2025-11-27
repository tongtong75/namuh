<?php

namespace App\Controllers;

use App\Models\HsptlMngModel;
use App\Models\CoMngModel;
use App\Models\CkupGdsMngModel;
use App\Models\CkupGdsArtclModel;
use App\Models\CkupGdsChcGroupModel;
use App\Models\CkupGdsChcArtclModel;
use App\Models\CkupGdsAddChcModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * CkupGdsController
 * 검진상품의 목록, 등록, 수정, 삭제 및 관련 AJAX 처리를 담당합니다.
 */
class CkupGdsController extends BaseController
{
    protected HsptlMngModel $hsptlModel;
    protected CoMngModel $coModel;
    protected CkupGdsMngModel $ckupGdsMngModel;
    protected CkupGdsArtclModel $ckupGdsArtclModel;
    protected CkupGdsChcGroupModel $ckupGdsChcGroupModel;
    protected CkupGdsChcArtclModel $ckupGdsChcArtclModel;
    protected CkupGdsAddChcModel $ckupGdsAddChcModel;
    protected $db;

    protected $helpers = ['form', 'url', 'text'];

    /**
     * 컨트롤러 생성자.
     * 필요한 모델과 데이터베이스 인스턴스를 초기화합니다.
     */
    public function __construct()
    {
        $this->hsptlModel = new HsptlMngModel();
        $this->coModel = new CoMngModel();
        $this->ckupGdsMngModel = new CkupGdsMngModel();
        $this->ckupGdsArtclModel = new CkupGdsArtclModel();
        $this->ckupGdsChcGroupModel = new CkupGdsChcGroupModel();
        $this->ckupGdsChcArtclModel = new CkupGdsChcArtclModel();
        $this->ckupGdsAddChcModel = new CkupGdsAddChcModel();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * 뷰에서 공통으로 사용하는 데이터(병원, 회사, 년도 목록)를 생성합니다.
     * @param string $title 페이지 제목
     * @return array 공통 데이터 배열
     */
    private function getCommonData(string $title): array
    {
        $currentYear = date('Y');
        
        $hospitals = [];
        if (session()->get('user_type') === 'H') {
            $hospitals = $this->hsptlModel->where('HSPTL_SN', session()->get('hsptl_sn'))->where('DEL_YN', 'N')->findAll();
        } else {
            $hospitals = $this->hsptlModel->where('DEL_YN', 'N')->orderBy('HSPTL_NM', 'ASC')->findAll();
        }

        return [
            'hospitals' => $hospitals,
            'companies' => $this->coModel->where('DEL_YN', 'N')->orderBy('CO_NM', 'ASC')->findAll(),
            'years' => range($currentYear + 1, $currentYear - 1),
            'title' => $title
        ];
    }

    /**
     * 검진상품 목록 페이지를 표시합니다.
     */
    public function index()
    {
        $data = $this->getCommonData('검진상품 목록');
        return view('mngr/ckup_gds/index', $data);
    }
    
    /**
     * DataTables 서버 사이드 처리를 위한 AJAX 요청을 핸들링합니다.
     * @return \CodeIgniter\HTTP\ResponseInterface JSON 응답
     */
    public function ajax_list()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.']);
        }

        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $searchValue = $this->request->getPost('search')['value'] ?? '';
        $orderColumn = $this->request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        
        $filters = [
            'ckup_yyyy' => $this->request->getPost('ckup_yyyy'),
            'hsptl_sn' => $this->request->getPost('hsptl_sn'),
            'co_sn' => $this->request->getPost('co_sn')
        ];

        if (session()->get('user_type') === 'H') {
            $filters['hsptl_sn'] = session()->get('hsptl_sn');
        }

        try {
            // 모델을 통해 데이터베이스에서 데이터를 가져옵니다.
            $result = $this->ckupGdsMngModel->getDatatablesList($start, $length, $searchValue, $orderColumn, $orderDir, $filters);
            
            $data = [];
            foreach ($result['data'] as $index => $row) {
                // DataTables에 맞게 데이터 형식을 가공합니다.
                $data[] = [
                    'no'          => $start + $index + 1,
                    'ckup_yyyy'   => esc($row['CKUP_YYYY']),
                    'hsptl_nm'    => esc($row['HSPTL_NM'] ?? '-'),
                    'co_nm'       => esc($row['CO_NM'] ?? '-'),
                    'ckup_gds_nm' => esc($row['CKUP_GDS_NM']),
                    'sprt_se'     => esc($row['SPRT_SE'] ?? '없음'),
                    'fam_sprt_se' => esc($row['FAM_SPRT_SE'] ?? '없음'),
                    'reg_ymd'     => esc(date('Y-m-d', strtotime($row['REG_YMD']))),
                    'actions'     => view('mngr/ckup_gds/action_buttons', ['ckupGds' => $row]) // 관리 버튼 HTML 생성
                ];
            }

            // DataTables가 요구하는 최종 JSON 포맷으로 응답합니다.
            $output = [
                "draw"            => intval($this->request->getPost('draw')),
                "recordsTotal"    => $result['recordsTotal'],
                "recordsFiltered" => $result['recordsFiltered'],
                "data"            => $data,
                "csrf_hash"       => csrf_hash()
            ];

            return $this->response->setJSON($output);
            
        } catch (\Exception $e) {
            log_message('error', 'Ajax list error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error', 'message' => '데이터 조회 중 오류가 발생했습니다.', 'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * 신규 검진상품 등록 페이지를 표시합니다.
     */
    public function add()
    {
        $data = $this->getCommonData('검진상품 신규 등록');
        return view('mngr/ckup_gds/add', $data);
    }
    
    /**
     * 기존 검진상품 수정 페이지를 표시합니다.
     * @param int|null $id 수정할 검진상품의 CKUP_GDS_SN
     */
    public function edit($id = null)
    {
        if (!$id || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 상품의 기본정보, 기본항목, 선택항목 그룹 및 하위 항목을 모두 조회
        $ckupGdsData = $this->ckupGdsMngModel->getCkupGdsWithDetail($id);

        log_message('debug', 'CkupGdsData in edit method: ' . json_encode($ckupGdsData));

        if (!$ckupGdsData) {
            throw PageNotFoundException::forPageNotFound("해당 ID({$id})의 검진상품을 찾을 수 없습니다.");
        }

        $data = $this->getCommonData('검진상품 정보 수정');
        $data['ckupGds'] = $ckupGdsData; // 조회된 전체 데이터를 뷰로 전달

        // 신규 등록과 동일한 'add' 뷰를 재사용하여 수정 페이지를 렌더링
        return view('mngr/ckup_gds/add', $data);
    }

    /**
     * 검진 상품 및 관련 항목들을 저장 또는 수정하는 AJAX 핸들러
     * @return \CodeIgniter\HTTP\ResponseInterface JSON 응답
     */
    public function ckupGdsSave()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }
        
        $data = $this->request->getJSON(true);

        $validationErrors = $this->validateCkupGdsData($data);
        if (!empty($validationErrors)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => implode("\n", $validationErrors), 'csrf_hash' => csrf_hash()]);
        }

        // CKUP_GDS_SN의 존재 여부로 신규/수정 모드를 판별
        $isEditMode = !empty($data['basicInfo']['CKUP_GDS_SN']);
        $ckupGdsSn = $isEditMode ? $data['basicInfo']['CKUP_GDS_SN'] : null;

        // 여러 테이블에 걸친 작업을 위해 데이터베이스 트랜잭션 시작
        $this->db->transStart();

        try {
            $basicInfo = $data['basicInfo'];

            if ($isEditMode) {
                // --- 수정 모드 ---
                $this->ckupGdsMngModel->update($ckupGdsSn, $basicInfo);
                $groupsToDelete = $this->ckupGdsChcGroupModel->where('CKUP_GDS_SN', $ckupGdsSn)->findColumn('CKUP_GDS_CHC_GROUP_SN');
                if (!empty($groupsToDelete)) {
                    $this->ckupGdsChcArtclModel->whereIn('CKUP_GDS_CHC_GROUP_SN', $groupsToDelete)->delete();
                }
                $this->ckupGdsChcGroupModel->where('CKUP_GDS_SN', $ckupGdsSn)->delete();
                $this->ckupGdsArtclModel->where('CKUP_GDS_SN', $ckupGdsSn)->delete();
                log_message('debug', 'Attempting to delete ckup_gds_add_chc for CKUP_GDS_SN: ' . $ckupGdsSn);
                $this->ckupGdsAddChcModel->where('CKUP_GDS_SN', $ckupGdsSn)->delete();

            } else {
                // --- 신규 등록 모드 ---
                $this->ckupGdsMngModel->insert($basicInfo);
                $ckupGdsSn = $this->ckupGdsMngModel->getInsertID();

                if (!$ckupGdsSn) {
                    throw new \Exception('검진상품 저장에 실패하여 ID를 가져올 수 없습니다.');
                }
            }
            
            // (신규/수정 공통) 관련 데이터 새로 삽입
            $this->saveRelatedItems($ckupGdsSn, $data);
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('데이터베이스 트랜잭션에 실패했습니다.');
            }

            return $this->response->setJSON([
                'success'   => true,
                'message'   => '성공적으로 ' . ($isEditMode ? '수정' : '저장') . '되었습니다.',
                'ckup_gds_sn' => $ckupGdsSn,
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CkupGds save/update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success'   => false,
                'message'   => '처리 중 오류가 발생했습니다: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    /**
     * 관련 항목들(기본, 선택)을 저장하는 헬퍼 메소드
     * @param int $ckupGdsSn 상품 마스터 SN
     * @param array $data 클라이언트로부터 받은 전체 데이터
     */
    private function saveRelatedItems(int $ckupGdsSn, array $data)
    {
        if (!empty($data['basicItems'])) {
            $basicItemsData = array_map(fn($artclSn) => ['CKUP_GDS_SN' => $ckupGdsSn, 'CKUP_ARTCL_SN' => $artclSn], $data['basicItems']);
            $this->ckupGdsArtclModel->insertBatch($basicItemsData);
        }

        if (!empty($data['choiceGroups'])) {
            foreach ($data['choiceGroups'] as $group) {
                $groupData = ['CKUP_GDS_SN' => $ckupGdsSn, 'GROUP_NM' => $group['GROUP_NM'], 'CHC_ARTCL_CNT' => $group['CHC_ARTCL_CNT']];
                $this->ckupGdsChcGroupModel->insert($groupData);
                $ckupGdsChcGroupSn = $this->ckupGdsChcGroupModel->getInsertID();

                if (!$ckupGdsChcGroupSn) continue;

                if (!empty($group['items'])) {
                    $choiceItemsData = array_map(fn($chcArtclSn) => ['CKUP_GDS_CHC_GROUP_SN' => $ckupGdsChcGroupSn, 'CHC_ARTCL_SN' => $chcArtclSn], $group['items']);
                    $this->ckupGdsChcArtclModel->insertBatch($choiceItemsData);
                }
            }
        }

        if (!empty($data['addChoiceItems'])) {
            $addChoiceItemsData = array_map(fn($artclSn) => ['CKUP_GDS_SN' => $ckupGdsSn, 'CHC_ARTCL_SN' => $artclSn], $data['addChoiceItems']);
            $this->ckupGdsAddChcModel->insertBatch($addChoiceItemsData);
        }
    }

    /**
     * 클라이언트로부터 받은 데이터의 유효성을 검사합니다.
     * @param array $data 검사할 데이터
     * @return array 오류 메시지 배열. 오류가 없으면 빈 배열 반환.
     */
    private function validateCkupGdsData($data): array
    {
        $errors = [];
        if (empty($data['basicInfo']['CKUP_GDS_NM'])) $errors[] = '기본정보: 검진상품명은 필수입니다.';
        if (empty($data['basicInfo']['CKUP_YYYY']))   $errors[] = '기본정보: 검진년도는 필수입니다.';
        if (empty($data['basicInfo']['HSPTL_SN']))    $errors[] = '기본정보: 검진병원은 필수입니다.';
        if (empty($data['basicInfo']['CO_SN']))       $errors[] = '기본정보: 회사는 필수입니다.';
        if (empty($data['basicItems']) || !is_array($data['basicItems'])) $errors[] = '항목정보: 기본 항목을 1개 이상 추가해야 합니다.';
        if (empty($data['addChoiceItems']) || !is_array($data['addChoiceItems'])) $errors[] = '추가선택항목정보: 추가 선택 항목을 1개 이상 추가해야 합니다.';

        if (!empty($data['choiceGroups']) && is_array($data['choiceGroups'])) {
            foreach ($data['choiceGroups'] as $index => $group) {
                if (empty($group['GROUP_NM'])) $errors[] = "선택항목정보: " . ($index + 1) . "번째 그룹의 그룹명이 없습니다.";
                if (!isset($group['CHC_ARTCL_CNT']) || !is_numeric($group['CHC_ARTCL_CNT']) || $group['CHC_ARTCL_CNT'] < 1) $errors[] = "선택항목정보: " . ($index + 1) . "번째 그룹의 선택갯수가 유효하지 않습니다.";
                if (empty($group['items']) || !is_array($group['items'])) $errors[] = "선택항목정보: " . ($index + 1) . "번째 그룹에 항목을 1개 이상 추가해야 합니다.";
            }
        }
        return $errors;
    }

    /**
     * 검진상품 및 관련 데이터를 삭제합니다.
     * @param int|null $id 삭제할 검진상품의 CKUP_GDS_SN
     * @return \CodeIgniter\HTTP\ResponseInterface JSON 응답
     */
    public function delete($id = null)
    {
        if (!$this->request->isAJAX() || !$id || !is_numeric($id)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }

        $this->db->transStart();

        try {
            // 1. 선택항목 그룹 및 하위 항목 삭제
            $groupsToDelete = $this->ckupGdsChcGroupModel->where('CKUP_GDS_SN', $id)->findColumn('CKUP_GDS_CHC_GROUP_SN');
            if (!empty($groupsToDelete)) {
                $this->ckupGdsChcArtclModel->whereIn('CKUP_GDS_CHC_GROUP_SN', $groupsToDelete)->delete();
            }
            $this->ckupGdsChcGroupModel->where('CKUP_GDS_SN', $id)->delete();

            // 2. 기본항목 삭제
            $this->ckupGdsArtclModel->where('CKUP_GDS_SN', $id)->delete();

            // 3. 추가선택항목 삭제
            $this->ckupGdsAddChcModel->where('CKUP_GDS_SN', $id)->delete();

            // 4. 검진상품 마스터 삭제
            $this->ckupGdsMngModel->delete($id);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('데이터베이스 트랜잭션에 실패했습니다.');
            }

            return $this->response->setJSON([
                'success'   => true,
                'message'   => '성공적으로 삭제되었습니다.',
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CkupGds delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success'   => false,
                'message'   => '처리 중 오류가 발생했습니다: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
}