Eloquent On The Fly
==============

Eloquent On The Fly is a package that has been designed to help you to use Laravel's Eloquent to query array.

* [Installation](#installation)
* [Usage](#usage)
  * [As a Helper](#use_as_a_helper)
  * [As a Collection Method](#use_as_a_collection)
  * [As a Class](#use_as_a_class)
    * [Simple](#simple)
    * [Advance](#advance)


## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "calhoun/eloquent-oft": "*"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "calhoun/eloquent-oft"

## Usage

#### Use as a Helper

To use Eloquent On The Fly as a Helper, all you need is an associative array, and closure containing your eloquent or builder query.

```php
<?php

$data = [
  [
    'id'    => 1
    'first' => 'Maurice',
    'last'  => 'Calhoun',
    'email' => 'maurice@mauricecalhoun.com',
    'age'   => '40',
    'manager_id' => 2
  ],
  [
    'id'    => 2
    'first' => 'John',
    'last'  => 'Doe',
    'email' => 'manager@job.com',
    'age'   => '45',
    'manager_id' => null
  ],
  ...
];

$result = eloquent($data, function($query){
    return $query->whereAge(40)->get();
});

// You can pass a third parameter for the table name (by default the name is oft)
 $result = eloquent($data, function($query, $name){
   return $query->join($name . " as manager", $name.'.manager_id', '=', 'manager.id')->find(1);
 }, 'employees');

```

#### Use as a Collection Method

To use Eloquent On The Fly as a Collection Method, all you need is a closure containing your eloquent or builder query.

```php
<?php

$data = [
  [
    'first' => 'Maurice',
    'last'  => 'Calhoun',
    'email' => 'maurice@mauricecalhoun.com',
    'age'   => '40'
  ],
  ...
];

$result = collect($data)->filter(function($item){
    return item['age'] >= 40;
})->eloquent(function($query){
  return $query->where('email', 'like', '%mauricecalhoun.com')->get();
});

```

#### Use as a Class

##### Simple
To use Eloquent On The Fly as a Class. Instantiate the OTF Class and use the create method, which takes a name and data as its parameters.

```php
<?php

use  Calhoun\OTF\Support\OTF;

$data = [
  [
    'first' => 'Maurice',
    'last'  => 'Calhoun',
    'email' => 'maurice@mauricecalhoun.com',
    'age'   => '40'
  ],
  ...
];

$otf = app()->make(OTF::class)->create('person', $data);
$maurice = $otf->person->whereLast('Calhoun')->first();
```

##### Advance
You can use Eloquent On The Fly as a Class with relationships.

```php
<?php

use  Calhoun\OTF\Support\OTF;

$users = [
  [
    'id'    => 1
    'first' => 'Maurice',
    'last'  => 'Calhoun',
  ],
  ...
];

$profiles = [
  [
    'id'    => 1
    'email' => 'maurice@mauricecalhoun.com',
    'age'   => '40',
    'user_id' => 1
  ],
  ...
];

$oft = app()->make(OTF::class);

$user     = $otf->create('user', $users);
$profile  = $otf->create('profile',$profiles);

$relationships = [
  'user' => [
      'profile' => function($self) use($profile){
        return $self->hasOne($profile, 'user_id');
      }
  ],
  'profile' => [
    'user' => function($self) use($user){
      return $self->belongsTo($user, 'id');
    }
  ]
];

$otf->setRelationships($relationships);

$maurice = $user->find(1)->profile;

