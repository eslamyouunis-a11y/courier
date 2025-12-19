<?php

if (!function_exists('it')) {
    function it(string $description, callable $closure = null) {}
}

if (!function_exists('expect')) {
    function expect($value = null) {
        return new class {
            public function toBeTrue() {}
            public function toBe($value) {}
        };
    }
}
