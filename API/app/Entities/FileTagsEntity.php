<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class FileTagsEntity extends Entity
{
    protected $attributes = [
        'id'      => null,
        'id_file' => null,
        'id_tag'  => null,
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

    public function getIdFile()
    {
        return $this->attributes['id_file'];
    }
    public function setIdFile(int $idFile)
    {
        $this->attributes['id_file'] = $idFile;
        return $this;
    }

    public function getIdTag()
    {
        return $this->attributes['id_tag'];
    }
    public function setIdTag(int $idTag)
    {
        $this->attributes['id_tag'] = $idTag;
        return $this;
    }
}