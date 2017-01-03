<?php

date_default_timezone_set('Asia/Jakarta');

$servername = "localhost";

$username = "root";

$password = "";

$dbname = "tryout-stis";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {

    die("Connection failed: " . mysqli_connect_error());

}

$sql = "SELECT id,batasPembayaran FROM transaksi WHERE status = '0'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {

    while($row = mysqli_fetch_assoc($result)) {

    	$deadline = strtotime(preg_replace("/\sWIB,\stanggal/", "",$row["batasPembayaran"]));

    	// echo ($deadline - time())."<br>";

    	if(($deadline - time())<0){

	        // echo "batasPembayaran: " . $row["batasPembayaran"]. "<br>";

	        $sql = "UPDATE transaksi SET status='2' WHERE id=".$row["id"];

	        if(mysqli_query($conn, $sql)) {

	        	// echo $row["id"]." sukses dibatalkan.<br>";

	        }

    	} else{

    		// echo "batasPembayaran: " . $row["batasPembayaran"]. " masih proses.<br>";

    	}

    }

} else {
    
    // echo "0 results";

}

mysqli_close($conn);