<?php namespace Taggr;

class Taggr 
{

	protected $key;
	protected $username;
	protected $tags = array();
	protected $posts = array();
	protected $offset;

	static $LIMIT = 20;

	public function __construct($key = null, $username = null)
	{
		$this->key = $key;
		$this->username = $username;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function setkey($key)
	{
		$this->key = $key;
		return $this;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function getPosts()
	{
		return $this->posts;
	}

	public function setPosts($posts)
	{
		$this->posts = $posts;
		return $this;
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function setTags($tags)
	{
		$this->tags = $tags;
		return $this;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	public function getLimit()
	{
		return self::$LIMIT;
	}

	/**
	* Increments the offset based on the supplied integer
	*
	* @param integer
	* @return self
	**/

	public function incrementOffset($amount)
	{
		$this->offset+=$amount;
		return $this;
	}

	/**
	* Fetches tags from api, filters out duplicate tags and pushes into the tags array
	*
	* @return array
	**/

	public function fetchTags()
	{
		// check if key and username isnt null else throw exception
		if (is_null($this->getKey()) && is_null($this->getUsername()))
			throw new \Exception("Please provide valid credentials - current username: {$this->getUsername()} current key {$this->getKey()}", 1);

		// do request for posts in while loop continue untill none are returned
		
		while ( count($posts = $this->fetchPosts($this->getOffset(), $this->getLimit())) > 0 ) {
			$this->setPosts(array_merge($this->posts, $posts));
			$this->incrementOffset($this->getLimit());
		}

		$this->getTagsFromPosts($this->getPosts());
		$tags = $this->getTags();
		sort($tags, SORT_NATURAL | SORT_FLAG_CASE);
		$this->setTags($tags);

		return $this->getTags();
	}

	/**
	* Fetches the posts from tumblr api based on the passed username, offset and limit
	*
	* @param integer
	* @param integer
	* @return object
	**/

	public function fetchPosts($offset = 0, $limit = 20)
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => "api.tumblr.com/v2/blog/{$this->getUsername()}.tumblr.com/posts?api_key={$this->getKey()}&offset={$offset}&limit={$limit}"
		));

		$request = curl_exec($ch);
		curl_close($ch);

		return json_decode($request)->response->posts;
	}

	/**
	* Iterate through posts array and pull out the tags, ignore any duplicate tags
	*
	* @param array
	* @return void
	**/

	public function getTagsFromPosts($posts)
	{
		foreach ($posts as $key => $value) {
			$tags = $this->filterTags($value->tags, $this->getTags());
			$this->setTags(array_merge($this->getTags(), $tags));
		}
	}

	/**
	* Compare the new tags found from a post to the current tags and return any tags that are not dupliate
	*
	* @param array
	* @param array
	* @return array
	**/

	public function filterTags($newTags, $currentTags)
	{
		$tagsToMerge = array();

		foreach ($newTags as $key => $tag) 
			if ( ! in_array($tag, $currentTags)) $tagsToMerge[] = $tag; 

		return $tagsToMerge;
	}

	/**
	* Write tags to file
	*
	* @param array
	* @param string
	* @return void
	**/

	public function writeTags($tags, $filename = 'tags.json')
	{
		$toWrite = array(
			'username' => $this->getUsername(),
			'tags'     => $tags
		);

		file_put_contents($filename, json_encode($toWrite));
	}

	/**
	* Read tags from file, return with json header
	*
	* @return void
	**/

	public function readTags()
	{
		header('Content-Type: application/json');
		echo file_get_contents('tags.json');
	}
}

?>