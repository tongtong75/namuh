<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvnCkupGdsChcArtclModel extends Model
{
    protected $table            = 'RSVN_CKUP_GDS_CHC_ARTCL';
    protected $primaryKey       = 'RSVN_CKUP_GDS_CHC_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'CKUP_GDS_EXCEL_CHC_ARTCL_SN', 'CKUP_TRGT_SN', 'REG_ID', 'MDFCN_ID', 'DEL_YN', 'REG_YMD', 'MDFCN_YMD'
    ];

    protected $useTimestamps = false;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setInsertDefaults'];
    protected $beforeUpdate   = ['setUpdateDefaults'];

    protected function setInsertDefaults(array $data)
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM_USER');
        $data['data']['REG_ID'] = $currentUserId;
        $data['data']['MDFCN_ID'] = $currentUserId;
        $data['data']['REG_YMD'] = date('Y-m-d H:i:s');
        $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        if (!isset($data['data']['DEL_YN'])) {
            $data['data']['DEL_YN'] = 'N';
        }
        return $data;
    }

    protected function setUpdateDefaults(array $data)
    {
        $currentUserId = session()->get('user_id') ?? (defined('SYSTEM_USER_ID') ? SYSTEM_USER_ID : 'SYSTEM_USER');
        $data['data']['MDFCN_ID'] = $currentUserId;
        $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        return $data;
    }
}
