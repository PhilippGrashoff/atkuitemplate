<?php declare(strict_types=1);

namespace PhilippR\Atk4\ExtendedTemplate\Tests;

use Atk4\Data\Schema\TestCase;
use Atk4\Ui\HtmlTemplate;
use PhilippR\Atk4\ExtendedTemplate\Tests\testclasses\ViewWithSubTemplateCloneTrait;

class SubTemplateCloneDeleteTraitTest extends TestCase
{

    public function testTemplateCloneAndDelete(): void
    {
        $view = new ViewWithSubTemplateCloneTrait();
        $view->template = new HtmlTemplate();
        $view->template->loadFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada']);
        self::assertSame('test1', $view->_tLala->renderToHtml());
        self::assertSame('test2', $view->_tDada->renderToHtml());
    }

    public function testTemplateCloneAndDeleteWithoutArgs(): void
    {
        $view = new ViewWithSubTemplateCloneTrait();
        $view->template = new HtmlTemplate();
        $view->template->loadFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete();
        self::assertSame('test1', $view->_tLala->renderToHtml());
        self::assertSame('test2', $view->_tDada->renderToHtml());
    }

    public function testCloneAndDeleteWithNonExistantRegion(): void
    {
        $view = new ViewWithSubTemplateCloneTrait();
        $view->template = new HtmlTemplate();
        $view->template->loadFromString('Hans{Lala}test1{/Lala}{Dada}test2{/Dada}');
        $view->templateCloneAndDelete(['Lala', 'Dada', 'NonExistantRegion']);
        self::assertSame('test1', $view->_tLala->renderToHtml());
        self::assertSame('test2', $view->_tDada->renderToHtml());
    }
}
