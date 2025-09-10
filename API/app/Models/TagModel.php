<?php
namespace App\Models;

use App\Entities\TagEntity;
use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table = 'tag';
    protected $primaryKey = 'id';
    protected $returnType = TagEntity::class;


    protected $allowedFields = [
            'id_owner',
            'name',
            'description',
            'color',
    ];
}