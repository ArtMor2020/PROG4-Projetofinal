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

    // add sorting by levenshtein distance
    public function findByNameAndOwnerId(string $name, int $ownerId): ?array
    {
        try {
            if (empty($name) || !is_string($name)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // Step 1: Fetch possible matches (use LIKE for flexibility)
            $rows = $this->fileModel
                ->where('owner_id', $ownerId)
                ->like('name', $name) // broader than exact match
                ->findAll();

            if (empty($rows)) {
                return null;
            }

            $files = [];

            // Step 2: Compute match percentage
            foreach ($rows as $row) {
                $levenshteinDistance = levenshtein($name, $row->name); 
                $maxLength = max(strlen($name), strlen($row->name));

                $matchPercentage = ($maxLength === 0) 
                    ? 100 
                    : (1 - ($levenshteinDistance / $maxLength)) * 100;

                $files[] = [
                    'file' => $row,
                    'matchPercentage' => round($matchPercentage, 2)
                ];
            }

            // Step 3: Sort descending by match %
            usort($files, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

            // Step 4: Return only the File entities/models
            return array_map(fn($f) => $f['file'], $files);

        } catch (Throwable $e) {
            log_message('error', 'Error finding file by name and owner ID: ' . $e->getMessage());
            return null;
        }
    }


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