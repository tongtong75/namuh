<?php

namespace App\Controllers;

use App\Models\MngrMngModel;
use App\Models\HsptlMngModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MngrMngController extends BaseController
{
    protected MngrMngModel $mngrModel;
    protected HsptlMngModel $hsptlModel;
    protected $helpers = ['form', 'url', 'text'];

    public function __construct()
    {
        $this->mngrModel = new MngrMngModel();
        $this->hsptlModel = new HsptlMngModel(); // 병원 목록 가져오기 위해
    }

    public function index()
    {
        $data['managers'] = $this->mngrModel->getManagersWithHospitalDetails();
        $data['hospitals'] = $this->hsptlModel->where('DEL_YN', 'N')
                                             ->orderBy('HSPTL_NM', 'ASC')
                                             ->findAll();
        $data['title'] = '관리자 목록';
        return view('mngr/mngr_mng/index', $data); // View 파일명은 실제 파일명에 맞게
        
    }

    public function ajax_list()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => '잘못된 요청입니다.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $searchKeyword = $this->request->getPost('search_keyword');

        $builder = $this->mngrModel
            ->select('MNGR_MNG.*, HSPTL_MNG.HSPTL_NM')
            ->join('HSPTL_MNG', 'HSPTL_MNG.HSPTL_SN = MNGR_MNG.HSPTL_SN');

        if (!empty($searchKeyword)) {
            $builder->groupStart();
            $builder->like('HSPTL_MNG.HSPTL_NM', $searchKeyword);
            $builder->orLike('MNGR_MNG.MNGR_NM', $searchKeyword);
            $builder->orLike('MNGR_MNG.MNGR_ID', $searchKeyword);
            $builder->groupEnd();
        }

        $mngrs = $builder->orderBy('MNGR_MNG.MNGR_SN', 'ASC')->findAll();

        $data = [];
        $index = 1;
        foreach ($mngrs as $row) {
            $data[] = [
                $index++,
                esc($row['HSPTL_NM']),
                esc($row['MNGR_NM']),
                esc($row['MNGR_ID']),
                view('mngr/mngr_mng/action_buttons', ['mngr' => $row])
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }

    public function ajax_get_mngr($id = null)
    {
        if ($this->request->isAJAX()) {
            $manager = $this->mngrModel->getManagerWithHospitalDetail($id);
            if ($manager) {
                unset($manager['MNGR_PSWD']); // 비밀번호는 응답에서 제외
                return $this->response->setJSON(['status' => 'success', 'data' => $manager, 'csrf_hash' => csrf_hash()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => '관리자를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_create()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $validation = \Config\Services::validation();
            // 모델의 buildValidationRules 사용 (등록 모드)
            $rules = $this->mngrModel->buildValidationRules(false);
            $validation->setRules($rules, $this->mngrModel->validationMessages);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status'    => 'fail',
                    'errors'    => $validation->getErrors(),
                    'message'   => '입력값을 확인해주세요.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $data = [
                'HSPTL_SN'  => $this->request->getPost('HSPTL_SN'),
                'MNGR_NM'   => $this->request->getPost('MNGR_NM'),
                'MNGR_ID'   => $this->request->getPost('MNGR_ID'),
                'MNGR_PSWD' => $this->request->getPost('MNGR_PSWD'), // 모델 콜백에서 해싱
            ];

            $insertedId = $this->mngrModel->insert($data);

            if ($insertedId) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '관리자가 성공적으로 등록되었습니다.',
                    'mngr_id'   => $insertedId,
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 저장 중 오류가 발생했습니다.',
                    'errors'    => $this->mngrModel->errors(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
    
    public function ajax_update()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $id = $this->request->getPost('MNGR_SN_modal');
            if (!$id) {
                 return $this->response->setJSON(['status' => 'error', 'message' => '관리자 ID(SN)가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $manager = $this->mngrModel->find($id);
            if (!$manager) {
                return $this->response->setJSON(['status' => 'error', 'message' => '수정할 관리자를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $validation =  \Config\Services::validation();
            // 모델의 buildValidationRules 사용 (수정 모드, 현재 ID 전달)
            $rules = $this->mngrModel->buildValidationRules(true, $id);
            $validation->setRules($rules, $this->mngrModel->validationMessages);
            
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status'    => 'fail',
                    'errors'    => $validation->getErrors(),
                    'message'   => '입력값을 확인해주세요.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $data = [
                'HSPTL_SN'  => $this->request->getPost('HSPTL_SN'),
                'MNGR_NM'   => $this->request->getPost('MNGR_NM'),
                'MNGR_ID'   => $this->request->getPost('MNGR_ID'),
            ];

            $password = $this->request->getPost('MNGR_PSWD');
            if (!empty($password)) {
                $data['MNGR_PSWD'] = $password; // 모델 콜백에서 해싱
            }
            // 비밀번호가 비어있으면 모델 콜백(hashPasswordIfNeeded)에서 unset 처리되어 기존 비밀번호 유지

            if ($this->mngrModel->update($id, $data)) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '관리자 정보가 성공적으로 수정되었습니다.',
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 업데이트 중 오류가 발생했습니다.',
                    'errors'    => $this->mngrModel->errors(),
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
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 관리자 ID(SN)가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $manager = $this->mngrModel->find($id);
            if (!$manager) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 관리자를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            if ($this->mngrModel->delete($id)) { // MngrMngModel은 물리적 삭제 사용
                return $this->response->setJSON(['status' => 'success', 'message' => '관리자 정보가 성공적으로 삭제되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '관리자 정보 삭제 중 오류가 발생했습니다.', 'errors' => $this->mngrModel->errors(), 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
}