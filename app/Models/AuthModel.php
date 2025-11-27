<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MngrMngModel; // 관리자 정보 조회를 위해
use App\Models\HsptlMngModel; // 병원 정보 조회를 위해

class AuthModel extends Model
{
    protected MngrMngModel $mngrModel;
    protected HsptlMngModel $hsptlModel;

    public function __construct()
    {
        parent::__construct();
        $this->mngrModel = new MngrMngModel();
        $this->hsptlModel = new HsptlMngModel();
    }

    /**
     * 사용자의 아이디와 비밀번호를 검증합니다.
     *
     * @param string $mngrId
     * @param string $password
     * @return array|false 로그인 성공 시 사용자 데이터 배열, 실패 시 false
     */
    public function attemptLogin(string $mngrId, string $password)
    {
        // 1. 아이디로 관리자 정보 조회 (DEL_YN 등 활성 사용자 조건이 있다면 추가)
        $manager = $this->mngrModel->where('MNGR_ID', $mngrId)->first();

        if (!$manager) {
            return false; // 해당 아이디의 관리자 없음
        }

        // 2. 비밀번호 검증
        if (!password_verify($password, $manager['MNGR_PSWD'])) {
            return false; // 비밀번호 불일치
        }

        // 3. 로그인 성공, 세션에 저장할 데이터 준비
        $sessionData = [
            'mngr_sn'   => $manager['MNGR_SN'], // 관리자 고유번호
            'mngr_id'   => $manager['MNGR_ID'],
            'mngr_nm'   => $manager['MNGR_NM'],
            'is_logged_in' => true,
        ];

        // 4. 병원 정보 처리
        if (!empty($manager['HSPTL_SN'])) {
            $hospital = $this->hsptlModel->find($manager['HSPTL_SN']);
            if ($hospital && $hospital['DEL_YN'] === 'N') { // 병원이 존재하고 삭제되지 않은 경우
                $sessionData['hsptl_sn'] = $hospital['HSPTL_SN'];
                $sessionData['hsptl_nm'] = $hospital['HSPTL_NM'];
                $sessionData['user_type'] = 'H'; // 일반 병원 관리자 (Namuh)
            } else{
                return false;//병원이 삭제되었을 경우에는 접속 금지
            }
        } else {
            // HSPTL_SN이 없는 경우 (슈퍼 어드민)
            $sessionData['hsptl_sn'] = null;
            $sessionData['hsptl_nm'] = '슈퍼어드민';
            $sessionData['user_type'] = 'S'; // 슈퍼 어드민 (Super)
        }
        
        return $sessionData;
    }
}