<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupGdsAddChcModel extends Model
{
    protected $DBGroup          = 'default';
        protected $table            = 'CKUP_GDS_ADD_CHC';
    protected $primaryKey       = 'CKUP_GDS_ADD_CHC_SN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'CKUP_GDS_SN', 'CHC_ARTCL_SN', 'DEL_YN'
    ];

    protected $useTimestamps = false;
    public $timestamps = false;
}
