<?php

date_default_timezone_set('Asia/Jakarta');

$servername = "localhost";

$username = "u175226341_root";

$password = "@j4nzky94@";

$dbname = "u175226341_stis";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {

    die("Connection failed: " . mysqli_connect_error());

}

$sql = "SELECT id,batasPembayaran FROM transaksi WHERE status = '0' AND confirmed!='1'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {

    while($row = mysqli_fetch_assoc($result)) {

        $deadline = strtotime(preg_replace("/\sWIB,\stanggal/", "",$row["batasPembayaran"]));

        if(($deadline - time())<0){

            echo "batasPembayaran: " . $row["batasPembayaran"]. "<br>";

            $sql = "UPDATE transaksi SET status='2' WHERE id=".$row["id"];

            if(mysqli_query($conn, $sql)) {

                echo $row["id"]." sukses dibatalkan.<br>";

            }

        } else{

            echo "batasPembayaran: " . $row["batasPembayaran"]. " masih proses.<br>";

        }

    }

}

$promo = "SELECT kode, expired FROM quota WHERE expired IS NOT NULL";

$promoResult = mysqli_query($conn, $promo);

if (mysqli_num_rows($promoResult) > 0) {

    while($row = mysqli_fetch_assoc($promoResult)) {

        $deadline = strtotime($row["expired"]);

        if(($deadline - time())<0){           

            $sql = "UPDATE quota SET isAvailable='0' WHERE kode='".$row["kode"]."'";

            if(mysqli_query($conn, $sql)) {

                echo $row["kode"]." sukses.<br>";

            } else echo $row["kode"]." gagal di unavailable-kan<br>";

        } else {

            echo $row["kode"]." belum deadline <br>";

        }

    }

}



mysqli_close($conn);