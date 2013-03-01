<?php

if (! defined('SOCIALCONNECTIONS')) {
    die();
}

/**
 * Cleans up input html code, indents it
 * and fixes any invalid syntax
 */
abstract class TidyHtml {
    public static function process($html)
    {
       /* // Specify configuration
        $config = array(
            'hide-comments'       => true,
            'indent'              => true,
            'indent-spaces'       => 2,
            'new-blocklevel-tags' => 'article,header,footer,section,nav',
            'new-inline-tags'     => 'video,audio,canvas,ruby,rt,rp',
            'output-xhtml'        => true,
            'wrap'                => 200
        );

        // Tidy
        $tidy = new tidy;
        $tidy->parseString($html, $config, 'utf8');

        //echo $tidy->errorBuffer . "\n";

        $tidy->cleanRepair();

        // Drop doctype
        $output = str_replace(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n",
            '',
            $tidy
        );

        // Output
        return str_replace(
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            "<!DOCTYPE html>\n<html>",
            $output
        );*/

        return $html;
    }
}