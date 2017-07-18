<?php

use Calhoun\OTF\Support\OTF;

if(!function_exists('eloquent'))
{
    function eloquent($value = null, $callback = null, $name = null)
    {
        $name = ($name) ?: 'onTheFly';
        $values = collect($value)->toArray();
        $otf = OTF::make($name, $values);
        if(is_null($callback)) throw new \Exception('A callback is required');

        return $callback($otf->{$name}, $name);
    }
}
?>