<?php
/**
 * Copyright (c) 2009 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Autoload
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\Tools {

   /**
    * Builds spl based autoload code for inclusion into projects
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   class AutoloadBuilder {

      /**
       * Associative array of classes (key) and the files (value) they are in
       *
       * @var array
       */
      protected $classes;

      /**
       * An optional base dir to strip for the realpath of the filename
       *
       * @var string
       */
      protected $baseDir;

      /**
       * Indenting char(s)
       *
       * @var string
       */
      protected $indent;

      /**
       * Char(s) used as linebreak
       *
       * @var string
       */
      protected $linebreak = PHP_EOL;

      /**
       * A flag to signal wether or not the generated code should have a closing ?> tag
       *
       * @var boolean
       */
      protected $omitClosingPHP = true;

      /**
       * Constructor of AutoloadBuilder class
       *
       * @param array $classlist Array of classes
       * @param $baseDir
       *
       * @return void
       */
      public function __construct(array $classlist, $baseDir = '', $indent  = '   ') {
         $this->classes = $classlist;
         $this->baseDir = $baseDir;
         $this->indent  = $indent;
      }

      /**
       * Setter for the Basedir
       *
       * @param string $dir Path to strip from beginning of filenames
       *
       * @return void
       */
      public function setBaseDir($dir) {
         $this->baseDir = $dir;
      }

      /**
       * Overwrite default or previously set indenting option
       *
       * @param string $indent Char(s) to use for indenting
       *
       * @return void
       */
      public function setIndent($indent) {
         $this->indent = $indent;
      }

      /**
       * Overwrite default or previously set linebreak chars
       *
       * @param string $lbs Code to set linebreak
       *
       * @return void
       */
      public function setLineBreak($lbs) {
         $this->linebreak = $lbs;
      }

      /**
       * Setter to switch closing php tag on and off
       *
       * @param boolean $omit Flag to enable or disable omitting
       *
       * @return void
       */
      public function omitClosingTag($omit) {
         $this->omitClosingPHP = $omit;
      }

      /**
       * Render autoload code into a string
       *
       * @return string
       */
      public function render() {
         $entries = array();
         foreach($this->classes as $class => $file) {
            $fname = realpath($file);
            if (!empty($this->baseDir) && strpos($fname, $this->baseDir)===0) {
               $fname=str_replace($this->baseDir,'\' . __DIR__ . \'', $fname);
            }
            $entries[] = "'$class' => '$fname'";
         }

         $i  = $this->indent;
         $ln = $this->linebreak;

         $body  = "<?php // this is an autogenerated file - do not edit (created " . date('Y-m-d H:i:s') . ")$ln";
         $body .= "spl_register_autoload({$ln}";
         $body .= "{$i}function(\$class) {{$ln}";
         $body .= "{$i}{$i}static \$classes = array({$ln}";
         $body .= "{$i}{$i}{$i}" . join(",$ln{$i}{$i}{$i}", $entries) . $ln;
         $body .= "{$i}{$i});{$ln}";
         $body .= "{$i}{$i}if (isset(\$classes[\$class]) { require \$classes[\$class]; }{$ln}";
         $body .= "{$i}}{$ln}";
         $body .= ");{$ln}";

         if (!$this->omitClosingPHP) {
            $body .= "?>{$ln}";
         }

         return $body;
      }

      /**
       * Save autoload code to given filename
       *
       * @param string $filename Filename to store code in
       *
       * @return integer|boolean
       */
      public function save($filename) {
         return file_put_contents($filename, $this->render());
      }
   }

}