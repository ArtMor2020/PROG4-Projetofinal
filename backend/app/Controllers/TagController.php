<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Repositories\TagRepository;
use App\Entities\TagEntity;

class TagController extends ResourceController
{
    protected $tagRepository;

    public function __construct()
    {
        $this->tagRepository = new TagRepository();
    }

    
}