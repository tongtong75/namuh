<?php

namespace App\Models;

use CodeIgniter\Model;

class CoHsptlLnkngModel extends Model
{
    protected $table            = 'CO_HSPTL_LNKNG';
    // Since this table has a composite primary key (HSPTL_SN, CO_SN),
    // and CI Model primarily works best with a single auto-incrementing PK,
    // we don't define $primaryKey here for typical find/update operations.
    // We'll manage inserts and deletes more manually.
    protected $returnType       = 'array';
    protected $allowedFields    = ['HSPTL_SN', 'CO_SN', 'REG_ID', 'REG_YMD'];

    // Dates
    protected $useTimestamps = false;
    // protected $createdField  = 'REG_YMD'; // CI would manage this if useTimestamps = true

    public function getLinkedHsptlSnsByCoSn($coSn)
    {
        $result = $this->select('HSPTL_SN')
                       ->where('CO_SN', $coSn)
                       ->findAll();
        
        return array_column($result, 'HSPTL_SN'); // Return an array of HSPTL_SN values
    }

    public function deleteLinksByCoSn($coSn)
    {
        return $this->where('CO_SN', $coSn)->delete();
    }

    public function addLink($data)
    {
        // REG_YMD has a default curdate() in DB, so we don't strictly need to set it here
        // unless we want to override or ensure application-level consistency.
        // $data['REG_YMD'] = date('Y-m-d H:i:s'); // Or just Y-m-d if column type is DATE
        return $this->insert($data);
    }
}