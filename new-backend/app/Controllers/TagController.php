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
        // gets data and logged user
        $data = $this->request->getJSON(true);
        $loggedUserId = $this->request->user->userId ?? null;

        // checks if user is logged in
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // checks if data is valid
        if (empty($data['name'])) {
            return $this->failValidationErrors('Tag name is required.');
        }

        // Force owner_id from JWT
        $data['id_owner'] = $loggedUserId;
        log_message('debug', 'Creating tag with data: ' . json_encode($data));

        // makes new tag entity and creates tag
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
        // get if of user logged in
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        // checks if id and logged user id are valod
        if ($id <= 0 || !$loggedUserId) {
            return $this->failValidationErrors('Valid tag ID is required and user must be authenticated.');
        }

        // gets tags and checks if logged user owns them
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
        // gets logged user id and checks if its valid
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // gets tags for logged user
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
        // checks user id and validates it
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // validates name
        if (empty($name)) {
            return $this->failValidationErrors('Missing name parameter.');
        }

        // gets tags owned by user by their name
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
        // validates id
        if (!$id) {
            return $this->failValidationErrors('Tag ID is required.');
        }

        // gets logged user and validates it
        $loggedUserId = $this->request->user->userId ?? null;
        if (!$loggedUserId) {
            return $this->failUnauthorized('User not authenticated.');
        }

        // checks for user permission
        $existingTag = $this->tagRepository->findById((int)$id);
        if (!$existingTag || $existingTag->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to update this tag.');
        }

        // gets data from request
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

        // updates tag
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
        // gets logged user id
        $loggedUserId = $this->request->user->userId ?? null;
        $id = (int)$id;

        // checks if user and tag id are valid
        if ($id <= 0 || !$loggedUserId) {
            return $this->failValidationErrors('Valid tag ID is required and user must be authenticated.');
        }

        // checks if user owns tag
        $tag = $this->tagRepository->findById($id);
        if (!$tag || $tag->getIdOwner() != $loggedUserId) {
            return $this->failForbidden('You do not have permission to delete this tag.');
        }

        // deletes tag
        $deleted = $this->tagRepository->deleteTag($id);
        return $deleted
            ? $this->respondDeleted(['message' => "Tag {$id} deleted successfully."])
            : $this->fail("Failed to delete tag {$id}.");
    }
}
