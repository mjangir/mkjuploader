<?php

namespace MkjUploader\Adapter;

use MkjUploader\File\Input;
use MkjUploader\File\FileInterface;
use MkjUploader\Object;

/**
 * Upload files locally
 */
class Local implements AdapterInterface {
    
    /**
     * The upload path where the files will be stored.
     *
     * @var string
     */
    protected $uploadPath;

    /**
     * The public path from where uploaded files are served. Generally it is our uploads folder
     *
     * @var string
     */
    protected $publicPath;

    /**
     * Constructor of the class
     * 
     * @param string $uploadPath        Where the files will be uploaded
     * @param string $publicPath        From where the files will be served means outer container
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($uploadPath, $publicPath) {
        
        //Check if upload path is provided
        if (empty($uploadPath)) {
            throw new \InvalidArgumentException('Upload path must be provided');
        }

        //Check if the upload path is writeable or not
        if (!is_writeable($uploadPath)) {
            throw new \RuntimeException("$uploadPath is not a writeable directory");
        }

        //Remove the forward or reverse slashes in right side of the path
        $this->uploadPath = rtrim($uploadPath, '\\/');
        $this->publicPath = rtrim($publicPath, '\\/');
    }

    /**
     * Get the full file path of a file with the given id and an optional path prefix.
     *
     * @param string $key      Key of the file
     * @param string $prefix   A path prefix that you want behind the file
     *
     * @return string
     */
    protected function getFilepath($key, $prefix = null) {
        
        //If prefix is not provided
        if ($prefix === null) {
            $prefix = $this->uploadPath;
        } else {
            
            //Clean the prefix
            $prefix = rtrim($prefix, '\\/');
        }

        return $prefix . '/' . $key;
    }

    /**
     * 
     * @param string        $key
     * @param FileInterface $inputFile
     * @throws \RuntimeException If file could not move successfully
     */
    public function upload($key, FileInterface $inputFile) {
        
        $uploadPath = $this->getFilepath($key);
        
        //Get the directory path of uploading file
        $uploadDir  = dirname($uploadPath);
        
        //Check if directory is already created. If not then create and permit it to write.
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        //Check if the file was uploaded through form
        if ($inputFile instanceof Input) {
            $uploaded = move_uploaded_file($inputFile->getPath(), $uploadPath);
        } else {
            $uploaded = rename($inputFile->getPath(), $uploadPath);
            chmod($uploadPath, 0660);
        }

        //If file not uploaded successfully then revert all the created directories
        if (!$uploaded) {
            $this->revert($uploadPath);
            throw new \RuntimeException("File {$inputFile->getPath()} could not be moved");
        }
    }

    /**
     * Provides the complete output file object for the given key
     * 
     * @param   string $key                 Key of the file
     * 
     * @return \MkjUploader\Object\Local
     * 
     * @throws \RuntimeException
     */
    public function get($key) {
        
        //Check if the file exists for this key. If not then throw exception
        if (!$this->has($key)) {
            throw new \RuntimeException("File could not be found ($key)");
        }

        //If file found then create the actually output object for this file
        $object = new Object\Local($this->getFilepath($key), $this->getFilepath($key, $this->publicPath));
        
        return $object;
    }

    /**
     * Move the file from its source to given file object destination path
     * 
     * @param string        $key
     * 
     * @param FileInterface $outputFile
     * 
     * @throws \RuntimeException    if could not copied successfully
     */
    public function move($key, FileInterface $outputFile) {
        
        $sourcePath = $this->getFilepath($key);
        $destination= $outputFile->getPath();

        $copied = copy($sourcePath, $destination);

        //If file not copied successfully then throw exception
        if (!$copied) {
            throw new \RuntimeException("File could not be copied ({$sourcePath})");
        }
    }

    /**
     * Check if the file exists in the storage folder for given key
     * 
     * @param  string $key  Key of the file
     * 
     * @return bool
     * 
     * @throws \InvalidArgumentException    If Key not provided
     */
    public function has($key) {
        
        //If key is empty
        if (empty($key)) {
            throw new \InvalidArgumentException('Key must be provided');
        }

        return file_exists($this->getFilepath($key));
    }

    /**
     * Delete a file for given Key
     * 
     * @param  string $key          Key of the file
     * 
     * @throws \RuntimeException    If file does not exist or could not deleted successfully
     */
    public function delete($key) {
        
        //Check if file exists for the given key
        if (!$this->has($key)) {
            throw new \RuntimeException("File could not be found ($key)");
        }

        //Get file path to delete it
        $filePath = $this->getFilepath($key);

        //Delete the file
        $deleted = unlink($filePath);

        //Throw exeption if could not deleted successfully
        if (!$deleted){
            throw new \RuntimeException("File could not be deleted ($filePath)");
        }

        //Also delete all parent directories
        $this->revert($filePath);
    }

    /**
     * Delete all empty parent directories in reverse order
     *
     * @param string $path
     */
    protected function revert($path) {
        
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        
        while ($directory != $this->uploadPath) { //Does not remove provided upload path
            
            //If still there is a parent directory found
            if (count(glob($directory .'/*'))) {
                    break;
            }
            
            //Remove the directory
            rmdir($directory);
            
            //Get parent of removed directory
            $directory = dirname($directory);
        }
    }
}
