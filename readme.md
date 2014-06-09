# Taggr - Fetch a users tumblr post tags

Construct a new taggr class with your application key and a username
```php
$taggr = new Taggr\Taggr('key', 'username');
```

Fetch tags with the fetchTags function
```php
$tags = $taggr->fetchTags();
```

Read and write tags with the readTags and writeTags functions
```php
$taggr->writeTags($tags, 'tags.json');
$taggr->readTags();
```