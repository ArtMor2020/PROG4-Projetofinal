<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\FileRepository;

class FileController extends ResourceController
{
    protected FileRepository $fileRepository;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
    }

    /**
     * Get a file by its ID (only if user owns it)
     * GET /files/{id}
     */
    public function getFile($id = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->failValidationError('Invalid file ID.');
        }

        $file = $this->fileRepository->findById($id);
        $loggedUserId = $this->request->user->userId ?? null;

        if (!$file) {
            return $this->failNotFound("File with ID {$id} not found.");
        }
        if (!$loggedUserId || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to view this file.');
        }

        return $this->respond($file);
    }

    /**
     * Get all files owned by the logged-in user
     * GET /files
     */
    public function getFilesByOwner()
    {
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        $files = $this->fileRepository->findByOwnerId($loggedUserId);
        return $this->respond($files ?? []);
    }

    /**
     * Search files by name for the logged-in user
     * GET /files/search?name=...
     */
    public function getFilesByName($name = null)
    {

        $loggedUserId = $this->request->user->userId ?? null;
        log_message('info',"Searching files with name: " . $name . " for user ID: " . $loggedUserId);

        if (empty($name) || !$loggedUserId) {
            return $this->failValidationError('Missing search name or user not authenticated.');
        }

        $files = $this->fileRepository->findByNameAndOwnerId($name, $loggedUserId);
        return $this->respond($files ?? []);
    }

    /**
     * Get files by type for the logged-in user
     * GET /files/type/{type}
     */
    public function getFilesByType($type = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        if (empty($type) || !$loggedUserId) {
            return $this->failValidationError('Missing type or user not authenticated.');
        }

        $files = $this->fileRepository->findByTypeAndOwnerId($type, $loggedUserId);
        return $this->respond($files ?? []);
    }

    /**
     * Delete a file by ID (must belong to logged-in user)
     * DELETE /files/{id}
     */
    public function deleteFile($id = null)
    {
        $id = (int) $id;
        $loggedUserId = $this->request->user->userId ?? null;

        if ($id <= 0) {
            return $this->failValidationError('Invalid file ID.');
        }

        $file = $this->fileRepository->findById($id);
        if (!$file || !$loggedUserId || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete this file.');
        }

        $success = $this->fileRepository->deleteById($id);
        return $success
            ? $this->respondDeleted(['message' => "File {$id} deleted successfully."])
            : $this->fail('Failed to delete file.');
    }

    /**
     * Update a file (must belong to logged-in user)
     * PUT /files/{id}
     */
    public function updateFile($id = null)
    {
        $id = (int) $id;
        $data = $this->request->getJSON(true);
        $loggedUserId = $this->request->user->userId ?? null;

        if ($id <= 0 || empty($data)) {
            return $this->failValidationError('Invalid file ID or missing update data.');
        }

        $file = $this->fileRepository->findById($id);
        if (!$file || !$loggedUserId || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to update this file.');
        }

        if (isset($data['name'])) {
            $file->setName($data['name']);
        }
        if (isset($data['type'])) {
            $file->setType($data['type']);
        }

        $success = $this->fileRepository->update($file);
        return $success
            ? $this->respondUpdated($file)
            : $this->fail('Failed to update file.');
    }
}
