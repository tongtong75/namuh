<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'MNGR_ID'   => 'admin',
            'MNGR_NM'   => '최고관리자',
            'MNGR_PSWD' => password_hash('1234', PASSWORD_DEFAULT),
            'HSPTL_SN'  => null,
        ];

        // Using Query Builder
        $this->db->table('MNGR_MNG')->insert($data);
    }
}
