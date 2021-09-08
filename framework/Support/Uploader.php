<?php

/**
 * @copyright 2021 - N'Guessan Kouadio Elisée (eliseekn@gmail.com)
 * @license MIT (https://opensource.org/licenses/MIT)
 * @link https://github.com/eliseekn/tinymvc
 */

namespace Framework\Support;

use Framework\System\Storage;

/**
 * Manage uploaded files
 */
class Uploader
{    
    /**
     * uploaded file
     *
     * @var array
     */
    private $file = [];
    
    /**
     * @var string
     */
    public $filename = '';

    /**
     * @var array
     */
    private $allowed_extensions = [];

    /**
     * __construct
     *
     * @param  array $file
     * @param  array $allowed_extensions
     * @param  int $max_size
     * @return void
     */
    public function __construct(array $file, array $allowed_extensions)
    {
        $this->file = $file;
        $this->allowed_extensions = $allowed_extensions;
    }
    
    /**
     * get original filename
     *
     * @return string
     */
    public function getOriginalFilename(): string
    {
        return $this->file['name'] ?? '';
    }
    
    /**
     * get temp filename
     *
     * @return string
     */
    public function getTempFilename(): string
    {
        return $this->file['tmp_name'] ?? '';
    }
        
    /**
     * get file type
     *
     * @return string
     */
    public function getFileType(): string
    {
        return $this->file['type'] ?? '';
    }
    
    /**
     * get file extension
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return get_file_extension($this->getOriginalFilename());
    }
        
    /**
     * check if file extension is allowed
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        return empty($this->allowed_extensions) ? true : in_array(strtolower($this->getFileExtension()), $this->allowed_extensions);
    }
        
    /**
     * check if file is uploaded
     *
     * @return bool
     */
    public function isUploaded(): bool
    {
        return is_uploaded_file($this->getTempFilename());
    }

    /**
     * check if file is oversize
     * 
     * @param  int $max_size
     * @return bool
     */
    public function isOverSized(int $max_size): bool
    {
        return $this->getFileSize() > $max_size;
    }
        
    /**
     * get file size
     *
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->file['size'] ?? 0;
    }
        
    /**
     * convert file size from byte to KB or MB
     *
     * @return string
     */
    public function fileSizeToString(): string
    {
        if ($this->getFileSize() <= 0) {
            return '0 KB';
        }

        $bytes = $this->getFileSize() / 1024;

        return $bytes > 1024 ? number_format($bytes/1024, 1) . ' MB' : number_format($bytes, 1) . ' KB';
    }
    
    /**
     * get file upload error
     *
     * @return string
     */
    public function getError(): string
    {
        if ($this->file['error'] != UPLOAD_ERR_OK) {
            switch($this->file['error']) {
                case UPLOAD_ERR_INI_SIZE: return 'Uploaded file exceeds the upload_max_filesize directive in php.ini';
                case UPLOAD_ERR_FORM_SIZE: return 'Uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                case UPLOAD_ERR_PARTIAL: return 'Uploaded file was only partially uploaded.';
                case UPLOAD_ERR_NO_FILE: return 'No file was uploaded';
                case UPLOAD_ERR_NO_TMP_DIR: return 'Missing a temporary folder';
                case UPLOAD_ERR_CANT_WRITE: return 'Failed to write file to disk';
                case 8: return 'File upload stopped by extension';
                default: return 'Unknow error';
            }
        }
    }

    /**
     * save uploaded file
     *
     * @param  string $destination
     * @param  string|null $filename
     * @return bool
     */
    public function save(string $destination, ?string $filename = null): bool
    {
        $this->filename = $filename ?? $this->getOriginalFilename();

        //create destination directory if not exists
        if (!Storage::path($destination)->isDir()) {
            if (!Storage::path($destination)->createDir('', true)) {
                return false;
            }
        }

        return move_uploaded_file($this->getTempFilename(), Storage::path($destination)->file($this->filename));
    }
}
