<?php

namespace Calhoun\OTF\Support;

use DB;
use Schema;

class OTF{

  protected $connection = 'otf';

  public function __construct()
	{
    $default = config("database.connections.sqlite");
    $default['database'] = ':memory:';
		config(["database.connections." . $this->connection => $default]);
	}

  /**
   * Set Relationships On The Fly
   * @param array $relationships
   */
  public function setRelationships($relationships)
  {
      config(['relation' => $relationships]);
      return $this;
  }

  /**
   * Static Call to Make A Table On The Fly
   * @param  string $table
   * @param  mixed $data
   * @return  Calhoun\OTF\Support\OTF
   */
  public static function make($table, $data){
      return tap((new static), function($otf) use($table, $data){
        $otf->create($table, $data);
      });
  }

  /**
   * Make A Table On The Fly
   * @param  string $table
   * @param  mixed $data
   * @return  Calhoun\OTF\Support\OTF
   */
  public function create($table, $data)
  {
     return $this->{$table} = tap($this->generateModel($table), function($object) use($table, $data){
        $this->schema($table, $data);
     });
  }

  /**
   * Generate a Model Class
   * @param  string $table
   * @return Illuminate\Database\Eloquent\Model
   */
  private function generateModel($table)
  {
      $class = studly_case($table);
      $namespace = sprintf("Calhoun\OTF\%s", $class);
      $file = sprintf("app/%s.php", $class);

      $content = file_get_contents("stubs/Model.stub");
      $content = str_replace("{class}", $class, $content);
      $content = str_replace("{table}", $table, $content);
      $content = str_replace("{connection}", $this->connection, $content);

      file_put_contents($file, $content);

      register_shutdown_function(function() use($file) {
          if(file_exists($file)) unlink($file);
      });

      return app()->make($namespace);
  }

  /**
   * Build A Table Schema
   * @param  string $table
   * @param  mixed $data
   */
  private function schema($table, $data)
  {
    if(!Schema::connection($this->connection)->hasTable($table))
    {
      Schema::connection($this->connection)->create($table, function ($schema) use($data){
        collect(current($data))->each(function($value, $key) use($schema) {
            $key    = strtolower(preg_replace("/[^A-Z0-9_]/i", '_', $key));
            $value  = $this->get_type($value);
            $schema->{$value}($key);
        });
      });

      $this->seed($table, $data);
    }
  }

  /**
   * Seed A Table
   * @param  string $table
   * @param  mixed $data
   */
  private function seed($table, $data)
  {
    collect($data)->chunk(100)->each(function($chunk) use($table){
      DB::connection($this->connection)->table($table)->insert($chunk->all());
    });
  }

  /**
   * Get The Column Type
   * @param  mixed $var
   * @return string
   */
  private function get_type($var)
  {
     if (is_array($var)) return "string";
     if (is_bool($var)) return "boolean";
     if (is_float($var)) return "float";
     if (is_int($var)) return "integer";
     if (is_null($var)) return "string";
     if (is_numeric($var)) return "integer";
     if (is_string($var)) return "string";
     return "string";
 }

}

?>
