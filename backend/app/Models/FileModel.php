<?php
namespace App\Models;

use App\Entities\FileEntity;
use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table = 'file';
    protected $primaryKey = 'id';
    protected $returnType = FileEntity::class;


    protected $allowedFields = [
            'id_owner',
            'name',
            'type',
            'path',
            'is_deleted',
    ];
}