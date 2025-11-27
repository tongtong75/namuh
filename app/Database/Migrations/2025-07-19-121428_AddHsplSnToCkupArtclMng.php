<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHsplSnToCkupArtclMng extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('HSPTL_SN', 'CKUP_ARTCL_MNG')) {
            $this->forge->addColumn('CKUP_ARTCL_MNG', [
                'HSPTL_SN' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'CKUP_ARTCL_SN',
                    'comment' => '병원 SN'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('CKUP_ARTCL_MNG', 'HSPTL_SN');
    }
}