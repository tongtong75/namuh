<?php

namespace App\Models;

use CodeIgniter\Model;

class MngrAuthModel extends Model
{
    protected $table            = 'MNGR_MNG';
    protected $primaryKey       = 'MNGR_SN';
    protected $allowedFields    = ['MNGR_ID', 'MNGR_NM', 'MNGR_PSWD', 'HSPTL_SN'];

    // 관리자 로그인 확인
        public function getMngrByCredentials($id, $pw)
    {
        $user = $this->where('MNGR_ID', $id)->first();

        if ($user && password_verify($pw, $user['MNGR_PSWD'])) {
            return $user;
        }

        return null;
    }
}
