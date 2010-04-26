<?php
namespace superload;

class Superload
{
    private $root;
    
    private $variable   = 'static $map';
    private $rules      = array();
    
    public function __construct($root) {
        $this->root = rtrim($root, '/') . '/';
    }
    
    public function set_variable($variable) {
        $this->variable = $variable;
    }
    
    public function add_rule($path, $extensions = 'php') {
        $this->rules[] = array('path' => rtrim($path, '/') . '/', 'extensions' => (array) $extensions);
    }
    
    public function files() {
        $files = array();
        foreach ($this->rules as $rule) {
            $stack = array($this->root . $rule['path']);
            $extensions = $rule['extensions'];
            while (count($stack)) {
                $dir = array_pop($stack);
                if (!($dh = @opendir($dir))) continue;
                while (($file = readdir($dh)) !== false) {
                    if ($file == '.' || $file == '..' || $file == '.git' || $file == '.svn') continue;
                    $extension = (($p = strrpos($file, '.')) !== false) ? substr($file, $p + 1) : '';
                    $path = $dir . $file;
                    if (is_dir($path)) {
                        $stack[] = $path . '/';
                    } elseif (in_array($extension, $extensions)) {
                        $files[] = $path;
                    }
                }
                closedir($dh);
            }
        }
        return $files;
    }
    
    public function build_map() {
        $map = array();
        foreach ($this->files() as $file) {
            foreach ($this->scan_file($file) as $file_class) {
                $map[$file_class] = str_replace($this->root, '', $file);
            }
        }
        return $map;
    }
    
    public function write($file, $indent = "\t") {
        
        $declaration = $this->variable . ' = ' . var_export($this->build_map(), true) . ";\n";
        $declaration = preg_replace('/^/m', $indent, $declaration);
        
        $lines = file($file);
        $fh = fopen($file, 'w');
        $in = false;
        foreach ($lines as $line) {
            if (!$in) {
                fwrite($fh, $line);
                if (preg_match('/^\s*\/\/\s*SUPERLOAD-BEGIN\s+$/', $line)) {
                    $in = true;
                    fwrite($fh, $declaration);
                }
            } else {
                if (preg_match('/^\s*\/\/\s*SUPERLOAD-END\s+$/', $line)) {
                    $in = false;
                    fwrite($fh, $line);
                }
            }
        }
    
    }
    
    // technique learned from:
    // http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file
    private function scan_file($file) {
        
        $tokens     = token_get_all(file_get_contents($file));
        $count      = count($tokens);
        $classes    = array();
        
        for ($i = 2; $i < $count; $i++) {
            if (($tokens[$i - 2][0] == T_CLASS || $tokens[$i - 2][0] == T_INTERFACE) &&
                 $tokens[$i - 1][0] == T_WHITESPACE &&
                 $tokens[$i - 0][0] == T_STRING) {
                
                // TODO: make this namespace-aware
                $classes[] = $tokens[$i][1];
            
            }
        }
        
        return $classes;
    
    }
}
?>