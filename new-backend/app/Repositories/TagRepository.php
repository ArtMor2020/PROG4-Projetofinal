<?php

namespace App\Repositories;
use App\Models\TagModel;
use App\Entities\TagEntity;
use Throwable;

class TagRepository
{
    protected TagModel $tagModel;
    protected FileTagsRepository $fileTagsRepository;

    public function __construct()
    {
        $this->tagModel = new TagModel();
        $this->fileTagsRepository = new FileTagsRepository();
    }

    /**
     * Creates tag
     */
    public function create(TagEntity $tag): ?TagEntity
    {
        try {
            // checks if tag with same name already exists
            $exists = $this->tagModel->where('name', $tag->getName())
                                    ->where('id_owner', $tag->getIdOwner())
                                    ->first();
            // if exists, returns it
            if ($exists) {
                log_message('info', 'Tag already exists with ID: ' . $exists->id);
                return $exists;
            }

            // create tag
            $this->tagModel->insert($tag);
            $tag->setId($this->tagModel->getInsertID());
            
            return $tag;
            
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Gets all tags of a user
     */
    public function findAllByOwnerId(int $ownerId): array
    {
        try {
            return $this->tagModel->where('id_owner', $ownerId)->findAll();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Find tags by name owned by user
     * Uses levenshtein distance for sorting
     */
    public function findByNameAndOwnerId(string $name, int $ownerId): ?array
    {
        try {
            // validates data
            if (empty($name) || !is_string($name)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // fetch candidates
            $rows = $this->tagModel
                ->where('id_owner', $ownerId)
                ->like('name', $name)
                ->findAll();

            if (empty($rows)) {
                return null;
            }

            $tags = [];

            // compute match percentage with levenshtein distance
            foreach ($rows as $row) {
                log_message('error', $row->getName());
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

            // sort by match %
            usort($tags, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

            // return sorted
            return $tags;

        } catch (Throwable $e) {
            log_message('error', 'Error finding tag by name and owner ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets a tag by ID
     */
    public function findById(int $id): ?TagEntity
    {
        try {
            // returns the tag
            return $this->tagModel->find($id);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Updates tag
     */
    public function updateTag(TagEntity $tag): bool
    {
        try {
            // updates tag
            return (bool) $this->tagModel->update($tag->getId(), $tag);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Deletes tag
     */
    public function deleteTag(int $id): bool
    {
        try {
            // validates ID
            if (empty($id) || !is_int($id)) return false;

            // deletes File-Tag relationships of tag
            $this->fileTagsRepository->deleteByTagId($id);
            
            // deletes tag
            return (bool) $this->tagModel->delete($id);
        } catch (Throwable) {
            return false;
        }
    }
}