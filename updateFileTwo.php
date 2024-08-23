`<?php
include_once "workDB.php";

$db = null;

$json = null;
$id = null;
$idSave = null;
$idTwo = null;
$img = null;

$query = null;
$queryTwo = null;
$result = null;
$resultTwo = null;
$countFile = null;

$uploadDir = 'File/';
$fileTypes = array('image/jpg', 'image/jpeg', 'image/png');
$uploadedFiles = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = $_POST['json'];
    $data = json_decode($json, true);
    $id = $data['id'] ?? null;
    $idTwo = $data['idTwo'] ?? null;

    // Проверяем, что оба ID не пустые
    if (empty($id) || empty($idTwo)) {
        echo json_encode(array('Error' => 'ID не должен быть пустым'));
        http_response_code(400);
        die;
    }

    $db = connectDB();

    if (!$db) {
        echo json_encode(array('Error' => mysqli_connect_error()));
        http_response_code(500);
        die;
    }

    // Убедитесь, что директория назначения существует
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(array('Error' => 'The catalog was not created'));
            http_response_code(500);
            die;
        }
    }

    // Проверка существующих изображений
    $query = mysqli_query($db, "SELECT path FROM Img WHERE id = '$id'");
    $queryTwo = mysqli_query($db, "SELECT path FROM Img WHERE id = '$idTwo'");

    if (!$query || !$queryTwo) {
        echo json_encode(array('Error' => mysqli_error($db)));
        http_response_code(500);
        mysqli_close($db);
        die;
    }

    $result = mysqli_fetch_assoc($query);
    $resultTwo = mysqli_fetch_assoc($queryTwo);

    // Удаляем старые изображения, если они существуют
    if (!empty($result['path']) && file_exists($result['path'])) {
        if (!unlink($result['path'])) {
            echo json_encode(array('Error' => 'Не удалось удалить изображение'));
            http_response_code(500);
            mysqli_close($db);
            die;
        }
    }

    if (!empty($resultTwo['path']) && file_exists($resultTwo['path'])) {
        if (!unlink($resultTwo['path'])) {
            echo json_encode(array('Error' => 'Не удалось удалить изображение idTwo'));
            http_response_code(500);
            mysqli_close($db);
            die;
        }
    }

    // Проверка загрузки файлов
    var_dump($_FILES);

    if (isset($_FILES['file']) && is_array($_FILES['file']['name'])) {
        $countFile = count($_FILES['file']['name']);

        for ($count = 0; $count < $countFile; ++$count) {
            if ($_FILES['file']['error'][$count] === UPLOAD_ERR_OK) {
                $img = $_FILES['file'];

                if ($img['size'][$count] <= 1000000) {
                    if (in_array($img['type'][$count], $fileTypes)) {
                        $uploadedFiles = uniqid('', true) . basename($img['name']);
                        $uploadFilePath = $uploadDir . $uploadedFiles;

                        if (move_uploaded_file($img['tmp_name'][$count], $uploadFilePath)) {
                            try {
                                mysqli_query($db, "UPDATE Img SET path = '$uploadFilePath' WHERE id = '$id'");
                                echo json_encode(array('Success' => 'Изображение загружено успешно'));
                                http_response_code(200);
                            } catch (Exception $e) {
                                echo json_encode(array('Error' => $e->getMessage()));
                                http_response_code(500);
                                die;
                            }
                        } else {
                            echo json_encode(array('Error' => 'Ошибка загрузки изображения'));
                            http_response_code(500);
                            die;
                        }
                    } else {
                        echo json_encode(array('Error' => 'Файл не соответствует требуемому типу'));
                        http_response_code(415);
                        die;
                    }
                } else {
                    echo json_encode(array('Error' => 'Файл превышает допустимый размер'));
                    http_response_code(411);
                    die;
                }
            } else {
                echo json_encode(array('Error' => 'Ошибка загрузки файла с клиента на сервер'));
                http_response_code(500);
                die;
            }
        }
    } else {
        echo json_encode(array('Error' => 'Ошибка загрузки файла с клиента на сервер'));
        http_response_code(400);
        die;
    }

    mysqli_close($db);
} else {
    echo json_encode(array('Error' => 'Неверный запрос к серверу'));
    http_response_code(501);
    die;
}
?>
