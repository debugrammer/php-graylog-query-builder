# Graylog Query Builder for PHP ![build](https://github.com/debugrammer/php-graylog-query-builder/actions/workflows/build.yml/badge.svg)
> PHP version of [Graylog Search Query](https://docs.graylog.org/en/latest/pages/searching/query_language.html) Builder especially useful for working with [Graylog REST API](https://docs.graylog.org/en/latest/pages/configuration/rest_api.html).

[![Latest Stable Version](https://poser.pugx.org/debugrammer/php-graylog-query-builder/v/stable)](https://packagist.org/packages/debugrammer/php-graylog-query-builder)
[![Total Downloads](https://poser.pugx.org/debugrammer/php-graylog-query-builder/downloads)](https://packagist.org/packages/debugrammer/php-graylog-query-builder)
[![License](https://poser.pugx.org/debugrammer/php-graylog-query-builder/license)](https://packagist.org/packages/debugrammer/php-graylog-query-builder)

## Getting Started
PHP versions 7.0 up to PHP 7.4 are currently supported.
Graylog Query Builder for PHP is recommended to use [composer](https://getcomposer.org/) to install the library.

Add php-graylog-query-builder to `composer.json` either by running composer:
```
$ composer require debugrammer/php-graylog-query-builder
```
or by defining it manually:
```
"require": {
   "debugrammer/php-graylog-query-builder": "~1.0"
}
```

## Usage
```
use GraylogQueryBuilder\GraylogQuery as GraylogQuery;

GraylogQuery::builder()
    ->field('type', 'ssh')
    ->and()->exists('id')
    ->and()->openParen()
        ->raw('source:(dog.org OR cat.org)')
    ->closeParen()
    ->and()->range('http_response_code', '[', 200, 300, ']')
    ->build();
```
Above code snippet generates the string below.
```
type:"ssh" AND _exists_:id AND ( source:(dog.org OR cat.org) ) AND http_response_code:[200 TO 300]
```

## Building Queries

### 1. Statements

#### 1.1. Term
Messages that include the term or phrase.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->term('ssh')
    ->build();
```
**Output:**
```
"ssh"
```

#### 1.2. Fuzz Term
Messages that include similar term or phrase.

##### 1.2.1. Fuzziness with default distance
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->fuzzTerm('ssh logni')
    ->build();
```
**Output:**
```
"ssh logni"~
```

##### 1.2.2. Fuzziness with custom distance
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->fuzzTerm('ssh logni', 1)
    ->build();
```
**Output:**
```
"ssh logni"~1
```

#### 1.3. Exists
Messages that have the field.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->exists('type')
    ->build();
```
**Output:**
```
_exists_:type
```

#### 1.4. Field

##### 1.4.1. Field (String)
Messages where the field includes the term or phrase.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->field('type', 'ssh')
    ->build();
```
**Output:**
```
type:"ssh"
```

##### 1.4.2. Field (Numeric)
Messages where the field includes the number.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->field('http_response_code', 500)
    ->build();
```
**Output:**
```
http_response_code:500
```

##### 1.4.3. One side unbounded range query
Messages where the field satisfies the condition.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->opField('http_response_code', '>', 500)
    ->build();
```
**Output:**
```
http_response_code:>500
```

#### 1.5. Fuzz Field
Messages where the field includes similar term or phrase.

##### 1.5.1. Fuzziness with default distance
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->fuzzField('source', 'example.org')
    ->build();
```
**Output:**
```
source:"example.org"~
```

##### 1.5.2. Fuzziness with custom distance
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->fuzzField('source', 'example.org', 1)
    ->build();
```
**Output:**
```
source:"example.org"~1
```

#### 1.6. Range

##### 1.6.1. Range query
Ranges in square brackets are inclusive, curly brackets are exclusive and can even be combined.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->range('bytes', '{', 0, 64, ']')
    ->build();
```
**Output:**
```
bytes:{0 TO 64]
```

##### 1.6.2. Date range query
The dates needs to be UTC.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->range('timestamp', '[', '2019-07-23 09:53:08.175', '2019-07-23 09:53:08.575', ']')
    ->build();
```
**Output:**
```
timestamp:["2019-07-23 09:53:08.175" TO "2019-07-23 09:53:08.575"]
```

#### 1.6. Raw
Raw query.

**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->raw('/ethernet[0-9]+/')
    ->build();
```
**Output:**
```
/ethernet[0-9]+/
```

### 2. Conjunctions

#### 2.1. And
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->term('ssh')
    ->and()->term('login')
    ->build();
```
**Output:**
```
"ssh" AND "login"
```

#### 2.2. Or
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->term('ssh')
    ->or()->term('login')
    ->build();
```
**Output:**
```
"ssh" OR "login"
```

#### 2.3. Not
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->not()->exists('type')
    ->build();
```
**Output:**
```
NOT _exists_:type
```

### 3. Parentheses
**Usage:**
```
GraylogQueryBuilder\GraylogQuery::builder()
    ->exists('type')
    ->and()->openParen()
        ->term('ssh')
        ->or()->term('login')
    ->closeParen()
    ->build();
```
**Output:**
```
_exists_:type AND ( "ssh" OR "login" )
```

## Advanced Usage
Sometimes you might want to compose dynamic queries by condition.

### 1. Prepend Graylog query
**Usage:**
```
$query = GraylogQueryBuilder\GraylogQuery::builder()
    ->not()->exists('type');

GraylogQueryBuilder\GraylogQuery::builder($query)
    ->and()->term('ssh')
    ->build();
```
**Output:**
```
NOT _exists_:type AND "ssh"
```

### 2. Append Graylog query
**Usage:**
```
$query = GraylogQueryBuilder\GraylogQuery::builder()
    ->or()->exists('type');

GraylogQueryBuilder\GraylogQuery::builder()
    ->term('ssh')
    ->append($query)
    ->build();
```
**Output:**
```
"ssh" OR _exists_:type
```
