<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 세션에 is_logged_in 값이 없거나 false이면 로그인 페이지로 리디렉션
        if (!session()->get('is_logged_in')) {
            // 현재 요청이 로그인 페이지 자체이거나 로그인 처리 경로가 아닌 경우에만 리디렉션
            // 이렇게 하지 않으면 무한 리디렉션 발생 가능성 있음
            // (이 로직은 Filters.php에서 경로별로 적용하므로 여기서는 단순 리디렉션만 해도 됨)
            return redirect()->to(site_url('mngrLogin'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }
}