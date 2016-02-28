<?php

namespace Humble\Pdo;

class Pdo extends \Pdo
{
    public function __construct($host, $dbname, $username, $password)
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $host, $dbname);

        parent::__construct($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    public function insert(string $table, array $data)
    {
        $keys = array_keys($data);

        $fields = implode(", ", $keys);
        $values = implode(', :', $keys);

        $sql = 'INSERT INTO %s (%s) VALUES (:%s);';
        $sql = sprintf($sql, $table, $fields, $values);

        return $this->run($sql, $data);
    }

    public function update(string $table, array $data, string $idKey, $idValue)
    {
        $set = implode(', ', array_map(function ($key) {
            return $key . ' = :' . $key;
        }, array_keys($data)));

        $sql = 'UPDATE %s SET %s WHERE %3$s = :%3$s;';
        $sql = sprintf($sql, $table, $set, $idKey);

        $data = array_merge($data, array($idKey => $idValue));

        return $this->run($sql, $data);
    }

    public function delete(string $table, string $idKey, $idValue)
    {
        $sql = 'DELETE FROM %s WHERE %2$s = :%2$s;';
        $sql = sprintf($sql, $table, $idKey);

        $data = array($idKey => $idValue);

        return $this->run($sql, $data);
    }

    public function run(string $sql, array $data)
    {
        $query = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $query->bindValue(':' . $key, $value);
        }

        return $query->execute();
    }
}
