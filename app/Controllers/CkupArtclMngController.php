<?php

namespace App\Controllers;

use App\Models\HsptlMngModel; // Add this line
use App\Models\CkupArtclMngModel;
use CodeIgniter\HTTP\ResponseInterface;

class CkupArtclMngController extends BaseController
{
    protected CkupArtclMngModel $model;
    protected $helpers = ['form', 'url', 'text', 'date'];

    protected HsptlMngModel $hsptlMngModel; // Add this line

    public function __construct()
    {
        $this->model = new CkupArtclMngModel();
        $this->hsptlMngModel = new HsptlMngModel(); // Add this line
    }

    public function index()
    {
        $data['title'] = '검진항목 목록';
        if (session()->get('user_type') === 'H') {
            $data['hospitals'] = $this->hsptlMngModel->where('HSPTL_SN', session()->get('hsptl_sn'))->findAll();
        } else {
            $data['hospitals'] = $this->hsptlMngModel->getActiveHospitals();
        }
        return view('mngr/ckup_artcl_mng/index', $data);
    }

    public function ajax_list()
    {
        if (!$this->request->isAJAX()) {
            return $this->ajaxErrorResponse('잘못된 접근입니다.', 403);
        }

        $hsptlSn = $this->request->getPost('hsptl_sn');
        
        if (session()->get('user_type') === 'H') {
            $hsptlSn = session()->get('hsptl_sn');
        }

        $searchKeyword = $this->request->getPost('search_keyword');
        $list = $this->model->getActiveItems($hsptlSn, $searchKeyword);
        $data = $this->formatListData($list);

        return $this->ajaxSuccessResponse(['data' => $data]);
    }

    public function ajax_get_ckup_artcl($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->ajaxErrorResponse('잘못된 접근입니다.', 403);
        }

        $item = $this->model->where('DEL_YN', 'N')->find($id);
        
        if (!$item) {
            return $this->ajaxErrorResponse('검진항목을 찾을 수 없습니다.');
        }

        return $this->ajaxSuccessResponse(['data' => $item]);
    }

    public function ajax_create()
    {
        if (!$this->isValidAjaxPost()) {
            return $this->ajaxErrorResponse('잘못된 요청입니다.', 403);
        }

        // 유효성 검사
        $this->model->setUniqueValidation(false);
        $validationResult = $this->validateRequest();
        
        if (!$validationResult['isValid']) {
            return $this->ajaxValidationErrorResponse($validationResult['errors']);
        }

        // 데이터 생성
        $data = $this->getPostData();
        $insertedId = $this->model->insert($data);

        if ($insertedId) {
            return $this->ajaxSuccessResponse([
                'message' => '검진항목이 성공적으로 등록되었습니다.',
                'item_id' => $insertedId
            ]);
        }

        return $this->ajaxErrorResponse(
            '데이터베이스 저장 중 오류가 발생했습니다.',
            500,
            $this->model->errors()
        );
    }

    public function ajax_update()
    {
        if (!$this->isValidAjaxPost()) {
            return $this->ajaxErrorResponse('잘못된 요청입니다.', 403);
        }

        $id = $this->request->getPost('CKUP_ARTCL_SN');
        if (!$id) {
            return $this->ajaxErrorResponse('검진항목 ID(SN)가 필요합니다.');
        }

        // 기존 데이터 확인
        $item = $this->model->where('DEL_YN', 'N')->find($id);
        if (!$item) {
            return $this->ajaxErrorResponse('수정할 검진항목을 찾을 수 없습니다.');
        }

        // 유효성 검사
        $this->model->setUniqueValidation(true, $id);
        $validationResult = $this->validateRequest();
        
        if (!$validationResult['isValid']) {
            return $this->ajaxValidationErrorResponse($validationResult['errors']);
        }

        // 데이터 업데이트
        $data = $this->getPostData();
        
        if ($this->model->update($id, $data)) {
            return $this->ajaxSuccessResponse([
                'message' => '검진항목 정보가 성공적으로 수정되었습니다.'
            ]);
        }

        return $this->ajaxErrorResponse(
            '데이터베이스 업데이트 중 오류가 발생했습니다.',
            500,
            $this->model->errors()
        );
    }

    public function ajax_delete($id = null)
    {
        if (!$this->isValidAjaxRequest(['POST', 'DELETE'])) {
            return $this->ajaxErrorResponse('잘못된 요청입니다.', 403);
        }

        if (!$id) {
            return $this->ajaxErrorResponse('삭제할 검진항목 ID가 필요합니다.');
        }

        $item = $this->model->where('DEL_YN', 'N')->find($id);
        if (!$item) {
            return $this->ajaxErrorResponse('삭제할 검진항목 정보를 찾을 수 없거나 이미 삭제된 항목입니다.');
        }

        if ($this->model->softDeleteCkupArtcl($id)) {
            return $this->ajaxSuccessResponse([
                'message' => '검진항목 정보가 성공적으로 삭제되었습니다.'
            ]);
        }

        return $this->ajaxErrorResponse(
            '검진항목 정보 삭제 중 오류가 발생했습니다.',
            500,
            $this->model->errors()
        );
    }

    // === Helper Methods ===

    /**
     * AJAX POST 요청 검증
     */
    private function isValidAjaxPost(): bool
    {
        return $this->request->isAJAX() && $this->request->getMethod() === 'POST';
    }

    /**
     * AJAX 요청 및 메소드 검증
     */
    private function isValidAjaxRequest(array $allowedMethods): bool
    {
        return $this->request->isAJAX() && 
               in_array($this->request->getMethod(), $allowedMethods);
    }

    /**
     * 목록 데이터 포맷팅
     */
    private function formatListData(array $list): array
    {
        $data = [];
        $no = 1;

        foreach ($list as $row) {
            $data[] = [
                'no'         => $no++,
                'CKUP_SE'    => esc($row['CKUP_SE']),
                'CKUP_ARTCL_SN'    => esc($row['CKUP_ARTCL_SN']),
                'CKUP_ARTCL' => esc($row['CKUP_ARTCL']),
                'ARTCL_CODE' => esc($row['ARTCL_CODE']),
                'HSPTL_NM'   => esc($row['HSPTL_NM']),
                'GNDR_SE'    => esc($row['GNDR_SE']),
                'DSS'        => esc($row['DSS']),
                'CKUP_TYPE'  => esc($row['CKUP_TYPE']), // Add this line
                'CKUP_CST'   => esc($row['CKUP_CST']),  // Add this line
                'RMRK'       => esc($row['RMRK']),      // Add this line
                'REG_YMD'    => date('Y-m-d', strtotime($row['REG_YMD'])),
                'action'     => view('mngr/ckup_artcl_mng/action_buttons', 
                                   ['item' => $row], 
                                   ['saveData' => false])
            ];
        }

        return $data;
    }

    /**
     * POST 데이터 가져오기
     */
    private function getPostData(): array
    {
        return [
            'CKUP_SE'    => $this->request->getPost('CKUP_SE'),
            'CKUP_ARTCL' => $this->request->getPost('CKUP_ARTCL'),
            'ARTCL_CODE' => $this->request->getPost('ARTCL_CODE'),
            'HSPTL_SN'   => $this->request->getPost('HSPTL_SN'),
            'GNDR_SE'    => $this->request->getPost('GNDR_SE'),
            'DSS'        => $this->request->getPost('DSS'),
            'CKUP_TYPE'  => $this->request->getPost('CKUP_TYPE'), // Add this line
            'CKUP_CST'   => $this->request->getPost('CKUP_CST'),  // Add this line
            'RMRK'       => $this->request->getPost('RMRK'),      // Add this line
        ];
    }

    /**
     * 유효성 검사 실행
     */
    private function validateRequest(): array
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->model->validationRules, $this->model->validationMessages);

        $isValid = $validation->withRequest($this->request)->run();

        return [
            'isValid' => $isValid,
            'errors'  => $isValid ? [] : $validation->getErrors()
        ];
    }

    /**
     * AJAX 성공 응답
     */
    private function ajaxSuccessResponse(array $data = []): ResponseInterface
    {
        $response = array_merge([
            'status'    => 'success',
            'csrf_hash' => csrf_hash()
        ], $data);

        return $this->response->setJSON($response);
    }

    /**
     * AJAX 에러 응답
     */
    private function ajaxErrorResponse(string $message, int $statusCode = 400, array $errors = []): ResponseInterface
    {
        $response = [
            'status'    => 'error',
            'message'   => $message,
            'csrf_hash' => csrf_hash()
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }

    /**
     * AJAX 유효성 검사 에러 응답
     */
    private function ajaxValidationErrorResponse(array $errors): ResponseInterface
    {
        return $this->response->setJSON([
            'status'    => 'fail',
            'errors'    => $errors,
            'message'   => '입력값을 확인해주세요.',
            'csrf_hash' => csrf_hash()
        ]);
    }
}