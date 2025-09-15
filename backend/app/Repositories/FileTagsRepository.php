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

    public function create(FileTagsEntity $fileTag): ?FileTagsEntity
    {
        try {
            // Check if the association already exists
            $exists = $this->fileTagsModel->where('file_id', $fileTag->getIdFile())
                                          ->where('tag_id', $fileTag->getIdTag())
                                          ->first();
            if ($exists) {
                return null; // Association already exists
            }

            $this->fileTagsModel->insert($fileTag);
            $fileTag->id = $this->fileTagsModel->getInsertID();
            return $fileTag;
            
        } catch (Throwable $e) {
            log_message('error', 'Error creating file tag: ' . $e->getMessage());
            return null;
        }
    }


    public function findTagsOnFile(int $fileId): array|null
    {
        try {
            return $this->fileTagsModel->where('file_id', $fileId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding file tags by file ID: ' . $e->getMessage());
            return null;
        }
    }

    public function findFilesOnTag(int $tagId): array|null
    {
        try {
            return $this->fileTagsModel->where('tag_id', $tagId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding file tags by tag ID: ' . $e->getMessage());
            return null;
        }
    }

    public function findAvailableTagsOnFile(): array|null
    {
        return null;
    }

    public function delete(int $id): bool
    {
        try {
            return (bool) $this->fileTagsModel->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tag: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteByFileId(int $fileId): bool
    {
        try {
            return $this->fileTagsModel->where('file_id', $fileId)->delete();
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tags by file ID: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteByTagId(int $tagId): bool
    {
        try {
            return $this->fileTagsModel->where('tag_id', $tagId)->delete();
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file tags by tag ID: ' . $e->getMessage());
            return false;
        }
    }
}