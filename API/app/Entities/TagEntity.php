<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TagEntity extends Entity
{
    protected $attributes = [
        'id'          => null,
        'id_owner'    => null,
        'name'        => null,
        'description' => null,
        'color'       => null,
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

    public function getDescription()
    {
        return $this->attributes['description'];
    }
    public function setDescription(string $description)
    {
        $this->attributes['description'] = $description;
        return $this;
    }

    public function getColor()
    {
        return $this->attributes['color'];
    }
    public function setColor(string $color)
    {
        $this->attributes['color'] = $color;
        return $this;
    }
}