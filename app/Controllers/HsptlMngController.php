<?php

namespace App\Controllers;

use App\Models\HsptlMngModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class HsptlMngController extends BaseController
{
    protected HsptlMngModel $model;
    protected $helpers = ['form', 'url', 'text'];

    public function __construct()
    {
        $this->model = new HsptlMngModel();
    }

    public function index()
    {
        return view('mngr/hsptl_mng/index', ['title' => '병원 목록']);
    }

    public function user_index()
    {
        return view('user/hsptl/index', ['title' => '병원 목록']);
    }

    public function ajax_list()
    {
        if ($this->request->isAJAX()) {
            $searchKeyword = $this->request->getGet('search_keyword');

            $this->model->where('DEL_YN', 'N');

            if (!empty($searchKeyword)) {
                $this->model->groupStart();
                $this->model->like('HSPTL_NM', $searchKeyword);
                $this->model->orLike('RGN', $searchKeyword);
                $this->model->orLike('PIC_NM', $searchKeyword);
                $this->model->groupEnd();
            }

            $hsptls = $this->model->orderBy('HSPTL_SN', 'DESC')->findAll();

            $data = [];
            foreach ($hsptls as $key => $hsptl) {
                $data[] = [
                    $key + 1,
                    esc($hsptl['RGN']),
                    esc($hsptl['HSPTL_NM']),
                    esc($hsptl['PIC_NM']),
                    esc($hsptl['CNPL1']),
                    esc(date('Y-m-d', strtotime($hsptl['REG_YMD']))),
                    view('mngr/hsptl_mng/action_buttons', ['hsptl' => $hsptl])
                ];
            }

            return $this->response->setJSON([
                'data' => $data,
                'csrf_hash' => csrf_hash()
            ]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.']);
    }
    public function ajax_get_hsptl($id = null)
    {
        if ($this->request->isAJAX()) {
            $hsptl = $this->model->where('DEL_YN', 'N')->find($id);
            if ($hsptl) {
                return $this->response->setJSON(['status' => 'success', 'data' => $hsptl, 'csrf_hash' => csrf_hash()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => '병원을 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_create()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $validation = \Config\Services::validation();
            // 모델의 buildValidationRules 사용 (등록 모드)
            $rules = $this->model->buildValidationRules(false); 
            // 모델에 정의된 validationMessages 사용
            $validation->setRules($rules, $this->model->validationMessages);


            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status'    => 'fail',
                    'errors'    => $validation->getErrors(),
                    'message'   => '입력값을 확인해주세요.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $data = [
                'RGN' => $this->request->getPost('RGN'),
                'HSPTL_NM' => $this->request->getPost('HSPTL_NM'),
                'PIC_NM'   => $this->request->getPost('PIC_NM'),
                'CNPL1'    => $this->request->getPost('CNPL1'),
            ];

            $insertedId = $this->model->insert($data);

            if ($insertedId) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '병원이 성공적으로 등록되었습니다.',
                    'hsptl_id'  => $insertedId,
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 저장 중 오류가 발생했습니다.',
                    'errors'    => $this->model->errors(), // 모델 자체의 DB 오류 등
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
    
    public function ajax_update()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $id = $this->request->getPost('HSPTL_SN'); // 뷰의 hidden input 이름 확인
            if (!$id) {
                 return $this->response->setJSON(['status' => 'error', 'message' => '병원 ID(SN)가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $hsptl = $this->model->where('DEL_YN', 'N')->find($id);
            if (!$hsptl) {
                return $this->response->setJSON(['status' => 'error', 'message' => '수정할 병원을 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $validation =  \Config\Services::validation();
            // 모델의 buildValidationRules 사용 (수정 모드, 현재 ID 전달)
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

            $data = [
                'RGN' => $this->request->getPost('RGN'),
                'HSPTL_NM' => $this->request->getPost('HSPTL_NM'),
                'PIC_NM'   => $this->request->getPost('PIC_NM'),
                'CNPL1'    => $this->request->getPost('CNPL1'),
                'CNPL2'    => $this->request->getPost('CNPL2'),
                // MDFCN_ID, MDFCN_YMD는 모델 콜백 및 타임스탬프 기능으로 처리
            ];

            if ($this->model->update($id, $data)) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '병원 정보가 성공적으로 수정되었습니다.',
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 업데이트 중 오류가 발생했습니다.',
                    'errors'    => $this->model->errors(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return  $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_delete($id = null)
    {
        if ($this->request->isAJAX() && ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'delete')) {
            if (!$id) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 병원 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $hsptl = $this->model->where('DEL_YN', 'N')->find($id);
            if (!$hsptl) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 병원 정보를 찾을 수 없거나 이미 삭제된 병원입니다.', 'csrf_hash' => csrf_hash()]);
            }

            if ($this->model->softDeleteHsptl($id)) { 
                return $this->response->setJSON(['status' => 'success', 'message' => '병원 정보가 성공적으로 삭제되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '병원 정보 삭제 중 오류가 발생했습니다.', 'errors' => $this->model->errors(), 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
}