# Token Strings

This simple library allows you to create a string that includes tokens, for example:

```
Dear [[FORENAME]] [[SURNAME]],
```

Then provide values for the tokens at runtime:

```php
[
	'FORENAME'  =>  'Joe',
	'SURNAME'   =>  'Bloggs',
]
```

And run a token substitution:

```
Dear Joe Bloggs,
```

It's useful for things like page titles, notification templates, mail merge and more.

## Installation

Install using Composer:

```bash
composer require lukaswhite/token-strings
```

## Basic Usage

Create an instance:

```php
use Lukaswhite\TokenStrings\Substitutor( );

$substitutor = new Substitutor(
	'Dear [[FORENAME]] [[SURNAME]],',
	[
		'forename'  =>  'Joe',
		'surname'   =>  'Bloggs',
	]
);
```

Then run it:

```php
$replaced = $substitutor->run( );
// or
$replaced = ( string ) $substitutor;
```

## Advanced Usage

You can modify the content of the template at any time:

```php
$substitutor->setContent( 'Hey [[FORENAME]]' );
```

To add tokens:

```php
$substitutor->addToken( 'age', 43 );
```

To clear the tokens:

```php
$substitutor->clearTokens( );
```

If you'd prefer differrnt markup for the tokens:

```php
$substitutor->setOpeningTag( '{{' )->setClosingTag( '}}' );
```

Since the closing tag in the example above is simply the reverse of the opening tag, you can simply do this:

```php
$substitutor->setOpeningTag( '{{' );
```

In addition to passing strings as token values, you can also pass an object, provided it implements the magic `__toString()` method:

```php
class Person {

    private $forename;

    private $surname;

    public function __construct( $forename, $surname )
    {
        $this->forename = $forename;
        $this->surname = $surname;
    }
    
    public function __toString( ) 
    {
        return sprintf( '%s %s', $this->forename, $this->surname );
    }
}

$substitutor = new Substitutor(
	'Dear [[NAME]],',
	[
		'NAME'  =>  new Person( 'Joe', 'Bloggs' ),
	]
);
```

To get a list of the available tokens, call `getAvailableTokens()`.

The `validate()` method checks that the string you provide does not contain any tokens for which you haven't provided values.

## Notes

* Tokens must only contain letters, numbers, dashes and underscores
* By convention tokens are uppercase, but if you provide an array with the tokens in lowercase, it'll convert them for you
* If the string contains tokens that you have not provide values for, it will replace them with empty strings
