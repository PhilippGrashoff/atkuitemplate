<?php declare(strict_types=1);

namespace PhilippR\Atk4\ExtendedTemplate\Tests\testclasses;

use Atk4\Ui\View;
use PhilippR\Atk4\ExtendedTemplate\SubTemplateCloneDeleteTrait;

class ViewWithSubTemplateCloneTrait extends View
{
    use SubTemplateCloneDeleteTrait;

    public $_tLala;
    public $_tDada;
}