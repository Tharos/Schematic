Následující dokumentace je také [dostupná v českém jazyce](https://github.com/Tharos/Schematic/blob/master/docs/index-cs.md).

# Schematic

**Table of contents**

*:heart: Since I'm not a native English speaker, I expect that the following documentation contains a lot of grammar mistakes and formulations that a native speaker would never use. If you find Schematic interesting, you can contribute to it by sending a pull request with corrections. Thank you!*

- [Introduction](#home)
- [How Schematic works](#introduction)
	- [`Entry`](#entry)
	- [`Entries`](#entries)
	- [`EntryViewer`](#entryviewer)
- [Mapping basics](#properties)
- [Associations mapping](#associations)
- [Working with collections](#collections)
	- [`Entries::has($key), Entries::get($key)`](#entries-getters)
	- [ `Entries::remove(...$keys)`](#entries-remove)
	- [ `Entries::reduceTo(...$keys)`](#entries-reduceto)
	- [`Entries::transform(Closure $callback, $entryClass = NULL)`](#entries-transform)
- [View objects in detail](#view-objects)
	- [ `EntryViewer::viewEntry($entry, Closure $converter)`](#entryviewer-viewentry)
	- [ `EntryViewer::viewEntries($entries, Closure $singleEntryConverter)`](#entryviewer-viewentries)
- [Advanced features of `Entry` class](#entry-advanced)
	- [ Embedded entries](#embedded-entries)
	- [Inheritance, traits](#inheritance)
	- [Parameter `$entriesClass` in `Entry::__construct`](#entriesclass)

## <a name="home"></a>Introduction

Schematic is a minimalist library (only three classes and less than 400 lines of code in total) that helps your IDE to understand the structure of associative arrays in your PHP applications.

It brings a lot of benefits:

- You will no longer need a fulltext search when searching for array value accesses by a given key. Your IDE will find all those key usages for you.
- Renaming array keys will be much easier! From now on, your IDE does that job for you.
- Typos in key names will no longer stay hidden from your eyes. Your IDE will warn you when working with non-existing keys.
- You will be able to replace a lot of your type hints using a general `array` by hints using concete classes.

And as a bonus, Schematic comes up with a way to initialize template data using *view objects*.

In short, Schematic will make your code more readable, less error-prone, and easier to understand and refactor. All that at minimum cost. Performance overhead of Schematic is absolutely negligible and you can learn how to use it in just a few minutes.

## <a name="introduction"></a>How Schematic works

Schematic consists of following three classes:

### <a name="entry"></a>`Entry`

The heart of the library. An abstract ancestor of all types of entries in your application. It *wraps* an associative array and, using *@property-read* annotations, lets your IDE understand its structure.

This is how a usual work with this class looks like:

```php
// Initialization of a sample associative array

$apiResponse = [
	'id' => 5,
	'title' => 'Schematic introduction',
	'subtitle' => NULL,
	'published' => new DateTime('2016-10-13 14:00:00'),
	'public' => TRUE,
	'author' => [
		'id' => 2,
		'name' => 'Vojtěch Kohout',
		'email' => NULL,
	],
];

// Definition of entry class descendants (that means concrete entries)

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|NULL $email
 */
class Author extends Entry
{
}

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string|NULL $subtitle
 * @property-read \DateTime $published
 * @property-read bool $public
 * @property-read Author $author
 */
class Article extends Entry
{

	protected static $associations = [
		'author' => Author::class,
	];

}

// Usage of defined classes for data access

$article = new Article($apiResponse);

echo $article->title; // Schematic introduction
echo $article->author->name; // $article->author instanceof Author, outputs Vojtěch Kohout
```

Entries in Schematic are read-only. The purpose of the library is to help with data reading.

### <a name="entries"></a>`Entries`

A collection of entries implementing `Iterator` and `Countable` interfaces and providing several useful methods. It creates instances of `Entry` only when needed (that means on demand). By default, it only wraps nested associative arrays holding related data.

This is how a usual work with this class looks like:

```php
$apiResponse = [
	[
		'id' => 10,
		'name' => 'PHP',
	],
	[
		'id' => 11,
		'name' => 'Library',
	],
	[
		'id' => 12,
		'name' => 'Recommended',
	],
];


/**
 * @property-read int $id
 * @property-read string $name
 */
class Tag extends Entry
{
}


/** @var Entries|Tag[] $tags */
$tags = new Entries($apiResponse, Tag::class);

echo count($tags); // 3

foreach ($tags as $tag) {
	echo $tag->name; // gradually outputs PHP, Library and Recommended
}
```

Collection `Entries` is immutable. Its methods return new instances of `Entries` rather than changing the state.

### <a name="entryviewer"></a>`EntryViewer`

Helper for creating *view objects*.

Imagine we have an instance of `Author` class containing properties `$id` and `$name`, and we want to output them in a template. This is how a usual solution would probably look like:

```php
// Somewhere in a controller

$template->author = $author;

// Output in a template
This article was written by author {{ author.name }} with ID {{ author.id }}.
```

This - quite usual - solution has one big disadvantage. If you decide to rename the property `$name` to `$title` using a *rename* function in your IDE, you will most probably make a bug. Even the most modern IDEs today still cannot resolve access to properties in templates (Twig, Latte, Smarty…). As a result, you will be still refering to the old property name (`$name`) in your templates despite having your IDE done the renaming for you.

Schematic offers following solution:

```php
// Somewhere in your controller

$template->author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->name,
	];
}));

// Output in a template
This article was written by author {$author->name} with ID {$author->id}.
```

Using this middle layer, Schematic lowers the coupling between the template and the API of the class `Author`.

If you decide to rename the property `$name` to `$title` using a *rename* function in your IDE now, you will get the following result:

```php
// Somewhere in a controller

$template->author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->title,
	]
}));

// Output in a template
This article was written by author {$author->name} with ID {$author->id}.
```

Note that there is still a legacy property name `$name` in the template, but it can be easily refactored in another step. The important thing is that **there wasn't an access to undefined property at all**.

## <a name="properties"></a>Mapping basics

Schematic helps your IDE understand the structure of an associative array by wrapping it with an instance of a class that has appropriate `@property-read` annotations. Method `Entry::__get` then only secures correct data reading from the wrapped array.

The usage of annotations is straightforward:

```php
/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|NULL $email
 * @property-read \DateTime $registered
 * @property-read int $articlesCount
 * @property-read float $rating
 * @property-read bool $premium
 */
class Author extends Entry
{
}
```

For the sake of completeness, lets take a look at an array which could be wrapped with the `Author` class:

```php
[
	'id' => 5,
	'name' => 'Vojtěch Kohout',
	'email' => NULL,
	'registered' => new DateTime('2016-10-22 12:45:01'),
	'articlesCount' => 12,
	'rating' => 1.21,
	'premium' => FALSE,
]
```

Schematic is a minimalist library and that's why it doesn't parse PHPDoc annotations and doesn't validate data types when accessing array data. PHPDoc annotations take place in Schematic *only for your IDE's needs*. Only validation is implemented to throw an exception when **accessing a property that misses related data in the wrapped array**. However, you can still access a property that has related data in the wrapped array even when it misses a PHPDoc annotation, but you get a warning from your IDE…

## <a name="associations"></a>Associations mapping

Every association mapping system must cover variants `many-to-one` and `one-to-many`. Schematic uses annotations (for your IDE) and static array `Entry::$associations` for that job.

```php
$articlePayload = [
	'id' => 5,
	'title' => 'Schematic introduction',
	'author' => [
		'id' => 1,
		'name' => 'Vojtěch Kohout',
	],
	'tags' => [
		[
			'id' => 10,
			'name' => 'PHP',
		],
		[
			'id' => 11,
			'name' => 'Library',
		],
		[
			'id' => 12,
			'name' => 'Recommended',
		],
	],
];


/**
 * @property-read int $id
 * @property-read string $title
 * @property-read Author $author
 * @property-read Tag[] $tags
 */
class Article extends Entry
{

	protected static $associations = [
		'author' => Author::class,
		'tags[]' => Tag::class,
	];

}

/**
 * @property-read int $id
 * @property-read string $name
 */
class Author extends Entry
{
}

/**
 * @property-read int $id
 * @property-read string $name
 */
class Tag extends Entry
{
}


$article = new Article($articlePayload);

echo $article->title; // Schematic introduction
echo $article->author->name; // $article->author instanceof Author, outputs Vojtěch Kohout

foreach ($article->tags as $tag) {
	echo $tag->name; // $tag instanceof Tag, gradually outputs PHP, Library and Recommended
}
```

A key in an array `$associations` defines a name of an association and its multiplicity and a value in that array defines: type of an associated entry in case of the `many-to-one` association; type of entries in an associated `IEntries` collection in case of the `one-to-many` association.

## <a name="collections"></a>Working with collections

Instances of a collection `Entries` are created by two ways:

- in background when accessing `one-to-many` association,
- or manually using the `new` keyword.

Creating a new instance using `new` is quite straightforward. The constructor `Entries::__construct` accepts data and an information defining a type of the data.

```php
$tagsPayload = [
	2 => [
		'id' => 10,
		'name' => 'PHP',
	],
	3 => [
		'id' => 11,
		'name' => 'Library',
	],
	4 => [
		'id' => 12,
		'name' => 'Recommended',
	],
];


/**
 * @property-read int $id
 * @property-read string $name
 */
class Tag extends Entry
{
}


/** @var Entries|Tag[] $tags */
$tags = new Entries($tagsPayload, Tag::class);

echo count($tags); // 3

foreach ($tags as $tagIndex => $tag) {
	echo $tagIndex . ': ' . $tag->name; // gradually outputs 2: PHP, 3: Library and 4: Recommended
}
```

Note the `@var` annotation helping an IDE to properly understand the values while iterating over `$tags`.

Besides implementing interfaces `Iterator` and `Countable` the class `Entries` contains some useful methods. Let's take a look at them.

Original indexes are also available using `foreach` even when defined by `one-to-many` association: 

```php
/**
 * @property-read Tag[] $tags
 */
class Article extends Entry
{
	protected static $associations = [
		'tags[]' => Tag::class,
	];
}
```

###<a name="entries-toarray"></a> `Entries::toArray`

A method that returns an *array* of instances of type defined in `Entries::__construct`. Following code will work:

```php
/** @var array|Tag[] $tags */
$tags = $tags->toArray();

foreach ($tags as $tag) {
	echo $tag->name; // $tag instanceof Tag, gradually outputs PHP, Library and Recommended
}
```

### <a name="entries-getters"></a>`Entries::has($key), Entries::get($key)`

Schematic allows you to read entries by keys. Let's take a look at an example:

```php
var_dump($tags->has(4)); // bool(true)
var_dump($tags->has(100)); // bool(false)

/** @var Tag $tag */
$tag = $tags->get(4);

echo $tag->name; // Recommended

$tags->get(100); // throws exception since the key 100 doesn't exist in the collection
```

### <a name="entries-remove"></a>`Entries::remove(...$keys)`

A method that returns a new instance of collection `Entries` which doesn't contain entries with the keys `$keys` anymore.

```php
echo count($tags); // 3

$tags = $tags->remove(4);

echo count($tags); // 2

$tags = $tags->remove(2, 3); // or $tags->remove(...[2, 3])

echo count($tags); // 0

$tags->remove(100); // throws exception since the key 100 doesn't exist in the collection
```

### <a name="entries-reduceto"></a>`Entries::reduceTo(...$keys)`

A method that returns a new instance of collection `Entries` which is reduced to entries with keys `$keys` only.

```php
echo count($tags); // 3

$tags = $tags->reduceTo(...[2, 3]); // or $tags->reduceTo(2, 3)

echo count($tags); // 2

$tags->reduceTo(2, 20, 21); // throws exception since keys 20 and 21 don't exist in the collection
```

### <a name="entries-transform"></a>`Entries::transform(Closure $callback, $entryClass = NULL)`

A method that returns a new instance of collection `Entries` to which it provides its array `$data` modified using `$callback`.

```php
class SerializableTag extends Tag implements JsonSerializable
{

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
		];
	}

}

/** @var Entries|SerializableTag[] $tags */
$tags = $tags->transform(
	function (array $data) {
		foreach ($data as $key => $value) {
			$data[$key]['name'] = strtoupper($value['name']);
		}
		return $data;
	},
	SerializableTag::class
);

foreach ($tags as $tag) {
	echo json_encode($tag);
}

/*
Outputs:
{"id":10,"name":"PHP"}
{"id":11,"name":"LIBRARY"}
{"id":12,"name":"RECOMMENDED"}
*/
```

## <a name="view-objects"></a>View objects in detail

In the chapter [How Schematic works](#introduction) we've already introduced the class `EntryViewer` and a motivation for using the view objects. Let's take a deep view on an API of the class `EntryViewer`.

### <a name="entryviewer-viewentry"></a>`EntryViewer::viewEntry($entry, Closure $converter)`

A method that returns an instance (usually of `stdClass`) obtained by passing an `$entry` parameter to a conversion function `$converter`.

```php
$author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->name,
	];
}));

print_r($author);

/*
Outputs:
stdClass Object
(
    [id] => 1
    [name] => Vojtěch Kohout
)
*/
```

Of course the convertion function itself can use the helper `EntryViewer` in its body. It is very useful when working with associations:

```php
$articleView = EntryViewer::viewEntry($article, function (Article $article) {
	return [
		'id' => $article->id,
		'title' => strtoupper($article->title),
		'author' => EntryViewer::viewEntry($article->author, function (Author $author) {
			return [
				'id' => $author->id,
				'name' => $author->name,
			];
		}),
	];
});

print_r($articleView);

/*
Outputs:
stdClass Object
(
    [id] => 5
    [title] => SCHEMATIC INTRODUCTION
    [author] => stdClass Object
        (
            [id] => 1
            [name] => Vojtěch Kohout
        )

)
*/
```

Pro tip: if you plan to pass equal entries to various templates, it is recommended to encapsulate the convertion map in a standalone service. It will abridge your code a lot:

```php
$articleView = EntryViewer::viewEntry($article, function (Article $article) {
	return $this->articleConverter->convertArticleToArray($article);
});
```

###<a name="entryviewer-viewentries"></a> `EntryViewer::viewEntries($entries, Closure $singleEntryConverter)`

A methot that returns an *array* of instances (usually of `stdClass`) obtained by passing every single entry from `$entries` collection (array or an instance of `Traversable`) to a conversion function `$singleEntryConverter`. An entry that translates to `NULL` in conversion function will be omitted in a result array.

It sounds complicated but an example will clarify it:

```php
$tagsView = EntryViewer::viewEntries($tags, function (Tag $tag) {
	return [
		'id' => $tag->id,
		'name' => $tag->name,
	];
});

print_r($tagsView);

/*
Outputs:
Array
(
    [0] => stdClass Object
        (
            [id] => 10
            [name] => PHP
        )

    [1] => stdClass Object
        (
            [id] => 11
            [name] => Library
        )

    [2] => stdClass Object
        (
            [id] => 12
            [name] => Recommended
        )

)
*/
```

Let's take a look at the possibility to omit some entry:

```php
$tagsView = EntryViewer::viewEntries($tags, function (Tag $tag) {
	return $tag->id === 11 ? NULL : [
		'id' => $tag->id,
		'name' => $tag->name,
	];
});

print_r($tagsView);

/*
Outputs:
Array
(
    [0] => stdClass Object
        (
            [id] => 10
            [name] => PHP
        )

    [2] => stdClass Object
        (
            [id] => 12
            [name] => Recommended
        )

)
*/
```

And finally let's take a look at a complex usage of described features:

```php
$articleView = EntryViewer::viewEntry($article, function (Article $article) {
	return [
		'id' => $article->id,
		'title' => strtoupper($article->title),
		'author' => EntryViewer::viewEntry($article->author, function (Author $author) {
			return [
				'id' => $author->id,
				'name' => $author->name,
			];
		}),
		'tags' => EntryViewer::viewEntries($article->tags, function (Tag $tag) {
			return $tag->id === 11 ? NULL : [
				'id' => $tag->id,
				'name' => $tag->name,
			];
		}),
	];
});

print_r($articleView);

/*
Outputs:
stdClass Object
(
    [id] => 5
    [title] => SCHEMATIC INTRODUCTION
    [author] => stdClass Object
        (
            [id] => 1
            [name] => Vojtěch Kohout
        )

    [tags] => Array
        (
            [0] => stdClass Object
                (
                    [id] => 10
                    [name] => PHP
                )

            [1] => stdClass Object
                (
                    [id] => 12
                    [name] => Recommended
                )

        )

)
*/
```

Remember that we do all this fun with the view objects in order to break the dependency between templates and APIs of entries. That will make a refactoring easier.

If we use this pattern strictly, we can be sure that automatic refactoring won't break our application.

## <a name="entry-advanced"></a>Advanced features of `Entry` class

The class `Entry` offers much more than we've [seen](#entry) in the chapter focused on Schematic basics.

### <a name="embedded-entries"></a>Embedded entries

When you decide to use Schematic for mapping results of SQL queries, you will encounter a problem: results of SQL queries are relations (we mean "two-dimensional tables") and DBAL libraries won't transform them into nested associative arrays without our help.

If we have following SQL query:

```sql
SELECT
	article.id, article.title,
	author.id, author.name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

a result in following format would be perfect for mapping:

```php
[
	'id' => 5,
	'title' => 'Schematic introduction',
	'author' => [
		'id' => 1,
		'name' => 'Vojtěch Kohout',
	]
]
```

but instead of that we'll end up with a following array:

```php
[
	'id' => 1,
	'title' => 'Schematic introduction',
	'name' => 'Vojtěch Kohout',
]
```

Flat relation, even lacking article IDs due to a conflict of multiple columns named `id`.

Poor situation. However Schematic can gracefully resolve it using so called embedded entries.

If we rewrite mentioned SQL query to a following version:

```sql
SELECT
	article.id, article.title,
	author.id a_id, author.name a_name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

we'll end up with a result:

```php
[
	'id' => 5,
	'title' => 'Schematic introduction',
	'a_id' => 1,
	'a_name' => 'Vojtěch Kohout',
]
```

which is much better: we didn't lose article IDs and thanks to the prefix `a_` we can reliably tell columns that belongs to the table `author` from columns that belongs to the table `article`.

And this is a result that can already be mapped by Schematic. All we have to do i to define the class `Article` this way:

```php
/**
 * @property-read int $id
 * @property-read string $title
 * @property-read Author $author
 */
class Article extends Entry
{

	protected static $associations = [
		'author.a_' => Author::class,
	];

}
```

A period in the association definition in the array `$associations` tells that an associated entry is embedded in a main entry. It means that main and associated entries are at the same level in a wrapped array.

Now, let's have a look that properties can be accessed as expected:

```php
echo $article->title; // Schematic introduction
echo $article->author->name; // Vojtěch Kohout
```

Schematic lets us be even briefer since we can define an `author` association also this way:


```php
protected static $associations = [
	'author.' => Author::class,
];
```

In this case so called default prefix is searched in the wrapped array and it consists of the name of the association followed by an underscore; thus `author_` in our example.

An SQL query directly leading to such an array would look like this:

```sql
SELECT
	article.id, article.title,
	author.id author_id, author.name author_name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

This way you can gracefully map results of queries that select rows from a table and join its many-to-one and one-to-one associations. That's exactly the example above: a type of an association between the article and the author is many-to-one.

However it is necessary to handle results of queries that select rows from a table and join its one-to-many and many-to-many associations a little bit more prudently. It is necessary to understand well nature of data duplicities in such results.

### <a name="inheritance"></a>Inheritance, traits

Following features are not features of Schematic, they are features of modern IDEs. The annotation `@property-read` can be inherited and also reused using traits:

```php
/**
 * @property-read string $name
 */
trait Named
{
}

/**
 * @property-read int $id
 */
abstract class Identified extends Entry
{
}

class Author extends Identified
{

	use Named;

}

class Tag extends Entry
{

	use Named;

}


// Ve všech níže uvedených případech IDE rozpozná na instancích properties, ke kterým se přistupuje

// In a following example an IDE recognizes all accessed properties
echo $author->id;
echo $author->name;
echo $tag->name;
```

### <a name="entriesclass"></a>Parameter `$entriesClass` in `Entry::__construct`

Of course you can group Schematic entries in a different collection than the built-in `Entries`. However, as we stated before, collections of entries are not only created using `new` keyword, but also in background when accessing `one-to-many` association.

And it is a parameter `$entriesClass` in the `Entry::__construct` that allows you to define what class representing an collection will by instantiated when accessing one-to-many association.
