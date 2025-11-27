<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHsplSnToChcArtclMng extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('HSPTL_SN', 'CHC_ARTCL_MNG')) {
            $this->forge->addColumn('CHC_ARTCL_MNG', [
                'HSPTL_SN' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'CHC_ARTCL_SN',
                    'comment' => '병원 SN'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('CHC_ARTCL_MNG', 'HSPTL_SN');
    }
}