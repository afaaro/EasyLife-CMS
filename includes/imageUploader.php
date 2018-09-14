<?php

/**
 * ImageUploader class
 * 
 * @author Muni <muni@smarttutorials.net>
 * 
 * @link www.smarttutorials.net
 */
class ImageUploader {

    var $allowedExts = ["gif", "jpeg", "jpg", "png"];
    var $path = BASEDIR."uploader";
    var $maxSize = 5*1024;
    private $_data = [];

    

    /**
     * Image Upload Handler
     * 
     * @return array|boolean
     */
    public function upload(array $images = [])
    {
        $imgs = [];
        $this->_checkDirExists();
        foreach ($images as $key => $image) {
            $i = $key + 1;
            $image_name = $image['name'];
            //get image extension
            $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            //assign unique name to image
            $name = $i*time().'.'.$ext;

            //image size calcuation in KB
            $image_size = $image["size"] / 1024;
            $image_flag = true;
            if($this->_checkAllowedExtensionAndSize($ext, $image_size)) {
                $image_flag = true;
            } else {
                $image_flag = false;
                $this->_data[$key]['success'] = false;
                $this->_data[$key]['message'] = 'Maybe ' . $image_name . ' exceeds max ' . $this->maxSize . ' KB size or incorrect file extension';
            }

            if ($image["error"] > 0) {
                $image_flag = false;
                $this->_data[$key]['success'] = false;
                $this->_data[$key]['message'] = '<br/> '.$image_name.' Image contains error - Error Code : '.$image["error"];
            }

            if ($image_flag) {
                $src = $name;
                move_uploaded_file($image["tmp_name"], $this->path.'/'.$src);
                $this->_data[$key]['success'] = true;
                $this->_data[$key]['src'] = $src;
                $this->_data[$key]['message'] = 'Uploaded successfully.';

                $imgs[$key]['name'] = $src;
            }
        }    
        return $this->_data;
    }   

    /**
     *  CheckDirExists method
     *  If folder not exists creates one
     */
    private function _checkDirExists()
    {
        try {
            //create directory if not exists
            if (!file_exists($this->path)) {
                mkdir($this->path, 0777, true);
            }
        } catch(Exception $e) {
            throw new Exception($e->message);
        }
    }

    /**
     * CheckAllowed Extension & Size
     * 
     * @return boolean true|false
     */
    private function _checkAllowedExtensionAndSize($ext, $image_size) 
    {
        if (in_array($ext, $this->allowedExts) && $image_size < $this->maxSize) { 
            return true;
        }  
        return false;
    }

}