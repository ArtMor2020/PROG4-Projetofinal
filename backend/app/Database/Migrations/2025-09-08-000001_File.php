<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class File extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_owner'    => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_deleted'  => ['type' => 'BOOLEAN'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('file');
    }

    public function down(){
                $this->forge->dropTable('file');
    }
}