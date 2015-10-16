<?php

namespace MkjUploader\File;

/**
 * Temporary file object from $_FILES array
 */
class Input extends File {
    
    public function __construct(array $file) {
        
            //Check if provided input is a valid $_FILES array
            if (!isset($file['name'])) {
                throw new \InvalidArgumentException("Input file array does not contain the 'name' key");
            }
            
            if (!isset($file['tmp_name'])) {
                throw new \InvalidArgumentException("Input array does not contain the 'tmp_name' key");
            }
            
            //Execute parent constructor and create File object
            parent::__construct($file['name'], $file['tmp_name']);
    }
}
