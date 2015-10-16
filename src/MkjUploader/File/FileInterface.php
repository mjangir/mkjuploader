<?php

namespace MkjUploader\File;

/**
 * Temporary File Object
 */
interface FileInterface {
    
    /**
     * Get the base name of the file
     * 
     * @return string
     */
    public function getBasename();

    /**
     * Get the name of the file
     * 
     * @return string
     */
    public function getFilename();

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @return string
     */
    public function getPath();
}
