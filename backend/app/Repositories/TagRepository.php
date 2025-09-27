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
            $exists = $this->tagModel->where('name', $tag->getName())
                                    ->where('id_owner', $tag->getIdOwner())
                                    ->first();
            if ($exists) {
                log_message('info', 'Tag already exists with ID: ' . $exists->id);
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
            return $this->tagModel->where('id_owner', $ownerId)->findAll();
        } catch (Throwable) {
            return [];
        }
    }

    // add sorting by levenshtein distance
    public function findByNameAndOwnerId(string $name, int $ownerId): ?TagEntity
    {
        try {
            if (empty($name) || !is_string($name)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // Step 1: Fetch candidates
            $rows = $this->tagModel
                ->where('id_owner', $ownerId)
                ->like('name', $name)
                ->findAll();

            if (empty($rows)) {
                return null;
            }

            $tags = [];

            // Step 2: Compute match percentage
            foreach ($rows as $row) {
                $levenshteinDistance = levenshtein($name, $row->name);
                $maxLength = max(strlen($name), strlen($row->name));

                $matchPercentage = ($maxLength === 0) 
                    ? 100 
                    : (1 - ($levenshteinDistance / $maxLength)) * 100;

                $tags[] = [
                    'tag' => $row,
                    'matchPercentage' => round($matchPercentage, 2)
                ];
            }

            // Step 3: Sort by match %
            usort($tags, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

            // Step 4: Return only the best match (highest similarity)
            return $tags[0]['tag'];

        } catch (Throwable $e) {
            log_message('error', 'Error finding tag by name and owner ID: ' . $e->getMessage());
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