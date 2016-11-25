<?php
namespace Uuling\Socialite;

/**
 * trait ItemTrait
 */
trait ItemTrait
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Return the items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get an item from the items by key.
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    public function getItem($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return $default instanceof \Closure ? $default() : $default;
    }

    /**
     * Set an item in the item by key.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return $this
     */
    public function setItem($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param  mixed $key
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Merge the items with the given items.
     *
     * @param  array $items
     * @return $this
     */
    public function merge(array $items)
    {
        $this->items = array_merge($this->items, $items);

        return $this;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getItems();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->getItems(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->getItem($key);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}