<?php namespace App\Core;

use App\Core\Database\Database;

abstract class BaseModel
{
    protected $id = 'id';
    protected $table = '';
    protected $fields = [];

    protected $createdAt = 'created_at';
    protected $updatedAt = 'updated_at';

    private function getFields()
    {
        return $this->fields;
    }

    public function all()
    {
        $database = Database::getInstant();
        $sql = "SELECT * FROM {$this->table};";
        return $database->query($sql, [], static::class);
    }

    public function get(string $field, string $value)
    {
        $database = Database::getInstant();
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value;";
        return $database->query($sql, [
            ':value' => $value,
        ], static::class);
    }

    public function insert(array $values)
    {
        $database = Database::getInstant();
        $fields = implode(',', [implode(',', $this->fields), $this->createdAt, $this->updatedAt]);
        $newValues = [];
        foreach ($this->fields as $field) {
            $newValues[$field] = '\'' . $values[$field] . '\'';
        }
        $newValues[$this->createdAt] = '\'' . date('Y-m-d H:i:s') . '\'';
        $newValues[$this->updatedAt] = '\'' . date('Y-m-d H:i:s') . '\'';
        $values = implode(',', $newValues);
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values});";
        return $database->query($sql, [], static::class);
    }

    public function delete(int $id)
    {
        $database = Database::getInstant();
        $sql = "DELETE FROM {$this->table} WHERE {$this->id} = :id";
        return $database->query($sql, [
            ':id' => $id,
        ], static::class);
    }

    public function update(int $id, array $data)
    {
        $database = Database::getInstant();
        $oldData = $this->get($this->id, $id)[0];
        $newData = [];
        foreach ($this->getFields() as $field) {
            if (isset($data[$field])) {
                $newData[$field] = '\'' . $data[$field] . '\'';
            } else {
                $newData[$field] = '\'' . $oldData->$field . '\'';
            }
        }
        $newData[$this->createdAt] = '\'' . $oldData->{$this->createdAt} . '\'';
        $newData[$this->updatedAt] = '\'' . date('Y-m-d H:i:s') . '\'';
        $query = '';
        foreach ($newData as $key => $value) {
            $query .= "{$key} = {$value},";
        }
        $query = mb_substr($query, 0, -1);
        $sql = "UPDATE {$this->table} SET {$query} WHERE {$this->id} = :id;";
        return $database->query($sql, [
            ':id' => $id,
        ], static::class);
    }
}
