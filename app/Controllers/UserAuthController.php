<?php
namespace App\Controllers;

use App\Models\CoMngModel;
use App\Models\CkupTrgtModel;

class UserAuthController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function login()
    {
        $coMngModel = new CoMngModel();
        // 회사 목록 가져오기
        $companies = $coMngModel->where('DEL_YN', 'N')->orderBy('CO_NM', 'ASC')->findAll();

        // 년도 생성 (현재 년도 기준 3년)
        $currentYear = date('Y');
        $years = range($currentYear, $currentYear - 2);

        return view('user/userLogin', [
            'companies' => $companies,
            'years' => $years
        ]);
    }

    public function loginProc()
    {
        $rules = [
            'examYear' => 'required',
            'companyCode' => 'required',
            'username' => 'required',
            'password' => 'required'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', '모든 필드를 입력해 주세요.');
        }

        $examYear = $this->request->getPost('examYear');
        $coSn = $this->request->getPost('companyCode');
        $username = $this->request->getPost('username'); // 사번(BUSINESS_NUM)
        $password = $this->request->getPost('password');

        $ckupTrgtModel = new CkupTrgtModel();

        // 사용자 인증 (회사, 년도, 사번 일치 확인)
        $user = $ckupTrgtModel->where('CO_SN', $coSn)
                              ->where('CKUP_YYYY', $examYear)
                              ->where('BUSINESS_NUM', $username)
                              ->where('RELATION', 'S') // 본인만 로그인 가능
                              ->where('DEL_YN', 'N')
                              ->first();

        // 1. 관리자 로그인 시도 (CO_MNG 테이블 확인)
        $coMngModel = new CoMngModel();
        $manager = $coMngModel->where('CO_SN', $coSn)
                              ->where('CO_MNGR_ID', $username)
                              ->where('DEL_YN', 'N')
                              ->first();

        if ($manager && password_verify($password, $manager['CO_MNGR_PSWD'])) {
             // 관리자 로그인 성공
             session()->set([
                'user_id'      => $manager['CO_MNGR_ID'],
                'user_name'    => $manager['PIC_NM'] ?: $manager['CO_MNGR_ID'], // 담당자명 없으면 ID 사용
                'user_type'    => 'M', // Manager
                'co_sn'        => $manager['CO_SN'],
                'co_nm'        => $manager['CO_NM'],
                'ckup_yyyy'    => $examYear // 관리자도 검진년도 선택해서 들어옴
            ]);

            return redirect()->to('/user/ckupTrgt')->with('message', '관리자 로그인 성공');
        }

        // 2. 일반 사용자 로그인 시도
        if ($user && password_verify($password, $user['PSWD'])) {
            // 회사명 가져오기
            $company = $coMngModel->where('CO_SN', $user['CO_SN'])->where('DEL_YN', 'N')->first();
            
            // 디버깅용 로그
            log_message('debug', 'User CO_SN: ' . $user['CO_SN']);
            log_message('debug', 'Company data: ' . json_encode($company));
            
            // 로그인 성공
            session()->set([
                'user_id'      => $user['BUSINESS_NUM'],
                'user_name'    => $user['NAME'],
                'user_type'    => 'U', // User
                'ckup_trgt_sn' => $user['CKUP_TRGT_SN'],
                'co_sn'        => $user['CO_SN'],
                'co_nm'        => $company ? $company['CO_NM'] : 'N/A',
                'ckup_yyyy'    => $user['CKUP_YYYY']
            ]);

            // AGREE_YN 상태에 따라 리다이렉트
            if ($user['AGREE_YN'] === 'Y') {
                return redirect()->to('/user/rsvn')->with('message', '로그인 성공');
            } else {
                return redirect()->to('/user/regist')->with('message', '로그인 성공');
            }
        } else {
            return redirect()->back()->withInput()->with('error', '정보가 일치하지 않습니다.');
        }
    }

    public function regist()
    {
        // 로그인 체크
        if (!session()->get('user_id')) {
            return redirect()->to('/user/login')->with('error', '로그인이 필요합니다.');
        }

        $ckupTrgtModel = new CkupTrgtModel();
        $familyMembers = $ckupTrgtModel->where('BUSINESS_NUM', session()->get('user_id'))
                                       ->where('CKUP_YYYY', session()->get('ckup_yyyy'))
                                       ->where('RELATION !=', 'S')
                                       ->where('DEL_YN', 'N')
                                       ->findAll();

        return view('user/regist', [
            'familyMembers' => $familyMembers
        ]);
    }

    public function updatePassword()
    {
        $request = service('request');
        $password = $request->getPost('password');

        if (!$password) {
            return $this->response->setJSON(['success' => false, 'message' => '비밀번호를 입력해주세요.']);
        }

        $ckupTrgtModel = new CkupTrgtModel();
        $userId = session()->get('user_id');
        $ckupYyyy = session()->get('ckup_yyyy');

        // 비밀번호 해싱
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 업데이트
        $result = $ckupTrgtModel->where('BUSINESS_NUM', $userId)
                                ->where('CKUP_YYYY', $ckupYyyy)
                                ->where('RELATION', 'S') // 본인만 변경 가능
                                ->set([
                                    'PSWD' => $hashedPassword,
                                    'AGREE_YN' => 'Y'
                                ])
                                ->update();

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => '비밀번호가 변경되었습니다.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => '비밀번호 변경에 실패했습니다.']);
        }
    }

    public function rsvn()
    {
        // 로그인 체크
        if (!session()->get('user_id')) {
            return redirect()->to('/user/login')->with('error', '로그인이 필요합니다.');
        }

        $coMngModel = new CoMngModel();
        $ckupTrgtModel = new CkupTrgtModel();

        // 회사 검진기간 정보 조회
        $coSn = session()->get('co_sn');
        $companyInfo = $coMngModel->where('CO_SN', $coSn)
                                   ->where('DEL_YN', 'N')
                                   ->first();

        // 사용자 및 가족 구성원 조회 (같은 BUSINESS_NUM) - 병원 정보 포함
        $userId = session()->get('user_id');
        $ckupYyyy = session()->get('ckup_yyyy');
        
        $db = db_connect();
        $builder = $db->table('CKUP_TRGT CT');
        $builder->select('CT.*, HM.HSPTL_NM');
        $builder->join('HSPTL_MNG HM', 'HM.HSPTL_SN = CT.CKUP_HSPTL_SN', 'left');
        $builder->where('CT.BUSINESS_NUM', $userId);
        $builder->where('CT.CKUP_YYYY', $ckupYyyy);
        $builder->where('CT.DEL_YN', 'N');
        // 본인(S)이 먼저 오도록 정렬 - CASE 문을 raw SQL로 처리
        $builder->orderBy('(CASE WHEN CT.RELATION = "S" THEN 0 ELSE 1 END)', 'ASC', false);
        $builder->orderBy('CT.RELATION', 'ASC');
        
        $familyMembers = $builder->get()->getResultArray();

        return view('user/rsvn/index', [
            'companyInfo' => $companyInfo,
            'familyMembers' => $familyMembers
        ]);
    }

    public function makeReservation()
    {
        $request = service('request');
        
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $ckupTrgtModel = new CkupTrgtModel();
        $ckupTrgtSn = $request->getPost('ckup_trgt_sn');

        if (!$ckupTrgtSn) {
            return $this->response->setJSON(['success' => false, 'message' => '대상자 정보가 없습니다.']);
        }

        // 예약 처리 (RSVT_STTS를 'Y'로 업데이트)
        $result = $ckupTrgtModel->where('CKUP_TRGT_SN', $ckupTrgtSn)
                                ->set(['RSVT_STTS' => 'Y'])
                                ->update();

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => '예약이 완료되었습니다.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => '예약 처리에 실패했습니다.']);
        }
    }

    public function cancelReservation()
    {
        $request = service('request');
        
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $ckupTrgtModel = new CkupTrgtModel();
        $ckupTrgtSn = $request->getPost('ckup_trgt_sn');

        if (!$ckupTrgtSn) {
            return $this->response->setJSON(['success' => false, 'message' => '대상자 정보가 없습니다.']);
        }

        // 예약 취소 처리 (RSVT_STTS를 'N'으로 업데이트)
        $result = $ckupTrgtModel->where('CKUP_TRGT_SN', $ckupTrgtSn)
                                ->set(['RSVT_STTS' => 'N'])
                                ->update();

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => '예약이 취소되었습니다.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => '예약 취소에 실패했습니다.']);
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/user/login')->with('message', '로그아웃 되었습니다.');
    }
}
