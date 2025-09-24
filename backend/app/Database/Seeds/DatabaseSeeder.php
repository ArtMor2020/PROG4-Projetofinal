<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
        public function run()
    {
        $data = [
            'email'      => 'testuser@example.com',
            'password'   => password_hash('password123', PASSWORD_DEFAULT), // hashed
            'is_deleted' => 0,
        ];

        // Insert into the users table
        $this->db->table('users')->insert($data);
    }
}

class FileSeeder extends Seeder
{
    public function run()
    {
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'id_owner'   => 1,          // belongs to user ID 1
                'name'       => (string) $i . ' test', // file name = "1", "2", ...
                'type'       => 'image',   // fixed type
                'path'       => (string)$i . '.jpg', // example path
                'is_deleted' => 0,
            ];
        }

        // Bulk insert all 10 rows
        $this->db->table('files')->insertBatch($data);
    }
}

class TagSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            '#FF5733', '#33FF57', '#3357FF', '#F333FF', '#33FFF5',
            '#F5FF33', '#FF33A6', '#A633FF', '#33FFA6', '#FFA633',
            '#FF3333', '#33FF33', '#3333FF', '#FF33FF', '#33FFFF',
            '#FFFF33', '#FF9933', '#9933FF', '#33FF99', '#FF3399'
        ];

        $data = [];

        for ($i = 1; $i <= 20; $i++) {
            $data[] = [
                'id_owner'    => 1,
                'name'        => 'tag' . $i,
                'description' => '',
                'color'       => $colors[$i - 1] ?? '#CCCCCC', // fallback color
            ];
        }

        $this->db->table('tags')->insertBatch($data);
    }
}


class FileTagSeeder extends Seeder
{
    public function run()
    {
        $fileCount = 10;  // 10 files
        $tagCount  = 20;  // 20 tags
        $data = [];

        for ($fileId = 1; $fileId <= $fileCount; $fileId++) {
            // Pick a random number of tags between 3 and 5
            $numTags = rand(3, 5);
            $tags = array_rand(range(1, $tagCount), $numTags);

            // array_rand returns keys if array, normalize to array
            if (!is_array($tags)) {
                $tags = [$tags];
            }

            foreach ($tags as $tagIndex) {
                // tagIndex is 0-based, so +1
                $data[] = [
                    'id_file' => $fileId,
                    'id_tag'  => $tagIndex + 1,
                ];
            }
        }

        $this->db->table('file_tags')->insertBatch($data);
    }
}

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('App\Database\Seeds\UserSeeder');
        $this->call('App\Database\Seeds\FileSeeder');
        $this->call('App\Database\Seeds\TagSeeder');
        $this->call('App\Database\Seeds\FileTagSeeder');
    }
}
