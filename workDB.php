<?php
	function connectDB()
	{
		$db = null;

		$db = mysqli_init();

		if(!$db){ return false; }

		if(!mysqli_real_connect($db, "localhost", "HolyCuttlefish", "Kippe154125", "Update_path_images")){ return false; }

		return $db;
	}
?>
