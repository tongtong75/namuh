<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsChcGroupModel extends Model
{
    protected $table            = 'CKUP_GDS_CHC_GROUP';
    protected $primaryKey       = 'CKUP_GDS_CHC_GROUP_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // 이 필드들만 insert, update가 허용됩니다.
    protected $allowedFields    = ['CKUP_GDS_SN', 'GROUP_NM', 'CHC_ARTCL_CNT', 'DEL_YN'];
}