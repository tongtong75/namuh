<?php

namespace App\Controllers; // Assuming Mngr namespace

use App\Controllers\BaseController; // Make sure BaseController is correctly namespaced
use App\Models\CoMngModel;
use App\Models\HsptlMngModel;      
use App\Models\CoHsptlLnkngModel; 
use CodeIgniter\Exceptions\PageNotFoundException;

class CoMngController extends BaseController
{
    protected CoMngModel $model;
    protected $helpers = ['form', 'url', 'text', 'date'];

    public function __construct()
    {
        $this->model = new CoMngModel();
    }

    public function index()
    {
        $data['title'] = '회사 목록'; // Page title for the view
        return view('mngr/co_mng/index', $data);
    }

    public function ajax_list()
    {
        if ($this->request->isAJAX()) {
            $searchKeyword = $this->request->getPost('search_keyword');

            $builder = $this->model->where('DEL_YN', 'N');

            if (!empty($searchKeyword)) {
                $builder->groupStart();
                $builder->like('CO_NM', $searchKeyword);
                $builder->orLike('PIC_NM', $searchKeyword);
                $builder->orLike('CNPL', $searchKeyword);
                $builder->groupEnd();
            }

            $list = $builder->orderBy($this->model->primaryKey, 'DESC')->findAll();

            $data = [];
            $no = 1;

            foreach ($list as $row) {
                $data[] = [
                    'no'        => $no++,
                    'CO_SN'     => esc($row['CO_SN']),
                    'CO_NM'     => esc($row['CO_NM']),
                    'CO_MNGR_ID'=> esc($row['CO_MNGR_ID']),
                    'PIC_NM'    => esc($row['PIC_NM']),
                    'CNPL'      => esc($row['CNPL']),
                    'BGNG_YMD'  => $row['BGNG_YMD'] ? date('Y-m-d', strtotime($row['BGNG_YMD'])) : '',
                    'END_YMD'   => $row['END_YMD'] ? date('Y-m-d', strtotime($row['END_YMD'])) : '',
                    'REG_YMD'   => $row['REG_YMD'] ? date('Y-m-d', strtotime($row['REG_YMD'])) : '',
                    'action'    => view('mngr/co_mng/action_buttons', ['item' => $row], ['saveData' => false])
                ];
            }

            return $this->response->setJSON([
                'data'      => $data,
                'csrf_hash' => csrf_hash()
            ]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.']);
    }

    public function ajax_get_co($id = null)
    {
        if ($this->request->isAJAX()) {
            $item = $this->model->where('DEL_YN', 'N')->find($id);
            if ($item) {
                // Format dates for display in form if needed
                if (!empty($item['BGNG_YMD'])) $item['BGNG_YMD'] = date('Y-m-d', strtotime($item['BGNG_YMD']));
                if (!empty($item['END_YMD'])) $item['END_YMD'] = date('Y-m-d', strtotime($item['END_YMD']));
                
                // Remove password from response
                unset($item['CO_MNGR_PSWD']);

                return $this->response->setJSON(['status' => 'success', 'data' => $item, 'csrf_hash' => csrf_hash()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => '회사 정보를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_create()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
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

            $data = [
                'CO_NM'     => $this->request->getPost('CO_NM'),
                'PIC_NM'    => $this->request->getPost('PIC_NM'),
                'CNPL'      => $this->request->getPost('CNPL'),
                'BGNG_YMD'  => $this->request->getPost('BGNG_YMD') ?: null, // Store null if empty
                'END_YMD'   => $this->request->getPost('END_YMD') ?: null,   // Store null if empty
                'CO_MNGR_ID' => $this->request->getPost('CO_MNGR_ID'),
            ];

            $password = $this->request->getPost('CO_MNGR_PSWD');
            if (!empty($password)) {
                $data['CO_MNGR_PSWD'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $insertedId = $this->model->insert($data);

            if ($insertedId) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '회사가 성공적으로 등록되었습니다.',
                    'item_id'   => $insertedId,
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 저장 중 오류가 발생했습니다.',
                    'errors'    => $this->model->errors(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
    
    public function ajax_update()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $id = $this->request->getPost('CO_SN'); 
            if (!$id) {
                 return $this->response->setJSON(['status' => 'error', 'message' => '회사 ID(SN)가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $item = $this->model->where('DEL_YN', 'N')->find($id);
            if (!$item) {
                return $this->response->setJSON(['status' => 'error', 'message' => '수정할 회사 정보를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $validation =  \Config\Services::validation();
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
                'CO_NM'     => $this->request->getPost('CO_NM'),
                'PIC_NM'    => $this->request->getPost('PIC_NM'),
                'CNPL'      => $this->request->getPost('CNPL'),
                'BGNG_YMD'  => $this->request->getPost('BGNG_YMD') ?: null,
                'END_YMD'   => $this->request->getPost('END_YMD') ?: null,
                'CO_MNGR_ID' => $this->request->getPost('CO_MNGR_ID'),
            ];

            $password = $this->request->getPost('CO_MNGR_PSWD');
            if (!empty($password)) {
                $data['CO_MNGR_PSWD'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($this->model->update($id, $data)) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '회사 정보가 성공적으로 수정되었습니다.',
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
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 회사 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $item = $this->model->where('DEL_YN', 'N')->find($id);
            if (!$item) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 회사 정보를 찾을 수 없거나 이미 삭제된 항목입니다.', 'csrf_hash' => csrf_hash()]);
            }

            if ($this->model->softDeleteCo($id)) { 
                return $this->response->setJSON(['status' => 'success', 'message' => '회사 정보가 성공적으로 삭제되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '회사 정보 삭제 중 오류가 발생했습니다.', 'errors' => $this->model->errors(), 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_get_hsptls_for_linking($coSn = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $hsptlModel = new HsptlMngModel();
        $lnkModel = new CoHsptlLnkngModel();

        $allHsptls = $hsptlModel->getAllActiveHsptls();
        $linkedHsptlSns = [];

        if ($coSn) {
            $linkedHsptlSns = $lnkModel->getLinkedHsptlSnsByCoSn($coSn);
            if ($linkedHsptlSns === null) { // Ensure it's an array even if no links found
                $linkedHsptlSns = [];
            }
        }

        $data = [];
        $no = 1;
        foreach ($allHsptls as $hsptl) {
            $hsptlData = [
                'no' => $no++,
                'HSPTL_SN' => $hsptl['HSPTL_SN'],
                'HSPTL_NM' => esc($hsptl['HSPTL_NM']),
                'PIC_NM' => esc($hsptl['PIC_NM']),
                'CNPL1' => esc($hsptl['CNPL1']),
                'is_linked' => in_array($hsptl['HSPTL_SN'], $linkedHsptlSns)
            ];
            $data[] = $hsptlData;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }

    public function ajax_save_hsptl_links()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $coSn = $this->request->getPost('CO_SN');
        $selectedHsptlSns = $this->request->getPost('selected_hsptls'); // This will be an array of HSPTL_SNs
        $regId = session()->get('user_id'); // Assuming you store logged-in manager's ID in session

        if (empty($coSn) || !is_numeric($coSn)) {
            return $this->response->setJSON([
                'status' => 'fail',
                'message' => '유효하지 않은 회사 정보입니다.',
                'csrf_hash' => csrf_hash()
            ]);
        }
        if (empty($regId)) {
             return $this->response->setJSON([
                'status' => 'fail',
                'message' => '로그인 정보가 필요합니다. 다시 로그인해주세요.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $lnkModel = new CoHsptlLnkngModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $lnkModel->deleteLinksByCoSn($coSn);

        if (!empty($selectedHsptlSns) && is_array($selectedHsptlSns)) {
            foreach ($selectedHsptlSns as $hsptlSn) {
                if (!empty($hsptlSn) && is_numeric($hsptlSn)) {
                    $lnkModel->addLink([
                        'CO_SN' => $coSn,
                        'HSPTL_SN' => $hsptlSn,
                        'REG_ID' => $regId,
                        // REG_YMD has a default in the DB schema (curdate())
                    ]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '병원 연결 정보 저장 중 오류가 발생했습니다.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '병원 연결 정보가 성공적으로 저장되었습니다.',
            'csrf_hash' => csrf_hash()
        ]);
    }
}