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
            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            $fileData = $this->request->getFile('file');
            $tagNames = $this->request->getVar('tags'); // array

            if (!$fileData || !$fileData->isValid()) {
                return $this->fail('Invalid or missing file.');
            }

            if (!is_array($tagNames)) {
                $tagNames = [];
            }

            $fileEntity = $this->fileDataService->uploadFileWithTags($fileData, $tagNames, $loggedUserId);

            if (!$fileEntity) {
                return $this->fail('Failed to upload file.');
            }

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

            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            $file = $this->fileRepository->findById($id);
            if (!$file || $file->getIdOwner() !== $loggedUserId) {
                return $this->failForbidden('You do not have permission to access this file.');
            }

            $fileContents = $this->fileDataService->getFileById($id);
            if ($fileContents === null) {
                return $this->failNotFound("File with ID {$id} not found.");
            }

            return $this->respond([
                'id'      => $id,
                'content' => base64_encode($fileContents),
            ]);

        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
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

            $loggedUserId = $this->request->user->userId ?? null;
            if (!$loggedUserId) {
                return $this->failUnauthorized('User not authenticated.');
            }

            $file = $this->fileRepository->findById($id);
            if (!$file || $file->getIdOwner() !== $loggedUserId) {
                return $this->failForbidden('You do not have permission to download this file.');
            }

            $response = $this->fileDataService->downloadFile($id);
            if (!$response) {
                return $this->failNotFound("File with ID {$id} not found or missing on disk.");
            }

            return $response;

        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
