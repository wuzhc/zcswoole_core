<?php

namespace zcswoole;


interface ServiceLocatorInterface
{
    function has($id);
    function get($id);
    function set($id, $definition);
}