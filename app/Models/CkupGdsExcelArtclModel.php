<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsExcelArtclModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'CKUP_GDS_EXCEL_ARTCL';
    protected $primaryKey       = 'CKUP_GDS_EXCEL_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_EXCEL_MNG_SN', 'CKUP_SE', 'CKUP_ARTCL', 'DSS', 'GNDR_SE', 'RMRK', 'DEL_YN'
    ];
}
