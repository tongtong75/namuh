<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsAddChcModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ckup_gds_add_chc';
    protected $primaryKey       = 'CKUP_GDS_ADD_CHC_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_SN', 'CHC_ARTCL_SN', 'DEL_YN', 'REG_ID', 'MDFCN_ID'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'REG_YMD';
    protected $updatedField  = 'MDFCN_YMD';

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setRegistrationInfo'];
    protected $beforeUpdate   = ['setModificationInfo'];

    /**
     * INSERT 전에 등록자 정보를 설정합니다.
     */
    protected function setRegistrationInfo(array $data): array
    {
        $userId = session()->get('user_id') ?? 'system';
        
        $data['data']['REG_ID'] = $userId;

        return $data;
    }

    /**
     * UPDATE 전에 수정자 정보를 설정합니다.
     */
    protected function setModificationInfo(array $data): array
    {
        $userId = session()->get('user_id') ?? 'system';
        
        $data['data']['MDFCN_ID'] = $userId;

        return $data;
    }
}
