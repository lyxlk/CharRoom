<?php
namespace Swoole;

class ArrayObject implements \ArrayAccess, \Serializable, \Countable, \Iterator
{
    protected $array;
    protected $index = 0;

    function __construct($array = array())
    {
        $this->array = $array;
    }

    function current()
    {
        return current($this->array);
    }

    function key()
    {
        return key($this->array);
    }

    function valid()
    {
        return count($this->array) >= $this->index;
    }

    function rewind()
    {
        $this->index = 0;
        return reset($this->array);
    }

    function next()
    {
        $this->index++;
        return next($this->array);
    }

    function serialize()
    {
        return serialize($this->array);
    }

    /**
     * @return StringObject
     */
    function json()
    {
        return new StringObject(json_encode($this->array));
    }

    function indexOf($value)
    {
        return $this->search($value);
    }

    function lastIndexOf($value)
    {
        $find = false;
        foreach ($this->array as $k => $v)
        {
            if ($value == $v)
            {
                $find = $k;
            }
        }

        return $find;
    }

    function unserialize($str)
    {
        $this->array = unserialize($str);
    }

    function __get($key)
    {
        return $this->array[$key];
    }

    function __set($key, $value)
    {
        $this->array[$key] = $value;
    }

    function set($key, $value)
    {
        $this->array[$key] = $value;
    }

    /**
     * @param $key
     * @return ArrayObject|StringObject
     */
    function get($key)
    {
        return self::detectType($this->array[$key]);
    }

    function delete($key)
    {
        if (isset($this->array[$key]))
        {
            return false;
        }
        else
        {
            unset($this->array[$key]);

            return true;
        }
    }

    /**
     * 删除所有数据
     */
    function clear()
    {
        $this->array = array();
    }

    function offsetGet($k)
    {
        return $this->array[$k];
    }

    function offsetSet($k, $v)
    {
        $this->array[$k] = $v;
    }

    function offsetUnset($k)
    {
        unset($this->array[$k]);
    }

    function offsetExists($k)
    {
        return isset($this->array[$k]);
    }

    function contains($val)
    {
        return in_array($val, $this->array);
    }

    function exists($key)
    {
        return array_key_exists($key, $this->array);
    }

    function join($str)
    {
        return new StringObject(implode($str, $this->array));
    }

    function insert($offset, $val)
    {
        if ($offset > count($this->array))
        {
            return false;
        }
        return new ArrayObject(array_splice($this->array, $offset, 0, $val));
    }

    /**
     * @param $find
     * @param $strict
     * @return mixed
     */
    function search($find, $strict = false)
    {
        return array_search($find, $this->array, $strict);
    }

    /**
     * @return int
     */
    function count()
    {
        return count($this->array);
    }

    /**
     * @return bool
     */
    function isEmpty()
    {
        return empty($this->array);
    }

    /**
     * 计算数组的合
     */
    function sum()
    {
        return array_sum($this->array);
    }

    /**
     * @return float|int
     */
    function product()
    {
        return array_product($this->array);
    }

    /**
     * 向数组尾部追加元素
     * @return int
     */
    function append($val)
    {
        return array_push($this->array, $val);
    }

    /**
     * 向数组头部追加元素
     * @return int
     */
    function prepend($val)
    {
        return array_unshift($this->array, $val);
    }

    /**
     * 从数组尾部弹出元素
     * @return mixed
     */
    function pop()
    {
        return array_pop($this->array);
    }

    /**
     * 从数组头部弹出元素
     * @return mixed
     */
    function shift()
    {
        return array_shift($this->array);
    }

    /**
     * 数组切片
     * @param $offset
     * @param $length
     * @return ArrayObject
     */
    function slice($offset, $length = null)
    {
        return new ArrayObject(array_slice($this->array, $offset, $length));
    }

    /**
     * 数组随机取值
     * @return mixed
     */
    function randGet()
    {
        return self::detectType($this->array[array_rand($this->array, 1)]);
    }

    /**
     * 移除元素
     * @param $value
     * @return ArrayObject
     */
    function remove($value)
    {
        $key = $this->search($value);
        if ($key)
        {
            unset($this->array[$key]);
        }

        return $this;
    }

    /**
     * 遍历数组
     * @param $fn callable
     * @return ArrayObject
     */
    function each(callable $fn)
    {
        if (array_walk($this->array, $fn) === false)
        {
            throw new \RuntimeException("array_walk() failed.");
        }

        return $this;
    }

    /**
     * 遍历数组，并构建新数组
     * @param $fn callable
     * @return ArrayObject
     */
    function map(callable $fn)
    {
        return new ArrayObject(array_map($fn, $this->array));
    }

    /**
     * 用回调函数迭代地将数组简化为单一的值
     * @param $fn callable
     * @return mixed
     */
    function reduce(callable $fn)
    {
        return array_reduce($this->array, $fn);
    }

    /**
     * 返回所有元素值
     *  @return ArrayObject
     */
    function values()
    {
        return new ArrayObject(array_values($this->array));
    }

    /**
     * array_column
     */
    function column($column_key, $index = null)
    {
        if ($index)
        {
            return array_column($this->array, $column_key, $index);
        }
        else
        {
            return new ArrayObject(array_column($this->array, $column_key));
        }
    }

    /**
     * 返回数组的KEY
     */
    function keys($search_value = null, $strict = false)
    {
        return new ArrayObject(array_keys($this->array, $search_value, $strict));
    }

    /**
     * 数组去重
     */
    function unique($sort_flags = SORT_STRING)
    {
        return new ArrayObject(array_unique($this->array, $sort_flags));
    }

    /**
     * 排序
     */
    function sort($sort_flags = SORT_REGULAR)
    {
        $newArray = $this->array;
        sort($newArray, $sort_flags);

        return new ArrayObject($newArray);
    }

    /**
     * 数组反序
     */
    function reverse($preserve_keys = false)
    {
        return new ArrayObject(array_reverse($this->array, $preserve_keys));
    }

    /**
     * 数组元素随机化
     * @return ArrayObject
     */
    function shuffle()
    {
        if (shuffle($this->array) === false)
        {
            throw new \RuntimeException("shuffle() failed.");
        }

        return $this;
    }

    /**
     * 将一个数组分割成多个数组
     */
    function chunk($size, $preserve_keys = false)
    {
        return new ArrayObject(array_chunk($this->array, $size, $preserve_keys));
    }

    /**
     * 交换数组中的键和值
     * @return ArrayObject
     */
    function flip()
    {
        return new ArrayObject(array_flip($this->array));
    }

    /**
     * 过滤数组中的元素
     * @param $fn callable
     * @return ArrayObject
     */
    function filter(callable $fn, $flag = 0)
    {
        return new ArrayObject(array_filter($this->array, $fn, $flag));
    }

    static function detectType($value)
    {
        if (is_array($value))
        {
            return new ArrayObject($value);
        }
        elseif (is_string($value))
        {
            return new StringObject($value);
        }
        else
        {
            return $value;
        }
    }

    /**
     * 转换为一个PHP数组
     * @return array
     */
    function toArray()
    {
        return $this->array;
    }
}
