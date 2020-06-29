<?php

namespace Common\Modules\Markdownify;

class Markdownify
{
    protected $renderer;

    public function render($template, $data = null, array $options = null)
    {
        $template = (string) $template;

        if (empty($data)) {
            $data = [];
        }

        if (empty($options)) {
            $options = [];
        }

        $config = config('Markdownify')->config;
        $options = array_merge_recursive_distinct($config, $options);
        unset($config);


    }

    public function renderString($template, $data = null, array $options = null)
    {
        $template = (string) $template;

        if (empty($data)) {
            $data = [];
        }

        if (empty($options)) {
            $options = [];
        }

        $config = config('Markdownify')->config;
        $options = array_merge_recursive_distinct($config, $options);
        unset($config);


    }

}
