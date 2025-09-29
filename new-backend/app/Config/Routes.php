<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('api', function(RouteCollection $routes) {
    /**
     * -----------------------
     * Public routes (no JWT required)
     * -----------------------
     */
    $routes->post('auth/login', 'UserController::authenticate');   // login
    $routes->post('auth/register', 'UserController::register');    // register

    /**
     * -----------------------
     * Protected routes (JWT required)
     * -----------------------
     */
    $routes->group('', ['filter' => 'jwt'], function(RouteCollection $routes) {

        /**
         * -----------------------
         * User routes
         * -----------------------
         */
        $routes->put('update_user', 'UserController::update');    // update user
        $routes->delete('delete_user', 'UserController::delete'); // soft delete user

        /**
         * -----------------------
         * File routes
         * -----------------------
         */
        $routes->get('files', 'FileController::getFilesByOwner');                   // all files for logged-in user
        $routes->get('files/(:num)', 'FileController::getFile/$1');                 // get single file
        $routes->put('files/(:num)', 'FileController::updateFile/$1');              // update file
        $routes->delete('files/(:num)', 'FileController::deleteFile/$1');           // delete file
        $routes->get('files/search/(:any)', 'FileController::getFilesByName/$1');   // search files by name
        $routes->get('files/type/(:any)', 'FileController::getFilesByType/$1');     // filter by type

        /**
         * -----------------------
         * FileData routes (raw content / download / upload)
         * -----------------------
         */
        $routes->post('file/upload', 'FileDataController::uploadFileWithTags');      // upload with tags
        $routes->get('file/(:num)', 'FileDataController::getFileById/$1');           // get base64 content
        $routes->get('file/content', 'FileDataController::getFilesWithContent');     // get raw content
        $routes->get('file/download/(:num)', 'FileDataController::downloadFile/$1'); // download file

        /**
         * -----------------------
         * Tag routes
         * -----------------------
         */
        $routes->post('tags', 'TagController::create');                            // create tag
        $routes->get('tags/(:num)', 'TagController::show/$1');                     // get tag by ID
        $routes->get('tags', 'TagController::byOwner');                            // all tags for logged-in user
        $routes->get('tags/search/(:any)', 'TagController::searchByName/$1');      // search tags
        $routes->put('tags/(:num)', 'TagController::update/$1');                   // update tag
        $routes->delete('tags/(:num)', 'TagController::delete/$1');                // delete tag

        /**
         * -----------------------
         * FileTag (many-to-many) routes
         * -----------------------
         */
        $routes->post('file-tags', 'FileTagsController::create');                          // create association
        $routes->get('file-tags/file/(:num)', 'FileTagsController::tagsOnFile/$1');        // all tags on file
        $routes->get('file-tags/tag/(:num)', 'FileTagsController::filesOnTag/$1');         // all files on tag
        $routes->delete('file-tags/(:num)', 'FileTagsController::delete/$1');              // delete association by ID
        $routes->delete('file-tags/file/(:num)', 'FileTagsController::deleteByFileId/$1'); // delete all tags on file
        $routes->delete('file-tags/tag/(:num)', 'FileTagsController::deleteByTagId/$1');   // delete all files for tag 
    });
});

