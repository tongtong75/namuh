<?php

namespace App\Controllers; 

use App\Models\CkupTrgtModel;
use App\Models\CkupTrgtMemoModel;
use App\Models\CoMngModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database; 
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class UserCkupTrgtController extends BaseController
{
    protected CkupTrgtModel $ckupTrgtModel;
    protected CkupTrgtMemoModel $ckupTrgtMemoModel;
    protected CoMngModel $coMngModel;
    protected $db; // ✅ DB 연결 객체
    protected $helpers = ['form', 'url', 'text', 'date', 'common'];

    public function __construct()
    {
        $this->ckupTrgtModel = new CkupTrgtModel();
        $this->ckupTrgtMemoModel = new CkupTrgtMemoModel();
        $this->coMngModel = new CoMngModel();
        $this->db = Database::connect(); // ✅ DB 연결 초기화
    }

    public function index()
    {
        $data['title'] = '검진대상자 관리';
        
        $userType = session()->get('user_type');
        $coSn = session()->get('co_sn');

        if ($userType === 'M' && $coSn) {
            $data['companies'] = $this->coMngModel->where('CO_SN', $coSn)->where('DEL_YN', 'N')->findAll();
        } else {
            // Fallback or error if not manager (though route should protect)
             $data['companies'] = []; 
        }

        $currentYear = date('Y');
        $data['years'] = range($currentYear + 1, $currentYear - 1);
        $data['userCoSn'] = $coSn; // Pass user's company SN to view
        return view('user/ckup_trgt/index', $data);
    }

    public function ajax_list()
    {
        if ($this->request->isAJAX()) {
            $requestData = $this->request->getPost();
            $extraWhere = [];
            
            // Force company filter for Manager
            $coSn = session()->get('co_sn');
            if ($coSn) {
                $extraWhere['CT.CO_SN'] = $coSn;
            }

            if (!empty($requestData['ckup_yyyy_filter'])) {
                $extraWhere['CT.CKUP_YYYY'] = $requestData['ckup_yyyy_filter'];
            }
             if (!empty($requestData['relation_filter'])) {
                $extraWhere['CT.RELATION'] = $requestData['relation_filter'];
            }
            if (!empty($requestData['name_filter'])) { // 성명 검색 필터 추가
                $extraWhere['CT.NAME LIKE'] = "%".$this->db->escapeLikeString(trim($requestData['name_filter']))."%";
            }
            if (!empty($requestData['ckup_name_filter'])) { // 수검자명 검색 필터 추가
                $extraWhere['CT.CKUP_NAME LIKE'] = "%".$this->db->escapeLikeString(trim($requestData['ckup_name_filter']))."%";
            }

            $result = $this->ckupTrgtModel->getDataTableList($requestData, $extraWhere);
            $dbData = $result['data'];
            $data = [];
            $no = intval($requestData['start'] ?? 0) + 1;

            foreach ($dbData as $row) {
                $memoData = $this->ckupTrgtMemoModel->getMemoByTargetSn($row['CKUP_TRGT_SN']);
                $memoContent = $memoData ? esc($memoData['MEMO'], 'html') : '';
                $memoSn = $memoData ? $memoData['CKUP_TRGT_MEMO_SN'] : null;

                $memoStatus = '';
                if (!empty($memoData)) {
                    $memoStatus = '<span data-bs-toggle="popover" data-bs-trigger="hover focus" title="메모" data-bs-content="' . $memoContent . '">있음</span>';
                } else {
                    $memoStatus = '없음';
                }

                $formattedBirthday = '-';
                if (!empty($row['BIRTHDAY']) && strlen($row['BIRTHDAY']) === 6) {
                    $y = substr($row['BIRTHDAY'], 0, 2);
                    $yearPrefix = ($y < (date('y') + 10)) ? '20' : '19';
                    $formattedBirthday = $yearPrefix . $y . '-' . substr($row['BIRTHDAY'], 2, 2) . '-' . substr($row['BIRTHDAY'], 4, 2);
                }
                $relationText = '본인';
                if (!empty($row['RELATION'])) {
                    if($row['RELATION'] === 'S'){
                        $relationText = '본인';
                    }
                    else if($row['RELATION'] === 'W'){
                        $relationText = '배우자';
                    }
                    else if($row['RELATION'] === 'C'){
                        $relationText = '자녀';
                    }
                    else if($row['RELATION'] === 'P'){
                        $relationText = '부모';
                    }
                    else if($row['RELATION'] === 'O'){
                        $relationText = '기타';
                    }
                }

                $ckupNameText = '';
                if (empty($row['CKUP_NAME'])) {
                    $ckupNameText = esc($row['NAME']);
                }else{
                    $ckupNameText = esc($row['CKUP_NAME']);
                }

                $data[] = [
                    'no'           => $no++,
                    'CO_NM'        => esc($row['CO_NM'] ?? '-'),
                    'CKUP_YYYY'    => esc($row['CKUP_YYYY']),
                    'NAME'         => esc($row['NAME']),
                    'CKUP_NAME'    => $ckupNameText,
                    'SUPPORT_FUND' => esc(($row['SUPPORT_FUND'] ?? null) ? rtrim($row['SUPPORT_FUND'], '원') . '원' : '0원'),
'FAMILY_SUPPORT_FUND' => esc(($row['FAMILY_SUPPORT_FUND'] ?? null) ? rtrim($row['FAMILY_SUPPORT_FUND'], '원') . '원' : '0원'),
                    'BUSINESS_NUM' => esc($row['BUSINESS_NUM'] ?? '-'),
                    'BIRTHDAY'     => esc($row['BIRTHDAY']),
                    'RELATION'     => $relationText,
                    'SEX'          => $row['SEX'] === 'M' ? '남' : ($row['SEX'] === 'F' ? '여' : '-'),
                    'HANDPHONE'    => esc($row['HANDPHONE'] ?? '-'),
                    'HANDPHONE'    => esc($row['HANDPHONE'] ?? '-'),
                    'CKUP_YN'      => $row['CKUP_YN'] === 'Y' ? '<span class="badge bg-success">완료</span>' : '<span class="badge bg-warning text-dark">미수검</span>',
                    'HSPTL_NM'     => esc($row['HSPTL_NM'] ?? '-'),
                    'GDS_NM'       => esc($row['GDS_NM'] ?? '-'),
                    'CKUP_RSVN_YMD'=> !empty($row['CKUP_RSVN_YMD']) ? date('Y-m-d', strtotime($row['CKUP_RSVN_YMD'])) : '-',
                    'RSVT_STTS'    => $row['RSVT_STTS'] === 'C' ? '<span class="badge bg-danger fs-6">예약확정</span>' : ($row['RSVT_STTS'] === 'Y' ? '<span class="badge bg-success fs-6">예약완료</span>' : '미예약'),
                    'rsvn_button'  => ($row['RSVT_STTS'] === 'C' || $row['RSVT_STTS'] === 'Y') 
                                      ? '<button class="btn btn-secondary btn-sm" onclick="alert(\'준비중입니다.\')">예약변경</button>' 
                                      : '<a href="/user/rsvnSel?ckup_trgt_sn=' . $row['CKUP_TRGT_SN'] . '" class="btn btn-primary btn-sm">예약</a>',
                    'memo_status'  => $memoStatus,
                    'action'       => view('user/ckup_trgt/action_buttons', [
                                            'item' => $row,
                                            'has_memo' => !empty($memoData),
                                            'memo_sn' => $memoSn
                                        ], ['saveData' => false])
                ];
            }

            return $this->response->setJSON([
                'draw'            => intval($requestData['draw'] ?? 0),
                'recordsTotal'    => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data'            => $data,
                'csrf_hash'       => csrf_hash()
            ]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function ajax_get_ckup_trgt($id = null)
    {
        if ($this->request->isAJAX()) {
            $coSn = session()->get('co_sn');
            $item = $this->ckupTrgtModel->where('DEL_YN', 'N')->where('CO_SN', $coSn)->find($id);
            if ($item) {
                // PSWD 필드는 반환하지 않거나, 필요한 경우에만 제한적으로 사용
                unset($item['PSWD']);
                return $this->response->setJSON(['status' => 'success', 'data' => $item, 'csrf_hash' => csrf_hash()]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => '대상자 정보를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 접근입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_create()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $validation = \Config\Services::validation();
            $rules = $this->ckupTrgtModel->buildValidationRules(false);
            $validation->setRules($rules, $this->ckupTrgtModel->validationMessages);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status'    => 'fail',
                    'errors'    => $validation->getErrors(),
                    'message'   => '입력값을 확인해주세요.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $passwordInput = $this->request->getPost('PSWD');
            $birthdayInput = $this->request->getPost('BIRTHDAY');

            if (!empty($passwordInput)) {
                $passwordValue = password_hash($passwordInput, PASSWORD_DEFAULT);
            } else {
                $passwordValue = password_hash($birthdayInput, PASSWORD_DEFAULT);
            }

            $data = [
                'CO_SN'        => session()->get('co_sn'), // Force session CO_SN
                'CKUP_YYYY'    => $this->request->getPost('CKUP_YYYY'),
                'NAME'         => $this->request->getPost('NAME'),
                'CKUP_NAME'    => $this->request->getPost('CKUP_NAME'),
                'BUSINESS_NUM' => $this->request->getPost('BUSINESS_NUM'),
                'BIRTHDAY'     => $this->request->getPost('BIRTHDAY'), //YYMMDD
                'PSWD'         => $passwordValue,
                'SEX'          => $this->request->getPost('SEX'),
                'HANDPHONE'    => $this->request->getPost('HANDPHONE'),
                'SUPPORT_FUND' => $this->request->getPost('SUPPORT_FUND'),
                'FAMILY_SUPPORT_FUND' => $this->request->getPost('FAMILY_SUPPORT_FUND'),
                'EMAIL'        => $this->request->getPost('EMAIL'),
                'WORK_STATUS'  => $this->request->getPost('WORK_STATUS'),
                'ASSIGN_CODE'  => $this->request->getPost('ASSIGN_CODE'),
                'JOB'          => $this->request->getPost('JOB'),
                'RELATION'     => $this->request->getPost('RELATION'),
                'CHECKUP_TARGET_YN' => $this->request->getPost('CHECKUP_TARGET_YN') ?? 'N',
                'RSVT_STTS'           => $this->request->getPost('RSVT_STTS') ?? 'N',
                'CKUP_YN'           => $this->request->getPost('CKUP_YN') ?? 'N',
                // REG_ID, MDFCN_ID, DEL_YN, REG_YMD, MDFCN_YMD는 모델 콜백에서 처리
            ];

            $insertedId = $this->ckupTrgtModel->insert($data);

            if ($insertedId) {
                // 메모 동시 저장 (선택 사항)
                $memoContent = trim((string)$this->request->getPost('MEMO_modal_main'));
                if (!empty($memoContent)) {
                    $this->ckupTrgtMemoModel->saveMemo([
                        'CKUP_TRGT_SN' => $insertedId,
                        'MEMO'         => $memoContent
                    ]);
                }

                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '대상자 정보가 성공적으로 등록되었습니다.',
                    'item_id'   => $insertedId,
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 저장 중 오류가 발생했습니다.',
                    'errors'    => $this->ckupTrgtModel->errors(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_update()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $id = $this->request->getPost('CKUP_TRGT_SN');
            if (!$id) {
                 return $this->response->setJSON(['status' => 'error', 'message' => '대상자 ID(SN)가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $coSn = session()->get('co_sn');
            $item = $this->ckupTrgtModel->where('DEL_YN', 'N')->where('CO_SN', $coSn)->find($id);
            if (!$item) {
                return $this->response->setJSON(['status' => 'error', 'message' => '수정할 대상자 정보를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $validation =  \Config\Services::validation();
            $rules = $this->ckupTrgtModel->buildValidationRules(true, $id);
            $validation->setRules($rules, $this->ckupTrgtModel->validationMessages);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'status'    => 'fail',
                    'errors'    => $validation->getErrors(),
                    'message'   => '입력값을 확인해주세요.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            $data = [
                'CO_SN'        => session()->get('co_sn'), // Force session CO_SN
                'CKUP_YYYY'    => $this->request->getPost('CKUP_YYYY'),
                'NAME'         => $this->request->getPost('NAME'),
                'CKUP_NAME'    => $this->request->getPost('CKUP_NAME'),
                'BUSINESS_NUM' => $this->request->getPost('BUSINESS_NUM'),
                'BIRTHDAY'     => $this->request->getPost('BIRTHDAY'),
                'SEX'          => $this->request->getPost('SEX'),
                'HANDPHONE'    => $this->request->getPost('HANDPHONE'),
                'SUPPORT_FUND' => $this->request->getPost('SUPPORT_FUND'),
                'FAMILY_SUPPORT_FUND' => $this->request->getPost('FAMILY_SUPPORT_FUND'),
                'EMAIL'        => $this->request->getPost('EMAIL'),
                'WORK_STATUS'  => $this->request->getPost('WORK_STATUS'),
                'ASSIGN_CODE'  => $this->request->getPost('ASSIGN_CODE'),
                'JOB'          => $this->request->getPost('JOB'),
                'RELATION'     => $this->request->getPost('RELATION'),
                'CHECKUP_TARGET_YN' => $this->request->getPost('CHECKUP_TARGET_YN') ?? 'N',
                'RSVT_STTS'           => $this->request->getPost('RSVT_STTS') ?? 'N',
                'CKUP_YN'           => $this->request->getPost('CKUP_YN') ?? 'N',
            ];

            // 비밀번호 변경 시에만 업데이트
            $newPassword = $this->request->getPost('PSWD');
            if (!empty($newPassword)) {
                $data['PSWD'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if ($this->ckupTrgtModel->update($id, $data)) {
                 // 메모 동시 저장 (선택 사항)
                $memoContent = trim((string)$this->request->getPost('MEMO_modal_main'));
                // 수정 시에는 기존 메모가 있든 없든 saveMemo 호출 (내부에서 분기)
                $this->ckupTrgtMemoModel->saveMemo([
                    'CKUP_TRGT_SN' => $id,
                    'MEMO'         => $memoContent
                ]);


                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => '대상자 정보가 성공적으로 수정되었습니다.',
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status'    => 'error',
                    'message'   => '데이터베이스 업데이트 중 오류가 발생했습니다.',
                    'errors'    => $this->ckupTrgtModel->errors(),
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
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 대상자 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $coSn = session()->get('co_sn');
            $item = $this->ckupTrgtModel->where('DEL_YN', 'N')->where('CO_SN', $coSn)->find($id);
            if (!$item) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 대상자 정보를 찾을 수 없거나 이미 삭제된 항목입니다.', 'csrf_hash' => csrf_hash()]);
            }

            // 트랜잭션 시작 (대상자 정보 삭제와 메모 삭제를 원자적으로 처리)
            $this->db->transStart();

            $deletedTrgt = $this->ckupTrgtModel->softDeleteCkupTrgt($id);
            $deletedMemo = $this->ckupTrgtMemoModel->deleteMemoByTargetSn($id); // 대상자 삭제 시 관련 메모도 삭제

            if ($deletedTrgt && $this->db->transStatus() === TRUE) {
                $this->db->transCommit();
                return $this->response->setJSON(['status' => 'success', 'message' => '대상자 정보 및 관련 메모가 성공적으로 삭제되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                $this->db->transRollback();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => '대상자 정보 삭제 중 오류가 발생했습니다.',
                    'errors' => $this->ckupTrgtModel->errors() ?: $this->ckupTrgtMemoModel->errors(), // 모델 에러 병합
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }


    // --- 메모 관련 AJAX 메소드 ---
    public function ajax_get_memo($ckupTrgtSn = null)
    {
        if ($this->request->isAJAX() && $ckupTrgtSn) {
            $memo = $this->ckupTrgtMemoModel->getMemoByTargetSn($ckupTrgtSn);
            if ($memo) {
                return $this->response->setJSON(['status' => 'success', 'data' => $memo, 'csrf_hash' => csrf_hash()]);
            }
            return $this->response->setJSON(['status' => 'not_found', 'message' => '메모를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_save_memo()
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            $ckupTrgtSn = $this->request->getPost('CKUP_TRGT_SN_memo');
            $memoContent = trim((string)$this->request->getPost('MEMO_modal')); // textarea의 name

            if (!$ckupTrgtSn) {
                return $this->response->setJSON(['status' => 'error', 'message' => '대상자 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }
            // MEMO 필드 유효성 검사 (예: 최대 길이)
            // $validation = \Config\Services::validation();
            // $validation->setRules(['MEMO_modal' => 'permit_empty|max_length[2000]']);
            // if (!$validation->withRequest($this->request)->run()) { ... }


            $data = [
                'CKUP_TRGT_SN' => $ckupTrgtSn,
                'MEMO'         => $memoContent
            ];

            if ($this->ckupTrgtMemoModel->saveMemo($data)) {
                return $this->response->setJSON(['status' => 'success', 'message' => '메모가 성공적으로 저장되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '메모 저장 중 오류가 발생했습니다.', 'errors' => $this->ckupTrgtMemoModel->errors(), 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    public function ajax_delete_memo($memoSn = null)
    {
        if ($this->request->isAJAX() && ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'delete')) {
             if (!$memoSn) { // 요청 본문에서 CKUP_TRGT_MEMO_SN을 받을 수도 있음
                $memoSn = $this->request->getPost('CKUP_TRGT_MEMO_SN_delete');
            }

            if (!$memoSn) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 메모 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $memo = $this->ckupTrgtMemoModel->find($memoSn);
            if (!$memo) {
                return $this->response->setJSON(['status' => 'error', 'message' => '삭제할 메모를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            if ($this->ckupTrgtMemoModel->deleteMemo($memoSn)) {
                return $this->response->setJSON(['status' => 'success', 'message' => '메모가 성공적으로 삭제되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '메모 삭제 중 오류가 발생했습니다.', 'errors' => $this->ckupTrgtMemoModel->errors(), 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }

    //엑셀 관련
    public function excel_upload()
    {
        $file = $this->request->getFile('excel_file');
        if (!$file || !$file->isValid()) { // !file 추가 및 isValid() 간소화
            log_message('error', 'Excel Upload: Invalid file.');
            return $this->response->setStatusCode(400)->setJSON(['error' => '유효하지 않은 파일입니다.']);
        }

        $coSn     = session()->get('co_sn'); // Force session CO_SN
        $ckupYyyy = $this->request->getPost('fileModal_ckup_yyyy');

        if (empty($coSn) || empty($ckupYyyy)) {
            log_message('error', 'Excel Upload: CO_SN or CKUP_YYYY missing. CO_SN: ' . $coSn . ', CKUP_YYYY: ' . $ckupYyyy);
            return $this->response->setStatusCode(400)->setJSON(['error' => '회사 정보 또는 검진년도가 누락되었습니다.']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            log_message('error', 'Excel Upload: Spreadsheet load failed - ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => '엑셀 파일을 읽는 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        $dataToInsert = []; // 삽입할 데이터를 모으는 배열

        foreach ($sheet->getRowIterator(3) as $rowIndex => $row) { // $rowIndex 추가 (로깅 등 활용)
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cells as $i => $cell) {
                // getCalculatedValue()는 수식 결과도 가져옵니다. 단순 값은 getValue()도 괜찮습니다.
                $rowData[] = trim((string)$cell->getCalculatedValue());
            }

            // 빈 행 체크 (모든 셀이 비어있는 경우 스킵)
            if (count(array_filter($rowData, fn($value) => !is_null($value) && $value !== '')) == 0) {
                continue;
            }

            // 성별 값 변환
            $rowSex = null; // 기본값
            if (isset($rowData[3])) {
                $genderString = trim($rowData[3]);
                if ($genderString == "남" || $genderString == "남자") {
                    $rowSex = 'M';
                } elseif ($genderString == "여" ||  $genderString == "여자") {
                    $rowSex = 'F';
                }
                // else: $rowSex는 null 또는 다른 기본값 유지
            }

            // 관계 값 변환
            $rowRelation = null; // 기본값
            if (isset($rowData[12])) {
                $relationString = trim($rowData[12]);
                if ($relationString == "본인") {
                    $rowRelation = 'S'; // Self
                } elseif ($relationString == "배우자") {
                    $rowRelation = 'W'; // Wife/Husband (Spouse)
                } elseif ($relationString == "부모") {
                    $rowRelation = 'P'; // Parent
                } elseif ($relationString == "자녀") {
                    $rowRelation = 'C'; // Child
                } elseif ($relationString == "기타") {
                    $rowRelation = 'O'; // Other
                }
                // else: $rowRelation은 null 또는 다른 기본값 유지
            }


            // 필수 값 체크 (예: 이름)
            //if (empty($rowData[0])) {
            //    log_message('warning', "Excel Upload: Skipping row {$rowIndex} due to empty NAME.");
                // 여기서 return 하거나, 오류 메시지를 모아서 마지막에 반환할 수도 있습니다.
                // 일단은 스킵하고 다음 행으로 진행.
             //   continue;
            //}


            $singleRowData = [
                'CO_SN'             => $coSn,
                'CKUP_YYYY'         => $ckupYyyy,
                'NAME'              => $rowData[0] ?? null,
                'CKUP_NAME'         => $rowData[0] ?? null, // CKUP_NAME이 엑셀에 없다면 NAME과 동일하게
                'BUSINESS_NUM'      => $rowData[1] ?? null,
                'BIRTHDAY'          => isset($rowData[2]) ? preg_replace("/[^0-9]/", "", $rowData[2]) : null, // 숫자만, YYYYMMDD 가정
                'PSWD'              => isset($rowData[2]) ? password_hash(preg_replace("/[^0-9]/", "", $rowData[2]), PASSWORD_DEFAULT) : null,
                'SEX'               => $rowSex, // 변환된 값 사용
                'HANDPHONE'         => $rowData[4],
                'CHECKUP_TARGET_YN' => $rowData[5], // F열: 검진대상여부
                // 'CKUP_YN'           => $rowData[5], // CKUP_YN 제외
                'SUPPORT_FUND'      => $rowData[6] ?? null,
                'FAMILY_SUPPORT_FUND'=> $rowData[7] ?? null,
                'EMAIL'             => $rowData[8] ?? null,
                'WORK_STATUS'       => $rowData[9] ?? null,
                'ASSIGN_CODE'       => $rowData[10] ?? null,
                'JOB'               => $rowData[11] ?? null,
                'RELATION'          => $rowRelation, // 변환된 값 사용
            ];

            // 데이터 정제 (예: 생년월일 형식 YYMMDD로 통일 등 - 모델의 allowedFields가 YYMMDD(6)을 가정)
            if (!empty($singleRowData['BIRTHDAY']) && strlen($singleRowData['BIRTHDAY']) === 8) {
                $singleRowData['BIRTHDAY'] = substr($singleRowData['BIRTHDAY'], 2); // YYYYMMDD -> YYMMDD
            }


            $dataToInsert[] = $singleRowData;
        }

        if (empty($dataToInsert)) {
            return $this->response->setJSON(['status' => 'info', 'message' => '업로드할 유효한 데이터가 없습니다.']);
        }

        $model = $this->ckupTrgtModel;

        // 데이터베이스 트랜잭션 시작
        $this->db->transStart(); // 또는 $db->transBegin();

        foreach ($dataToInsert as $single_row_data_to_insert) {
            if (!$model->insert($single_row_data_to_insert)) {
                // 오류 발생 시 로그 남기고 트랜잭션 실패 처리 (아래 transStatus에서 감지됨)
                log_message('error', 'Excel Upload: DB insert failed for row. Data: ' . print_r($single_row_data_to_insert, true) . ' Errors: ' . print_r($model->errors(), true));
                // $db->transRollback(); // 여기서 바로 롤백하고 리턴해도 됨
                // return $this->response->setStatusCode(500)->setJSON(['error' => '데이터 저장 중 오류 발생 (일부 데이터).', 'details' => $model->errors()]);
            }
        }

        // 트랜잭션 상태 확인 및 완료
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            log_message('error', 'Excel Upload: Transaction rolled back due to errors.');
            return $this->response->setStatusCode(500)->setJSON(['error' => '데이터 저장 중 오류가 발생하여 모든 작업이 취소되었습니다.']);
        } else {
            $this->db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => count($dataToInsert) . '개의 데이터가 성공적으로 업로드되었습니다.']);
        }
    }


    public function excel_download()
    {
        helper('download');
        $ckupYYYY = $this->request->getGet('ckup_yyyy_filter');
        $coSn = session()->get('co_sn'); // Force session CO_SN

        //$model = $this->ckupTrgtModel;
        $rows = $this->ckupTrgtModel->getExcelData($ckupYYYY, $coSn);  // 커스텀 쿼리 필요

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['년도', '회사명', '사번', '수검자', '성명', '관계', '성별', '전화번호', '핸드폰', '주소', '예약여부', '수검여부'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['CKUP_YYYY']);
            $sheet->setCellValue('B' . $rowNum, $row['CO_SN']);
            $sheet->setCellValue('C' . $rowNum, $row['BUSINESS_NUM']);
            $sheet->setCellValue('D' . $rowNum, $row['CKUP_NAME']);
            $sheet->setCellValue('E' . $rowNum, $row['NAME']);
            $sheet->setCellValue('F' . $rowNum, $row['RELATION']);
            $sheet->setCellValue('G' . $rowNum, $row['SEX']);
            $sheet->setCellValue('H' . $rowNum, $row['TEL_NO']);
            $sheet->setCellValue('I' . $rowNum, $row['HANDPHONE']);
            $sheet->setCellValue('J' . $rowNum, $row['ADDR']);
            $sheet->setCellValue('K' . $rowNum, $row['RSVT_STTS']);
            $sheet->setCellValue('L' . $rowNum, $row['CKUP_YN']);
            $rowNum++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = '검진대상' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function ajax_reset_password($id = null)
    {
        if ($this->request->isAJAX() && $this->request->getMethod() === 'POST') {
            if (!$id) {
                return $this->response->setJSON(['status' => 'error', 'message' => '대상자 ID가 필요합니다.', 'csrf_hash' => csrf_hash()]);
            }

            $coSn = session()->get('co_sn');
            $item = $this->ckupTrgtModel->where('CO_SN', $coSn)->find($id);
            if (!$item) {
                return $this->response->setJSON(['status' => 'error', 'message' => '대상자 정보를 찾을 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $birthday = $item['BIRTHDAY'];
            if (empty($birthday)) {
                return $this->response->setJSON(['status' => 'error', 'message' => '생년월일 정보가 없어 비밀번호를 초기화할 수 없습니다.', 'csrf_hash' => csrf_hash()]);
            }

            $newPassword = password_hash($birthday, PASSWORD_DEFAULT);
            $updated = $this->ckupTrgtModel->update($id, ['PSWD' => $newPassword]);

            if ($updated) {
                return $this->response->setJSON(['status' => 'success', 'message' => '비밀번호가 생년월일로 초기화되었습니다.', 'csrf_hash' => csrf_hash()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => '비밀번호 초기화 중 오류가 발생했습니다.', 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '잘못된 요청입니다.', 'csrf_hash' => csrf_hash()]);
    }
}