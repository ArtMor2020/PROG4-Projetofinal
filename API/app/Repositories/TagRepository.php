<?php

namespace App\Repositories;
use App\Models\TagModel;
use App\Entities\TagEntity;
use Throwable;

class TagRepository
{
    protected TagModel $tagModel;

    public function __construct()
    {
        $this->tagModel = new TagModel();
    }

    public function create(TagEntity $tag): ?TagEntity
    {
        try {
            $exists = $this->findByNameAndOwnerId($tag->getName(), $tag->getIdOwner());
            if ($exists) {
                return $exists;
            }

            $this->tagModel->insert($tag);
            $tag->setId($this->tagModel->getInsertID());
            return $tag;
            
        } catch (Throwable) {
            return null;
        }
    }

    public function findAllByOwnerId(int $ownerId): array
    {
        try {
            return $this->tagModel->where('owner_id', $ownerId)->findAll();
        } catch (Throwable) {
            return [];
        }
    }

    // add sorting by levenshtein distance
    public function findByNameAndOwnerId(string $name, int $ownerId): ?TagEntity
    {
        try {
            return $this->tagModel->where('name', $name)->where('owner_id', $ownerId)->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function findById(int $id): ?TagEntity
    {
        try {
            return $this->tagModel->find($id);
        } catch (Throwable) {
            return null;
        }
    }

    public function updateTag(TagEntity $tag): bool
    {
        try {
            return (bool) $this->tagModel->update($tag->getId(), $tag);
        } catch (Throwable) {
            return false;
        }
    }

    public function deleteTag(int $id): bool
    {
        try {
            return (bool) $this->tagModel->delete($id);
        } catch (Throwable) {
            return false;
        }
    }
}