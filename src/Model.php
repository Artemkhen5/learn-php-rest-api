<?php


namespace api\src;


class Model
{
    private \PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM phones";

        $stmt = $this->conn->query($sql);

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['is_available'] = (bool) $row['is_available'];
            $data[] = $row;
        }

        return $data;
    }

    public function create($data): string
    {
        $sql = "INSERT INTO phones (name, price, is_available) VALUES (:name, :price, :is_available)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $data['name']);
        $stmt->bindValue(":price", $data['price'] ?? 0, \PDO::PARAM_INT);
        $stmt->bindValue(":is_available", $data['is_available'] ?? false, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT * FROM phones WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, \PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($data !== false) {
            $data['is_available'] = (bool) $data['is_available'];
        }

        return $data;
    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE phones SET name = :name, price = :price, is_available = :is_available WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $new['name']);
        $stmt->bindValue(":price", $new['price'] ?? $current['price'], \PDO::PARAM_INT);
        $stmt->bindValue(":is_available", $new['is_available'] ?? $current['is_available'], \PDO::PARAM_BOOL);
        $stmt->bindValue(":id", $current['id'], \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM phones WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getValidationErrors(array $data): array
    {
        $errors = [];

        if(empty($data['name'])) {
            $errors[] = "Name is required";
        }

        if(array_key_exists('price', $data)) {
            if(filter_var($data['price'], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Price must be an integer";
            }
        }

        return $errors;
    }
}