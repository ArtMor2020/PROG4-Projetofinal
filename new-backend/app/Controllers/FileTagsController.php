<?php

namespace App\Controllers;

use App\Repositories\TagRepository;
use CodeIgniter\RESTful\ResourceController;
use App\Repositories\FileTagsRepository;
use App\Entities\FileTagsEntity;
use App\Repositories\FileRepository;

class FileTagsController extends ResourceController
{
    protected FileTagsRepository $fileTagsRepository;
    protected FileRepository $fileRepository;
    protected TagRepository $tagRepository;

    public function __construct()
    {
        $this->fileTagsRepository = new FileTagsRepository();
        $this->fileRepository = new FileRepository();
        $this->tagRepository = new TagRepository();
    }

    /**
     * Create a file-tag association (only if user owns the file)
     */
    public function create()
    {
        // gets data and logged user id
        $data = $this->request->getJSON(true);
        $loggedUserId = $this->request->user->userId ?? null;

        // checks data integrity
        if (empty($data['id_file']) || empty($data['id_tag'])) {
            return $this->failValidationErrors('file_id and tag_id are required.');
        }

        // gets filetags and checks for ownership
        $file = $this->fileRepository->findById((int)$data['id_file']);
        if (!$file || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to tag this file.');
        }
        
        // makes association var
        $association = [
            'id_file' => (int)$data['id_file'],
            'id_tag' => (int)$data['id_tag']
        ];

        // makes entity and creates relationship
        $entity = new FileTagsEntity($association);
        $fileTag = $this->fileTagsRepository->create($entity);

        // returns relationship
        return $fileTag
            ? $this->respondCreated($fileTag)
            : $this->fail('Failed to create file-tag association.');
    }

    /**
     * Get all tags on a given file (only if user owns the file)
     */
    public function tagsOnFile($fileId = null)
    {
        // gets data and logged user id
        $loggedUserId = $this->request->user->userId ?? null;
        $fileId = (int)$fileId;

        if ($fileId <= 0) {
            return $this->failValidationErrors('Valid fileId is required.');
        }

        // checks if logged user owns file
        $file = $this->fileRepository->findById($fileId);
        if (!$file || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to view tags for this file.');
        }

        // returns filetags on file
        $tags = $this->fileTagsRepository->findTagsOnFile($fileId);
        return $tags
            ? $this->respond($tags)
            : $this->failNotFound("No tags found for file ID {$fileId}");
    }

    /**
     * Get all files on a given tag (only files owned by logged-in user)
     */
    public function filesOnTag($tagId = null)
    {
        // gets data and logged user id
        $loggedUserId = $this->request->user->userId ?? null;
        $tagId = (int)$tagId;

        if ($tagId <= 0) {
            return $this->failValidationErrors('Valid tagId is required.');
        }

        // checks if logged user owns tag
        $tag = $this->tagRepository->findById($tagId);
        if(!$tag || $tag->getIdOwner() != $loggedUserId){
            log_message('error', $tag->getIdOwner(), $loggedUserId);
            return $this->failForbidden('You do not have permission to view tags for this file.');
        }

        // gets files on tag
        $filesOnTag = $this->fileTagsRepository->findFilesOnTag($tagId);

        // returns files
        return !empty($filesOnTag)
            ? $this->respond(array_values($filesOnTag))
            : $this->failNotFound("No files found for tag ID {$tagId} owned by you.");
    }

    /**
     * Delete a file-tag association by ID (only if user owns the file)
     */
    public function delete($id = null)
    {
        // gets logged user id
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        if ($id <= 0) {
            return $this->failValidationErrors('Association ID is required.');
        }

        // checks if user owns filetag
        $fileTag = $this->fileTagsRepository->findById($id);
        $file = $fileTag ? $this->fileRepository->findById($fileTag->getIdFile()) : null;
        if (!$file || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete this association.');
        }

        // deletes filetag
        $deleted = $this->fileTagsRepository->delete($id);
        return $deleted
            ? $this->respondDeleted(['message' => "Association {$id} deleted."])
            : $this->fail("Failed to delete association {$id}.");
    }

    /**
     * Delete all tags for a file (only if user owns the file)
     */
    public function deleteByFileId($fileId = null)
    {
        // gets logged user
        $loggedUserId = $this->request->user->userId ?? null;
        $fileId = (int)$fileId;

        if ($fileId <= 0) {
            return $this->failValidationErrors('Valid fileId is required.');
        }

        // checks if user owns filetag
        $file = $this->fileRepository->findById($fileId);
        if (!$file || $file->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete tags for this file.');
        }

        // deletes filetag
        $deleted = $this->fileTagsRepository->deleteByFileId($fileId);
        return $deleted
            ? $this->respondDeleted(['message' => "Tags for file {$fileId} deleted."])
            : $this->fail("Failed to delete tags for file {$fileId}.");
    }

    /**
     * Delete all files for a tag (only files owned by logged-in user)
     */
    public function deleteByTagId($tagId = null)
    {
        // gets logged user
        $loggedUserId = $this->request->user->userId ?? null;
        $tagId = (int)$tagId;

        if ($tagId <= 0) {
            return $this->failValidationErrors('Valid tagId is required.');
        }

        // checks if user owns tag
        $allFiles = $this->fileTagsRepository->findFilesOnTag($tagId) ?? [];
        $ownedFileIds = array_map(
            fn($fileTag) => $fileTag->getIdFile(),
            array_filter($allFiles, function ($fileTag) use ($loggedUserId) {
                $file = $this->fileRepository->findById($fileTag->getIdFile());
                return $file && $file->getIdOwner() === $loggedUserId;
            })
        );

        //check if user owns any filetags
        if (empty($ownedFileIds)) {
            return $this->failNotFound("No files found for tag ID {$tagId} owned by you.");
        }

        $deleted = true;
        foreach ($ownedFileIds as $fileId) {
            $deleted = $deleted && $this->fileTagsRepository->deleteByFileId($fileId);
        }

        return $deleted
            ? $this->respondDeleted(['message' => "Files for tag {$tagId} deleted successfully."])
            : $this->fail("Failed to delete files for tag {$tagId}.");
    }
}
