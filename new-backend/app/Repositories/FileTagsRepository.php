<?php


namespace App\Repositories;
use App\Models\FileTagsModel;
use App\Entities\FileTagsEntity;
use Throwable;

class FileTagsRepository
{
    protected $fileTagsModel;

    public function __construct()
    {
        $this->fileTagsModel = new FileTagsModel();
    }

    /**
     * Creates File-Tag association
     */
    public function create(FileTagsEntity $fileTag): ?FileTagsEntity
    {
        try {
            // Check if the association already exists
            $exists = $this->fileTagsModel->where('id_file', $fileTag->getIdFile())
                                          ->where('id_tag', $fileTag->getIdTag())
                                          ->first();
            // returns association if exists
            if ($exists) {
                return $exists;
            }

            // makes association
            $this->fileTagsModel->insert(['id_file' => $fileTag->getIdFile(), 'id_tag' => $fileTag->getIdTag()]);
            $fileTag->id = $this->fileTagsModel->getInsertID();
            return $fileTag;
            
        } catch (Throwable $e) {
            log_message('error', 'Error creating file tag: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets association by ID
     */
    public function findById(int $id): ?FileTagsEntity
    {
        try {
            // returns association
            return $this->fileTagsModel->find($id);
        } catch (Throwable $e) {
            log_message('error', 'Error finding file tag by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets all associations for a certain file
     */
    public function findTagsOnFile(int $fileId): array|null
    {
        try {
            // returns associations
            return $this->fileTagsModel->where('id_file', $fileId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding file tags by file ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets all associations for a certain tag
     */
    public function findFilesOnTag(int $tagId): array|null
    {
        try {
            // returns associations
            return $this->fileTagsModel->where('id_tag', $tagId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding file tags by tag ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete association by ID
     */
    public function delete(int $id): bool
    {
        try {
            return (bool) $this->fileTagsModel->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tag: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all associations for a certain file
     */
    public function deleteByFileId(int $fileId): bool
    {
        try {
            // deletes association
            return $this->fileTagsModel->where('id_file', $fileId)->delete();
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tags by file ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all associations for a certain tag
     */
    public function deleteByTagId(int $tagId): bool
    {
        try {
            // deletes associations
            return $this->fileTagsModel->where('id_tag', $tagId)->delete();
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tags by tag ID: ' . $e->getMessage());
            return false;
        }
    }
}