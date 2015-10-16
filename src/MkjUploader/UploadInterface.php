<?php

namespace MkjUploader;

use MkjUploader\Adapter\AdapterInterface;
use MkjUploader\File\FileInterface;
use MkjUploader\Object\ObjectInterface;

/**
 * Upload Container to actually upload the files
 */
interface UploadInterface {
    
    /**
     * Upload a file and return the Id of that uploaded object
     *
     * Input must be a $_FILES array or an object of type FileInterface.
     *
     * @param array|FileInterface $inputFile
     *
     * @return string
     */
    public function upload($inputFile);

    /**
     * Get the file object of the given file Id
     *
     * @param string $id
     *
     * @return ObjectInterface
     */
    public function get($id);

    /**
     * Create a copy of any uploaded file as new file
     *
     * @param string $id
     *
     * @return FileInterface
     */
    public function move($id);

    /**
     * Check if the file exists with the given Id
     *
     * @param string $id
     *
     * @return boolean
     */
    public function has($id);

    /**
     * Delete a file of given Id
     *
     * @param string $id
     */
    public function delete($id);

    /**
     * Set the valid file adapter in container
     * 
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter);

    /**
     * Get the adapter object
     * 
     * @return AdapterInterface
     */
    public function getAdapter();
}
