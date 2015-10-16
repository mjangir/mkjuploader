<?php

namespace MkjUploader\Adapter;

use MkjUploader\File\FileInterface;
use MkjUploader\Object\ObjectInterface;

/**
 * This is the main adapter that actually handles file operations
 */
interface AdapterInterface {
    
    /**
     * Actually uploads the file and returns the file key
     *
     * @param string        $key        Key of the uploaded file for further usage
     * @param FileInterface $inputFile  File object that will be used to upload
     */
    public function upload($key, FileInterface $inputFile);

    /**
     * Get an output file object from storage directory for the given key.
     *
     * @param string $key    File key got at upload time
     *
     * @return ObjectInterface
     */
    public function get($key);

    /**
     * Move the uploaded file object to another file
     *
     * @param string        $key            Key of the file that will be moved
     * @param FileInterface $outputFile     New moved file object
     */
    public function move($key, FileInterface $outputFile);

    /**
     * Check if the storage directory contains a file for the given key.
     *
     * @param string $key    Key of the file to check in storage folder
     *
     * @return bool
     */
    public function has($key);

    /**
     * Delete an file object from storage directory for the given key.
     *
     * @param string $key
     */
    public function delete($key);
}
