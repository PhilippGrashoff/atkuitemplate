<?php declare(strict_types=1);

namespace PhilippR\Atk4\ExtendedTemplate\Tests;

use Atk4\Data\Persistence\Array_;
use Atk4\Data\Schema\TestCase;
use DateTime;
use PhilippR\Atk4\ExtendedTemplate\ExtendedHtmlTemplate;
use PhilippR\Atk4\ExtendedTemplate\Tests\testclasses\SomeTestModel;

class ExtendedHtmlTemplateTest extends TestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->db = new Array_([]);
    }

    public function testSetTagsFromModel(): void
    {
        $template = $this->getTemplate('Hallo {$some_string} Test {$some_integer} Miau {$some_text}!');
        $template->setTagsFromModel(
            $this->getTestModelWithFieldValuesSet(),
            ['some_string', 'some_integer', 'some_text'],
            ''
        );
        self::assertSame(
            'Hallo BlaDU Test 3 Miau LALALALA!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithNonExistingTagAndField(): void
    {
        $template = $this->getTemplate('Hallo {$some_string} Test {$some_float} Miau {$nottext}!');
        $template->setTagsFromModel(
            $this->getTestModelWithFieldValuesSet(),
            ['some_string', 'some_float', 'some_text', 'nilla'],
            ''
        );
        self::assertSame(
            'Hallo BlaDU Test 9.99 Miau !',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithDateFormatsFromUiPersistence(): void
    {
        $template = $this->getTemplate('Hallo {$some_datetime} Test {$some_date} Miau {$some_time}!');
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet(), [], '');
        self::assertSame(
            'Hallo 05.05.2019 10:30 Test 05.05.2019 Miau 10:30!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithCustomDateFormats(): void
    {
        $template = $this->getTemplate('Hallo {$some_datetime} Test {$some_date} Miau {$some_time}!');
        $template->dateTimeFormat = 'Ymd His';
        $template->dateFormat = 'Ymd';
        $template->timeFormat = 'His';
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet(), [], '');
        self::assertSame(
            'Hallo 20190505 103000 Test 20190505 Miau 103000!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithLimitedFields(): void
    {
        $template = $this->getTemplate('Hallo {$some_string} Test {$some_integer} Miau {$some_text}!');
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet(), ['some_string', 'some_integer'], '');
        self::assertSame(
            'Hallo BlaDU Test 3 Miau !',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithEmptyFieldArray(): void
    {
        $template = $this->getTemplate('Hallo {$some_string} Test {$some_integer} Miau {$some_text}!');
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet(), [], '');
        self::assertSame(
            'Hallo BlaDU Test 3 Miau LALALALA!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithPrefix(): void
    {
        $template = $this->getTemplate(
            'Hallo {$group_some_string} Test {$group_some_integer} Miau {$group_some_text}!'
        );
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet(), [], 'group_');
        self::assertSame(
            'Hallo BlaDU Test 3 Miau LALALALA!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithOnlyOneParameter(): void
    {
        $template = $this->getTemplate(
            'Hallo {$sometestmodel_some_string} Test {$sometestmodel_some_text}!'
        );
        $template->setTagsFromModel($this->getTestModelWithFieldValuesSet());
        self::assertSame(
            'Hallo BlaDU Test LALALALA!',
            $template->renderToHtml()
        );
    }

    public function testSetTagsFromModelWithTwoModelsWithPrefix(): void
    {
        $model1 = $this->getTestModelWithFieldValuesSet();
        $model2 = $this->getTestModelWithFieldValuesSet();
        $model2->set('some_string', 'ABC');
        $model2->set('some_integer', 9);
        $model2->set('some_text', 'DEF');

        $template = $this->getTemplate(
            'Hallo {$group_some_string} Test {$group_some_integer} Miau {$group_some_text},'
            . ' du {$tour_some_string} Hans {$tour_some_integer} bist toll {$tour_some_text}!'
        );
        $template->setTagsFromModel($model1, [], 'group_');
        $template->setTagsFromModel($model2, [], 'tour_');
        self::assertSame(
            'Hallo BlaDU Test 3 Miau LALALALA, du ABC Hans 9 bist toll DEF!',
            $template->renderToHtml()
        );
    }

    public function testWithLineBreaks(): void
    {
        $template = $this->getTemplate('Hallo {$with_line_break} Test');
        $template->setWithLineBreaks('with_line_break', 'Hans' . PHP_EOL . 'Neu');

        self::assertSame(
            'Hallo Hans<br />' . PHP_EOL . 'Neu Test',
            $template->renderToHtml()
        );
    }

    public function testSetAsAndList(): void
    {
        $template = $this->getTemplate('Hallo {$list_tag} Test');
        $template->setAsAndList('list_tag', ['Peter', 'Walter', 'Hans']);
        self::assertSame(
            'Hallo Peter, Walter and Hans Test',
            $template->renderToHtml()
        );

        $template->setAsAndList('list_tag', ['Peter', 'Walter', 'Hans'], 'und');
        self::assertSame(
            'Hallo Peter, Walter und Hans Test',
            $template->renderToHtml()
        );
    }

    protected function getTemplate(string $content): ExtendedHtmlTemplate
    {
        return new ExtendedHtmlTemplate($content);
    }

    protected function getTestModelWithFieldValuesSet(): SomeTestModel
    {
        $model = (new SomeTestModel($this->db))->createEntity();
        $model->set('some_string', 'BlaDU');
        $model->set('some_integer', 3);
        $model->set('some_float', 9.99);
        $model->set('some_text', 'LALALALA');

        $dt = DateTime::createFromFormat('Y-m-d H:i:s', '2019-05-05 10:30:00');
        $model->set('some_datetime', clone $dt);
        $model->set('some_date', clone $dt);
        $model->set('some_time', clone $dt);

        return $model;
    }

}
