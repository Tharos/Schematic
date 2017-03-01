Following documentation is also [available in English language](https://github.com/Tharos/Schematic/blob/master/docs/index.md).

# Schematic

**Obsah**

- [Úvod](#home)
- [Jak Schematic funguje](#introduction)
	- [`Entry`](#entry)
	- [`Entries`](#entries)
	- [`EntryViewer`](#entryviewer)
- [Základy mapování](#properties)
- [Mapování asociací](#associations)
- [Práce s kolekcemi](#collections)
	- [`Entries::has($key), Entries::get($key)`](#entries-getters)
	- [ `Entries::remove(...$keys)`](#entries-remove)
	- [ `Entries::reduceTo(...$keys)`](#entries-reduceto)
	- [`Entries::transform(Closure $callback, $entryClass = NULL)`](#entries-transform)
- [ View objekty podrobně](#view-objects)
	- [ `EntryViewer::viewEntry($entry, Closure $converter)`](#entryviewer-viewentry)
	- [ `EntryViewer::viewEntries($entries, Closure $singleEntryConverter)`](#entryviewer-viewentries)
- [ Pokročilé možnosti třídy `Entry`](#entry-advanced)
	- [ Embedded záznamy](#embedded-entries)
	- [Dědičnost, traity](#inheritance)
	- [Parametr `$entriesClass` v `Entry::__construct`](#entriesclass)

## <a name="home"></a>Úvod

Schematic je minimalistická knihovna (tři třídy o celkové délce necelých 400 řádků), která umožňuje vašemu IDE porozumět struktuře asociativních polí, se kterými vaše aplikace pracují.

To má několik skvělých důsledků:

- Již nebudete muset fulltextově vyhledávat, kde všude v kódu čtete z pole podle nějakého klíče. Budete moci použít funkci IDE *find usages*.
- Přejmenovávání klíčů pole se díky tomu stane hračkou! Vše vyřeší funkce IDE *rename*.
- Již vám neuniknou žádné překlepy v názvech klíčů, protože IDE na práci s neexistujícím klíčem formou varování upozorní.
- Z vašich *type hintů* zmizí spousta obecných `array` a nahradí je vyžadování instancí konkrétních tříd.

A jako bonus vám Schematic elegantně vyřeší předávání dat do šablon formou *view objektů*.

Zkrátka Schematic váš kód zpřehlední a učiní ho méně náchylným na chyby, srozumitelnějším a snáze refaktorovatelným. To vše s minimální režií. Výkonnostní režie Schematicu je takřka neměřitelná a naučit se ho používat je záležitostí pár minut.

## <a name="introduction"></a>Jak Schematic funguje

Schematic tvoří následující tři třídy:

### <a name="entry"></a>`Entry`

Srdce knihovny. Abstraktní předek jednotlivých typů záznamů (chcete-li entit) přítomných ve vaší aplikaci. *Obaluje* vlastní asociativní pole a díky *@property-read* anotacím umožňuje IDE rozumět jejich struktuře. 

Takto vypadá typická práce s touto třídou:

```php
// Inicializace ukázkového asociativního pole

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

// Definice tříd reprezentujících entity

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

// Použití nadefinovaných tříd pro přístup k datům

$article = new Article($apiResponse);

echo $article->title; // Schematic introduction
echo $article->author->name; // $article->author instanceof Author, vypíše Vojtěch Kohout
```

Záznamy jsou ve Schematicu read-only, knihovna si klade za cíl řešit čtení dat.

### <a name="entries"></a>`Entries`

Kolekce záznamů s lazy strategií vytváření instancí `Entry`, implementující `Iterator` a `Countable` a obsahující pár dalších užitečných metod.

Takto vypadá typická práce s touto třídou:

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
	echo $tag->name; // postupně vypíše PHP, Library a Recommended
}
```

 Kolekce `Entries` je immutable, volání metod vedoucích ke změně stavu vrací novou instanci.

### <a name="entryviewer"></a>`EntryViewer`

Helper k výrobě *view objektů*.

Představte si, že máme instanci třídy `Author` s properties `$id` a `$name` a ty chceme vypsat v šabloně. Typické řešení vypadá zhruba následovně:

```php
// Kdesi v controlleru

$template->author = $author;

// Vlastní výpis v šabloně
This article was written by author {$author->name} with ID {$author->id}.
```

Toto docela přirozené řešení má jedno velké úskalí. Pokud se rozhodnete property `$name` přejmenovat na `$title` a použijete k tomu funkci IDE *rename*, spolehlivě si vyrobíte chybu, protože žádné z dnešních IDE neumí v šablonách (Twig, Latte, Smarty…) vyhledat přístupy k properties.  Ve výsledku tedy budete mít všude v aplikaci property `$title`, ale v šablonách budete přistupovat k již neexistující property `$name`.

Schematic nabízí následující řešení:

```php
// Kdesi v controlleru

$template->author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->name,
	];
}));

// Vlastní výpis v šabloně
This article was written by author {$author->name} with ID {$author->id}.
```

Vložením jistého meziprvku (mapy) Schematic rozváže přímou závislost kódu v šabloně na API třídy `Author`.

Pokud se nyní rozhodneme v IDE přejmenovat pomocí *rename* `$name` na `$title`, toto bude výsledek:

```php
// Kdesi v controlleru

$template->author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->title,
	]
}));

// Vlastní výpis v šabloně
This article was written by author {$author->name} with ID {$author->id}.
```

V šabloně sice zůstal zastaralý název property `$name`, nicméně to je možné snadno zrefaktorovat v dalším kroku. Důležité je, že v kódu **ani na okamžik nevznikla chyba**, totiž přístup k neexistující property.

## <a name="properties"></a>Základy mapování

Schematic umožňuje IDE rozumět struktuře pole tím, že jej obalí instancí třídy s odpovídajícími `@property-read` anotacemi. Metoda `Entry::__get` pak jen zajišťuje správné čtení dat z obaleného pole.

Využití anotací je přímočaré:

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
A pro úplnost si už jen ukažme příklad pole, které by mohla výše uvedená třída korektně obalit:

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

Schematic je minimalistická knihovna, a proto neparsuje PHPDoc anotace a nevaliduje, zda je čtený prvek pole požadovaného typu. PHPDoc anotace v něm reálně slouží *jen pro potřeby IDE*. Jediná naimplementovaná kontrola zajišťuje vyhození výjimky v případě, že **přistoupíte k property, pro kterou v obaleném poli neexistuje klíč**. Spíše jako kuriozitu pak už jen uveďme, že lze přistoupit k property, pro kterou sice chybí anotace, ale pro kterou v obaleném poli klíč existuje. Jen to bude s varováním ze strany IDE…

## <a name="associations"></a>Mapování asociací

Každý systém mapování asociací musí technicky pokrývat varianty `many-to-one` a `one-to-many`. Schematic k tomu používá anotace (pro IDE) a statické pole `Entry::$associations`:

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
echo $article->author->name; // $article->author instanceof Author, vypíše Vojtěch Kohout

foreach ($article->tags as $tag) {
	echo $tag->name; // $tag instanceof Tag, postupně vypíše PHP, Library a Recommended
}
```

Klíč v poli `$associations` definuje název asociace a její násobnost a hodnota v tomto poli definuje v případě `many-to-one` typ asociovaného záznamu a v případě `one-to-many` typ záznamů v asociované kolekci `IEntries`.

## <a name="collections"></a>Práce s kolekcemi

Instance kolekce `Entries` vznikají dvěma způsoby:

- na pozadí při přístupu k `one-to-many` asociaci,
- anebo ručním vytvořením.

Ruční vytvoření instance je přímočaré, konstruktor přijímá jen vlastní data a informaci, jakého typu data jsou:

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
	echo $tagIndex . ': ' . $tag->name; // postupně vypíše 2: PHP, 3: Library a 4: Recommended
}
```

Všimněte si `@var` anotace, díky které IDE bude kódu správně rozumět i při iterování.

Kromě implementování rozhraní `Iterator` a `Countable` poskytuje třída `Entries` i řadu dalších užitečných, dále popsaných metod.

Původní indexy hodnot jsou přes `foreach` také dostupné a to i při definici pomocí `one-to-many` asociace: 

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

Metoda, která vrací *pole* instancí typu určeného v `Entries::__construct`. Následující kód bude fungovat:

```php
/** @var array|Tag[] $tags */
$tags = $tags->toArray();

foreach ($tags as $tag) {
	echo $tag->name; // $tag instanceof Tag, postupně vypíše PHP, Library a Recommended
}
```

### <a name="entries-getters"></a>`Entries::has($key), Entries::get($key)`

Schematic umožňuje číst záznamy z kolekce podle klíče. Viz ukázka:

```php
var_dump($tags->has(4)); // bool(true)
var_dump($tags->has(100)); // bool(false)

/** @var Tag $tag */
$tag = $tags->get(4);

echo $tag->name; // Recommended

$tags->get(100); // skončí výjimkou, protože klíč 100 v kolekci neexistuje
```

### <a name="entries-remove"></a>`Entries::remove(...$keys)`

Metoda vrací novou instanci kolekce `Entries`, která už neobsahuje záznamy s klíči`$keys`.

```php
echo count($tags); // 3

$tags = $tags->remove(4);

echo count($tags); // 2

$tags = $tags->remove(2, 3); // anebo $tags->remove(...[2, 3])

echo count($tags); // 0

$tags->remove(100); // skončí výjimkou, protože klíč 100 v kolekci neexistuje
```

### <a name="entries-reduceto"></a>`Entries::reduceTo(...$keys)`

Metoda vrací novou instanci kolekce `Entries`, která je redukovaná pouze na záznamy s klíči `$keys`.

```php
echo count($tags); // 3

$tags = $tags->reduceTo(...[2, 3]); // anebo $tags->reduceTo(2, 3)

echo count($tags); // 2

$tags->reduceTo(2, 20, 21); // skončí výjimkou, protože klíče 20 a 21 v kolekci neexistují
```

### <a name="entries-transform"></a>`Entries::transform(Closure $callback, $entryClass = NULL)`

Metoda vrací novou instanci kolekce `Entries`, které předá „své“ pole `$data` modifikované pomocí funkce `$callback`.

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
Vypíše:
{"id":10,"name":"PHP"}
{"id":11,"name":"LIBRARY"}
{"id":12,"name":"RECOMMENDED"}
*/
```

## <a name="view-objects"></a>View objekty podrobně

V kapitole [Jak Schematic funguje](#introduction) jsme si již ve stručnosti [představili](#entryviewer) třídu `EntryViewer` a vysvětlili si, k čemu jsou view objekty vůbec dobré. Nyní se zaměříme na API třídy `EntryViewer` podrobněji.

### <a name="entryviewer-viewentry"></a>`EntryViewer::viewEntry($entry, Closure $converter)`

Metoda vrací instanci (standardně třídy `stdClass`) získanou předáním parametru `$entry` konverzní funkci `$converter`.

```php
$author = EntryViewer::viewEntry($author, function (Author $author) {
	return [
		'id' => $author->id,
		'name' => $author->name,
	];
}));

print_r($author);

/*
Vypíše:
stdClass Object
(
    [id] => 1
    [name] => Vojtěch Kohout
)
*/
```

Samozřejmě sama konverzní funkce může ve svém těle pracovat s helperem `EntryViewer`, což je velmi užitečné při práci s asociacemi:

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
Vypíše:
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

Pro tip: pokud v aplikaci budete běžně předávat stejné záznamy do různých šablon, doporučuji vyčlenit si vlastní konverze do samostatných služeb. Váš kód to velmi zestruční:

```php
$articleView = EntryViewer::viewEntry($article, function (Article $article) {
	return $this->articleConverter->convertArticleToArray($article);
});
```

###<a name="entryviewer-viewentries"></a> `EntryViewer::viewEntries($entries, Closure $singleEntryConverter)`

Metoda vrací *pole* instancí (standardně třídy `stdClass`) získaných předáním každého záznamu z kolekce `$entries` (pole nebo instance `Traversable`) konverzní funkci `$singleEntryConverter`. Záznam, pro který konverzní funkce vrátí `NULL`, nebude ve výsledném poli obsažen.

Vše nejlépe osvětlí ukázka:

```php
$tagsView = EntryViewer::viewEntries($tags, function (Tag $tag) {
	return [
		'id' => $tag->id,
		'name' => $tag->name,
	];
});

print_r($tagsView);

/*
Vypíše:
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

Ukažme si také v praxi možnost vynechat některý ze záznamů:

```php
$tagsView = EntryViewer::viewEntries($tags, function (Tag $tag) {
	return $tag->id === 11 ? NULL : [
		'id' => $tag->id,
		'name' => $tag->name,
	];
});

print_r($tagsView);

/*
Vypíše:
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

A na závěr si už jen ukažme kombinované využití spolu s `viewEntry`:

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
Vypíše:
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

Připomeňme si ještě, že tuhle celou zábavu s view objekty děláme proto, abychom uvolnili *přímé provázání šablon a API záznamů*, čímž učiníme kód lépe automaticky refaktorovatelný.

Při důsledném používání toho patternu lze dosáhnout úplné jistoty, že automatický refaktoring aplikaci „nerozbije“.

## <a name="entry-advanced"></a>Pokročilé možnosti třídy `Entry`

Třída `Entry` toho umí ještě víc, než jsme si [ukázali](#entry) v kapitole věnované základům Schematicu.

### <a name="embedded-entries"></a>Embedded záznamy

Pokud se rozhodnete Schematic používat pro mapování výsledků SQL dotazů, narazíte na komplikaci: výsledky SQL dotazů jsou relace (lidově řečeno *dvourozměrné tabulky*) a DBAL knihovny je bez vaší pomoci na zanořenou hierarchii asociativních polí nepřevedou.

Vezmeme-li v úvahu následující dotaz:

```sql
SELECT
	article.id, article.title,
	author.id, author.name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

výborný pro mapování by pro nás byl výsledek v takovémto formátu:

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
ale my dostaneme takovéto pole:

```php
[
	'id' => 1,
	'title' => 'Schematic introduction',
	'name' => 'Vojtěch Kohout',
]
```

Plochou relaci, ve které se navíc kvůli kolizi sloupců s názvem `id` ztratilo ID článku.

Prekérní situace, nicméně Schematic ji umí elegantně vyřešit pomocí tzv. vložených (embedded) záznamů.

Pokud výše uvedený SQL dotaz přepíšeme do následující podoby:

```sql
SELECT
	article.id, article.title,
	author.id a_id, author.name a_name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

dostaneme výsledek:

```php
[
	'id' => 5,
	'title' => 'Schematic introduction',
	'a_id' => 1,
	'a_name' => 'Vojtěch Kohout',
]
```

což je výrazně lepší: již se nám neztratilo žádné ID a také lze díky prefixu `a_` spolehlivě odlišit sloupce z tabulky `author` od sloupců z tabulky `article`.

A takovýto výsledek již Schematic umí namapovat. Stačí nadefinovat třídu `Article` takto:

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

Tečka v definici asociace v poli `$associations` vyjadřuje, že asociovaný záznam je do hlavního záznamu vložený. To znamená, že se v obaleném poli nachází na stejné úrovni, jako hlavní záznam.

Přístup k properties pak už funguje přesně podle očekávání:

```php
echo $article->title; // Schematic introduction
echo $article->author->name; // Vojtěch Kohout
```

Schematic nám umožňuje být ještě o něco stručnější, asociaci `author` můžeme totiž nadefinovat také takto:

```php
protected static $associations = [
	'author.' => Author::class,
];
```

V takovém případě se v obaleném poli hledá takzvaný výchozí prefix, který se sestává z názvu property doplněného o podtržítko; v ukázce tedy prefix `author_`.

SQL dotaz, který by přímo vedl k požadovanému poli, by tedy vypadal následovně:

```sql
SELECT
	article.id, article.title,
	author.id author_id, author.name author_name
FROM article
JOIN author ON article.author_id = author.id
WHERE article.id = 5
```

Tímto způsobem lze elegantně namapovat výsledky dotazů, které vybírají záznamy z tabulky, k níž JOINují její many-to-one a one-to-one asociace. Tomu přesně odpovídá ukázka výše: vztah článku a autora je many-to-one.

S výsledky dotazů, které vybírají záznamy z tabulky, k níž JOINují její one-to-many a many-to-many asociace, je ale zapotřebí pracovat trochu obezřetně: je zapotřebí si uvědomit povahu duplicit v takovém výsledku.

### <a name="inheritance"></a>Dědičnost, traity

Následující vlastnost není vlastností Schematicu, nýbrž moderních IDE. Anotace `@property-read` se dědí a je možné je znovupoužívat i pomocí trait:

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
echo $author->id;
echo $author->name;
echo $tag->name;
```

### <a name="entriesclass"></a>Parametr `$entriesClass` v `Entry::__construct`

Pochopitelně vám nic nebrání seskupovat Schematicové záznamy v jiné kolekci, než v instanci vestavěné `Entries`. Nicméně řekli jsme si, že kolekce záznamů nevznikají pouze přímým vytvářením jejích instancí, nýbrž také na pozadí při přístupu k one-to-many asociacím.

A právě parametr `$entriesClass` v `Entry::__construct` vám umožňuje ovlivnit, jaká třída reprezentující kolekci se bude při přístupu k one-to-many asociaci instanciovat. Jedinou podmínkou je, že daná třída musí implementovat rozhraní `IEntries`.
