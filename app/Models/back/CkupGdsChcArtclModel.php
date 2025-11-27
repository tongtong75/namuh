<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsChcArtclModel extends Model
{
    protected $table            = 'ckup_gds_chc_artcl';
    protected $primaryKey       = 'CKUP_GDS_CHC_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // 이 필드들만 insert, update가 허용됩니다.
    protected $allowedFields    = ['CKUP_GDS_CHC_GROUP_SN', 'CHC_ARTCL_SN', 'DEL_YN'];
}