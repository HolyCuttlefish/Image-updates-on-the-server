<?php
include_once "workDB.php";

$db = null;

$json = null;
$id = null;
$img = null;
$query = null;
$result = null;

$uploadDir = 'File/';
$fileTypes = array('image/jpg', 'image/jpeg', 'image/png');
$uploadedFiles = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	$json = $_POST['json'];
	$data = json_decode($json, true);
	$id = $data['id'];

	$db = connectDB();

	if(!$db)
	{
		echo json_encode(array('Error' => mysqli_connect_error()));
		http_response_code(500);

		die;
	}

	if(is_null($id))
	{
		mysqli_close($db);

		echo json_encode(array('Error' => 'The replacement image is not selected'));
		http_response_code(400);

		die;
	}

	// Убедитесь, что директория назначения существует
	if (!is_dir($uploadDir))
	{
  	if(!mkdir($uploadDir, 0755, true))
		{
			mysqli_close($db);

			echo json_encode(array('Error' => 'The catalog was not created'));
			http_response_code(500);

			die;
			}
		}

	$query = mysqli_query($db, "select path from Img where id = '$id'");

	if(!$query)
	{
		mysqli_close($db);

		echo json_encode(array('Error' => mysqli_error()));
		http_response_code(500);

		die;
	}

	$result = mysqli_fetch_assoc($query);

	if(!$result['path'])
	{
		mysqli_close($db);

		echo json_encode(array('Error' => 'There is no way'));
		http_response_code(500);

		die;
	}

	if(!unlink($result['path']))
	{
		mysqli_close($db);

		echo json_encode(array('Error' => 'There is no specified image to change'));
		http_response_code(500);

		die;
	}

	if(isset($_FILES['file']))
	{
		$img = $_FILES['file'];

		if($img['error'] ===	UPLOAD_ERR_OK)
		{
			if($img['size'] <= 1000000)
			{
				if(in_array($img['type'], $fileTypes))
				{
					$uploadedFiles = uniqid('', true) . basename($img['name']);

    			$uploadFilePath = $uploadDir . $uploadedFiles;

    			if (move_uploaded_file($img['tmp_name'], $uploadFilePath)) 
					{
     				try
						{
							mysqli_query($db, "update Img set path = '$uploadFilePath' where id = '$id'");
		
							echo json_encode(array('Succes' => 'The image has been uploaded'));
							http_response_code(200);
						} catch(Exception $e) 
						{
							mysqli_close($db);

							echo json_encode(array('Error' => $e->getMessage()));
							http_response_code(500);

							die;
						}
    			} else 
					{
						mysqli_close($db);

        		echo json_encode(array('Error' => 'Error loading the image'));
    				http_response_code(500);

						die;
					}
				} else
				{
					mysqli_close($db);

					echo json_encode(array('Error' => 'The file does not match the required type'));
					http_response_code(415);

					die;
				}
			} else
			{
				mysqli_close($db);

				echo json_encode(array('Error' => 'The file is larger than the required size'));
				http_response_code(411);

				die;
			}
		} else
		{
			mysqli_close($db);

			echo json_encode(array('Error' => 'Error uploading a file from the client to the server'));
			http_response_code(500);

			die;
		}
	} else
	{
		mysqli_close($db);

    echo json_encode(array('Error' => 'Error uploading a file from the client to the server'));
		http_response_code(400);

		die;
	}

mysqli_close($db);
} else
{
	echo json_encode(array('Error' =>'Invalid request to the server'));
	http_response_code(501);

	die;
}
