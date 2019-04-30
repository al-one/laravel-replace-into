# SQL replace into For Laravel

## Installing

```
# composer.json

"minimum-stability": "dev",
"prefer-stable": true,
```

```sh
$ composer require "al-one/laravel-replace-into" -vvv
```


## Usage

```php
# optional if >= 5.5
# config/app.php
<?php

return [

    'providers' => [
        Alone\LaravelReplaceInto\ServiceProvider::class,
    ],

];
```

```php
use Illuminate\Support\Facades\DB;

DB::table('user_attrs')->replace(
    ['uid' => 10000,'type' => 'key','value' => 'val'],
    ['uid','type'] // uniqueKeys
);

DB::table('user_attrs')->replace([
    ['uid' => 10000,'type' => 'key1','value' => 'val1'],
    ['uid' => 10001,'type' => 'key2','value' => 'val2'],
],['uid','type']);
```

```php
use Illuminate\Database\Eloquent\Model;

class UserAttr extends Model
{
    public function uniqueKeys()
    {
        return ['uid','type'];
    }
}

UserAttr::replace(
    ['uid' => 10000,'type' => 'key','value' => 'val']
);

UserAttr::replace([
    ['uid' => 10000,'type' => 'key1','value' => 'val1'],
    ['uid' => 10001,'type' => 'key2','value' => 'val2'],
]);
```


## License

MIT