<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ArchiverZipArchive class file
 *
 * PHP version 5
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category  Utilities
 * @package   ZipFactory
 * @author    Yani Iliev <yani@iliev.me>
 * @copyright 2014 Yani Iliev
 * @license   https://raw.github.com/yani-/zip-factory/master/LICENSE The MIT License (MIT)
 * @version   GIT: 1.0.0
 * @link      https://github.com/yani-/zip-factory/
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ArchiverInterface.php';

if (class_exists('ZipArchive')) {
    /**
     * ArchiverZipArchive class
     *
     * @category  Tests
     * @package   ZipFactory
     * @author    Yani Iliev <yani@iliev.me>
     * @copyright 2014 Yani Iliev
     * @license   https://raw.github.com/yani-/zip-factory/master/LICENSE The MIT License (MIT)
     * @link      https://github.com/yani-/zip-factory/
     */
    class ArchiverZipArchive extends ZipArchive implements ArchiverInterface
    {
        /**
         * [$archive description]
         * @var [type]
         */
        protected $archive  = null;

        /**
         * [$root_dir description]
         * @var [type]
         */
        protected $root_dir = null;

        /**
         * [__construct description]
         *
         * @param [type] $file [description]
         *
         * @return [type]       [description]
         */
        public function __construct($file)
        {
            if (is_resource($file)) {
                $meta = stream_get_meta_data($file);
                $this->archive = $meta['uri'];
            } else {
                $this->archive = $file;
            }

            // Open Archive File
            if (!($this->open($this->archive) === true)) {
                throw new RuntimeException('Archive file cound not be created.');
            }
        }

        /**
         * [addFile description]
         *
         * @param [type] $filepath  [description]
         * @param [type] $entryname [description]
         * @param [type] $start     [description]
         * @param [type] $length    [description]
         *
         * @return  null [description]
         */
        public function addFile(
            $filepath,
            $entryname = null,
            $start = null,
            $length = null
        ) {
            parent::addFile($filepath, $entryname, $start, $length);
        }

        /**
         * [addDir description]
         *
         * @param [type] $path       [description]
         * @param [type] $parent_dir [description]
         * @param array  $include    [description]
         *
         * @return null [description]
         */
        public function addDir($path, $parent_dir = null, $include = array())
        {
            // Use Recursive functions
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::SELF_FIRST
            );

            // Prepare File Filter Pattern
            $file_pattern = null;
            if (is_array($include)) {
                $filters = array();
                foreach ($include as $file) {
                    $filters[] = str_replace(
                        '\.\*', '.*',
                        preg_quote($file, '/')
                    );
                }

                $file_pattern = implode('|', $filters);
            }

            foreach ($iterator as $item) {
                // Skip dots
                if ($iterator->isDot()) {
                    continue;
                }

                // Validate file pattern
                if ($file_pattern) {
                    if (!preg_match(
                        '/^(' . $file_pattern . ')$/',
                        $iterator->getSubPathName()
                    )) {
                        continue;
                    }
                }

                // Add to archive
                if ($item->isDir()) {
                    $this->addEmptyDir(
                        $parent_dir .
                        DIRECTORY_SEPARATOR .
                        $iterator->getSubPathName()
                    );
                } else {
                    $this->addFile(
                        $item->getPathname(),
                        $parent_dir .
                        DIRECTORY_SEPARATOR .
                        $iterator->getSubPathName()
                    );
                }
            }
        }

        /**
         * [addFromString description]
         *
         * @param [type] $name    [description]
         * @param [type] $content [description]
         *
         * @return null [description]
         */
        public function addFromString($name, $content)
        {
            parent::addFromString($name, $content);
        }

        /**
         * [getArchive description]
         *
         * @return [type] [description]
         */
        public function getArchive()
        {
            return $this->archive;
        }

        /**
         * [extractTo description]
         *
         * @param string $pathto Path to extract to
         * @param mixed  $files  Optional files parameter
         *
         * @return [type]              [description]
         */
        public function extractTo($pathto, $files = null)
        {
            parent::extractTo($pathto);
        }

        /**
         * [close description]
         *
         * @return [type] [description]
         */
        public function close()
        {
            parent::close();
        }
    }
}
