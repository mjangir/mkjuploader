<?php

namespace MkjUploader;

use MkjUploader\Adapter\AdapterInterface;
use MkjUploader\File\FileInterface;
use MkjUploader\File\Input;

/**
 * This is the main upload class to handle uploading operations
 */
class Upload implements UploadInterface {
    
    /**
     * Adapter used to perform the actual file operations like Local File or Amazon etc.
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Temporary directory where the files will be stored temporary
     * 
     * @var string
     */
    protected $tmpDir;

    /**
     * Constructor of the class
     * 
     * @param AdapterInterface $adapter
     * @param string $tempDir
     */
    public function __construct(AdapterInterface $adapter = NULL, $tmpDir = NULL) {
        
        if(!ini_get('upload_tmp_dir')) {
            $this->tmpDir = sys_get_temp_dir();
        }
        
        if ($adapter) {
            $this->setAdapter($adapter);
        } 
        
        if ($tmpDir) {
            $this->tmpDir = $tmpDir;
        }
    }

    /**
     * Set the adapter
     * 
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter) {
        
        $this->adapter = $adapter;
    }

    /**
     * Get the adapter
     * 
     * @return AdapterInterface
     */
    public function getAdapter() {
        
        return $this->adapter;
    }

    /**
     * Actually uploads the file
     * 
     * @param File\Input $inputFile   File input like array or plain object
     * 
     * @return string       Id of the new file
     * 
     * @throws \InvalidArgumentException    If input was not an interface of file class
     */
    public function upload($inputFile) {
        
        if (is_array($inputFile)) {
            $inputFile = new Input($inputFile);
        }

        if (!$inputFile instanceof FileInterface) {
            throw new \InvalidArgumentException(sprintf(
                    "Input should be a valid file object implementing %s"
                    , __NAMESPACE__ .'\File\FileInterface'
            ));
        }

        $id = $this->generateDirectoryStructure($inputFile->getBasename());
        
        if ($this->has($id)) {
            return $this->upload($inputFile);
        }

        $this->adapter->upload($id, $inputFile);

        return $id;
    }

    /**
     * Get the uploaded file object
     * 
     * @param type $id  Id of the uploaded file
     * 
     * @return object
     * 
     * @throws \InvalidArgumentException If Id not provided
     */
    public function get($id) {
        
        if (empty($id)) {
            throw new \InvalidArgumentException('ID must be provided');
        }

        return $this->adapter->get($id);
    }

    /**
     * Move a file to another location
     * 
     * @param type $id  Id of the uploaded file
     * 
     * @return \MkjUploader\File\File
     * 
     * @throws \InvalidArgumentException    If Id not provided
     */
    public function move($id) {
        
        if (empty($id)) {
            throw new \InvalidArgumentException('ID must be provided');
        }

        $file       = $this->adapter->get($id);
        $outputFile = new File\File($file->getBasename(), $this->generateTemporaryPath());

        $this->adapter->download($id, $outputFile);

        return $outputFile;
    }

    /**
     * Check if the file exists with the given Id
     * 
     * @param type $id  Id of the uploaded file
     * 
     * @return boolean
     */
    public function has($id) {
        
        if (empty($id)) {
            return false;
        }

        return $this->adapter->has($id);
    }

    /**
     * Delete a file of given Id
     * 
     * @param type $id  Id of the uploaded file
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function delete($id) {
        
        if (empty($id)) {
            throw new \InvalidArgumentException('ID must be provided');
        }

        return $this->adapter->delete($id);
    }

    /**
     * Generate unique file directory structure sequence
     *
     * @param string $basename
     *
     * @return string
     */
    protected function generateDirectoryStructure($basename) {
        
        $basename = $this->clean($basename);

        $hashValue  = hash('sha1', uniqid(rand(), true).rand().$basename);
        $splitName  = str_split(substr($hashValue, 0, 3));
        $prefixDir  = implode('/', $splitName);

        return $prefixDir .'/'. $hashValue .'/'. $basename;
    }

    /**
     * @return string
     */
    protected function generateTemporaryPath() {
        return tempnam($this->tmpDir, 'mjangir');
    }

    /**
     * Sanitize the basename and remove any special characters
     * 
     * @param string $basename
     *
     * @return string
     */
    protected function clean($basename) {
        
        $fileName = pathinfo($basename, PATHINFO_FILENAME);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        $fileName = str_replace(' ', '-', $fileName);
        $fileName = preg_replace('/[^a-z0-9\.\-\_]/i', '', $fileName);
        $fileName = substr($fileName, 0, 120);

        $fileName = trim($fileName, '-_.') ?: 'no-file';

        return $fileName . ($extension ? '.'. $extension : '');
    }
}
