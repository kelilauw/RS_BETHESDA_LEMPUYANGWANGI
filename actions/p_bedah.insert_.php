<?php // Nugraha, Thu Apr 22 11:58:22 WIT 2004
      // sfdn, 23-04-2004: tambah harga obat
      // sfdn, 09-05-2004
	  // sfdn, 31-05-2004
//echo $_POST["list"];exit;
session_start();
$PID = "p_bedah";

require_once("../lib/dbconn.php");
require_once("../lib/querybuilder.php");
require_once("../lib/class.PgTrans.php");

//echo"konsul=".$_POST["konsultasi"];exit;
$noreg = $_POST["f_no_reg"];
$_GET["mr"] = $_POST["mr"];
$rg = isset($_GET["rg"])? $_GET["rg"] : $_POST["rg"];
$mr = isset($_GET["mr"])? $_GET["mr"] : $_POST["mr"];
//echo "rm={$_GET["mr"]}";exit;
$tokit = pg_query("select nextval('rs00008_seq_group')");
pg_query("select nextval('kasir_seq')");
$tr = new PgTrans;
$tr->PgConn = $con;

// LAYANAN TINDAKAN MEDIS DAN PEMBAGIAN JASA MEDIS
if (is_array($_SESSION["layanan"])) {
	   if ($_POST["sub"] == "byr") {
		$v1 = ",is_bayar,no_kwitansi";
		$v2 = ",'Y',currval('rs88888_seq')";
   } else {
		$v1 = "";
		$v2 = "";
   }

   foreach ($_SESSION["layanan"] as $v) {
        $total += $v["total"];
   }
   //$kodepoli = getFromTable("SELECT POLI FROM RS00006 WHERE ID = lpad('".$_POST["rg"]."',10,'0')");
   // -- edited $kodepoli 110210 without lpad()
   $kodepoli = getFromTable("SELECT POLI FROM RS00006 WHERE ID = '".$_POST["rg"]."'");
   if ($_POST[rawatan] == "IGD") {
      $loket = "IGD";
   } elseif ($_POST[rawatan] == "Rawat Inap") {
      $loket = "RIN";
      $kodepoli = 0;
   } else {
      $loket = "RJL";
   }

   
   // insert to rs00005
   pg_query("INSERT INTO rs00005 VALUES( currval('kasir_seq'), '".$_POST["rg"]."', ".
        "CURRENT_DATE, '$loket', 'N', 'N', $kodepoli, $total, 'N','".$_SESSION["uid"]."')") or die("Error");

    // insert to rs00008
    foreach ($_SESSION["layanan"] as $v) {
        if ($v["nip"]) {
           $dokter = $v["nip"];
        } else {
           $dokter = 0;
        }

        $tr->addSQL(
            	"insert into rs00008 (" .
                "id,            trans_type,  trans_form, trans_group, tanggal_trans, " .
                "tanggal_entry, waktu_entry, no_reg,     item_id,     referensi, ".
                "qty,           harga,       tagihan,    pembayaran, no_kwitansi,user_id ".
            	") values (".
                "nextval('rs00008_seq'), 'LTM', '$PID', currval('rs00008_seq_group'), CURRENT_DATE, " .
                "CURRENT_DATE, CURRENT_TIME, '".$_POST["rg"]."', '".$v["id"]."', '', " .
                $v["jumlah"].",".$v["harga"].",".$v["total"].", 0, $dokter,'".$_SESSION["uid"]."')"
        );
        
        //$tr->addSQL("update c_visit set id_dokter=$dokter where no_reg = lpad('".$_POST["rg"]."',10,'0')");
    }
	
    }
   
// ICD
if (is_array($_SESSION["icd"])) {
    foreach ($_SESSION["icd"] as $v) {
        $tr->addSQL(
            	"insert into rs00008 (" .
                "id,            trans_type,  trans_form, trans_group, tanggal_trans, " .
                "tanggal_entry, waktu_entry, no_reg,     item_id,     referensi, ".
                "qty,           harga,       tagihan,    pembayaran,user_id ".
            	") values (".
                "nextval('rs00008_seq'), 'ICD', '$PID', currval('rs00008_seq_group'), CURRENT_DATE, " .
                "CURRENT_DATE, CURRENT_TIME, '".$_POST["rg"]."', '".$v["id"]."', '', " .
                "0,0,0,0,'".$_SESSION["uid"]."')"
        );
    }
}
// PEMBAYARAN
if (isset($_POST["byr"])) {
    $tr->addSQL(
        	"insert into rs00008 (" .
            "id,            trans_type,  trans_form, trans_group, tanggal_trans, " .
            "tanggal_entry, waktu_entry, no_reg,     item_id,     referensi, ".
            "qty,           harga,       tagihan,    pembayaran, is_bayar, no_kwitansi ".
        	") values (".
            "nextval('rs00008_seq'), 'BYR', '$PID', currval('rs00008_seq_group'), CURRENT_DATE, " .
            "CURRENT_DATE, CURRENT_TIME, '".$_POST["rg"]."', '', '', " .
            "0,0,0,'".$_POST["byr"]."', 'Y',currval('rs88888_seq'))"
    );

    // tambahan sfdn, 31-05-2004
    $reg_count = getFromTable("select count(mr_no) from rs00006 ".
            "where mr_no = (select mr_no from rs00006 ".
            "where id = '".$_POST["rg"]."')");
    $baru    = "Y";
    if ($reg_count > 1 ) $baru = "T";

    $r1 = pg_query($con,
        	"select tipe, id as no_reg, tanggal_reg, rawat_inap, rujukan ".
        	"from rs00006 ".
        	"where id = '".$_POST["rg"]."'");

    $n1 = pg_num_rows($r1);
    if($n1 > 0) $d1 = pg_fetch_object($r1);
    pg_free_result($r1);

    $loket	 = "RJN";
    if ($d1->rawat_inap == "N") {
	        $loket	 = "IGD";
	} elseif ($d->rawat_inap == "I"){
			$loket	 = "RIN";
    }
    $tr->addSQL(
        	"insert into rs00008 (" .
   	    	"id,trans_type, is_inout, qty, no_reg, is_baru, tanggal_trans, ".
	    	"datang_id, trans_group,is_bayar )".
	    	"values (" .
            "nextval('rs00008_seq'),'$loket', 'O', 1,$d1->no_reg, ".
            "'$baru',CURRENT_DATE,'$d1->rujukan',currval('rs00008_seq_group'), 'Y')");
    $tr->addSQL(
        	"update rs00006 set is_karcis='Y' where id = '".$_POST["rg"]."'");
        //insert c_vis_jantung
	
	// pada saat pasien pulang/keluar, maka beberapa info yg. harus di-record
	// adalah: status_keluar: Sembuh, Mati, Dirujuk dll., cara bayar: Tunai, Gratis dll
	// jika pasiennya adalah RI, maka ditambah dgn. info: lama dirawat
	// dikarenakan saat ini belum ada fasilitas untuk mencatat info tsb,
	// maka cukup SQL seperti di atas
	// jika telah ada info-info tsb, maka SQL di bawah ini yg. dipergunakan
	$tr->addSQL(
			"insert into rs00008 (" .
			"trans_type, is_inout, qty ,  no_reg, is_baru, ".
			"tanggal_trans, ".
			"status_out, cara_bayar_id, gol_tindakan_id ".
		") values (" .
			"'$loket', 'O', 1,   $d1->no_reg, ".
			"'$baru',CURRENT_DATE,".
			"<<<<kode tindakan>>>>,<<<<kode status keluar>>>>,<<<<kode cara bayar>>>>,".
			"<<<<kode gol.tindakan,BESAR,KECIL dll.>>>>)");

	// akhir tambahan
    $r1 = pg_query($con,
        	"select * from rs00008 where trans_group = currval('rs00008_seq_group')");
    while ($d1 = pg_fetch_object($r1)) {
                 $tr->addSQL(
                      "update rs00008 set is_bayar = 'Y'");
   }
}

//----tambahan for update.... hery------------------------
	if ($_POST['act'] == "edit") { 
	
		//if ($_POST['row'] > 0)	{
			
			$SQL2 = "select * from c_visit where no_reg='$noreg' ".
					"and id_poli='{$_POST["f_id_poli"]}' and tanggal_reg='{$_POST["f_tanggal_reg"]}' ";
			$r2 = pg_query($con,$SQL2);
			//echo $SQL2;		
			if ($d2 = pg_fetch_object($r2)){		
				$qb = New UpdateQuery();
				$qb->HttpAction = "POST";
				$qb->TableName = "c_visit";
				$qb->VarPrefix = "f_";
				$qb->addPrimaryKey("no_reg", "'$noreg'");
				$qb->addPrimaryKey("id_poli", "'" . $_POST["f_id_poli"] . "'");
				$qb->addPrimaryKey("tanggal_reg", "'" . $_POST["f_tanggal_reg"] . "'");
				$SQL = $qb->build();
				
				pg_query($con, $SQL);
				unset($_SESSION["SELECT_EMP"]);
				unset($_SESSION["SELECT_EMP2"]);
				header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=$noreg&poli=".$_POST["f_id_poli"]."&mr=".$_POST["mr"]);
				exit;
			}
	}
//--------------------------------------------------------	
	if ($_POST['act'] == "new") { 	
		
			$qb = New InsertQuery();
			$qb->TableName = "c_visit";
			$qb->HttpAction = "POST";
			$qb->VarPrefix = "f_";
			//$qb->addFieldValue("id", "lpad(currval('rs00006_seq'),10,'0')");
			$SQL = $qb->build();
			//echo $SQL;
			pg_query($con, $SQL);
			//pg_query("update rs00006 set periksa='Y' where id =lpad('$noreg',10,'0')");
			pg_query("update rs00006 set periksa='Y' where id ='$noreg'");
			//pg_query("update rs00006 set status_akhir_pasien='".$_POST["status_akhir"]."' where id =lpad('$noreg',10,'0')");
			pg_query("update rs00006 set status_akhir_pasien='".$_POST["status_akhir"]."' where id ='$noreg'");
			unset($_SESSION["SELECT_EMP"]);
			unset($_SESSION["SELECT_EMP2"]);
			header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=$noreg&poli=".$_POST["f_id_poli"]."&mr=".$_POST["mr"]);
		   	exit;
	}
	if ($_POST['act'] == "new1") { 	
			//pg_query("update rs00006 set status_akhir_pasien='".$_POST["status_akhir"]."' where id =lpad('$noreg',10,'0')");
			pg_query("update rs00006 set status_akhir_pasien='".$_POST["status_akhir"]."' where id ='$noreg'");
			unset($_SESSION["SELECT_EMP"]);
			unset($_SESSION["SELECT_EMP2"]);
			header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=$noreg&poli=".$_POST["f_id_poli"]."&mr=".$_POST["mr"]);
		   	exit;
	}
	if ($_POST['act'] == "new2") { 	
			$r = pg_query($con,"SELECT * FROM c_visit WHERE no_reg ='$noreg' and id_poli ='".$_POST["f_id_poli"]."'");
		    $d = pg_fetch_object($r);
			$knsl=getFromTable("select id_konsul from c_visit where no_reg ='$noreg' and id_poli ='".$_POST["f_id_poli"]."'");
			$knsl2=getFromTable("select id_konsul from c_visit where no_reg ='$noreg' and id_poli ='".$_POST["f_id_poli"]."' and id_konsul='".$_POST["konsultasi"]."'");
			if ($knsl==""){
			pg_query("update c_visit set id_konsul='".$_POST["konsultasi"]."' where no_reg ='$noreg' and id_poli ='".$_POST["f_id_poli"]."'");
			}
			if ($knsl2=="" and $knsl!=""){
			$SQL = "select * from c_visit where no_reg ='$noreg' and id_poli ='".$_POST["f_id_poli"]."' ";
			$r2 = pg_query($con,$SQL);
			$d2 = pg_fetch_object($r2);
			pg_free_result($r2);
			$tm1=date("Y-m-d H:i:s");
			/* $SQLa="insert into c_visit (no_reg,tanggal_reg,id_poli,id_konsul,vis_1,vis_2,vis_3,vis_4,vis_5,vis_6,vis_7,vis_8,vis_9,vis_10,vis_11,vis_12,vis_13,vis_14,vis_15) 
					values ('$noreg', '$tm1', '".$_POST['f_id_poli']."', '".$_POST["konsultasi"]."', '$d2->vis_1', '$d2->vis_2', '$d2->vis_3', '$d2->vis_4',
					, '$d2->vis_5', '$d2->vis_6', '$d2->vis_7', '$d2->vis_8', '$d2->vis_9', '$d2->vis_10', '$d2->vis_11', '$d2->vis_12', '$d2->vis_13', '$d2->vis_14', '$d2->vis_15')"; */
					$SQLa="insert into c_visit (no_reg,tanggal_reg,id_poli,id_konsul,id_dokter,id_perawat) 
					values ('$noreg', '$d2->tanggal_reg', '".$_POST['f_id_poli']."', '".$_POST["konsultasi"]."','$d2->id_dokter', '$d2->id_perawat')";
			pg_query($con, $SQLa);
			}
			
			unset($_SESSION["SELECT_EMP"]);
			unset($_SESSION["SELECT_EMP2"]);
			header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=$noreg&poli=".$_POST["f_id_poli"]."&mr=".$_POST["mr"]);//end of edit line
		   	exit;
	}

if ($tr->execute()) {

    unset($_SESSION["layanan"]);
    unset($_SESSION["icd"]);
    if($_POST['sub'] != "retur"){  
    header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=".$_POST[rg]."&poli=".$_POST["poli"]."&mr=".$_GET["mr"]."&sub=".$_POST[sub]);
    exit;
    }else{
    	header("Location: ../index2.php?p=$PID&list={$_POST["list"]}&rg=".$_POST[rg]."&poli=".$_POST["poli"]."&mr=".$_GET["mr"]."&sub=".$_POST[sub]);
    exit;
    }
} else {
    echo $tr->ErrMsg;
    
}

?>
