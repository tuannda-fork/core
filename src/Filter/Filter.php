<?php

namespace Terranet\Administrator\Filter;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Terranet\Administrator\Architect;
use Terranet\Administrator\Contracts\Form\Queryable;
use Terranet\Administrator\Traits\Form\ExecutesQuery;
use Terranet\Translatable\Translatable;

abstract class Filter implements Queryable
{
    use ExecutesQuery;

    /** @var string */
    protected $id;

    /** @var string */
    protected $title;

    /** @var mixed */
    protected $value;

    /**
     * Generic constructor.
     *
     * @param $title
     * @param null $id
     */
    public function __construct($title, $id = null)
    {
        $this->title = Architect::humanize($title);
        $this->id = Str::snake($id ?: $this->title);
    }

    /**
     * @param $title
     * @param null $id
     * @param \Closure $callback
     *
     * @return static
     */
    public static function make($title, $id = null, \Closure $callback = null): self
    {
        $instance = new static($title, $id);

        if (null !== $callback) {
            $callback->call($instance, $instance);
        }

        return $instance;
    }

    /**
     * Return Element ID.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Form name.
     *
     * @return string
     */
    public function name()
    {
        $parts = explode('.', $this->id());

        if (\count($parts) > 1) {
            $first = Arr::first($parts);
            $other = \array_slice($parts, 1);

            $other = array_map(function ($part) {
                return "[$part]";
            }, $other);

            return implode('', array_merge([$first], $other));
        }

        return $this->id();
    }

    /**
     * Return Element title.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Set Filter value.
     *
     * @param $value
     *
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function render()
    {
        return View::make($this->template(), [
                'field' => $this,
            ] + $this->renderWith());
    }

    /**
     * @return array
     */
    protected function renderWith()
    {
        return [];
    }

    /**
     * @param string $page
     * @param string $field
     *
     * @return string
     */
    protected function template(): string
    {
        return sprintf(
            'administrator::filters.%s',
            $this->component
        );
    }

    /**
     * @param $model
     *
     * @return bool
     */
    protected function shouldSearchInTranslations($model): bool
    {
        return $model instanceof Translatable
            && \in_array($this->name(), $model->getTranslatedAttributes(), true);
    }
}
