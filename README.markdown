Superload
=========

&copy; 2010 Jason Frame [ [jason@onehackoranother.com](mailto:jason@onehackoranother.com) / [@jaz303](http://twitter.com/jaz303) ]  
Released under the MIT License.

Usage
-----

Superload is designed for integration with [phake](http://github.com/jaz303/phake) but there's nothing to stop you running it manually from a PHP script, too.

    desc('Regenerate class autoload map');
    task('regenerate_autoload_map', 'environment', function() {

        require APP_ROOT . '/vendor/superload/superload.php';
        $superload = new superload\Superload(APP_ROOT);
        $superload->add_rule('vendor/my-lib/classes');
        $superload->add_rule('vendor/another-lib/code');
        $superload->add_rule('app');
        $superload->add_rule('framework/classes');
  
        $superload->write(CONFIG_ROOT . '/boot.php');

    });
    
In `boot.php`, you'd have a skeleton like this:

    function __autoload($class) {

        // SUPERLOAD-BEGIN
        // SUPERLOAD-END
    
        if (isset($map[$class])) {
            require $map[$class];
        }
    
    }
    
Which Superload will then populate:
    
    function __autoload($class) {

        // SUPERLOAD-BEGIN
        static $map = array (
          'RecordNotFoundException' => 'vendor/spitfire/runtime/spitfire.php',
          'RecordInvalidException' => 'vendor/spitfire/runtime/spitfire.php',
          'Errors' => 'vendor/spitfire/runtime/spitfire.php',
          'SpitfireModel' => 'vendor/spitfire/runtime/spitfire.php',
          'IllegalArgumentException' => 'vendor/base-php/inc/base.php',
          'IllegalStateException' => 'vendor/base-php/inc/base.php',
          ...
          ...
          ...
        );
        // SUPERLOAD-END
      
        if (isset($map[$class])) {
            require $map[$class];
        }

    }