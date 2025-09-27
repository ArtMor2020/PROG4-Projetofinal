<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FileTags extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_file'    => ['type' => 'INT', 'unsigned' => true],
            'id_tag'    => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('file_tags');
    }

    public function down(){
                $this->forge->dropTable('file_tags');
    }
}