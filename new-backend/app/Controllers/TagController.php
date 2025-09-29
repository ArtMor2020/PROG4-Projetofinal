<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Repositories\TagRepository;
use App\Entities\TagEntity;

class TagController extends ResourceController
{
    protected TagRepository $tagRepository;

    public function __construct()
    {
        $this->tagRepository = new TagRepository();
    }

    /**
     * Create a new tag (only for logged-in user)
     */
    public function create()
    {
        $data = $this->request->getJSON(true);
        $loggedUserId = $this->request->user->userId ?? null;

        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        if (empty($data['name'])) {
            return $this->failValidationErrors('Tag name is required.');
        }

        // Force owner_id from JWT
        $data['id_owner'] = $loggedUserId;
        log_message('debug', 'Creating tag with data: ' . json_encode($data));

        $tag = new TagEntity($data);
        $created = $this->tagRepository->create($tag);

        return $created
            ? $this->respondCreated($created)
            : $this->fail('Failed to create tag.');
    }


    /**
     * Get a tag by ID (only if owned by logged-in user)
     */
    public function show($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        if ($id <= 0 || !$loggedUserId) {
            return $this->failValidationErrors('Valid tag ID is required and user must be authenticated.');
        }

        $tag = $this->tagRepository->findById($id);
        if (!$tag || $tag->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to view this tag.');
        }

        return $this->respond($tag);
    }

    /**
     * Get all tags for the logged-in user
     * GET /api/tags
     */
    public function byOwner()
    {
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        $tags = $this->tagRepository->findAllByOwnerId($loggedUserId);

        return $tags
            ? $this->respond($tags)
            : $this->failNotFound("No tags found for your account.");
    }


    /**
     * Search tags by name for the logged-in user (fuzzy match)
     * GET /api/tags/search?name=...
     */
    public function searchByName($name)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        if (empty($name)) {
            return $this->failValidationErrors('Missing name parameter.');
        }

        $tags = $this->tagRepository->findByNameAndOwnerId($name, $loggedUserId);

        return $tags
            ? $this->respond($tags)
            : $this->failNotFound("No matching tags found for name '{$name}'.");
    }


    /**
     * Update a tag (only if owned by logged-in user)
     */
    public function update($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors('Tag ID is required.');
        }

        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        $existingTag = $this->tagRepository->findById((int)$id);
        if (!$existingTag || $existingTag->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to update this tag.');
        }

        $data = $this->request->getJSON(true);
        if (empty($data)) {
            return $this->failValidationError('No update data provided.');
        }

        // Only allow updating the name, desc and color; owner_id cannot be changed
        if (isset($data['name'])) {
            $existingTag->setName($data['name']);
        }

        if (isset($data['description'])) {
            $existingTag->setDescription($data['description']);
        }

        if (isset($data['color'])) {
            $existingTag->setColor($data['color']);
        }

        $updated = $this->tagRepository->updateTag($existingTag);

        return $updated
            ? $this->respond(['message' => "Tag {$id} updated successfully."])
            : $this->fail("Failed to update tag {$id}.");
    }

    /**
     * Delete a tag (only if owned by logged-in user)
     */
    public function delete($id = null)
    {
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        if ($id <= 0 || !$loggedUserId) {
            return $this->failValidationErrors('Valid tag ID is required and user must be authenticated.');
        }

        $tag = $this->tagRepository->findById($id);
        if (!$tag || $tag->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete this tag.');
        }

        $deleted = $this->tagRepository->deleteTag($id);
        return $deleted
            ? $this->respondDeleted(['message' => "Tag {$id} deleted successfully."])
            : $this->fail("Failed to delete tag {$id}.");
    }
}
