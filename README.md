# Practicing CakePHP

This will serve as a perpetual reference and tutorial for myself too.

I am currently attempting to build an open-source profile. Hopefully, someone will see that I have learned CakePHP well and hire me!

# Deploying My Codes

Unpack into your CakePHP's `app` folder.

# Plugins Needed

Place these in your CakePHP's `plugins` folder (not in your `app/Plugin`):

* [Migrations](https://github.com/CakeDC/migrations) (CakePHP way to define database schemas. _"Look Ma! No SQL!"_)
* [DebugKit](https://github.com/cakephp/debug_kit) (nice debug and learning tool)
* [AclExtras](https://github.com/markstory/acl_extras) (Used by Migrations script)
* [AssetCompress](https://github.com/markstory/asset_compress) (packs CSS and Javascript files into a single file; avoids HTTP request overheads for multiple files)

_AssetCompress_ isn't used yet. But the concept is obvious and simple enough. Multiple CSS/Javascript files require multiple requests to pull down, increasing overheads. CSS/Javascript codes _should_ be split into separate modules to allow easier maintenance, debugging, etc.

_AclExtras_ is used by the [_Migrations_ script](https://github.com/hannwong/cakephp-practice/tree/master/Config/Migration).

_Migrations_ is used extensively. It makes for a very good RDBMS-agnostic way to define database schemas.

I had used it so extensively that I unconsciously created rather extensive docs for it.

In fact, I include here the updated docs for _"CakePHP Schema Definition Language"_. Neither the [official CakePHP docs](http://book.cakephp.org/2.0/en/console-and-shells/schema-management-and-migrations.html) nor the [Migrations plugin docs](https://github.com/CakeDC/migrations/blob/master/readme.md) are complete or update-to-date. (TODO for self: Learn how I can contribute to CakePHP's documentation.)

# CakePHP Schema Definition Language

## Overview and Example:

    'create_table' => array(
            'notes' => array(
                    // Take this as THE only way to specify a primary key in CakePHP schemas.
                    'id' => array('type' => 'integer', 'key' => 'primary'),
                    'title' => array(
                            'type' => 'string',
                            'length' => 250, // CakePHP's default is 255. Just to show a use of 'length'.
                            'null' => false, // Mandatory attribute! (Or you risk undefined behavior)
                            'default' => 'No Title'),
                    'body' => array('type' => 'text', 'null' => true),
                    // CakePHP updates these fields; leave them NULLABLE just in case.
                    'created' => array('type' => 'datetime', 'null' => true),
                    'modified' => array('type' => 'datetime', 'null' => true),
                    // Indexes! We may want fast look-up on 'title' column.
                    'indexes' => array(
                            'notes_title' => array('column' => array('title'))
                            )),

            'users' => array(
                    'id' => array('type' => 'integer', 'key' => 'primary'),
                    'first_name' => array('type' => 'string', 'null' => false),
                    'last_name' => array('type' => 'string', 'null' => false),
                    'created' => array('type' => 'datetime', 'null' => true),
                    'modified' => array('type' => 'datetime', 'null' => true),
                    'indexes' => array(
                            'users_last_name_first_name' => array(
                                    'unique' => true,
                                    'column' => array('last_name', 'first_name')))
                    )
            )

Special mention:

* Primary keys of types `integer` and `biginteger` are translated as `NOT NULL` and `AUTO_INCREMENT` in SQL.
* Indexes should never be used to define primary key constraints; the primary key column itself defines this just fine.

## Table Attributes

About the only useful construct here is:
'`indexes`' to define indexes (for fast look-up) and/or unique constraints.

Eg:

    'indexes' => array(
        'first_last_name' => array(
            'unique' => true,
            'column' => array('first_name', 'last_name')
        )
    )

* '`unique`': Omit if unique constraint is not required.
* '`column`': can be a string (single column) or an array of strings (multi-column).

That's all you need to know for table attributes!

Details. TL;DR...

* `indexes`: Used to define indexes for fast-searching
    * `'name_of_index' => array of values`. Values include:
        * '`column`': Can be a string (for a single column) or an array of strings (for multiple columns)
        * '`unique`': `true` if constraint '`unique`' is desired; omit otherwise.
    * `'PRIMARY' => array of values`. DO NOT USE! Redundant. Define primary keys on columns instead. Note that CakePHP does not support composite keys.
* `tableParameters`: Not often used, since your database will usually be defined with global defaults.
    * Common settings for a MySQL database:
        * '`charset`': utf8
        * '`collate`': utf8_unicode_ci
        * '`engine`' : INNODB

## Field Attributes

    array(
        'id' => array(
            'type'    =>'integer',
            'key'     => 'primary'),
        'first_name' => array(
            'type'    => 'string',
            'length'  => 36,
            'null'    => false,
            'default' => "John"),
        'last_name' => array(
            'type'    => 'string',
            'null'    => true,
            'default' => "Doe"),
        'created' => array(
            'type'    => 'datetime',
            'null'        => true),
        'modified' => array(
            'type'    => 'datetime',
            'null'        => true),
    )

The above will give you the following columns:

* id
    * Type `integer`
    * Auto-incrementing.
    * Primary key.
* first_name
    * Type `string`
    * Length: 36.
    * `NOT NULL`able.
    * Default value: "John"
* last_name
    * Type `string`
    * Length: 255 (CakePHP's default)
    * `NULL`able.
    * Default value: "Doe"
* created
    * Type `datetime`
    * Automagically updated by CakePHP.
* modified
    * Type `datetime`
    * Automagically updated by CakePHP.

### Field Attribute `'type'`

* `string`: MySQL's `varchar`. CakePHP's default length is 255.
* `text`: MySQL's `text`.
* `biginteger`: MySQL's `bigint`. CakePHP's default _display width_ is 20; size is a fixed 8 bytes.
* `integer`: MySQL's `int`. CakePHP's default _display width_ is 11; size is a fixed 4 bytes.
* `float`: MySQL's `float`. Precision defaults to (10,2) (display length, decimals). Do not change precision!
* `datetime`: MySQL's `datetime`.
* `timestamp`: MySQL's `timestamp`.
* `time`: MySQL's `time`.
* `date`: MySQL's `date`.
* `binary`: MySQL's `blob`.
* `boolean`: MySQL's `tinyint` with size of 1. DO NOT change this size, or the meaning of this type will be lost!

### Field Attribute `'length'`

If `length` is not defined, CakePHP's defined max size for the column's type is used.
In cases where CakePHP does not define a max size for a type, the RDBM's defaults are used.

Some types do not allow definition of length.
**Do not define length for these types**: `text`, `datetime`, `timestamp`, `time`, `date`, `binary`, `boolean`

Use RDBMS default length for these types (unless you know the RDBMS well enough): `biginteger`, `integer`, `float`.

See [MySQL's warning about portability for floats](http://dev.mysql.com/doc/refman/5.6/en/floating-point-types.html):

_"For maximum portability, code requiring storage of approximate numeric data values should use FLOAT or DOUBLE PRECISION with no specification of precision or number of digits."_

Try not to set length for this type (until you are ready to do space-optimization): `string`.

Use `text` if you expect more than 1 line of text; CakePHP's default size for `string` is 255.

**In all, it would be wise to _not_ define `length` at all when you first build your application.**

### Field Attribute `'null'`

`true` for a `NULL`able column; `false` for a `NOT NULL`able column.

Unless this column is to be a primary key, this attribute is considered **mandatory**. (There's a case of undefined behavior when both `null` and `default` attributes are omitted. Just take `null` as mandatory to avoid that case.)

(For this attribute to be considered at all, _attribute '`key`' must not be set_. If it is, the `'null'` attribute has no effect at all.)

Note: By default, type of `timestamp` are defined as `NOT NULL`. Omitting attribute '`null`' will leave the column as `NOT NULL`able.
See [MySQL Docs on Timestamp Initialization](http://dev.mysql.com/doc/refman/5.6/en/timestamp-initialization.html) (_"TIMESTAMP Initialization and the NULL Attribute"_)

### Field Attribute `'default'`

Defines a default value for the column. Can be `NULL` if attribute '`null`' is `true`.

(For this attribute to be considered at all, _attribute '`key`' must not be set_. If it is, the `'default'` attribute has no effect at all.)

### Field Attribute `'key'`

It's value is only ever '`primary`'.
When defined, it sets the column as the primary key of the table.
(CakePHP doesn't support composite keys.)

For types `integer` and `biginteger`, the column is set to `NOT NULL`able and `AUTO_INCREMENT`.
For any other types, it is only set to `NOT NULL`able.

Note: The presence of this attribute will cause attributes '`null`' and '`default`' to be ignored.

Just FYI...
When defined, it is a shortcut for a table index definition like this:

    'indexes' => array(
        'PRIMARY' => array(
            'column' => column_name,
            'unique' => 1))

**Don't bother looking up _table's `indexes` definition for primary key constraint_ because this is the better/best practice for defining a _primary key constraint_.**
