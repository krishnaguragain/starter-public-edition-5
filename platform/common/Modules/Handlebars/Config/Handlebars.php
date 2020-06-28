<?php namespace Common\Modules\Handlebars\Config;

use CodeIgniter\Config\BaseConfig;

class Handlebars extends BaseConfig
{
    public $config = [];

    public function __construct()
    {
        parent::__construct();

        // Handlebars Environment --------------------------------------------

        // Filesystem path to the disk cache location.
        $this->config['cache'] = ENVIRONMENT === 'production' ? HANDLEBARS_CACHE : null;

        // Cache file prefix, defaults to empty string.
        $this->config['cache_file_prefix'] = '';

        // Cache file extension, defaults to empty string.
        $this->config['cache_file_suffix'] = '';

        // A Handlebars template loader instance. Uses a StringLoader if not specified.
        $this->config['loader'] = null;

        // A Handlebars loader instance for partials. Uses a StringLoader if not specified.
        $this->config['partials_loader'] = null;
    }

    //------------------------------------------------------------------------

}
