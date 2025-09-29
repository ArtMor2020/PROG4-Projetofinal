<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\FileRepository;
use App\Services\FileDataService;

class FileController extends ResourceController
{
    protected FileRepository $fileRepository;
    protected FileDataService $fileDataService;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->fileDataService = new FileDataService();
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

        // gets file and id of logged user
        $file = $this->fileRepository->findById($id);
        $loggedUserId = $this->request->user->userId ?? null;

        // if no file was found returns failNotFound
        if (!$file) {
            return $this->failNotFound("File with ID {$id} not found.");
        }
        // if file doesnt belong to logged user returns forbidden
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
        // check if user is authenticated
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // gets files for logged user
        $files = $this->fileRepository->findByOwnerId($loggedUserId);
        return $this->respond($files ?? []);
    }

    /**
     * Search files by name for the logged-in user
     * GET /files/search?name=...
     */
    public function getFilesByName($name = null)
    {

        // gets logged user id
        $loggedUserId = $this->request->user->userId ?? null;

        if (empty($name) || !$loggedUserId) {
            return $this->failValidationError('Missing search name or user not authenticated.');
        }

        //gets files for logged user
        $files = $this->fileRepository->findByNameAndOwnerId($name, $loggedUserId);
        $fullFiles = [];

        // formats files
        foreach ($files as $file) {
            $fullFiles[] = [
                'id'         => $file->getId(),
                'id_owner'   => $file->getIdOwner(),
                'name'       => $file->getName(),
                'type'       => $file->getType(),
                'path'       => $file->getPath(),
                'is_deleted' => $file->getIsDeleted(),
                'content'    => base64_encode($this->fileDataService->getFileById($file->getId()))
            ];
        }

        return $this->respond($fullFiles ?? []);
    }

    /**
     * Get files by type for the logged-in user
     * GET /files/type/{type}
     */
    public function getFilesByType($type = null)
    {
        // checks if there is a type and logged user
        $loggedUserId = $this->request->user->userId ?? null;
        if (empty($type) || !$loggedUserId) {
            return $this->failValidationError('Missing type or user not authenticated.');
        }

        // gets files for logged user
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
        // gets logged user id
        $loggedUserId = $this->request->user->userId ?? null;

        if ($id <= 0) {
            return $this->failValidationError('Invalid file ID.');
        }

        $file = $this->fileRepository->findById($id);

        // checks if user has permission to delete file
        if (!$file || !$loggedUserId || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete this file.');
        }

        // deletes file
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

        // gets request data
        $data = $this->request->getJSON(true);

        //gets logged user id
        $loggedUserId = $this->request->user->userId ?? null;

        // checks id and data
        if ($id <= 0 || empty($data)) {
            return $this->failValidationError('Invalid file ID or missing update data.');
        }

        // gets file
        $file = $this->fileRepository->findById($id);

        // check if logged user has permission
        if (!$file || !$loggedUserId || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to update this file.');
        }

        // sets same and data if they are valid
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
