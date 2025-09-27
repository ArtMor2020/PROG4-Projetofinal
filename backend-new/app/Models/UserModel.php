<?php
namespace App\Models;

use App\Entities\UserEntity;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $returnType = UserEntity::class;


    protected $allowedFields = [
            'email',
            'password',
            'is_deleted',
    ];
}