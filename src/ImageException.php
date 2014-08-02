<?php
namespace danperron\imagelib;

/**
 * Description of ImageException
 *
 * @author dan
 */
class ImageException extends \Exception {
    const ERR_UNKNOWN = 0;
    const ERR_LOAD = 1;
    const ERR_SAVE = 2;
    const ERR_BOUNDS = 3;
  
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    
}