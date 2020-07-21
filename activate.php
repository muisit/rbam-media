<?php

/**
 * MediaProtector Activation routines
 *
 * @package             wp-media-protector
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * This file is part of wp-media-protector.
 *
 * wp-media-protector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wp-media-protector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wp-media-protector.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Gracefully based loosely on the AAM Protected Media Files plugin
 * by Vasyl Martyniuk <vasyl@vasyltech.com>
 */

 namespace WPMediaProtector;

 class Activator {
    const MARKSTART="### BEGIN wp-media-protector rewrite block";
    const MARKEND="### END wp-media-protector rewrite block";

    public function deactivate() {
        return $this->adjustAccessFile(function($file) {
            $this->unwriteAccessFile($file);
        });
    }

    public function activate() {
        return $this->adjustAccessFile(function($file) {
            $this->writeAccessFile($file);
        });
    }

    private function adjustAccessFile($callback) {
        // check that we are using apache. If not, bail out
        if(!isset($_SERVER['SERVER_SOFTWARE']) || strstr(strtolower($_SERVER["SERVER_SOFTWARE"]),"apache") === false) {
            return false;
        }

        try {
            // find the .htaccess file at the root
            $file = $this->checkAccessPath(ABSPATH);

            if($file === null) {
                $file = $this->createAccessFile($path);
            }
            $callback($file);
            return true;
        }
        catch(Exception $e) {
            // cannot be written? Don't care: something is amiss
            return false;
        }
    }

    private function unwriteAccessFile($file) {
        if(!file_exists($file) || !is_writable($file)) {
            throw new Exception(__("Unable to write .htaccess"));
        }
        $contents = @file_get_contents($file);
        if(strstr($contents, Activator::MARKSTART) !== false) {
            $contents = preg_replace("/(.*?)".Activator::MARKSTART.".*".Activator::MARKEND."[\\r\\n]*(.*?)/s",'${1}${2}',$contents);
            @file_put_contents($file, $contents);
        }
    }

    private function writeAccessFile($file) {
        if(!file_exists($file) || !is_writable($file)) {
            throw new Exception(__("Unable to write .htaccess"));
        }

        $contents = @file_get_contents($file);
        $marker1=Activator::MARKSTART;
        $marker2=Activator::MARKEND;
        if(strstr($contents, $marker1) === false) {
            $contents = <<< DEMARK
$marker1
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_URI} wp-content/uploads/(.*)\$
    RewriteRule . /index.php?wp-media-protector=1 [L]
</IfModule>
$marker2

$contents
DEMARK;
            @file_put_contents($file, $contents);
        }
    }

    private function createAccessFile($path) {
        $file = $this->checkAccessPath($path);
        if($file === null) {
            $file = $path . DIRECTORY_SEPARATOR . ".htaccess";
            $resource = fopen($file, "w");
            if($resource === false) {
                throw new Exception(__("Unable to write .htaccess"));
            }
            if(fwrite($resource, "\r\n") === false) {
                throw new Exception(__("Unable to write .htaccess"));
            }
            fflush($resource);
            fclose($resource);
        }
        return $file;
    }

    private function checkAccessPath($path) {
        $filename = $path . DIRECTORY_SEPARATOR. '.htaccess';
        if(file_exists($filename)) {
            return $filename;
        }
        return null;
    }    
 }