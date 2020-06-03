<?php

namespace Alone\LaravelReplaceInto;

use Illuminate\Database;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Arr;

class ServiceProvider extends BaseServiceProvider
{

    public function boot()
    {

        Database\Eloquent\Builder::macro('replace',function(array $values = [],array $uniqueKeys = null)
        {
            if(empty($values))
            {
                return true;
            }
            /* @var $this Database\Eloquent\Builder */
            $query = $this;
            if(!is_array(reset($values)))
            {
                $values = [$values];
            }
            $values = array_map(function($v) use($query)
            {
                $mod = $query->newModelInstance($v);
                if($mod->usesTimestamps())
                {
                    $tim = $mod->freshTimestampString();
                    $mod->setAttribute($mod->getCreatedAtColumn(),$tim);
                    $mod->setAttribute($mod->getUpdatedAtColumn(),$tim);
                }
                return $mod->getAttributes();
            },$values);
            if(is_null($uniqueKeys))
            {
                $uniqueKeys = (method_exists($query->model,'uniqueKeys') ? $query->model->uniqueKeys() : null) ?: [];
            }
            return $query->getQuery()->replace($values,$uniqueKeys);
        });

        Database\Query\Builder::macro('replace',function(array $values = [],array $uniqueKeys = [])
        {
            $ret = false;
            if(empty($values))
            {
                return true;
            }
            /* @var $this Database\Query\Builder */
            $query = $this;
            $drv = $query->connection->getDriverName();
            if($drv == 'mysql' || $drv == 'sqlite')
            {
                if(!is_array(reset($values)))
                {
                    $values = [$values];
                }
                else
                {
                    foreach($values as $key => $value)
                    {
                        ksort($value);
                        $values[$key] = $value;
                    }
                }
                $sql = $query->grammar->compileInsert($query,$values);
                $sql = preg_replace('/^\s*insert\s+into\b/i','replace into',$sql);
                $bds = $query->cleanBindings(Arr::flatten($values,1));
                $ret = $query->connection->insert($sql,$bds);
            }
            elseif($uniqueKeys)
            {
                if(!is_array(reset($values)))
                {
                    $values = [$values];
                }
                foreach($values as $key => $value)
                {
                    $attributes = Arr::only($value,$uniqueKeys);
                    if($attributes)
                    {
                        $value = Arr::except($value,$uniqueKeys);
                        $query->wheres = [];
                        $ret = $query->updateOrInsert($attributes,$value);
                    }
                }
            }
            return $ret;
        });

    }

}