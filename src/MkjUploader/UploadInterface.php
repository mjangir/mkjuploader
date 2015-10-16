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
     * Upload a file and return the key of that uploaded object
     *
     * Input must be a $_FILES array or an object of type FileInterface.
     *
     * @param array|FileInterface $inputFile
     *
     * @return string
     */
    public function upload($inputFile);

    /**
     * Get the file object of the given file key
     *
     * @param string $key
     *
     * @return ObjectInterface
     */
    public function get($key);

    /**
     * Create a copy of any uploaded file as new file
     *
     * @param string $key
     *
     * @return FileInterface
     */
    public function move($key);

    /**
     * Check if the file exists with the given key
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has($key);

    /**
     * Delete a file of given key
     *
     * @param string $key
     */
    public function delete($key);

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
