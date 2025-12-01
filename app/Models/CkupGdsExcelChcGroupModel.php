<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsExcelChcGroupModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'CKUP_GDS_EXCEL_CHC_GROUP';
    protected $primaryKey       = 'CKUP_GDS_EXCEL_CHC_GROUP_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_EXCEL_MNG_SN', 'GROUP_NM', 'CHC_ARTCL_CNT', 'CHC_ARTCL_CNT2', 'DEL_YN'
    ];
}
