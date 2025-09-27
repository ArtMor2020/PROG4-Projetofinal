<?php

namespace App\Services;

use App\Entities\FileEntity;
use App\Entities\TagEntity;
use App\Entities\FileTagsEntity;  
use App\Repositories\FileRepository;
use App\Repositories\TagRepository;
use App\Repositories\FileTagsRepository;
use Config\Database;

class FileDataService
{
    protected $fileRepository;
    protected $tagRepository;
    protected $fileTagsRepository;
    protected $db;
    
    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->tagRepository = new TagRepository();
        $this->fileTagsRepository = new FileTagsRepository();
        $this->db = Database::connect();
    }

    // Recieves a file and an array of tag names, creates any new tags the owner doesnt have, associates them with the file, and saves everything to the database.
    public function uploadFileWithTags($fileData, array $tagData, int $idOwner): FileEntity|null
    {
        try{
            // Save file to disk
            $path = $this->saveFileToDisk($fileData);
            if (!$path) {
                return null; // File saving failed
            }
            
            // Start transaction
            $this->db->transStart();

            // Save file metadata
            $fileEntity = new FileEntity();
            $fileEntity->setName($fileData->getClientName());
            $fileEntity->setIdOwner($idOwner);
            $fileEntity->setType(
                $this->getExtensionType(pathinfo($fileData->getClientName(), PATHINFO_EXTENSION))
            );
            $fileEntity->setPath($path);

            log_message('info', 'Saving file entity: ' . json_encode($fileEntity));

            $fileEntity = $this->fileRepository->create($fileEntity);
            log_message('info', 'File entity saved: ' . json_encode($fileEntity));
            if (!$fileEntity) {
                $this->db->transRollback();
                return null;
            }

            // Process tags
            foreach ($tagData as $tag) {
                if (!isset($tag['name'])) {
                    continue; // skip invalid tags
                }

                $tagEntity = new TagEntity();
                $tagEntity->setName($tag['name']);
                $tagEntity->setIdOwner($idOwner);
                $tagEntity->setDescription($tag['description'] ?? '');
                $tagEntity->setColor($tag['color'] ?? '#C80000');
                $tag = $this->tagRepository->create($tagEntity);
                log_message('info', 'Tag processed: ' . json_encode($tag));

                if ($tag) {
                    $fileTag = new FileTagsEntity();
                    $fileTag->setIdFile($fileEntity->getId());
                    $fileTag->setIdTag($tag->getId());
                    $this->fileTagsRepository->create($fileTag);
                }
            }

            $this->db->transComplete();

            return $fileEntity;

        } catch (\Exception $e) {
            log_message('error', 'Error uploading file with tags: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getFileById(int $fileId): ?string
    {
        try {
            if ($fileId <= 0) {
                return null;
            }

            // Step 1: get metadata from DB
            $fileEntity = $this->fileRepository->findById($fileId);
            if (!$fileEntity) {
                return null; // file not found in DB
            }

            // Step 2: build file path
            $filePath = WRITEPATH . 'uploads/' . $fileEntity->getPath();

            if (!is_file($filePath)) {
                log_message('error', "File not found on disk: {$filePath}");
                return null;
            }

            // Step 3: return contents (or you could return path or stream)
            return file_get_contents($filePath);

        } catch (\Throwable $e) {
            log_message('error', 'Error reading file by ID: ' . $e->getMessage());
            return null;
        }
    }

    public function downloadFile(int $fileId)
    {
        $file = $this->fileRepository->findById($fileId);
        if (!$file) {
            return null; // or throw PageNotFoundException
        }

        $filePath = WRITEPATH . 'uploads/' . $file->getPath();

        if (!is_file($filePath)) {
            return null; // File missing on disk
        }

        // Return as a download response
        return service('response')->download($filePath, null)->setFileName($file->getName());
    }


    public function saveFileToDisk($file): string|false
    {
        $uploadPath = WRITEPATH . 'uploads/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $file->getRandomName();

        if( !$file->move($uploadPath, $fileName) ) return false;

        return $fileName;
    }

        public function getExtensionType(string $EXT): string
    {
        $EXT = strtolower($EXT);
        $IMAGE = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif'];
        $VIDEO = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
        $DOCUMENT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt'];
        $ZIP = ['zip', 'rar', '7z', 'tar', 'gz'];

        return match (true) {
            in_array($EXT, $IMAGE) => 'IMAGE',
            in_array($EXT, $VIDEO) => 'VIDEO',
            in_array($EXT, $DOCUMENT) => 'DOCUMENT',
            in_array($EXT, $ZIP) => 'ZIP',
            default => 'OTHER'
        };
    }
}