<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvnCkupTrgtAddrModel extends Model
{
    protected $table            = 'RSVN_CKUP_TRGT_ADDR';
    protected $primaryKey       = 'RSVN_CKUP_TRGT_ADDR_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'CKUP_TRGT_SN', 'ZIP_CODE', 'ADDR', 'ADDR2', 'REG_YMD', 'MDFCN_YMD'
    ];

    protected $useTimestamps = false;
    // Dates
    // REG_YMD: DATETIME, MDFCN_YMD: DATETIME
    
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setInsertDefaults'];
    protected $beforeUpdate   = ['setUpdateDefaults'];

    protected function setInsertDefaults(array $data)
    {
        $data['data']['REG_YMD'] = date('Y-m-d H:i:s');
        $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function setUpdateDefaults(array $data)
    {
        $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        return $data;
    }
}
