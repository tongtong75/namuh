<?php

namespace App\Controllers;

use App\Models\HsptlMngModel; // Add this line
use App\Models\ChcArtclMngModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ChcArtclMngController extends BaseController
{
    protected ChcArtclMngModel $model;
    protected HsptlMngModel $hsptlMngModel; // Add this line
    protected $helpers = ['form', 'url', 'text', 'date'];

    public function __construct()
    {
        $this->model = new ChcArtclMngModel();
        $this->hsptlMngModel = new HsptlMngModel(); // Add this line
    }

    /**
     * 선택항목 목록 페이지
     */
    public function index()
    {
        $data['title'] = '선택항목 목록';
        if (session()->get('user_type') === 'H') {
            $data['hospitals'] = $this->hsptlMngModel->where('HSPTL_SN', session()->get('hsptl_sn'))->findAll();
        } else {
            $data['hospitals'] = $this->hsptlMngModel->getActiveHospitals();
        }
        return view('mngr/chc_artcl_mng/index', $data);
    }

    /**
     * AJAX로 선택항목 목록 조회
     */
    public function ajax_list()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.']);
        }

        $hsptlSn = $this->request->getPost('hsptl_sn');
        
        if (session()->get('user_type') === 'H') {
            $hsptlSn = session()->get('hsptl_sn');
        }
        $searchKeyword = $this->request->getPost('search_keyword');

        $list = $this->model->getFilteredItems($hsptlSn, $searchKeyword);

        $data = [];
        $no = 1;

        foreach ($list as $row) {
            $data[] = [
                'no'           => $no++,
                'CHC_ARTCL_SN' => esc($row['CHC_ARTCL_SN']),
                'CKUP_ARTCL'   => esc($row['CKUP_ARTCL']),
                'ARTCL_CODE'   => esc($row['ARTCL_CODE']),
                'HSPTL_NM'     => esc($row['HSPTL_NM'] ?? ''), // Add this line
                'GNDR_SE'      => esc($row['GNDR_SE']),
                'CKUP_SE'      => esc($row['CKUP_SE']),
                'CKUP_CST'     => esc($row['CKUP_CST']),
                'AGREE_SUBMIT_YN' => esc($row['AGREE_SUBMIT_YN']),
                'RMRK'         => esc($row['RMRK']),
                'REG_YMD'      => $row['REG_YMD'] ? date('Y-m-d', strtotime($row['REG_YMD'])) : '',
                'action'       => view('mngr/chc_artcl_mng/action_buttons', ['item' => $row], ['saveData' => false])
            ];
        }

        return $this->response->setJSON([
            'data'      => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * 특정 선택항목 조회
     */
    public function ajax_get_chc_artcl($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.', 'csrf_hash' => csrf_hash()]);
        }

        $item = $this->model->where('DEL_YN', 'N')->find($id);
        
        if ($item) {
            // 병원 정보 추가
            if (isset($item['HSPTL_SN'])) {
                $hospital = $this->hsptlMngModel->getHospitalNameBySn($item['HSPTL_SN']);
                $item['HSPTL_NM'] = $hospital['HSPTL_NM'] ?? '';
            }
            return $this->response->setJSON([
                'status' => 'success', 
                'data' => $item, 
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error', 
            'message' => '선택항목을 찾을 수 없습니다.', 
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * 새 선택항목 등록
     */
    public function ajax_create()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }

        // 유효성 검사
        $validation = \Config\Services::validation();
        $rules = $this->model->buildValidationRules(false);
        $validation->setRules($rules, $this->model->validationMessages);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status'    => 'fail',
                'errors'    => $validation->getErrors(),
                'message'   => '입력값을 확인해주세요.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // 데이터 준비
        $data = [
            'CKUP_ARTCL' => $this->request->getPost('CKUP_ARTCL'),
            'ARTCL_CODE' => $this->request->getPost('ARTCL_CODE'),
            'GNDR_SE'    => $this->request->getPost('GNDR_SE'),
            'CKUP_SE'    => $this->request->getPost('CKUP_SE'),
            'CKUP_CST'   => $this->request->getPost('CKUP_CST'),
            'AGREE_SUBMIT_YN' => $this->request->getPost('AGREE_SUBMIT_YN') ?? 'N',
            'RMRK'       => $this->request->getPost('RMRK'),
            'HSPTL_SN'   => $this->request->getPost('HSPTL_SN'), // Add this line
        ];

        $insertedId = $this->model->insert($data);

        if ($insertedId) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => '선택항목이 성공적으로 등록되었습니다.',
                'item_id'   => $insertedId,
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'error',
            'message'   => '데이터베이스 저장 중 오류가 발생했습니다.',
            'errors'    => $this->model->errors(),
            'csrf_hash' => csrf_hash()
        ]);
    }
    
    /**
     * 선택항목 정보 수정
     */
    public function ajax_update()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }

        $id = $this->request->getPost('CHC_ARTCL_SN');
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => '선택항목 ID(SN)가 필요합니다.', 
                'csrf_hash' => csrf_hash()
            ]);
        }

        // 기존 데이터 확인
        $item = $this->model->where('DEL_YN', 'N')->find($id);
        if (!$item) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => '수정할 선택항목을 찾을 수 없습니다.', 
                'csrf_hash' => csrf_hash()
            ]);
        }

        // 유효성 검사
        $validation = \Config\Services::validation();
        $rules = $this->model->buildValidationRules(true, $id);
        $validation->setRules($rules, $this->model->validationMessages);
        
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status'    => 'fail',
                'errors'    => $validation->getErrors(),
                'message'   => '입력값을 확인해주세요.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // 데이터 업데이트
        $data = [
            'CKUP_ARTCL' => $this->request->getPost('CKUP_ARTCL'),
            'ARTCL_CODE' => $this->request->getPost('ARTCL_CODE'),
            'GNDR_SE'    => $this->request->getPost('GNDR_SE'),
            'CKUP_SE'   => $this->request->getPost('CKUP_SE'),
            'CKUP_CST'   => $this->request->getPost('CKUP_CST'),
            'AGREE_SUBMIT_YN' => $this->request->getPost('AGREE_SUBMIT_YN') ?? 'N',
            'RMRK'       => $this->request->getPost('RMRK'),
            'HSPTL_SN'   => $this->request->getPost('HSPTL_SN'), // Add this line
        ];

        if ($this->model->update($id, $data)) {
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => '선택항목 정보가 성공적으로 수정되었습니다.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'error',
            'message'   => '데이터베이스 업데이트 중 오류가 발생했습니다.',
            'errors'    => $this->model->errors(),
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * 선택항목 삭제 (소프트 삭제)
     */
    public function ajax_delete($id = null)
    {
        if (!$this->request->isAJAX() || !in_array($this->request->getMethod(), ['POST', 'DELETE'])) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
        }

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => '삭제할 선택항목 ID가 필요합니다.', 
                'csrf_hash' => csrf_hash()
            ]);
        }

        // 기존 데이터 확인
        $item = $this->model->where('DEL_YN', 'N')->find($id);
        if (!$item) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => '삭제할 선택항목 정보를 찾을 수 없거나 이미 삭제된 항목입니다.', 
                'csrf_hash' => csrf_hash()
            ]);
        }

        if ($this->model->softDeleteChcArtcl($id)) {
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => '선택항목 정보가 성공적으로 삭제되었습니다.', 
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error', 
            'message' => '선택항목 정보 삭제 중 오류가 발생했습니다.', 
            'errors' => $this->model->errors(), 
            'csrf_hash' => csrf_hash()
        ]);
    }
}