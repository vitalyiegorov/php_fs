<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 03.12.2014
 * Time: 20:26
 */
namespace samson\fs;

use samson\core\CompressableService;

/**
 * Abstract IFileService implementation
 * with higher level functions implemented
 * @package samson\fs
 */
abstract class AbstractFileService extends CompressableService implements IFileSystem
{
    /** @var array Collection of mime => extension */
    public static $mimes = array
    (
        'text/css' 					=> 'css',
        'application/x-font-woff' 	=> 'woff',
        'application/x-javascript' 	=> 'js',
        'text/html;charset=utf-8'	=>'htm',
        'text/x-component' 		=> 'htc',
        'image/jpeg' 			=> 'jpg',
        'image/pjpeg' 			=> 'jpg',
        'image/png' 			=> 'png',
        'image/x-png' 			=> 'png',
        'image/jpg' 			=> 'jpg',
        'image/gif' 			=> 'gif',
        'text/plain' 			=> 'txt',
        'application/pdf' 		=> 'pdf',
        'application/zip' 		=> 'zip',
        'application/rtf' 		=> 'rtf',
        'application/msword' 	=> 'doc',
        'application/msexcel' 	=> 'xls',
        'application/vnd.ms-excel'  => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/octet-stream' 	=> 'sql',
        'audio/mpeg'	=> 'mp3',
        'text/x-c++'    => 'php',
    );

    /**
     * Override default service function to meet correct return type requirements.
     * @see \samson\core\Service::getInstance()
     *
     * @param string $className Class name for getting service instance
     * @return \samson\fs\AbsctractFileService File service instance
     */
    public static function & getInstance($className)
    {
        return parent::getInstance($className);
    }

    /**
     * Get file mime type in current file system
     * @param $filePath string Path to file
     * @return string|bool false if mime not found, otherwise file mime type
     */
    public function mime($filePath)
    {
        // Get file extension from path
        $extension = $this->extension($filePath);

        // Search mimes array and return mime for file otherwise false
        return array_search($extension, self::$mimes);
    }

    /**
     * Get relative path from $path
     * @param string $fullPath  Full file path
     * @param string $fileName  File name
     * @param string $basePath  Base path, must end WITHOUT '/', if not passed
     *                          $fullPath one level top directory is used.
     * @return string Relative path to file
     */
    public function relativePath($fullPath, $fileName, $basePath = null)
    {
        // If no basePath is passed consider that we must go ne level up from $fullPath
        $basePath = !isset($basePath) ? dirname($fullPath) : $basePath;

        // Get dir from path and remove file name of it if no dir is present
        return str_replace($basePath.'/', '', str_replace($fileName, '', $fullPath));
    }

    /**
     * Copy file/folder to selected location.
     * Copy can be performed from file($filePath) to file($newPath),
     * also copy can be performed from folder($filePath) to folder($newPath),
     * currently copying from file($filePath) to folder($newPath) is not supported.
     *
     * @param string $filePath      Source path or file path
     * @param string $newPath       New path or file path
     * @return boolean False if failed otherwise true if file/folder has been copied
     */
    public function copyPath($filePath, $newPath)
    {
        // Check if source file exists
        if ($this->exists($filePath)) {
            // If this is directory
            if ($this->isDir($filePath)) {
                // Check if we are copying dir - dir
                if ($this->isDir($newPath)) {
                    // Read directory
                    foreach ($this->dir($filePath) as $file) {
                        // Get file name
                        $fileName = basename($file);
                        // Read source file and write to new location
                        $this->write(
                            $this->read($file, $fileName),
                            $fileName,
                            $newPath
                        );
                    }
                } else { // Signal error
                    return e(
                        'Cannot copy directory[##] - Destination file specified instead of directory[##]',
                        E_SAMSON_CORE_ERROR,
                        array($filePath, $newPath)
                    );
                }
            } else { // Read source file and write to new location
                // Get file name
                $fileName = basename($newPath);
                // Read and write file
                $this->write(
                    $this->read($filePath, $fileName),
                    $fileName,
                    dirname($newPath)
                );
            }

            // Return copied file path
            return true;
        } else {
            return e(
                'Cannot copy file[##] to [##] - Source file does not exists',
                E_SAMSON_CORE_ERROR,
                array($filePath, $newPath)
            );
        }
    }

    /**
     * Move file to selected location
     * @param string $filePath      Source path or file path
     * @param string $newPath       New path or file path
     * @return string|false False if failed otherwise path to moved file
     */
    public function movePath($filePath, $newPath)
    {
        // Copy path to a new location
        if (($this->copyPath($filePath, $newPath)) !== false) {
            // Remove current path
            $this->delete($filePath);

            return $newPath;
        } else { // Copy failed
            return false;
        }
    }
}
