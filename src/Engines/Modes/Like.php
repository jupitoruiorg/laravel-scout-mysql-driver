<?php

namespace Yab\MySQLScout\Engines\Modes;

use Laravel\Scout\Builder;
use Yab\MySQLScout\Services\ModelService;

class Like extends Mode
{
    protected $fields;

    public function buildWhereRawString(Builder $builder)
    {
        $table = $builder->model->getTable();

        $queryString = '';

        $this->fields = $this->modelService->setModel($builder->model)->getSearchableFields();

        $queryString .= $this->buildWheres($builder);

        $queryString .= '(';

        foreach ($this->fields as $field) {
            $queryString .= "`$table`.`$field` LIKE ? OR ";
        }

        $indexRelations = $this->modelService->setModel($builder->model)->getSearchableRelations();

        if (filled($indexRelations)) {
            foreach ($indexRelations as $indexRelationFields) {
                foreach ($indexRelationFields as $relationsField) {
                    $queryString .= "$relationsField LIKE ? OR ";
                }
            }
        }

        $queryString = trim($queryString, 'OR ');
        $queryString .= ')';

        return $queryString;
    }

    public function buildParams(Builder $builder)
    {
        $params = '%'.$builder->query.'%';

        for ($itr = 0; $itr < count($this->fields); ++$itr) {
            $this->whereParams[] = $params;
        }

        $indexRelations = $this->modelService->setModel($builder->model)->getSearchableRelations();

        if (filled($indexRelations)) {
            foreach ($indexRelations as $indexRelationFields) {
                foreach ($indexRelationFields as $relationsField) {
                    $this->whereParams[] = $params;
                }
            }
        }

        return $this->whereParams;
    }

    public function isFullText()
    {
        return false;
    }
}
