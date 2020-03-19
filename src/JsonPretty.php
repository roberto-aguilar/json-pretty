<?php

namespace Rcubitto\JsonPretty;

function dd()
{
    array_map(function($x) {
        var_dump($x);
    }, func_get_args());
    die;
}

class JsonPretty
{
    protected $fifo = []; // up
    protected $lifo = []; // down

    public static function format($sample)
    {
        return (new self)->analyze((array) $sample)->build();
    }

    //

    private function build()
    {
        return "<pre>" .
            implode(PHP_EOL, $this->fifo) .
            PHP_EOL .
            implode(PHP_EOL, array_reverse($this->lifo))
        . "</pre>";
    }

    private function analyze($sample, $depth = 1)
    {
        $keys = array_keys($sample);

        $isArray = false;
        foreach ($keys as $key) {
            if (is_integer($key)) {
                $isArray = true;
                break;
            }
        }

        if ($isArray) {
            $this->stackParenthesis($depth - 1);
            foreach ($sample as $value) {
                $this->analyze($value, $depth + 1);
            }
        } else {
            $this->stackBrackets($depth - 1);
            foreach ($sample as $key => $value) {
                $valueColor = $this->stringColor($value);
                $value = $this->stringValue($value);
                $this->stackKeyValue($depth, $key, $value, $valueColor);
            }
        }

        return $this;
    }

    private function stringColor($value)
    {
        if (is_string($value)) return 'green';

        if (is_bool($value)) return 'red';

        return 'blue'; // numbers
    }

    private function stringValue($value)
    {
        if (is_string($value)) return "\"$value\"";

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    private function indent($depth)
    {
        return str_repeat(' ', 4 * $depth);
    }

    private function stackBrackets($depth)
    {
        $this->fifo[] = $this->indent($depth) . "<span style=\"color:black\">{</span>";
        $this->lifo[] = $this->indent($depth) . "<span style=\"color:black\">}</span>";
    }

    private function stackParenthesis($depth)
    {
        $this->fifo[] = $this->indent($depth) . "<span style=\"color:black\">[</span>";
        $this->lifo[] = $this->indent($depth) . "<span style=\"color:black\">]</span>";
    }

    private function stackKeyValue($depth, $key, $value, $valueColor)
    {
        $this->fifo[] = $this->indent($depth) . "<span style=\"color:black\">$key</span>: <span style=\"color:$valueColor\">$value</span>";
    }
}
