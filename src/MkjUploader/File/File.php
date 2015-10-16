<?php

namespace MkjUploader\File;

/**
 * Main input file object to upload
 */
class File implements FileInterface {
    
    /**
     * Base name of the file
     * 
     * @var string
     */
    protected $baseName;

    /**
     * Path of the file
     * 
     * @var string
     */
    protected $path;

    /**
     * @param string $baseName  Base name of the file
     * @param string $path      Path of the file
     *
     * @throws \RuntimeException
     */
    public function __construct($baseName, $path) {
        
        $this->baseName = $baseName;
        $this->path     = $path;
    }

    /**
     * Get the base name of file
     *
     * @return $baseName
     */
    public function getBasename() {
        return $this->baseName;
    }

    /**
     * Get the name of the file
     *
     * @return string
     */
    public function getFilename() {
        return pathinfo($this->baseName, PATHINFO_FILENAME);
    }

    /**
     * Get the extension of the file
     *
     * @return string
     */
    public function getExtension() {
        return pathinfo($this->baseName, PATHINFO_EXTENSION);
    }

    /**
     * Get the path of the file
     *
     * @return $path
     */
    public function getPath() {
        return $this->path;
    }
}
