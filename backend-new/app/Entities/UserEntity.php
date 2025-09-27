<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $attributes = [
        'id'         => null,
        'email'      => null,
        'password'   => null,
        'is_deleted' => false
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

    public function getEmail()
    {
        return $this->attributes['email'];
    }
    public function setEmail(string $email)
    {
        $this->attributes['email'] = $email;
        return $this;
    }

    public function getPassword()
    {
        return $this->attributes['password'];
    }
    public function setPassword(string $password)
    {
        $this->attributes['password'] = $password;
        return $this;
    }
    
    public function toPublicArray(): array
    {
        return [
            'id'    => $this->id,
            'email' => $this->email,
        ];
    }
}