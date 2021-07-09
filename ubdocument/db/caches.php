<?php

$definitions = array(
    // Cache course contacts for the courses
    'table_definition' => array(
            'mode' => cache_store::MODE_APPLICATION,
            'persistent' => true,
            'simplekeys' => true,
            'ttl' => 3600,
    ),
);
