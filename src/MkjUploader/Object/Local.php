<?php

namespace MkjUploader\Object;

use DateTime;

/**
 * Refers to the local file object in the directory
 */
class Local implements ObjectInterface
{
    /**
     * @var string
     */
    protected $localPath;

    /**
     * @var string
     */
    protected $publicPath;

    /**
     * Constructor of the class
     * 
     * @param string $localPath     The upload path of the file.
     * @param string $publicPath    The public serving path of the file.
     */
    public function __construct($localPath, $publicPath) {
        
        $this->localPath = $localPath;
        $this->publicPath = $publicPath;
    }

    /**
     * Get file's path
     * 
     * @return string
     */
    public function getLocalPath() {

        return $this->localPathpath;
    }

    /**
     * Get the public serving path of the file for e.g. http://domain/uploads/xyz.jpg
     * 
     * @return string
     */
    public function getPublicPath() {

        return $this->publicPath;
    }

    /**
     * Get the uploaded file's content in string
     * 
     * @return string
     */
    public function getContent() {

        ob_start();
        readfile($this->localPath);
        return ob_get_clean();
    }

    /**
     * Get the basename of the uploaded file
     * 
     * @return string
     */
    public function getBasename() {

        return pathinfo($this->localPath, PATHINFO_BASENAME);
    }

    /**
     * Get the extension of the uploaded file
     * 
     * @return string
     */
    public function getExtension() {

        return pathinfo($this->localPath, PATHINFO_EXTENSION);
    }

    /**
     * Get the size of the uploaded file
     * 
     * @return integer
     */
    public function getContentLength() {

        return filesize($this->localPath);
    }

    /**
     * Get the mime type of the uploaded file
     * 
     * @return string
     */
    public function getContentType() {

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        return $fileInfo->file($this->localPath);
    }

    /**
     * Get last modified time of the uploaded file
     * 
     * @return DateTime
     */
    public function getLastModified() {

        $time = new DateTime;
        $time->setTimestamp(filemtime($this->localPath));
        return $time;
    }

    /**
     * Return the public path if object gets converted into string
     * 
     * @return string
     */
    public function __toString() {
        return $this->publicPath;
    }
}
