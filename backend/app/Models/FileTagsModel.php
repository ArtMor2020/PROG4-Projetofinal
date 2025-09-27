<?php
namespace App\Models;

use App\Entities\FileTagsEntity;
use CodeIgniter\Model;

class FileTagsModel extends Model
{
    protected $table = 'file_tags';
    protected $primaryKey = 'id';
    protected $returnType = FileTagsEntity::class;


    protected $allowedFields = [
            'id_file',
            'id_tag',
    ];
}