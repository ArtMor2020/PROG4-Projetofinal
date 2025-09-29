<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Services\FileDataService;
use App\Repositories\FileRepository;

class FileDataController extends ResourceController
{
    protected FileDataService $fileDataService;
    protected FileRepository $fileRepository;

    public function __construct()
    {
        $this->fileDataService = new FileDataService();
        $this->fileRepository = new FileRepository();
    }

    /**
     * Upload a file with tags for the logged-in user
     * POST /file/upload
     * Body: multipart/form-data with "file" and "tags[]"
     */
    public function uploadFileWithTags()
    {
        try {

            // checks if user is logged
            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            // gets data from request
            $fileData = $this->request->getFile('file');
            $tagDataRaw = $this->request->getVar('tags');

            // checks data
            if (!$fileData || !$fileData->isValid()) {
                return $this->fail('Invalid or missing file.');
            }

            // Decode JSON tags into array
            $tagData = [];
            if (!empty($tagDataRaw)) {
                $tagData = json_decode($tagDataRaw, true);
                if (!is_array($tagData)) {
                    return $this->failValidationErrors('Invalid tags format, must be JSON array.');
                }
            }

            // uploads file and tags
            $fileEntity = $this->fileDataService->uploadFileWithTags($fileData, $tagData, $loggedUserId);

            // checks for success
            if (!$fileEntity) {
                return $this->fail('Failed to upload file.');
            }

            // returns file data
            return $this->respondCreated([
                'id'    => $fileEntity->getId(),
                'name'  => $fileEntity->getName(),
                'type'  => $fileEntity->getType(),
                'owner' => $fileEntity->getIdOwner(),
            ]);

        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get file contents by ID (base64 encoded)
     * GET /file/{id}
     */
    public function getFileById($id = null)
    {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return $this->failValidationError('Invalid file ID.');
            }

            // checks if user is logged
            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            // gets file and checks if user has permission to access it
            $file = $this->fileRepository->findById($id);
            if (!$file || $file->getIdOwner() != $loggedUserId) {
                log_message('error', "Unauthorized access attempt by user {$loggedUserId} to file {$id}, owned by {$file?->getIdOwner()}");
                return $this->failForbidden('You do not have permission to access this file.');
            }

            // gets file contents
            $fileContents = $this->fileDataService->getFileById($id);
            if ($fileContents === null) {
                return $this->failNotFound("File with ID {$id} not found.");
            }

            // returns file
            return $this->respond([
                'id'      => $id,
                'content' => base64_encode($fileContents),
            ]);

        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * GET files with data
     */
    public function getFilesWithContent()
    {
        // checks if user is logged in
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // gets files' data for logged in user
        $files = $this->fileRepository->findByOwnerId($loggedUserId);

        // gets file content for all file data
        $result = [];
        foreach ($files as $file) {
            $content = $this->fileDataService->getFileById($file->getId());
            $result[] = [
                'id'        => $file->getId(),
                'name'      => $file->getName(),
                'type'      => $file->getType(),
                'path'      => $file->getPath(),
                'is_deleted'=> $file->getIsDeleted(),
                'content'   => base64_encode($content), // base64
            ];
        }

        // returns files with content
        return $this->respond($result);
    }


    /**
     * Download a file by ID
     * GET /file/{id}/download
     */
    public function downloadFile($id = null)
    {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return $this->failValidationError('Invalid file ID.');
            }

            // checks if user is logged in
            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            // gets file and checks if logged in user has permission
            $file = $this->fileRepository->findById($id);
            if (!$file || $file->getIdOwner() != $loggedUserId) {
                return $this->failForbidden('You do not have permission to download this file.');
            }

            // gets file from disk
            $response = $this->fileDataService->downloadFile($id);
            if (!$response) {
                return $this->failNotFound("File with ID {$id} not found or missing on disk.");
            }

            // returns file
            return $response;

        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
