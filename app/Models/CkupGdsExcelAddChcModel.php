<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsExcelAddChcModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'CKUP_GDS_EXCEL_ADD_CHC';
    protected $primaryKey       = 'CKUP_GDS_EXCEL_ADD_CHC_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_EXCEL_SN', 'CKUP_SE', 'CKUP_TYPE', 'CKUP_ARTCL', 'DSS', 
        'GNDR_SE', 'CKUP_CST', 'RMRK', 'DEL_YN'
    ];
}
