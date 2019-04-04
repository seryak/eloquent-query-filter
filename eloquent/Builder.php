<?php

namespace Wtolk\Eloquent;

class Builder extends Illuminate\Database\Eloquent\Builder
{
    /**
     * Custom filters handler
     *
     * @param $filter
     * @return $this
     * @throws \Exception
     */
    public function filter($filter)
    {
        foreach ($filter as $column => $value) {
            if ($value == '') continue; // Если значение пустое, переходим к следующей итерации

            // Получаем имя поля в таблице и оператор
            $column_operator = explode(':', $column);
            if (count($column_operator) !== 1) {
                $column = explode(':', $column)[0];
                $operator = explode(':', $column)[1];
            } else {
                $method = $column_operator[0];
                if (method_exists($this->model, $method)) {
                    $this->{$method}($value);
                    continue;
                }
            }

            //Если поле не передано
            if ($column == '') {
                // Если в модели существуе данный метод, выполняем его, иначе бросаем исключение
                if (method_exists($this->model, $operator)) {
                    $this->model->{$operator}($value, $this);
                } else {
                    throw new \Exception('Undefined method ' . $operator . ' in Model ' . get_class($this->model));
                }
            } else {

                if ($operator == '') {
                    $operator = '=';
                }

                if (in_array($operator, $this->query->operators)) {

                    if (method_exists($this->model, 'set' . $column . 'attribute')) {
                        $this->model->{'set' . $column . 'attribute'}($value);
                        $value = $this->model->getAttributes()[$column];
                    }

                    if ($operator == 'like' || $operator == 'ilike') {
                        $value = '%' . $value . '%';
                    }

                    $this->where($column, $operator, $value);
                } else {
                    throw new \Exception('Undefined operator ' . $operator);
                }
            }

        }
        return $this;
    }
}