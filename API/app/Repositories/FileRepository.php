<?php


namespace App\Repositories;
use App\Models\FileModel;
use App\Entities\FileEntity;
use Throwable;

class FileRepository
{
    protected $fileModel;

    public function __construct()
    {
        $this->fileModel = new FileModel();
    }

    public function create(FileEntity $file): ?FileEntity
    {
        try {
            if (empty($file->getIdOwner()) || !is_int($file->getIdOwner())) return null;
            if (empty($file->getName()) || !is_string($file->getName())) return null;
            if (empty($file->getType()) || !is_string($file->getType())) return null;

            $this->fileModel->insert($file);
            $file->id = $this->fileModel->getInsertID();
            return $file;
        } catch (Throwable $e) {
            log_message('error', 'Error creating file: ' . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?FileEntity
    {
        try {
            if (empty($id) || !is_int($id)) return null;

            return $this->fileModel->find($id);
        } catch (Throwable $e) {
            log_message('error', 'Error finding file by ID: ' . $e->getMessage());
            return null;
        }
    }

    // add sorting by type, date, name
    public function findByOwnerId(int $ownerId): array|null
    {
        try {
            if (empty($ownerId) || !is_int($ownerId)) return null;

            return $this->fileModel->where('owner_id', $ownerId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding files by owner ID: ' . $e->getMessage());
            return null;
        }
    }

    // add sorting name by levenshtein distance
    public function findByNameAndOwnerId(string $name, int $ownerId): array|null
    {
        try {
            if (empty($name) || !is_string($name)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            return $this->fileModel->where('name', $name)->where('owner_id', $ownerId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding file by name and owner ID: ' . $e->getMessage());
            return null;
        }
    }

    // add sorting by date, name
    public function findByTypeAndOwnerId(string $type, int $ownerId): array|null
    {
        try {
            if (empty($type) || !is_string($type)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            return $this->fileModel->where('type', $type)->where('owner_id', $ownerId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding files by type and owner ID: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            if (empty($id) || !is_int($id)) return false;

            return $this->fileModel->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function update(FileEntity $file): bool
    {
        try {
            return $this->fileModel->update($file->id, $file);
        } catch (Throwable $e) {
            log_message('error', 'Error updating file: ' . $e->getMessage());
            return false;
        }
    }
}