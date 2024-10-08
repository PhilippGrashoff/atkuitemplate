<?php declare(strict_types=1);

namespace PhilippR\Atk4\ExtendedTemplate;

use Atk4\Data\Field;
use Atk4\Data\Model;
use Atk4\Ui\HtmlTemplate;
use DateTimeInterFace;
use ReflectionClass;

class ExtendedHtmlTemplate extends HtmlTemplate
{

    public string $dateTimeFormat = 'd.m.Y H:i';
    public string $dateFormat = 'd.m.Y';
    public string $timeFormat = 'H:i';

    public function setAsAndList(string $tag, array $items, string $and = 'and'): void
    {
        $string = '';
        $counter = 0;
        foreach ($items as $item) {
            $counter++;
            if ($item === '') {
                continue;
            }
            if ($counter === 1) {
                $string .= $item;
            } elseif ($counter === count($items)) {
                $string .= ' ' . $and . ' ' . $item;
            } else {
                $string .= ', ' . $item;
            }
        }
        $this->set($tag, $string);
    }

    /**
     * Tries to set each passed tag with its value from passed model; if no tag list is passed,
     * check for each model field if a tag is available
     */
    public function setTagsFromModel(Model $entity, array $tags = [], string $prefix = null): void
    {
        $entity->assertIsEntity();
        if (!$tags) {
            $tags = array_keys($entity->getFields());
        }
        if ($prefix === null) {
            $prefix = strtolower((new ReflectionClass($entity))->getShortName()) . '_';
        }

        foreach ($tags as $tag) {
            if (
                !$entity->hasField($tag)
                || !$this->hasTag($prefix . $tag)
            ) {
                continue;
            }

            $this->setFieldValueToTag($entity, $tag, $prefix);
        }
    }

    protected function setFieldValueToTag(Model $entity, string $tag, string $prefix): void
    {
        $field = $entity->getField($tag);
        $fieldValue = $entity->get($tag);
        //try converting non-scalar values
        if (!is_scalar($fieldValue)) {
            if ($fieldValue instanceof DateTimeInterFace) {
                $this->set(
                    $prefix . $tag,
                    $this->dateTimeFieldToString($field, $fieldValue)
                );
            } else {
                $this->set($prefix . $tag, (string)$fieldValue);
            }
        } elseif ($field->type === 'text') {
            $this->dangerouslySetHtml(
                $prefix . $tag,
                nl2br(htmlspecialchars($fieldValue))
            );
        } else {
            $this->set($prefix . $tag, (string)$fieldValue);
        }
    }

    protected function dateTimeFieldToString(Field $field, mixed $fieldValue): string
    {
        if ($fieldValue instanceof \DateTimeInterface) {
            if ($field->type === 'datetime') {
                return $fieldValue->format($this->dateTimeFormat);
            }

            if ($field->type === 'date') {
                return $fieldValue->format($this->dateFormat);
            }
            if ($field->type === 'time') {
                return $fieldValue->format($this->timeFormat);
            }
        }

        //field value can be null
        return (string)$fieldValue;
    }

    public function setWithLineBreaks(string $tag, string $value): void
    {
        $this->dangerouslySetHtml($tag, nl2br(htmlspecialchars($value)));
    }
}
