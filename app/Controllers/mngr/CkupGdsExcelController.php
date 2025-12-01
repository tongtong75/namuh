<?php

namespace App\Controllers\mngr;

use App\Controllers\BaseController;
use App\Models\HsptlMngModel;
use App\Models\CoMngModel;
use App\Models\CkupGdsExcelMngModel;
use App\Models\CkupGdsExcelArtclModel;
use App\Models\CkupGdsExcelChcGroupModel;
use App\Models\CkupGdsExcelChcArtclModel;
use App\Models\CkupGdsExcelAddChcModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class CkupGdsExcelController extends BaseController
{
    protected HsptlMngModel $hsptlModel;
    protected CoMngModel $coModel;
    protected CkupGdsExcelMngModel $ckupGdsExcelMngModel;
    protected CkupGdsExcelArtclModel $ckupGdsExcelArtclModel;
    protected CkupGdsExcelChcGroupModel $ckupGdsExcelChcGroupModel;
    protected CkupGdsExcelChcArtclModel $ckupGdsExcelChcArtclModel;
    protected CkupGdsExcelAddChcModel $ckupGdsExcelAddChcModel;
    protected $db;

    public function __construct()
    {
        $this->hsptlModel = new HsptlMngModel();
        $this->coModel = new CoMngModel();
        $this->ckupGdsExcelMngModel = new CkupGdsExcelMngModel();
        $this->ckupGdsExcelArtclModel = new CkupGdsExcelArtclModel();
        $this->ckupGdsExcelChcGroupModel = new CkupGdsExcelChcGroupModel();
        $this->ckupGdsExcelChcArtclModel = new CkupGdsExcelChcArtclModel();
        $this->ckupGdsExcelAddChcModel = new CkupGdsExcelAddChcModel();
        $this->db = \Config\Database::connect();
    }

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

    public function index()
    {
        $data = $this->getCommonData('검진상품 엑셀 목록');
        return view('mngr/ckup_gds_excel/index', $data);
    }

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
            $result = $this->ckupGdsExcelMngModel->getDatatablesList($start, $length, $searchValue, $orderColumn, $orderDir, $filters);
            
            $data = [];
            foreach ($result['data'] as $index => $row) {
                $data[] = [
                    'no'          => $start + $index + 1,
                    'ckup_yyyy'   => esc($row['CKUP_YYYY']),
                    'hsptl_nm'    => esc($row['HSPTL_NM'] ?? '-'),
                    'ckup_gds_nm' => esc($row['CKUP_GDS_NM']),
                    'sprt_se'     => esc($row['SPRT_SE'] ?? '없음'),
                    'fam_sprt_se' => esc($row['FAM_SPRT_SE'] ?? ''),
                    'reg_ymd'     => esc(date('Y-m-d', strtotime($row['REG_YMD']))),
                    'actions'     => view('mngr/ckup_gds_excel/action_buttons', ['ckupGds' => $row])
                ];
            }

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

    public function add()
    {
        $data = $this->getCommonData('검진상품 엑셀 신규 등록');
        return view('mngr/ckup_gds_excel/add', $data);
    }

    public function edit($id = null)
    {
        if (!$id || !is_numeric($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $ckupGdsData = $this->ckupGdsExcelMngModel->getCkupGdsExcelWithDetail($id);

        if (!$ckupGdsData) {
            throw PageNotFoundException::forPageNotFound("해당 ID({$id})의 검진상품을 찾을 수 없습니다.");
        }

        $data = $this->getCommonData('검진상품 엑셀 정보 수정');
        $data['ckupGds'] = $ckupGdsData;

        return view('mngr/ckup_gds_excel/add', $data);
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }
        
        $data = $this->request->getJSON(true);

        // Basic Validation
        if (empty($data['basicInfo']['CKUP_GDS_NM'])) return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => '검진상품명은 필수입니다.', 'csrf_hash' => csrf_hash()]);

        $isEditMode = !empty($data['basicInfo']['CKUP_GDS_EXCEL_MNG_SN']);
        $ckupGdsSn = $isEditMode ? $data['basicInfo']['CKUP_GDS_EXCEL_MNG_SN'] : null;

        $this->db->transStart();

        try {
            $basicInfo = $data['basicInfo'];

            if ($isEditMode) {
                $this->ckupGdsExcelMngModel->update($ckupGdsSn, $basicInfo);
            } else {
                $this->ckupGdsExcelMngModel->insert($basicInfo);
                $ckupGdsSn = $this->ckupGdsExcelMngModel->getInsertID();
            }

            // Insert New Basic Items
            if (!empty($data['newBasicItems'])) {
                $this->insertBasicItems($ckupGdsSn, $data['newBasicItems']);
            }

            // Insert New Choice Items (Groups and Items)
            if (!empty($data['choiceGroups'])) {
                $this->insertChoiceItems($ckupGdsSn, $data['choiceGroups']);
            }

            // Insert New Additional Choice Items
            if (!empty($data['newAddChoiceItems'])) {
                $this->insertAddChoiceItems($ckupGdsSn, $data['newAddChoiceItems']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('데이터베이스 트랜잭션에 실패했습니다.');
            }

            return $this->response->setJSON([
                'success'   => true,
                'message'   => '저장되었습니다.',
                'ckup_gds_sn' => $ckupGdsSn,
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'CkupGdsExcel save error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success'   => false,
                'message'   => '처리 중 오류가 발생했습니다: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    private function insertBasicItems($sn, $items)
    {
        $batchData = [];
        foreach ($items as $item) {
            $batchData[] = [
                'CKUP_GDS_EXCEL_MNG_SN' => $sn,
                'CKUP_SE'     => $item['CKUP_SE'] ?? '',
                'CKUP_ARTCL'  => $item['CKUP_ARTCL'] ?? '',
                'DSS'         => $item['DSS'] ?? '',
                'GNDR_SE'     => $item['GNDR_SE'] ?? 'C',
                'RMRK'        => $item['RMRK'] ?? ''
            ];
        }
        if (!empty($batchData)) {
            $this->ckupGdsExcelArtclModel->insertBatch($batchData);
        }
    }

    private function insertChoiceItems($sn, $groups)
    {
        foreach ($groups as $groupData) {
            // Check if group exists or create new
            $groupName = $groupData['GROUP_NM'];
            $group = $this->ckupGdsExcelChcGroupModel
                ->where('CKUP_GDS_EXCEL_MNG_SN', $sn)
                ->where('GROUP_NM', $groupName)
                ->first();
            
            if ($group) {
                $groupId = $group['CKUP_GDS_EXCEL_CHC_GROUP_SN'];
                // Update counts if needed? Let's assume user might update counts in UI.
                $this->ckupGdsExcelChcGroupModel->update($groupId, [
                    'CHC_ARTCL_CNT' => $groupData['CHC_ARTCL_CNT'],
                    'CHC_ARTCL_CNT2' => $groupData['CHC_ARTCL_CNT2']
                ]);
            } else {
                $this->ckupGdsExcelChcGroupModel->insert([
                    'CKUP_GDS_EXCEL_MNG_SN' => $sn,
                    'GROUP_NM' => $groupName,
                    'CHC_ARTCL_CNT' => $groupData['CHC_ARTCL_CNT'],
                    'CHC_ARTCL_CNT2' => $groupData['CHC_ARTCL_CNT2']
                ]);
                $groupId = $this->ckupGdsExcelChcGroupModel->getInsertID();
            }

            // Insert New Items for this group
            if (!empty($groupData['newItems'])) {
                $batchItems = [];
                foreach ($groupData['newItems'] as $item) {
                    $batchItems[] = [
                        'CKUP_GDS_EXCEL_CHC_GROUP_SN' => $groupId,
                        'CKUP_GDS_EXCEL_MNG_SN' => $sn,
                        'CKUP_SE'    => $item['CKUP_SE'] ?? '',
                        'CKUP_TYPE'  => $item['CKUP_TYPE'] ?? '',
                        'CKUP_ARTCL' => $item['CKUP_ARTCL'] ?? '',
                        'DSS'        => $item['DSS'] ?? '',
                        'GNDR_SE'    => $item['GNDR_SE'] ?? 'C',
                        'RMRK'       => $item['RMRK'] ?? ''
                    ];
                }
                if (!empty($batchItems)) {
                    $this->ckupGdsExcelChcArtclModel->insertBatch($batchItems);
                }
            }
            
            // Note: Existing items (IDs) are already in DB. If user removed them from UI, they should have been deleted via deleteItem API.
            // If user moved items between groups? That's complex. Let's assume delete/add flow.
        }
    }

    private function insertAddChoiceItems($sn, $items)
    {
        $batchData = [];
        foreach ($items as $item) {
            $batchData[] = [
                'CKUP_GDS_EXCEL_SN' => $sn,
                'CKUP_SE'     => $item['CKUP_SE'] ?? '',
                'CKUP_TYPE'   => $item['CKUP_TYPE'] ?? '',
                'CKUP_ARTCL'  => $item['CKUP_ARTCL'] ?? '',
                'DSS'         => $item['DSS'] ?? '',
                'GNDR_SE'     => $item['GNDR_SE'] ?? 'C',
                'CKUP_CST'    => $item['CKUP_CST'] ?? '',
                'RMRK'        => $item['RMRK'] ?? ''
            ];
        }
        if (!empty($batchData)) {
            $this->ckupGdsExcelAddChcModel->insertBatch($batchData);
        }
    }

    public function delete($id = null)
    {
        if (!$this->request->isAJAX() || !$id || !is_numeric($id)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }

        $this->db->transStart();

        try {
            // Delete related items first
            $this->ckupGdsExcelArtclModel->where('CKUP_GDS_EXCEL_MNG_SN', $id)->delete();
            $this->ckupGdsExcelChcArtclModel->where('CKUP_GDS_EXCEL_MNG_SN', $id)->delete();
            $this->ckupGdsExcelChcGroupModel->where('CKUP_GDS_EXCEL_MNG_SN', $id)->delete();
            $this->ckupGdsExcelAddChcModel->where('CKUP_GDS_EXCEL_SN', $id)->delete(); // Note field name
            
            $this->ckupGdsExcelMngModel->delete($id);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('삭제 중 오류가 발생했습니다.');
            }

            return $this->response->setJSON([
                'success'   => true,
                'message'   => '삭제되었습니다.',
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'   => false,
                'message'   => '오류: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    // Additional methods for deleting individual items/groups could be added here if the UI supports it.
    // For now, I'll stick to the plan which focuses on "Excel Paste".
    // But the user mentioned "Group Add/Delete" in the feedback.
    // I should probably add endpoints for that or handle it in `save` if the UI sends a "delete" flag?
    // The user said: "Group configuration and Add/Delete function...".
    // This implies the UI will have buttons to delete groups.
    // I will add a `deleteGroup` method.
    
    public function deleteGroup($groupId) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $this->db->transStart();
        $this->ckupGdsExcelChcArtclModel->where('CKUP_GDS_EXCEL_CHC_GROUP_SN', $groupId)->delete();
        $this->ckupGdsExcelChcGroupModel->delete($groupId);
        $this->db->transComplete();
        
        return $this->response->setJSON(['success' => $this->db->transStatus(), 'csrf_hash' => csrf_hash()]);
    }
    
    public function deleteItem($type, $id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $model = null;
        switch($type) {
            case 'basic': $model = $this->ckupGdsExcelArtclModel; break;
            case 'choice': $model = $this->ckupGdsExcelChcArtclModel; break;
            case 'add': $model = $this->ckupGdsExcelAddChcModel; break;
        }
        
        if ($model) {
            $model->delete($id);
            return $this->response->setJSON(['success' => true, 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid type', 'csrf_hash' => csrf_hash()]);
    }
    public function deleteItems($type) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
             return $this->response->setJSON(['success' => false, 'message' => 'No items selected', 'csrf_hash' => csrf_hash()]);
        }

        $model = null;
        switch($type) {
            case 'basic': $model = $this->ckupGdsExcelArtclModel; break;
            case 'choice': $model = $this->ckupGdsExcelChcArtclModel; break;
            case 'add': $model = $this->ckupGdsExcelAddChcModel; break;
        }
        
        if ($model) {
            $model->whereIn($model->primaryKey, $ids)->delete();
            return $this->response->setJSON(['success' => true, 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid type', 'csrf_hash' => csrf_hash()]);
    }

    public function updateChoiceItem($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $data = $this->request->getPost();
        
        // Remove CSRF token from update data
        unset($data[csrf_token()]);
        
        if ($this->ckupGdsExcelChcArtclModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'csrf_hash' => csrf_hash()]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Update failed', 'csrf_hash' => csrf_hash()]);
        }
    }

    public function updateBasicItem($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $data = $this->request->getPost();
        
        // Remove CSRF token from update data
        unset($data[csrf_token()]);
        
        if ($this->ckupGdsExcelArtclModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'csrf_hash' => csrf_hash()]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Update failed', 'csrf_hash' => csrf_hash()]);
        }
    }

    public function updateAddChoiceItem($id) {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);
        
        $data = $this->request->getPost();
        
        // Remove CSRF token from update data
        unset($data[csrf_token()]);
        
        if ($this->ckupGdsExcelAddChcModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'csrf_hash' => csrf_hash()]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Update failed', 'csrf_hash' => csrf_hash()]);
        }
    }
}
