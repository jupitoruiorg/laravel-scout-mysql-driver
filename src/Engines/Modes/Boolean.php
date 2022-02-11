<?php

namespace Yab\MySQLScout\Engines\Modes;

use Laravel\Scout\Builder;
use Yab\MySQLScout\Services\ModelService;

class Boolean extends Mode
{
    public function buildWhereRawString(Builder $builder)
    {
        $queryString = '';

        $queryString .= $this->buildWheres($builder);

        $indexFields = implode(',',  $this->modelService->setModel($builder->model)->getFullTextIndexFields());

        $indexRelations = $this->modelService->setModel($builder->model)->getSearchableRelations();

        $queryString .= "(";

        $queryString .= "MATCH($indexFields) AGAINST(? IN BOOLEAN MODE)";

        if (filled($indexRelations)) {
            foreach ($indexRelations as $indexRelation) {
                $indexRelationFields = implode(',',  $indexRelation);
                $queryString .= " OR MATCH($indexRelationFields) AGAINST(? IN BOOLEAN MODE)";
            }
        }

        $queryString .= ")";

        return $queryString;
    }

    public function buildSelectColumns(Builder $builder)
    {
        $indexFields = implode(',',  $this->modelService->setModel($builder->model)->getFullTextIndexFields());

        return "*, MATCH($indexFields) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance";
    }

    public function buildParams(Builder $builder)
    {
        $params = $builder->query;

        $words = explode(' ', $params);

        $whereParam = '(' . implode(' ', $words) . ') ' .
            '(' . implode('* ', $words) . '*)';
        $this->whereParams[] = $whereParam;

        $indexRelations = $this->modelService->setModel($builder->model)->getSearchableRelations();

        if (filled($indexRelations)) {
            foreach ($indexRelations as $indexRelation) {
                $this->whereParams[] = $whereParam;
            }
        }

        //$this->whereParams[] = $builder->query;

        return $this->whereParams;
    }

    public function isFullText()
    {
        return true;
    }
}
