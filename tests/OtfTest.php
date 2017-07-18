<?php

use Calhoun\OTF\Support\OTF;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class OtfTest extends TestingCase
{

  protected $otf;
  protected $faker;

  public function setUp()
  {
      parent::setUp();

      $this->otf = new OTF();
      $this->faker = Faker::create();
  }

  /**
   * @test
   */
public function can_use_otf_class()
{
  $user = $this->otf->create('user', $this->userStubs());

  $this->assertEquals($user->find(1)->first, 'Maurice');
  $this->assertEquals($user->find(1)->last, 'Calhoun');

  $this->assertEquals($user->whereLast('Doe')->first()->first, 'John');
}

/**
 * @test
 */
public function can_use_otf_class_with_relationship()
{
  $user = $this->otf->create('user', $this->userStubs());
  $profile = $this->otf->create('profile', $this->profileStubs());

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

$this->otf->setRelationships($relationships);

$this->assertEquals($user->find(1)->first, 'Maurice');
$this->assertEquals($user->find(1)->last, 'Calhoun');

$this->assertEquals($user->whereLast('Doe')->first()->first, 'John');
}

/**
 * @test
 */
public function can_use_otf_class_as_collection_macro()
{
    $user = collect($this->userStubs())->eloquent(function($query){
        return $query->find(1);
    });

    $this->assertEquals($user->first, 'Maurice');
    $this->assertEquals($user->last, 'Calhoun');
}

/**
 * @test
 */
public function can_use_otf_class_as_helper()
{
  $user = eloquent($this->userStubs(), function($query){
    return $query->whereFirst('Maurice')->first();
  });

  $this->assertEquals($user->first, 'Maurice');
  $this->assertEquals($user->last, 'Calhoun');
}

/**
 * @test
 */
public function can_use_join_and_self_relation()
{
  $employees = $this->employeeStubs();

  $helper = eloquent($employees, function($query, $name){
      return $query->join($name . " as manager", $name.'.manager_id', '=', 'manager.id')->find(1);
  }, 'employee');

  $this->assertEquals($helper->first, 'Mary');

  $collection = collect($employees)->eloquent(function($query, $name){
    return $query->join($name . " as manager", $name.'.manager_id', '=', 'manager.id')->find(1);
  }, 'employee');

  $this->assertEquals($collection->first, 'Mary');

  $employee = $this->otf->create('employee', $employees);

  $relationships = [
    'employee' => [
        'manager' => function($self) use($employee){
          return $self->belongsTo($employee, 'manager_id');
        },
        'manage' => function($self) use($employee){
          return $self->hasMany($employee, 'manager_id');
        }
    ]
  ];

  $this->otf->setRelationships($relationships);

  $this->assertEquals($employee->find(1)->manager->first, 'Mary');
  $this->assertCount(3, $employee->find(4)->manage);
  $this->assertEquals($employee->find(4)->manager->first, 'Jane');
}

private function employeeStubs()
{
  return [
    [
      'id'    => 1,
      'first' => 'Maurice',
      'last'  => 'Calhoun',
      'manager_id' => 4
    ],
    [
      'id'    => 2,
      'first' => 'John',
      'last'  => 'Doe',
      'manager_id' => 4
    ],
    [
      'id'    => 3,
      'first' => 'Richard',
      'last'  => 'Miles',
      'manager_id' => 4
    ],
    [
      'id'    => 4,
      'first' => 'Mary',
      'last'  => 'Major',
      'manager_id' => 5
    ],
    [
      'id'    => 5,
      'first' => 'Jane',
      'last'  => 'Stiles',
      'manager_id' => 0
    ]
  ];
}

private function userStubs()
  {
    return [
      [
        'id'    => 1,
        'first' => 'Maurice',
        'last'  => 'Calhoun'
      ],
      [
        'id'    => 2,
        'first' => 'John',
        'last'  => 'Doe'
      ]
    ];
  }

  private function profileStubs()
    {
      return [
        [
          'id'    => 1,
          'email' => 'maurice.calhoun@test.com',
          'age'  => '40',
          'user_id' => 1
        ],
        [
          'id'    => 2,
          'email' => 'john.doe@test.com',
          'age'  => '22',
          'user_id' => 2
        ]
      ];
    }
}