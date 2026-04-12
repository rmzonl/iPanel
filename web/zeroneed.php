<?php
/**
 * ZN PHP Web Framework
 * 
 * "Simplicity is the ultimate sophistication." ~ Da Vinci
 * 
 * @package ZN
 * @license MIT [http://opensource.org/licenses/MIT]
 * @author  Ozan UYKUN [ozan@znframework.com]
 * @since   2011
 */

/*
|--------------------------------------------------------------------------
| Require Core File
|--------------------------------------------------------------------------
|
| It includes the necessary things for the operation of the system.
|
*/

require __DIR__ . '/Packages/autoload.php';

/*
|--------------------------------------------------------------------------
| Run ZN
|--------------------------------------------------------------------------
|
| Simplicity is our principle. Enjoy it.
|
*/

ZN\ZN::defines
([
    'BUTCHERY_DIR'    => '',
    'CONTROLLERS_DIR' => 'iApp/Controllers/', # [required] default base directory.
    'MODELS_DIR'      => 'iApp/Models/',
    'VIEWS_DIR'       => 'Views/',
    'ROUTES_DIR'      => 'iApp/Routes/',
    'CONFIG_DIR'      => 'iApp/Config/',  # Its use is not mandatory, but we recommend it.
    'DATABASES_DIR'   => '',
    'STORAGE_DIR'     => 'iApp/Storage/',
    'COMMANDS_DIR'    => 'iApp/Commands/',
    'LANGUAGES_DIR'   => 'iApp/Languages/',
    'LIBRARIES_DIR'   => 'iApp/Libraries/',
    'AUTOLOAD_DIR'    => '',
    'FILES_DIR'       => 'iApp/Storage/Files/',
    'TEMPLATES_DIR'   => 'iApp/Templates/',
    'THEMES_DIR'      => 'iApp/Themes/',
    'PLUGINS_DIR'     => 'iApp/Plugins/',
    'UPLOADS_DIR'     => 'Uploads/'

])::run('CE');