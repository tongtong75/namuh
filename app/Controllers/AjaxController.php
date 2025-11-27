<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class AjaxController extends Controller
{
    public function index()
    {
        return view('ajax_test'); // 뷰 렌더링
    }

    public function handle()
    {
        $json = $this->request->getJSON();
        $name = $json->name ?? '이름 없음';

        return $this->response->setJSON([
            'message' => "안녕하세요, {$name}님!"
        ]);
    }
}
