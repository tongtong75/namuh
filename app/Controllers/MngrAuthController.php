<?php
namespace App\Controllers;

use App\Models\MngrAuthModel;
use App\Models\HsptlMngModel;

class MngrAuthController extends BaseController
{
    protected $helpers = ['form', 'url'];
    public function login()
    {
        return view('mngr/mngrLogin'); // 로그인 화면 렌더링
    }

    public function loginProc()
    {
        helper(['form']);

        $rules = [
            'mngr_id' => 'required',
            'password' => 'required'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', '아이디와 비밀번호를 모두 입력해 주세요.');
        }

        $id = $this->request->getPost('mngr_id');
        $pw = $this->request->getPost('password');
        
        // 병원 관리자 로그인 로직 (기존)
        $mngrAuthModel = new MngrAuthModel();
        $user = $mngrAuthModel->getMngrByCredentials($id, $pw);

        if (! $user) {
            return redirect()->back()->withInput()->with('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
        }

        $isSuper = empty($user['HSPTL_SN']);
        $hospitalName = '';

        if (! $isSuper) {
            $hsptlModel = new HsptlMngModel();
            $hsptlData = $hsptlModel
                ->select('HSPTL_NM')
                ->where('HSPTL_SN', $user['HSPTL_SN'])
                ->where('DEL_YN', 'N')
                ->first();
            $hospitalName = $hsptlData['HSPTL_NM'] ?? ($isSuper ? '슈퍼어드민' : '');
        }

        // 세션 설정
        session()->set([
            'user_id'       => $user['MNGR_ID'],
            'user_name'     => $user['MNGR_NM'],
            'user_type'     => $isSuper ? 'S' : 'H', // 관리자 구분 (수정: N -> H)
            'hsptl_sn'      => $user['HSPTL_SN'] ?? null, // 추가: 병원 SN
            'hsptl_nm'      => $hospitalName // 추가: 병원명 (일관성 유지)
        ]);

        return redirect()->to($isSuper =='S' ? '/mngr/mngrMng' : 'mngr/ckupArtclMng');
                            
        
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/mngr/login')->with('message', '로그아웃 되었습니다.');
    }
}
