<?php


namespace api\src;


class Controller
{
    public function __construct(private Model $model)
    {
    }

    public function handleRequest(string $method, ?string $id): void
    {
        if($id) {
            $this->handleResourceRequest($method, $id);
        } else {
            $this->handleCollectionRequest($method);
        }
    }

    private function handleResourceRequest(string $method, string $id):void
    {
        $product = $this->model->get($id);

        if($product === false) {
            http_response_code(404);

            echo json_encode([
                'message' => 'Product not found'
            ]);
            return;
        }

        if($method === "GET") {
            echo json_encode($product);
        } elseif ($method === "PATCH") {
            $data = (array) json_decode(file_get_contents("php://input"));

            $errors = $this->model->getValidationErrors($data);

            if(!empty($errors)) {
                http_response_code(422);

                echo json_encode($errors);
            } else {
                $rows = $this->model->update($product, $data);

                echo json_encode([
                    'message' => "Procuct with id = $id updated",
                    'rows' => $rows
                ]);
            }
        } elseif ($method === "DELETE") {
            $rows = $this->model->delete($id);

            echo json_encode([
                'message' => "Product whith id = $id deleted",
                'rows' => $rows
            ]);
        } else {
            http_response_code(405);
            header("Allow: GET, PATCH, DELETE");
        }
    }

    private function handleCollectionRequest(string $method): void
    {
        if($method === 'GET') {
            http_response_code(200);

            echo json_encode($this->model->getAll());
        } elseif ($method === 'POST') {
            $data = (array) json_decode(file_get_contents("php://input"));

            $errors = $this->model->getValidationErrors($data);

            if(!empty($errors)) {
                http_response_code(422);

                echo json_encode($errors);
            } else {
                http_response_code(201);

                $id = $this->model->create($data);

                echo json_encode([
                   'message' => "Procuct with id = $id created"
                ]);
            }
        } else {
            http_response_code(405);
            header("Allow: GET, POST");
        }
    }
}