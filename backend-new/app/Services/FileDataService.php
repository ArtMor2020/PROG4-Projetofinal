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
    public function uploadFileWithTags($fileData, array $tagNames, int $idOwner): FileEntity|null
    {
        try{
            // Save file to disk
            $name = $this->saveFileToDisk($fileData);
            if (!$name) {
                return null; // File saving failed
            }
            
            // Start transaction
            $this->db->transStart();

            // Save file metadata
            $fileEntity = new FileEntity();
            $fileEntity->setName($name);
            $fileEntity->setIdOwner($idOwner);
            $fileEntity->setType(
                $this->getExtensionType(pathinfo($fileData->getClientName(), PATHINFO_EXTENSION))
            );

            $fileEntity = $this->fileRepository->create($fileEntity);
            if (!$fileEntity) {
                $this->db->transRollback();
                return null;
            }

            // Process tags
            foreach ($tagNames as $tagName) {

                $tagEntity = new TagEntity();
                $tagEntity->setName($tagName);
                $tagEntity->setIdOwner($idOwner);
                $tag = $this->tagRepository->create($tagEntity);

                if ($tag) {
                    $fileTag = new FileTagsEntity();
                    $fileTag->setIdFile($fileEntity->getId());
                    $fileTag->setIdTag($tag->getId());
                    $this->fileTagsRepository->create($fileTag);
                }
            }

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
            $filePath = WRITEPATH . 'uploads/' . $fileEntity->getName();

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

        $filePath = WRITEPATH . 'uploads/' . $file->getName();

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