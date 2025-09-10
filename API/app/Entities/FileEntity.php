<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class FileEntity extends Entity
{
    protected $attributes = [
        'id'          => null,
        'id_owner'    => null,
        'name'        => null,
        'type'        => null,
        'is_deleted'  => false,
    ];

    public function getId()
    {
        return $this->attributes['id'];
    }
    public function setId(int $id)
    {
        $this->attributes['id'] = $id;
        return $this;
    }

    public function getIdOwner()
    {
        return $this->attributes['id_owner'];
    }
    public function setIdOwner(int $idOwner)
    {
        $this->attributes['id_owner'] = $idOwner;
        return $this;
    }
    
        public function getName()
    {
        return $this->attributes['name'];
    }
    public function setName(string $name)
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function getType()
    {
        return $this->attributes['type'];
    }
    public function setType(string $type)
    {
        $this->attributes['type'] = $type;
        return $this;
    }

    public function getIsDeleted()
    {
        return $this->attributes['is_deleted'];
    }
    public function setIsDeleted(bool $isDeleted)
    {
        $this->attributes['is_deleted'] = $isDeleted;
        return $this;
    }

}