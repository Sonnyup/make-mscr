<?php

namespace Jixk\MakeMscr;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use DB;

class MSCRCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:mscr {table : 数据库表名} {name : 文件名} {connection? : 数据库连接名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为数据库中的表生成对应的Controller、Model、Request、Service类';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
        $name = $this->argument('name');
        // 数据库连接
        $connection = $this->argument('connection');
        if($connection){
            Config::set('database.default', $connection);
        }

        $fields = DB::select(DB::raw('SHOW COLUMNS FROM ' . $table));

        $fields = array_map(function ($field) {
            return $field->Field;
        }, $fields);

        // Generate Controller
        $this->call('make:controller', [
            'name' => $name . 'Controller',
            '--resource' => true,
        ]);

        // Generate Service
        $this->call('create:service', [
            'name' => $name . 'Service',
            'model' => $table,
        ]);

        // Generate Model
        $this->call('create:model', [
            'name' => $table,
        ]);

        // Generate Request
        $postName = class_basename(str_replace('\\', '/', $name));
        $requestPath = app_path('Http/Requests/' . $postName . 'Post.php');
        $result = $this->generateRules($table, $fields);

        $this->call('make:request', [
            'name' => $postName . 'Post',
        ]);

        $template = str_replace(
            ['{{class}}'],
            [ $postName . 'Post'],
            File::get(__DIR__ . '/stubs/request.stub')
        );

        $template = str_replace(
            ['{{rules}}'],
            [implode("," . PHP_EOL . "\t\t\t", $result['rules'])],
            $template
        );

        $template = str_replace(
            ['{{message}}'],
            [implode("," . PHP_EOL . "\t\t\t", $result['message'])],
            $template
        );

        File::put($requestPath, $template);
    }

    /**
     * Generate validation rules for the given table fields.
     *
     * @param string $table
     * @param array $fields
     * @return array
     */
    protected function generateRules(string $table, array $fields): array
    {
        $rules = [];
        $message = [];

        foreach ($fields as $field) {
            $type = $this->getFieldType($table, $field);
            $pos = strcspn($type, '(1234567890)'); // 获取类型名称和长度分割点的索引值
            $typeName = substr($type, 0, $pos);
            switch ($typeName) {
                case 'int':
                case 'bigint':
                case 'tinyint':
                case 'smallint':
                case 'integer':
                case 'float':
                case 'double':
                case 'decimal':
                    $rules[] = "'$field' => 'numeric'";
                    break;
                case 'string':
                case 'varchar':
                case 'text':
                case 'tinytext':
                case 'longtext':
                case 'char':
                    $rules[] = "'$field' => 'string|max:255'";
                    break;
                case 'date':
                    $rules[] = "'$field' => 'date'";
                    break;
                case 'datetime':
                case 'timestamp':
                    $rules[] = "'$field' => 'date_format:Y-m-d H:i:s'";
                    break;
            }
            $message[] = "'$field'=>'$field 参数错误'";
        }

        return ["rules" => $rules, "message" => $message];
    }

    /**
     * Get the data type of the given field in the specified table.
     *
     * @param string $table
     * @param string $field
     * @return string|null
     */
    protected function getFieldType(string $table, string $field): ?string
    {
        $type = DB::select(DB::raw('SHOW COLUMNS FROM ' . $table . ' WHERE Field = "' . $field . '"'));

        return $type[0]->Type ?? null;
    }
}
