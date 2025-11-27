<?php

namespace App\Controllers;

use App\Models\AuthModel;

class AuthController extends BaseController
{
    protected AuthModel $authModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    /**
     * 로그인 폼을 보여줍니다.
     */
    public function loginForm()
    {
        // 이미 로그인한 경우 대시보드 등으로 리디렉션
        if (session()->get('is_logged_in')) {
            return redirect()->to('mngr_mng/index'); // 예시 대시보드 URL
        }
        return view('auth/mngrLogin'); // app/Views/auth/mngrLogin.php
    }

    /**
     * 로그인 처리를 합니다.
     */
    public function attemptLogin()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'mngr_id' => 'required',
                'password' => 'required',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', '아이디와 비밀번호를 모두 입력해주세요.');
            }

            $mngrId = $this->request->getPost('mngr_id');
            $password = $this->request->getPost('password');

            $userData = $this->authModel->attemptLogin($mngrId, $password);

            if ($userData) {
                // 로그인 성공, 세션에 사용자 정보 저장
                session()->set($userData);
                
               if ($userData['user_type'] === 'S') {
                    // CodeIgniter의 redirect()는 내부적으로 exit을 처리합니다.
                    return redirect()->to(site_url('hsptlmng'))->with('message', '슈퍼어드민으로 로그인 되었습니다.'); 
                } else {
                    return redirect()->to(site_url('mngrmng'))->with('message', '로그인 되었습니다.'); 
                }
                //return redirect()->to('mngr_mng/index')->with('message', '로그인 되었습니다.'); // 예시 대시보드 URL
            } else {
                // 로그인 실패
                return redirect()->back()->withInput()->with('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
            }
        }

        // POST 요청이 아니면 로그인 폼으로 리디렉션
        return redirect()->to('/mngrLogin');
    }

    /**
     * 로그아웃 처리를 합니다.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/mngrLogin')->with('message', '로그아웃 되었습니다.');
    }
}