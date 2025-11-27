<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsChcArtclModel extends Model
{
    protected $table            = 'CKUP_GDS_CHC_ARTCL';
    protected $primaryKey       = 'CKUP_GDS_CHC_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // 이 필드들만 insert, update가 허용됩니다.
    protected $allowedFields    = ['CKUP_GDS_CHC_GROUP_SN', 'CHC_ARTCL_SN', 'DEL_YN'];
}