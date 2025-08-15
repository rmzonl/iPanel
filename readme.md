<h2>ZN Framework Creator's Edition</h2>
<p>
This version allows you to create your own directory structure. Using the ZN Framework's core, you can build the appropriate code framework by building your own directory.
</p>

<h3>Installation</h3>
<p>
You only need to run the following code for the installation.
</p>

```
composer create-project znframework/custom-edition
```

<h3>Directory Configuration</h3>
<p>
For index configuration, open the zeroneed.php file. And define the necessary directories. Do you not forget to add a slash (/) at the end of the path information!
</p>

```php
ZN\ZN::defines
([
    'CONTROLLERS_DIR' => '/', # It is necessary to define this constant.
    'MODELS_DIR'      => '',     
    'VIEWS_DIR'       => '',
    'ROUTES_DIR'      => '',
    'CONFIG_DIR'      => 'config/',
    'DATABASES_DIR'   => '',
    'STORAGE_DIR'     => '',
    'COMMANDS_DIR'    => '',
    'LANGUAGES_DIR'   => '',
    'LIBRARIES_DIR'   => '',
    'AUTOLOAD_DIR'    => '',
    'FILES_DIR'       => '',
    'TEMPLATES_DIR'   => '',
    'THEMES_DIR'      => '',
    'PLUGINS_DIR'     => '',
    'UPLOADS_DIR'     => '',

])::run('CE');
```