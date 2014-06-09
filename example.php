<?php

require 'src/Taggr/Taggr.php';

$taggr = new Taggr\Taggr('key', 'username');
$tags = $taggr->fetchTags();
$taggr->writeTags($tags, 'tags.json');
$taggr->readTags();