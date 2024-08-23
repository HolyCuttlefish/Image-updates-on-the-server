<?php
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

if($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	$json = $_POST['json'];
	$data = json_decode($json, true);
	$id = $data['id'];
	$idTwo = $data['idTwo'];

	$idSave = $id;

	$db = connectDB();

	if(!$db)
	{
		echo json_encode(array('Error' => mysqli_connect_error()));
		http_response_code(500);

		die;
	}

	if(empty($id))
	{
		mysqli_close($db);

		echo json_encode(array('Error' => 'The replacement image is not selected'));
		http_response_code(400);

		die;
	}

	if(empty($idTwo))
	{
		mysqli_close($db);

		echo json_encode(array('Error' => 'The replacement image two is not selected'));
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

		$queryTwo = mysqli_query($db, "select path from Img where id = '$idTwo'");

		if(!$query)
		{
			mysqli_close($db);

			echo json_encode(array('Error' => mysqli_error($db)));
			http_response_code(500);

			die;
		}

		if(!$queryTwo)
		{
			mysqli_close($db);

			echo json_encode(array('Error' => mysqli_error($db)));
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

		$resultTwo = mysqli_fetch_assoc($queryTwo);

		if(!$resultTwo['path'])
		{
			mysqli_close($db);

			echo json_encode(array('Error' => 'There is no way idTwo'));
			http_response_code(500);

			die;
		}

		if(!unlink($resultTwo['path']))
		{
			mysqli_close($db);

			echo json_encode(array('Error' => 'There is no specified image to change idTwo'));
			http_response_code(500);

			die;
		}

	if((isset($_FILES['file'])) && (is_array($_FILES['file']['name'])))
	{
		$countFile = count($_FILES['file']['name']);

		for($count = 0; $count < $countFile; ++$count)
		{
			if($_FILES['file']['error'][$count] ===	UPLOAD_ERR_OK)
			{
				$img = $_FILES['file'];

				if($img['size'][$count] <= 2000000)
				{
					if(in_array($img['type'][$count], $fileTypes))
					{
						$uploadedFiles = uniqid('', true) . basename($img['name'][$count]);

    				$uploadFilePath = $uploadDir . $uploadedFiles;

    				if (move_uploaded_file($img['tmp_name'][$count], $uploadFilePath)) 
						{
     					try
							{
								mysqli_query($db, "update Img set path = '$uploadFilePath' where id = '$id'");
		
								$id = $idTwo;
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
		}
	} else
	{
		mysqli_close($db);

    echo json_encode(array('Error' => 'Error uploading a file from the client to the server'));
		http_response_code(400);

		die;
	}

echo json_encode(array('Succes' => 'The image has been uploaded'));
http_response_code(200);


mysqli_close($db);
} else
{
	echo json_encode(array('Error' =>'Invalid request to the server'));
	http_response_code(501);

	die;
}
