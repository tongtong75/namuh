<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDayCkupSeToDayCkupMng extends Migration
{
    public function up()
    {
        $fields = [
            'DAY_CKUP_SE' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'after'      => 'CKUP_YMD',
                'comment'    => '요일검진구분 (WEEKDAY, SAT)',
            ],
        ];
        $this->forge->addColumn('DAY_CKUP_MNG', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('DAY_CKUP_MNG', 'DAY_CKUP_SE');
    }
}