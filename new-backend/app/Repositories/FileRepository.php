<?php


namespace App\Repositories;

use App\Database\Migrations\FileTags;
use App\Models\FileModel;
use App\Entities\FileEntity;
use Throwable;

class FileRepository
{
    protected FileModel $fileModel;
    protected FileTagsRepository $fileTagsRepository;

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->fileTagsRepository = new FileTagsRepository();
    }

    /**
     * Create File
     */
    public function create(FileEntity $file): ?FileEntity
    {
        try {
            // validates $file
            if (empty($file->getIdOwner()) || !is_int($file->getIdOwner())) return null;
            if (empty($file->getName()) || !is_string($file->getName())) return null;
            if (empty($file->getType()) || !is_string($file->getType())) return null;
            if (empty($file->getPath()) || !is_string($file->getPath())) return null;
            if (!is_bool($file->getIsDeleted())) return null;

            // creates file
            $this->fileModel->insert($file);
            $file->id = $this->fileModel->getInsertID();
            return $file;
        } catch (Throwable $e) {
            log_message('error', 'Error creating file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?FileEntity
    {
        try {
            // validates id
            if (empty($id) || !is_int($id)) return null;

            // returns file
            return $this->fileModel->find($id);
        } catch (Throwable $e) {
            log_message('error', 'Error finding file by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find by owner ID
     */
    public function findByOwnerId(int $ownerId): array|null
    {
        try {
            // validates owner id
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // returns files owned by user
            return $this->fileModel->where('id_owner', $ownerId)
                ->orderBy('type', 'DESC')
                ->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding files by owner ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Searches by name tags owned by user
     * Uses levenshtein distance to order matches
     */
    public function findByNameAndOwnerId(string $name, int $ownerId): ?array
    {
        try {
            // validates data
            if (empty($name) || !is_string($name)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // fetch possible matches
            $rows = $this->fileModel
                ->where('id_owner', $ownerId)
                ->like('name', $name)
                ->findAll();

            if (empty($rows)) {
                return null;
            }

            $files = [];

            // compute match percentage with levenshtein distance
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

            // sort descending by match %
            usort($files, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

            // return files
            return array_map(fn($f) => $f['file'], $files);

        } catch (Throwable $e) {
            log_message('error', 'Error finding file by name and owner ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find files by type owned by user
     */
    public function findByTypeAndOwnerId(string $type, int $ownerId): array|null
    {
        try {
            // validates data
            if (empty($type) || !is_string($type)) return null;
            if (empty($ownerId) || !is_int($ownerId)) return null;

            // returns files by type
            return $this->fileModel->where('type', $type)->where('id_owner', $ownerId)->findAll();
        } catch (Throwable $e) {
            log_message('error', 'Error finding files by type and owner ID: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            // validate id
            if (empty($id) || !is_int($id)) return false;

            // delelete associations with tags
            $this->fileTagsRepository->deleteByFileId($id);

            // delete file
            return $this->fileModel->delete($id);
        } catch (Throwable $e) {
            log_message('error', 'Error deleting file by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function update(FileEntity $file): bool
    {
        try {
            // updates file
            return $this->fileModel->update($file->id, $file);
        } catch (Throwable $e) {
            log_message('error', 'Error updating file: ' . $e->getMessage());
            return false;
        }
    }
}