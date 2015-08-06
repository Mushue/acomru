<?php

/*
 * This file is part of KoolKode Utilities.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Util;

/**
 * Provides local filesystem related helper methods.
 *
 * @author Martin Schröder
 */
abstract class Filesystem
{
    /**
     * Default MIME type for files.
     *
     * @var string
     */
    const MIMETYPE_DEFAULT = 'application/octet-stream';

    /**
     * The prefix to be used when creating temporary file names.
     *
     * @var string
     */
    const TEMP_FILE_PREFIX = 'k2t';

    /**
     * Hardcoded file extension to MIME type mapping
     *
     * @var array<string, string>
     */
    protected static $mimeTypes = [
        'jpe' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'txt' => 'text/plain',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'htm' => 'text/html',
        'html' => 'text/html',
        'xml' => 'application/xml',
        'xsl' => 'application/xslt+xml',
        'xsd' => 'application/xml',
        'wsdl' => 'application/wsdl+xml',
        'rng' => 'application/xml',
        'pdf' => 'application/pdf',
        'swf' => 'application/x-shockwave-flash',
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'gz' => 'application/x-gzip',
        'ico' => 'image/x-icon',
        'latex' => 'application/x-latex',
        'm3u' => 'audio/x-mpegurl',
        'mp2' => 'video/mpeg',
        'mp3' => 'audio/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'pot' => 'application/vnd.ms-powerpoint',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppt' => 'application/vnd.ms-powerpoint',
        'ps' => 'application/postscript',
        'qt' => 'video/quicktime',
        'rtf' => 'application/rtf',
        'svg' => 'image/svg+xml',
        'tar' => 'application/x-tar',
        'wav' => 'audio/x-wav',
        'zip' => 'application/zip',
        'php' => 'application/x-httpd-php',

        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
// 		'ttf' => 'application/font-ttf',
// 		'woff' => 'application/font-woff'
    ];

    /**
     * Get the guessed MIME type of a file.
     *
     * @param string $file
     * @param string $default
     * @return string
     */
    public static function guessMimeTypeFromFilename($file, $default = self::MIMETYPE_DEFAULT)
    {
        $file = strtolower($file);

        if (($pos = strrpos($file, '.')) !== false) {
            $file = substr($file, $pos + 1);
        }

        if (array_key_exists($file, self::$mimeTypes)) {
            return self::$mimeTypes[$file];
        }

        return $default;
    }

    /**
     * Suggest a file extension that is related to the given media type.
     *
     * @param string $mediaType
     * @param string $default
     * @return string
     */
    public static function suggestExtensionByMediaType($mediaType)
    {
        if (false !== ($ext = array_search(strtolower($mediaType), self::$mimeTypes))) {
            return $ext;
        }

        if (func_num_args() > 1) {
            return func_get_arg(1);
        }

        return NULL;
    }

    /**
     * Get the internal MIME type table that is used to lookup
     * a type based on a (lowercase) file extension.
     *
     * @return array<string, string>
     */
    public static function getMimeTypeLookupTable()
    {
        return self::$mimeTypes;
    }

    /**
     * Normalize a filesystem path by converting directory separators to "/" and replacing
     * special parts "." and ".." accordingly.
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $path = str_replace('/./', '/', str_replace('\\', '/', $path));

        $m = NULL;
        while (preg_match("'/([^/]+/\.\./)'", $path, $m)) {
            $path = str_replace($m[1], '', $path);
        }

        return $path;
    }

    /**
     * Read contents of the given file into a string and return it.
     *
     * @param string $file
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function readFile($file)
    {
        $contents = @file_get_contents($file);

        if ($contents === false) {
            throw new \RuntimeException('File not found: ' . $file);
        }

        return $contents;
    }

    /**
     * Open a read stream to the given file (file needs to exist).
     *
     * @param string $file
     * @param string $mode The read mode, defaults to "rb".
     * @param string $lock Lock the file handle using one of the LOCK_* constants.
     * @return resource
     *
     * @throws \InvalidArgumentException When the given file does not exist.
     * @throws \RuntimeException When no read stream could be opened.
     */
    public static function & openReadStream($file, $mode = 'rb', $lock = NULL)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('File not found: "%s"', $file));
        }

        $fp = @fopen($file, $mode);

        if (false === $fp) {
            throw new \RuntimeException(sprintf('File "%s" could not be opened in mode "%s"%s', $file, $mode, self::getErrorInfo(error_get_last())));
        }

        if ($lock !== NULL && !@flock($fp, $lock)) {
            throw new \RuntimeException(sprintf('File "%s" could not be locked', $file, self::getErrorInfo(error_get_last())));
        }

        return $fp;
    }

    protected static function getErrorInfo($error)
    {
        $error = (array)$error;
        $info = '';

        if (array_key_exists('message', $error)) {
            $info .= ' caused by "' . $error['message'] . '"';

            if (array_key_exists('file', $error)) {
                $info .= ' in ' . $error['file'];

                if (array_key_exists('line', $error)) {
                    $info .= ' at line ' . $error['line'];
                }
            }
        }

        return $info;
    }

    /**
     * Open a write stream to the given file (file will be created when it does not exist yet).
     *
     * @param string $file
     * @param string $mode The write mode, defaults to "wb".
     * @param integer $permissions The access permissions to be set.
     * @param integer $lock Optional locking using one of the LOCK_* constants.
     *
     * @throws \RuntimeException When no write stream could be opened.
     */
    public static function openWriteStream($file, $mode = 'wb', $permissions = 0777, $lock = NULL)
    {
        $file = self::touchFile($file, $permissions);

        $fp = @fopen($file, $mode);

        if (false === $fp) {
            throw new \RuntimeException(sprintf('File "%s" could not be opened in mode "%s"%s', $file, $mode, self::getErrorInfo(error_get_last())));
        }

        if ($lock !== NULL && !@flock($fp, $lock)) {
            throw new \RuntimeException(sprintf('File "%s" could not be locked', $file, self::getErrorInfo(error_get_last())));
        }

        return $fp;
    }

    /**
     * Touches the given file and ensures the given access permissions are set.
     *
     * @param string $file
     * @param integer $permissions
     * @param string The touched file's location.
     *
     * @throws \RuntimeException When the given file could not be touched.
     */
    public static function touchFile($file, $permissions = 0777)
    {
        self::createDirectory(dirname($file));

        if (!@touch($file)) {
            throw new \RuntimeException(sprintf('Unable to touch file "%s"%s', $file, self::getErrorInfo(error_get_last())));
        }

        self::changePermissions($file, $permissions);

        clearstatcache(true, $file);

        return $file;
    }

    /**
     * Create a directory (if it does not exist).
     *
     * @param string $dir
     * @param integer $permissions
     * @return string The directory location.
     *
     * @throws \RuntimeException When the directory could not be created.
     */
    public static function createDirectory($dir, $permissions = 0777)
    {
        if (!is_dir($dir)) {
            if (DIRECTORY_SEPARATOR == '\\') {
                $done = @mkdir($dir, NULL, true);
            } else {
                $old = umask(0);

                try {
                    $done = @mkdir($dir, (int)$permissions, true);
                } finally {
                    umask($old);
                }
            }

            if (!$done) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s"%s', $dir, self::getErrorInfo(error_get_last())));
            }
        }

        clearstatcache(true, $dir);

        return $dir;
    }

    /**
     * Change access permissions of the given file.
     *
     * @param string $file
     * @param integer $permissions Permissions to be set, needs to be an integer like 0777 (octal notation).
     * @param string The file's location.
     *
     * @throws \InvalidArgumentException When no such file exists or permissions are invalid.
     * @throws \RuntimeException When access permissions could not be changed.
     */
    public static function changePermissions($file, $permissions)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('No such file found: "%s"', $file));
        }

        if (DIRECTORY_SEPARATOR == '\\') {
            return $file;
        }

        $old = umask(0);

        try {
            $done = @chmod($file, $permissions);
        } finally {
            umask($old);
        }

        if (!$done) {
            throw new \RuntimeException(sprintf('Unable to change access permissions of file "%s"', $file));
        }

        clearstatcache(true, $file);

        return $file;
    }

    /**
     * Atomic file writer.
     *
     * @param string $file
     * @param string $contents
     * @param integer $permissions
     * @return string The location of the generated file.
     */
    public static function writeFile($file, $contents, $permissions = 0777)
    {
        self::createDirectory(dirname($file));

        $tempFile = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

        file_put_contents($tempFile, (string)$contents);

        if (DIRECTORY_SEPARATOR == '\\') {
            if (is_file($file)) {
                @unlink($file);
            }

            if (!@rename($tempFile, $file)) {
                throw new \RuntimeException(sprintf('Could not move temp file to "%s"%s', $file, self::getErrorInfo(error_get_last())));
            }
        } else {
            if (!@rename($tempFile, $file)) {
                throw new \RuntimeException(sprintf('Could not move temp file to "%s"%s', $file, self::getErrorInfo(error_get_last())));
            }

            self::changePermissions($file, $permissions);
        }

        clearstatcache(true, $file);

        return $file;
    }

    /**
     * Remove a file if it exists.
     *
     * @param string $file
     *
     * @throws \RuntimeException When the file could not be removed.
     */
    public static function removeFile($file)
    {
        if (is_file($file)) {
            if (!@unlink($file)) {
                throw new \RuntimeException(sprintf('Unable to delete file "%s"%s', $file, self::getErrorInfo(error_get_last())));
            }
        }

        clearstatcache(true, $file);
    }

    /**
     * Recursively empty (and remove) a directory.
     *
     * @param string $dir
     * @param boolean $remove Remove the empty directory
     */
    public static function removeDirectory($dir, $remove = true)
    {
        if (is_dir($dir)) {
            $entries = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT);

            if (empty($entries)) {
                $entries = [];
            }

            foreach ($entries as $tmp) {
                if (is_dir($tmp)) {
                    self::removeDirectory($tmp);
                } else {
                    if (!@unlink($tmp)) {
                        throw new \RuntimeException(sprintf('Could not remove directory "%s" due to failed removal of file "%s"%s', $dir, $tmp, self::getErrorInfo(error_get_last())));
                    }
                }
            }

            if ($remove) {
                if (!@rmdir($dir)) {
                    throw new \RuntimeException(sprintf('Could not remove directory "%s"%s', $dir, self::getErrorInfo(error_get_last())));
                }
            }
        }

        clearstatcache(true);
    }
}
