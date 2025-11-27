<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsArtclModel extends Model
{
    protected $table            = 'CKUP_GDS_ARTCL';
    protected $primaryKey       = 'CKUP_GDS_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // 이 필드들만 insert, update가 허용됩니다.
    protected $allowedFields    = ['CKUP_GDS_SN', 'CKUP_ARTCL_SN', 'DEL_YN'];
}