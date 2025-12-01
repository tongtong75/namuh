<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsExcelChcArtclModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'CKUP_GDS_EXCEL_CHC_ARTCL';
    protected $primaryKey       = 'CKUP_GDS_EXCEL_CHC_ARTCL_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_EXCEL_CHC_GROUP_SN', 'CKUP_GDS_EXCEL_MNG_SN', 'CKUP_SE', 'CKUP_TYPE', 
        'CKUP_ARTCL', 'DSS', 'GNDR_SE', 'RMRK', 'DEL_YN'
    ];
}
