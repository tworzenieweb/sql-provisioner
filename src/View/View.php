<?php

namespace Tworzenieweb\SqlProvisioner\View;

use Twig_Environment;
use Twig_Loader_Filesystem;

class View
{
    /** @var \Twig_Environment */
    private $twig;



    public function __construct(string $pathToTemplates)
    {
        $loader = new Twig_Loader_Filesystem($pathToTemplates . DIRECTORY_SEPARATOR . 'templates');
        $this->twig = new Twig_Environment($loader, array(
            'cache' => '/tmp',
        ));
    }



    public function render(array $variables): string
    {
        return $this->twig->render('template.twig.html', $variables);
    }
}
