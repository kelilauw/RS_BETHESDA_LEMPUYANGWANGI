<?php 	// Nugraha, Sun Apr 18 18:58:42 WIT 2004
      	// sfdn, 22-04-2004: hanya merubah beberapa title
      	// sfdn, 23-04-2004: tambah harga obat
      	// sfdn, 30-04-2004
      	// sfdn, 09-05-2004
      	// sfdn, 18-05-2004: age
      	// sfdn, 02-06-2004
      	// Nugraha, Sun Jun  6 18:14:41 WIT 2004 : Paket Transaksi
      	// sfdn, 24-12-2006 --> layanan hanya diberikan kpd. pasien yang blm. lunas
		// rs00006.is_bayar = 'N'
		// sfdn, 27-12-2006
		// App,02-06-2007 --> Developer

session_start();
$PID = "p_dots";
$SC = $_SERVER["SCRIPT_NAME"];

require_once("startup.php");
require_once("lib/visit_setting.php");
//--fungsi column color-------------- 
function color( $dstr, $r ) {
	    //if ($dstr[7] == '-') {
	    if($_GET['list2']=="tab1"){
	    	if ($dstr[9] == 'BELUM BAYAR' ){
	    		return "<font color=red>{$dstr[$r]}</font>";
	    	}else{
	    		return "<font color=blue>{$dstr[$r]}</font>";
	    	}
	    }else{
	    	if ($dstr[8] == 'BELUM BAYAR' ){
	    		return "<font color=red>{$dstr[$r]}</font>";
	    	}else{
	    		return "<font color=blue>{$dstr[$r]}</font>";
	    	}
	    }
}
//-------------------------------       	
$_GET["mPOLI"]=$setting_poli["dots"];
$rg = isset($_GET["rg"])? $_GET["rg"] : $_POST["rg"];
$mr = isset($_GET["mr"])? $_GET["mr"] : $_POST["mr"];


		if (isset($_GET["del"])) {
		    $temp = $_SESSION["layanan"];
		    unset($_SESSION["layanan"]);
		    foreach ($temp as $k => $v) {
		        if ($k != $_GET["del"]) $_SESSION["layanan"][count($_SESSION["layanan"])] = $v;
		    }
		    	header("Location: $SC?p=" . $_GET["p"] . "&list=layanan&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"]."&sub=layanan");
		    	exit;
		    
		} elseif (isset($_GET["del-icd"])) {
		    $temp = $_SESSION["icd"];
		    unset($_SESSION["icd"]);
		    foreach ($temp as $k => $v) {
		        if ($k != $_GET["del-icd"]) $_SESSION["icd"][count($_SESSION["icd"])] = $v;
		    }
		    	header("Location: $SC?p=" . $_GET["p"] . "&list=icd&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=icd");
		    	exit;
		    
		} elseif (isset($_GET["del-obat"])) {
		    $temp = $_SESSION["obat"];
		    unset($_SESSION["obat"]);
		    foreach ($temp as $k => $v) {
		        if ($k != $_GET["del-obat"]) $_SESSION["obat"][count($_SESSION["obat"])] = $v;
		    }
		    	header("Location: $SC?p=" . $_GET["p"] . "&list=resepobat&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=obat");
		    	exit;
		    
		} elseif (isset($_GET["del-pjm"])) {
		    $temp = $_SESSION["pjm"][$_GET["del-pjm"]];
		    unset($_SESSION["pjm"][$_GET["del-pjm"]]);
		    foreach ($temp as $k => $v) {
		        if ($k != $_GET["del-emp"])
		            $_SESSION["pjm"][$_GET["del-pjm"]][count($_SESSION["pjm"][$_GET["del-pjm"]])] = $v;
		    }
		    	header("Location: $SC?p=" . $_GET["p"] . "&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=pjm");
		    	exit;
		    
		} elseif (isset($_GET["s2note"])) {
		    $_SESSION["s2note"] = $_GET["s2note"];
		    	header("Location: $SC?p=" . $_GET["p"] . "&list=icd&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=icd");
		    	exit;
		    
		} elseif (isset($_GET["obat"])) {
		    $r = @pg_query($con,"SELECT * FROM RSV0004 WHERE ID = '".$_GET["obat"]."'");
		    $d = @pg_fetch_object($r);
		    @pg_free_result($r);
		
		    if (is_array($_SESSION["obat"])) {
		        $cnt = count($_SESSION["obat"]);
		    } else {
		        $cnt = 0;
		    }
		    
		    if (!empty($d->obat)) {
		        $_SESSION["obat"][$cnt]["id"]     = $_GET["obat"];
		        $_SESSION["obat"][$cnt]["desc"]   = $d->obat;
		        //$_SESSION["obat"][$cnt]["dosis"]  = $_GET["dosis_obat"];
		        $_SESSION["obat"][$cnt]["jumlah"] = $_GET["jumlah_obat"];
		        $_SESSION["obat"][$cnt]["harga"]  = $d->harga;
		        $_SESSION["obat"][$cnt]["total"]  = $d->harga * $_GET["jumlah_obat"];
		        //$_SESSION["obat"][$cnt]["satuan"] = $d->satuan;
		        unset($_SESSION["SELECT_OBAT"]);
		    }
		    	header("Location: $SC?p=" . $_GET["p"] . "&list=resepobat&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=obat");
		    	exit;
    
		} elseif (isset($_GET["icd"])) {
		    $r = pg_query($con,"SELECT * FROM RSV0005 WHERE DIAGNOSIS_CODE = '" . $_GET["icd"] . "'");
		    $d = pg_fetch_object($r);
		    pg_free_result($r);
		    if (is_array($_SESSION["icd"])) {
		        $cnt = count($_SESSION["icd"]);
		    } else {
		        $cnt = 0;
		    }
		    
		    if (strlen($d->description) > 0) {
		        $_SESSION["icd"][$cnt]["id"]   = $_GET["icd"];
		        $_SESSION["icd"][$cnt]["desc"] = $d->description;
		        $_SESSION["icd"][$cnt]["kate"] = $d->category;
		        unset($_SESSION["SELECT_ICD"]);
		    }
		    header("Location: $SC?p=" . $_GET["p"] . "&list=icd&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"] . "&sub=icd");
		    exit;
		    
		} elseif (isset($_GET["layanan"])) {
			
		    $r = pg_query($con,"SELECT * FROM RSV0034 WHERE ID = '" . $_GET["layanan"] . "'");
		    $d = pg_fetch_object($r);
		    pg_free_result($r);

    $gol_tindakan = getFromTable("select golongan_tindakan_id from rs00034 where id='".$_GET["layanan"]."'");
    $is_range = $d->harga_atas > 0 || $d->harga_bawah > 0;

    if ($d->id) {
        if (($is_range && isset($_GET["harga"])) || (!$is_range)) {
            if (is_array($_SESSION["layanan"])) {
                $cnt = count($_SESSION["layanan"]);
            } else {
                $cnt = 0;
            }
           
            $dokter = getFromTable("select nama from rs00017 where id = '".$_SESSION[SELECT_EMP]."'");
            $harga = $is_range ? $_GET["harga"] : $d->harga;
            $_SESSION["layanan"][$cnt]["id"]     = str_pad($_GET["layanan"],5,"0",STR_PAD_LEFT);
            
            if ($d->klasifikasi_tarif) $embel= " - ".$d->klasifikasi_tarif;
            $_SESSION["layanan"][$cnt]["nama"]   = $d->layanan . $embel;
            $_SESSION["layanan"][$cnt]["jumlah"] = $_GET["jumlah"];
            $_SESSION["layanan"][$cnt]["satuan"] = $d->satuan;
            $_SESSION["layanan"][$cnt]["harga"]  = $harga;
            $_SESSION["layanan"][$cnt]["total"]  = $harga * $_GET["jumlah"];
            $_SESSION["layanan"][$cnt]["dokter"]  = $dokter;
            $_SESSION["layanan"][$cnt]["nip"]  = $_SESSION[SELECT_EMP];
            
            
            // tindakan non operatif
            if (substr($d->hierarchy,0,9) == "006001008") {

	               $t = pg_query($con,"SELECT * FROM RS00034 WHERE HIERARCHY LIKE '006001007%' AND GOLONGAN_TINDAKAN_ID = '$gol_tindakan'");
	               $tr = pg_fetch_object($t);
	               
	            do {
	            $cnt++;
	            $harga = $tr->harga;
	            $_SESSION["layanan"][$cnt]["id"]     = str_pad($tr->id,5,"0",STR_PAD_LEFT);
	            if ($tr->klasifikasi_tarif) $embel= " - ".$tr->klasifikasi_tarif;
	            $_SESSION["layanan"][$cnt]["nama"]   = $tr->layanan . $embel;
	            $_SESSION["layanan"][$cnt]["jumlah"] = $_GET["jumlah"];
	            $_SESSION["layanan"][$cnt]["satuan"] = $tr->satuan;
	            $_SESSION["layanan"][$cnt]["harga"]  = $harga;
	            $_SESSION["layanan"][$cnt]["total"]  = $harga * $_GET["jumlah"];
	            
	            } while ($tr = pg_fetch_object($t));
            }


            // tindakan operatif
            if (substr($d->hierarchy,0,9) == "006003002") {

               $t = pg_query($con,"SELECT * FROM RS00034 WHERE HIERARCHY LIKE '006003006%' AND GOLONGAN_TINDAKAN_ID = '$gol_tindakan'");
               $tr = pg_fetch_object($t);
         
	            do {
	            $cnt++;
	            $harga = $tr->harga;
	            $_SESSION["layanan"][$cnt]["id"]     = str_pad($tr->id,5,"0",STR_PAD_LEFT);
	            if ($tr->klasifikasi_tarif) $embel= " - ".$tr->klasifikasi_tarif;
	            $_SESSION["layanan"][$cnt]["nama"]   = $tr->layanan . $embel;
	            $_SESSION["layanan"][$cnt]["jumlah"] = $_GET["jumlah"];
	            $_SESSION["layanan"][$cnt]["satuan"] = $tr->satuan;
	            $_SESSION["layanan"][$cnt]["harga"]  = $harga;
	            $_SESSION["layanan"][$cnt]["total"]  = $harga * $_GET["jumlah"];
	            } while ($tr = pg_fetch_object($t));

            }

            // tindakan rawat jalan
            if (substr($d->hierarchy,0,9) == "006001001") {

               $t = pg_query($con,"SELECT * FROM RS00034 WHERE HIERARCHY LIKE '006001007%' AND GOLONGAN_TINDAKAN_ID = '$gol_tindakan'");
               $tr = pg_fetch_object($t);
               
	            do {
	            $cnt++;
	            $harga = $tr->harga;
	            $_SESSION["layanan"][$cnt]["id"]     = str_pad($tr->id,5,"0",STR_PAD_LEFT);
	            if ($tr->klasifikasi_tarif) $embel= " - ".$tr->klasifikasi_tarif;
	            $_SESSION["layanan"][$cnt]["nama"]   = $tr->layanan . $embel;
	            $_SESSION["layanan"][$cnt]["jumlah"] = $_GET["jumlah"];
	            $_SESSION["layanan"][$cnt]["satuan"] = $tr->satuan;
	            $_SESSION["layanan"][$cnt]["harga"]  = $harga;
	            $_SESSION["layanan"][$cnt]["total"]  = $harga * $_GET["jumlah"];
	            } while ($tr = pg_fetch_object($t));

            }

            unset($_SESSION["SELECT_LAYANAN"]);
            unset($_SESSION["SELECT_EMP"]);

            header("Location: $SC?p=" . $_GET["p"] . "&list=layanan&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"]."&sub=layanan");
            exit;
            
        } elseif ($is_range) {
            $_SESSION["SELECT_LAYANAN"] = $_GET["layanan"];
            header("Location: $SC?p=" . $_GET["p"] . "&list=layanan&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"]. "&jumlah=" . $_GET["jumlah"]);
            exit;
        }
    } else {
        header("Location: $SC?p=" . $_GET["p"] . "&list=layanan&rg=" . $_GET["rg"]."&mr=" . $_GET["mr"]."&sub=layanan");
        exit;
    }
}
echo "<table border=0 width='100%'><tr><td>";
title("<img src='icon/rawat-jalan-2.gif' align='absmiddle' >  POLIKLINIK DOTS");
echo "</td></tr></table>";

unset($_GET["layanan"]);

$reg = $_GET["rg"];
$reg2 = $_GET["rg"];//identifikasi rincian.php

	$tab_disabled = array("pemeriksaan"=>true, "layanan"=>true, "icd"=>true, "riwayat"=>true,"riwayat_klinik"=>true,"unit_rujukan"=>true,"konsultasi"=>true,"resepobat"=>true);
	if ($_GET["act"] == "del" ) {
	$tab_disabled = array("pemeriksaan"=>false, "layanan"=>false, "icd"=>false, "riwayat"=>false,"riwayat_klinik"=>false,"unit_rujukan"=>false,"konsultasi"=>false,"resepobat"=>false);
	$tab_disabled[$_GET["sub"]] = true;
	$tab_disabled[$_POST["sub"]] = true;
	}
	
	$T = new TabBar();
	$T->addTab("$SC?p=$PID&list=pemeriksaan&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr ", "Hasil Pemeriksaan Pasien"	, $tab_disabled["pemeriksaan"]);
	$T->addTab("$SC?p=$PID&list=layanan&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr&sub=layanan", "layanan / Tindakan"	, $tab_disabled["layanan"]);
	$T->addTab("$SC?p=$PID&list=icd&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr&sub=icd", "Pilih I C D"	, $tab_disabled["icd"]);
	$T->addTab("$SC?p=$PID&list=riwayat&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr", "Riwayat Klinik"	, $tab_disabled["riwayat"]);
	$T->addTab("$SC?p=$PID&list=riwayat_klinik&rg=$rg&mr=$mr", "Riwayat Medis"	, $tab_disabled["riwayat_klinik"]);
	$T->addTab("$SC?p=$PID&list=unit_rujukan&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr", "Status Akhir Pasien"	, $tab_disabled["unit_rujukan"]);
	$T->addTab("$SC?p=$PID&list=konsultasi&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr", "Konsultasi"	, $tab_disabled["konsultasi"]);
       $T->addTab("$SC?p=$PID&list=resepobat&rg=$rg&poli=".$_GET["mPOLI"]."&mr=$mr", "Resep Obat"	, $tab_disabled["resepobat"]);

if ($reg > 0) {
    $r = pg_query($con,
       "select a.id,a.mr_no,a.nama,a.umur,a.tgl_lahir,a.tmp_lahir,a.tanggal_reg,a.status_akhir,a.diagnosa_sementara, ".
		"a.jenis_kelamin,a.pangkat_gol,a.nrp_nip,a.kesatuan,a.tipe_desc ".
		"from rsv_pasien2 a  ".
		//"where a.id= '{$_GET['rg']}'";
        //-edited 160210 -> menghilangkan fungsi lpad()
		//"WHERE A.ID = lpad($reg,10,'0')");
		"WHERE A.ID = '$reg'");
    
    $n = pg_num_rows($r);
    if($n > 0) $d = pg_fetch_object($r);
    pg_free_result($r);
    $rawatan = $d->rawatan;

    // ambil bangsal
    $id_max = getFromTable("select max(id) from rs00010 where no_reg = '".$_GET["rg"]."'");
    if (!empty($id_max)) {
    $bangsal = getFromTable("select c.bangsal || ' / ' || e.tdesc ".
                       "from rs00010 as a ".
                       "    join rs00012 as b on a.bangsal_id = b.id ".
                       "    join rs00012 as c on c.hierarchy = substr(b.hierarchy,1,6) || '000000000' ".
                       //"    join rs00012 as d on d.hierarchy = substr(b.hierarchy,1,3) || '000000000000' ".
                       "    join rs00001 as e on c.klasifikasi_tarif_id = e.tc and e.tt = 'KTR' ".
                       "where a.id = '$id_max'");
    }
    $umure = umur($d->umur);
    $umure = explode(" ",$umure);
    $umur = $umure[0]." Tahun";

	//===============update to rs00006 (status pemeriksaan)=============
    if($_GET['act'] == "periksa"){
	//pg_query("update rs00006 set periksa='Y' where id =lpad('".$_GET["rg"]."',10,'0')");
	}
	echo "<hr noshade size='1'>";
    echo "<table border=1 width='100%' cellspacing=0 cellpadding=0><tr><td valign=top>";
    $f = new ReadOnlyForm();
    $f->text("<b>"."Nama",$d->nama);
    $f->text("<b>"."No RM",$d->mr_no);
    $f->text("<b>"."No Reg.", formatRegNo($d->id));
    //$f->text("Kedatangan",$d->datang);
    $f->execute();
    echo "</td><td align=left valign=top>";
    $f = new ReadOnlyForm();
    $f->text("<b>"."NRP/NIP",$d->nrp_nip);
    $f->text("<b>"."Pangkat/Gol",ucwords($d->pangkat_gol));
    $f->text("<b>"."Kesatuan/Pekerjaan",ucwords($d->kesatuan)); 
    $f->execute();
    echo "</td><td align=left valign=top>";
    $f = new ReadOnlyForm();
    $f->text("<b>"."Umur", "$d->umur");
    $f->text("<b>"."Seks",$d->jenis_kelamin);
   $f->text("<b>"."Tipe Pasien",$d->tipe_desc);
    $f->execute();
    echo "</td><td valign=top>";
    $f = new ReadOnlyForm();
    echo "<table border=0 width='100%'>";
    echo "<tr><td class=TBL_BODY><strong>Diagnosa Sementara:</strong></td></tr>";
    echo "<tr><td align=justify class=TBL_BODY>$d->diagnosa_sementara</td></tr>";
    echo "</table>";
    $f->execute();
    
    echo "</td></tr></table>";
    echo"<hr noshade size='2'>";
        
    echo "</div>";
    if(!$GLOBALS['print']){
 	echo " <BR><DIV ALIGN=RIGHT><img src=\"icon/back.gif\" align=absmiddle ><A CLASS=SUB_MENU HREF='index2.php".
            "?p=$PID'>".
            "  Kembali  </A></DIV>";
    }else{}	
 	echo"<br>";
    	
    //disini

    $total = 0.00;
    if (is_array($_SESSION["layanan"])) {
        foreach($_SESSION["layanan"] as $k => $l) {
            $total += $l["total"];
        }
    }

    //echo "<div class=box>";
if ($_GET["sub"] == "byr") {
        title("Total Tagihan: Rp. ".number_format($total,2));
        echo "<br><br><br>";
        $f = new Form("actions/p_dots.insert.php");
        $f->hidden("rg",$_GET["rg"]);
        $f->hidden("mr",$_GET["mr"]);
        $f->hidden("poli",$_GET["poli"]);
	$f->hidden("sub",$_GET["sub"]);
        $f->hidden("byr",$total);
        //$f->text("byr","Jumlah Pembayaran",15,15,$total,"STYLE='text-align:right'");
        $f->submit(" Simpan &amp; Bayar ");
        $f->execute();
    
} elseif ($_GET["list"] == "icd") {  // -------- ICD
		if(!$GLOBALS['print']){
		$T->show(2);
		}else{}
        echo"<div align=center class=form_subtitle1>KLASIFIKASI PENYAKIT</div>";
        echo "<table width='100%' border=0 cellspacing=0 cellpadding=0><tr>";
        echo "<script language='JavaScript'>\n";
        echo "document.Form3.b2.disabled = true;\n";
        echo "document.Form3.b4.disabled = false;\n";
        echo "</script>\n";
        echo "<form action='$SC'>";
        echo "<INPUT TYPE=HIDDEN NAME=p VALUE='$PID'>";
        echo "<INPUT TYPE=HIDDEN NAME=rg VALUE='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=sub VALUE='".$_GET["sub"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=list VALUE='icd'>";
        echo "<INPUT TYPE=HIDDEN NAME=httpHeader VALUE='1'>";
        echo "</form>";
        echo "<td valign=top>";

        $namaICD = getFromTable("SELECT DESCRIPTION FROM RSV0005 WHERE DIAGNOSIS_CODE = '".$_SESSION["SELECT_ICD"]."'");
        $katICD = getFromTable("SELECT CATEGORY FROM RSV0005 WHERE DIAGNOSIS_CODE = '".$_SESSION["SELECT_ICD"]."'");
        
        $t = new BaseTable("100%");
        $t->printTableOpen();
        echo "<FORM ACTION='$SC' NAME=Form11>";
        echo "<INPUT TYPE=HIDDEN NAME=p VALUE='$PID'>";
        echo "<INPUT TYPE=HIDDEN NAME=rg VALUE='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=list VALUE='icd'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=httpHeader VALUE='1'>";
        $t->printTableHeader(Array("KODE ICD", "KETERANGAN","KATEGORI", "&nbsp;"));
        
        if (is_array($_SESSION["icd"])) {
            foreach($_SESSION["icd"] as $k => $l) {
                $t->printRow(
                    Array($l["id"], $l["desc"],$l["kate"], "<A HREF='$SC?p=$PID&list=icd&rg=".$_GET["rg"]."&mr=".$_GET["mr"]."&del-icd=$k&httpHeader=1'>".icon("del-left")."</A>"), Array("CENTER", "LEFT", "LEFT","CENTER")
                );
            }
        }
		// sfdn, 27-12-2006 --> pembetulan directory icon = ../simrs/images/*.png
        $t->printRow(
            Array("<INPUT OnKeyPress='refreshSubmit()' NAME=icd STYLE='text-align:center' TYPE=TEXT SIZE=5 MAXLENGTH=10 VALUE='".$_SESSION["SELECT_ICD"]."'>&nbsp;<A HREF='javascript:selectICD()'><IMG BORDER=0 SRC='images/icon-view.png'></A>", $namaICD,"$katICD", "<INPUT NAME='submitButton' TYPE=SUBMIT VALUE='OK'>"),
            Array("CENTER", "LEFT", "LEFT","CENTER")
        );
		// --- eof 27-12-2006 ---
        echo "</FORM>";
        
        $t->printTableClose();
        //echo "</td></tr></table>";
        echo "\n<script language='JavaScript'>\n";
        echo "function selectICD() {\n";
        echo "sWin = window.open('popup/icd.php', 'xWin', 'top=0,left=0,width=500,height=400,menubar=no,scrollbars=yes');\n";
        echo "sWin.focus();\n";
        echo "}\n";
        echo "</script>\n";
        
        echo "<form name='Form9' action='actions/p_dots.insert.php' method=POST>";
        echo "<input type=hidden name=rg value='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=sub VALUE='".$_GET["sub"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=list VALUE='icd'>";
        echo "<input type=hidden name=rawatan value='".$rawatan."'>";
        echo "<br><div align=right><input type=button value='Simpan' onClick='document.Form9.submit()'>&nbsp;</div>";
        echo "</form>";
     
        include("rincian2.php");
        
    }elseif ($_GET["list"] == "layanan") { // ----------------------------- LAYANAN MEDIS
    	if(!$GLOBALS['print']){
    	$T->show(1);
    	}else{}
        echo"<div align=center class=form_subtitle1>LAYANAN DAN TINDAKAN MEDIS</div>";
        echo "<script language='JavaScript'>\n";
        echo "document.Form3.b1.disabled = true;\n";
        echo "document.Form3.b2.disabled = false;\n";
        echo "document.Form3.b4.disabled = false;\n";
        echo "</script>\n";

        echo "<FORM ACTION='$SC' NAME=Form8>";
        echo "<INPUT TYPE=HIDDEN NAME=p VALUE='$PID'>";
        echo "<INPUT TYPE=HIDDEN NAME=rg VALUE='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=list VALUE='layanan'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=httpHeader VALUE='1'>";
        


        $t = new BaseTable("100%");
        $t->printTableOpen();
        $t->printTableHeader(Array("KODE", "LAYANAN", "YANG MELAKUKAN TINDAKAN", "JUMLAH", "SATUAN",
            "HARGA SATUAN", "HARGA TOTAL", ""));
            
        if (is_array($_SESSION["layanan"])) {
            $total = 0.00;
            foreach($_SESSION["layanan"] as $k => $l) {

                $q = pg_query("SELECT B.TDESC AS KELAS_TARIF, SUBSTR(A.HIERARCHY,1,6) AS HIE FROM RS00034 A ".
                        "LEFT JOIN RS00001 B ON A.KLASIFIKASI_TARIF_ID = B.TC AND B.TT = 'KTR' ".
                        "WHERE A.ID = $l[id]");
                $qr = pg_fetch_object($q);

                if ($qr->hie == "003002") {
                   $tambahan = " - ".$qr->kelas_tarif;

                }

                $t->printRow(
                    Array($l["id"], $l["nama"].$tambahan, $l["dokter"], $l["jumlah"], $l["satuan"],
                        number_format($l["harga"],2), number_format($l["total"],2),
                        "<A HREF='$SC?p=$PID&list=layanan&rg=".$_GET["rg"]."&mr=".$_GET["mr"]."&del=$k&httpHeader=1'>".icon("del-left")."</A>"),
                    Array("CENTER", "LEFT", "CENTER","RIGHT", "LEFT", "RIGHT", "RIGHT", "CENTER")
                );
                $total += $l["total"];
            }
        }
        
        if (isset($_SESSION["SELECT_LAYANAN"])) {
            $r = pg_query($con,"select * from rsv0034 where id = '" . $_SESSION["SELECT_LAYANAN"] . "'");
            $d = pg_fetch_object($r);
            pg_free_result($r);

            $is_range = $d->harga_atas > 0 || $d->harga_bawah > 0;
            $harga = $is_range ? $_GET["harga"] : $d->harga;

            $hargaHtml = $is_range ?
                "<INPUT TYPE=TEXT NAME=harga SIZE=10 MAXLENGTH=12 VALUE='$d->harga'>" : $d->harga;
        }
		// sfdn, 27-12-2006 -> pembetulan directory gambar = ../simrs/images/*.png
        $t->printRow(
            Array("<INPUT OnKeyPress='refreshSubmit()' NAME=layanan STYLE='text-align:center' TYPE=TEXT SIZE=5 MAXLENGTH=10 VALUE='".$_SESSION["SELECT_LAYANAN"].
			"'>&nbsp;<A HREF='javascript:selectLayanan()'><IMG BORDER=0 SRC='images/icon-view.png'></A>",
			$d->layanan . " - " . $d->klasifikasi_tarif, "<INPUT OnKeyPress='refreshSubmit()' NAME=dokter STYLE='text-align:center' TYPE=TEXT SIZE=5 MAXLENGTH=12 VALUE='"
                        .$_SESSION["SELECT_EMP"]."'>&nbsp;<A HREF='javascript:selectPegawai()'><IMG BORDER=0 SRC='images/icon-view.png'></a>", "<INPUT VALUE='".(isset($_GET["jumlah"]) ? $_GET["jumlah"] : "1").
			"'NAME=jumlah OnKeyPress='refreshSubmit()' TYPE=TEXT SIZE=5 MAXLENGTH=10 VALUE='1' STYLE='text-align:right'>", $d->satuan, $hargaHtml,
			"", "<INPUT NAME='submitButton' TYPE=SUBMIT VALUE='OK' >"),
            Array("CENTER", "LEFT", "CENTER","CENTER", "LEFT", "RIGHT", "LEFT", "CENTER")
        );
		// --- eof 27-12-2006 ---
        $t->printRow(
            Array("", "", "", "", "", "", number_format($total,2),""),
            Array("RIGHT", "RIGHT", "RIGHT", "RIGHT", "RIGHT", "RIGHT", "RIGHT")
        );
        $t->printTableClose();
        echo "</FORM>";
        
        if (isset($_SESSION["SELECT_LAYANAN"]) && $is_range) {
            echo "<br>";
            info("Informasi Harga:",
                "$d->unit_layanan, $d->sub_unit_layanan, $d->layanan<BR>".
                "Harga: <big>Rp. $d->harga_bawah</big> sampai dengan <big>Rp. $d->harga_atas</big>");
        }
        echo "<form name='Form10' action='actions/p_dots.insert.php' method=POST>";
        echo "<input type=hidden name=rg value='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=sub VALUE='".$_GET["sub"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<input type=hidden name=rawatan value='".$rawatan."'>";
        echo "<input type=hidden name=list value='layanan'>";
        echo "<br><div align=right><input type=button value='Simpan' onClick='document.Form10.submit()'>&nbsp;";
        echo "</form>";
        
       include("rincian2.php"); 
       
    } elseif($_GET["list"] == "riwayat") {
    	if(!$GLOBALS['print']){
    	$T->show(3);
    	}else{}
		
    	if ($_GET["act"] == "detail") {
				$sql = "select a.*,b.nama,to_char(a.tanggal_reg,'dd Month yyyy')as tanggal_reg,f.layanan 
						from c_visit a 
						left join rs00017 b on a.id_dokter = B.ID 
						left join rsv0002 c on a.no_reg=c.id 
						left join rs00006 d on d.id = a.no_reg
						left join rs00008 e on e.no_reg = a.no_reg
						--left join rs00034 f on f.id = trim(e.item_id,0)
						left join rs00034 f on 'f.id' = e.item_id
						where a.no_reg='{$_GET['rg']}' and a.id_poli='".$_GET["mPOLI"]."' ";
				$r = pg_query($con,$sql);
				$n = pg_num_rows($r);
			    if($n > 0) $d = pg_fetch_array($r);
			    pg_free_result($r);
				//echo $sql;exit;			
			    $_GET['id'] = $_GET['rg'] ;	
	 			
			echo"<div class=box>";
			echo "<table width='100%' border='0'><tr><td colspan='2'>";
			echo"<div class=form_subtitle>PEMERIKSAAN PASIEN</div>";
			echo "</td></tr>";
    		echo "<tr><td valign=top>";
			$f = new ReadOnlyForm();
			$f->text("Tanggal Pemeriksaan","<b>".$d["tanggal_reg"]);
			$f->title1("<U>ANAMNESA</U>","LEFT");
			$f->text($visit_paru["vis_1"],$d[3] );
			$f->text($visit_paru["vis_2"],$d[4] );
			$f->text($visit_paru["vis_3"],$d[5] );
			$f->title1("<U>PEMERIKSAAN FISIK</U>","LEFT");
			$f->text($visit_paru["vis_4"],$d[6] );
			$f->text($visit_paru["vis_5"],$d[7]."&nbsp;Kg");
			$f->text($visit_paru["vis_6"],$d[8]."&nbsp;mm Hg" );
			$f->text($visit_paru["vis_7"],$d[9]."&nbsp;/Menit" );
			$f->text($visit_paru["vis_8"],$d[10]."&deg;C" );
			$f->title1("<U>LABORATORIUM</U>","LEFT");
			$f->text($visit_paru["vis_9"],$d[11]);
			$f->text($visit_paru["vis_10"],$d[12]);
			$f->text($visit_paru["vis_11"],$d[13]);
			$f->text($visit_paru["vis_12"],$d[14] );
			$f->text($visit_paru["vis_13"],$d[15] );
			$f->execute();
			echo "</td><td valign=top>";
			$f = new ReadOnlyForm();
			$f->text($visit_paru["vis_14"],$d[16] );
			$f->text($visit_paru["vis_15"],$d[17]);
			$f->text($visit_paru["vis_16"],$d[18]);
			$f->text($visit_paru["vis_17"],$d[19]);
			$f->text($visit_paru["vis_18"],$d[20] );
			$f->text($visit_paru["vis_19"],$d[21]);
			$f->title1("<U>PEMERIKSAAN LAIN</U>","LEFT");
			$f->text($visit_paru["vis_22"],$d[24]);
			$f->text($visit_paru["vis_23"],$d[25]);
			$f->text($visit_paru["vis_24"],$d[26]);
			$f->text($visit_paru["vis_25"],$d[27]);
			$f->text($visit_paru["vis_26"],$d[28]);
			$f->title1("<U>DIAGNOSA</U>","LEFT");
			$f->text($visit_paru["vis_27"],$d[29]);
			$f->title1("<U>DOKTER PEMERIKSA</U>","LEFT");
			$f->text("Nama",$d["nama"]);
			$f->execute();
			echo "</td></tr>";
  			echo "<tr><td colspan='3'>";
  			echo "<br>";
  			include(rm_tindakan3);
  			echo "</td><td>";
  			echo "</td></tr></table>";

			
			}else {
				echo"<div align=center class=form_subtitle1>RIWAYAT PENYAKIT PASIEN</div>";
		//detail riwayat
		echo "<table border=0 width='100%' cellspacing=0 cellpadding=0><tr><td valign=top width='33%'  colspan=2>";
		
		//$f = new Form($SC, "GET");
		//edited 160210
				$sql = "SELECT A.NO_REG,TO_CHAR(A.TANGGAL_REG,'DD MON YYYY')AS TANGGAL,TO_CHAR(A.TANGGAL_REG,'HH:MM:SS') AS WAKTU,A.VIS_1,'DUMMY' ". 
					   "FROM C_VISIT A ".
					   "LEFT JOIN RS00006 B ON A.NO_REG=B.ID ".
					   "WHERE B.MR_NO = '".$_GET["mr"]."' AND A.ID_POLI = '{$_GET["mPOLI"]}' ";
				$t = new PgTable($con, "100%");
			    $t->SQL = $sql ;
			    $t->setlocale("id_ID");
			    $t->ShowRowNumber = true;
			   	//$t->ColHidden[4]= true;
			    $t->RowsPerPage = $ROWS_PER_PAGE;
			    $t->ColHeader = array("NO REGISTRASI","TANGGAL PEMERIKSAAN","WAKTU KUNJUNGAN","KELUHAN UTAMA","DETAIL");
			   	$t->ColAlign = array("center","center","center","left","center");
				$t->ColFormatHtml[4] = "<A CLASS=TBL_HREF HREF='$SC?p=$PID&list=riwayat&act=detail&mr=".$_GET["mr"]."&poli=".$_GET["mPOLI"]."&rg=<#0#>'>".icon("view","View")."</A>";	
				$t->execute();
				
				echo"<br>";
         		echo"</div>";
				echo "</td></tr></table></div>";
    	
			}
    }elseif($_GET["list"] == "riwayat_klinik") {
    	if(!$GLOBALS['print']){
    	$T->show(4);
    	}else{}
		//edited 160210
    	if ($_GET["act"] == "detail_klinik") {
				$sql = "select a.*,b.nama,to_char(a.tanggal_reg,'dd Month yyyy')as tanggal_reg,f.layanan,a.id_poli 
						from c_visit a 
						left join rs00017 b on a.id_dokter = B.ID 
						left join rsv0002 c on a.no_reg=c.id 
						left join rs00006 d on d.id = a.no_reg
						left join rs00008 e on e.no_reg = a.no_reg
						--left join rs00034 f on f.id = trim(e.item_id,0)
						left join rs00034 f on 'f.id' = e.item_id
						where a.no_reg='{$_GET['rg']}' ";
				$r = pg_query($con,$sql);
				$n = pg_num_rows($r);
			    if($n > 0) $d = pg_fetch_array($r);
			    pg_free_result($r);
				//echo $sql;exit;			
			    $_GET['id'] = $_GET['rg'] ;	
	 			
			//echo"<div class=box>";
			echo "<table width='100%' border='0'><tr><td colspan='2'>";
			//echo"<div class=form_subtitle>PEMERIKSAAN PASIEN</div>";
			echo "</td></tr>";
    		echo "<tr><td>";
    		$f = new ReadOnlyForm();
    		$poli=$_GET["polinya"];
    		$f->text("Poli","<b>".$poli);
    		if ($poli == $setting_poli["igd"]) {
    			include(detail_igd);
    		}elseif ($poli == $setting_poli["umum"]){
    			include(detail_umum);
    		}elseif ($poli == $setting_poli["mata"]){
    			include(detail_mata);
    		}elseif ($poli == $setting_poli["peny_dalam"]){
    			include(detail_peny_dalam);
    		}
    		elseif ($poli == $setting_poli["anak"]){
    			include(detail_anak);
    		}
    		elseif ($poli == $setting_poli["gigi"]){
    			include(detail_gigi);
    		}
    		elseif ($poli == $setting_poli["tht"]){
    			include(detail_tht);
    		}
    		elseif ($poli == $setting_poli["bedah"]){
    			include(detail_bedah);
    		}
    		elseif ($poli == $setting_poli["kulit_kelamin"]){
    			include(detail_kulit_kelamin);
    		}
    		elseif ($poli == $setting_poli["akupunktur"]){
    			include(detail_akupunktur);
    		}
    		elseif ($poli == $setting_poli["jantung"]){
    			include(detail_jantung);
    		}
    		elseif ($poli == $setting_poli["paru"]){
    			include(detail_paru);
    		}
    		elseif ($poli == $setting_poli["kebidanan_obstetri"]){
    			include(detail_obstetri);
    		}
    		elseif ($poli == $setting_poli["saraf"]){
    			include(detail_saraf);
    		}
    		elseif ($poli == $setting_poli["kebidanan_ginekologi"]){
    			include(detail_ginekologi);
    		}
    		elseif ($poli == $setting_poli["psikiatri"]){
    			include(detail_psikiatri);
    		}
    		elseif ($poli == $setting_poli["fisioterapi"]){
    			include(detail_fisioterapi);
    		}
    		elseif ($poli == $setting_poli["radiologi"]){
    			include(detail_radiologi);
    		}
    		else{
    			include(detail_laboratorium);
    		}
    		
			}else {
				echo"<div align=center class=form_subtitle1>RIWAYAT PENYAKIT PASIEN</div>";
		//detail riwayat
		echo "<table border=0 width='100%' cellspacing=0 cellpadding=0><tr><td valign=top width='33%'  colspan=2>";
		
		//$f = new Form($SC, "GET");
		//edited 160210
				$sql = "SELECT A.NO_REG,TO_CHAR(A.TANGGAL_REG,'DD MON YYYY')AS TANGGAL,TO_CHAR(A.TANGGAL_REG,'HH:MM:SS') AS WAKTU,C.TDESC,D.NAMA,A.ID_POLI,'DUMMY' ". 
					   "FROM C_VISIT A ".
					   "LEFT JOIN RS00006 B ON A.NO_REG=B.ID ".
					   "LEFT JOIN RS00001 C ON A.ID_POLI = C.TC_POLI AND C.TT='LYN'".
					   "LEFT JOIN RS00017 D ON A.ID_DOKTER = D.ID ".
					   "LEFT JOIN RS00001 E ON A.ID_KONSUL = E.TC AND E.TT='LYN'".
					   "WHERE B.MR_NO = '".$_GET["mr"]."' AND A.ID_KONSUL = '' AND A.ID_POLI != 100";
					
				$t = new PgTable($con, "100%");
			    $t->SQL = $sql ;
			    $t->setlocale("id_ID");
			    $t->ShowRowNumber = true;
			   	$t->ColHidden[6]= true;
			   	$t->ColHidden[1]= true;
			    $t->RowsPerPage = $ROWS_PER_PAGE;
			    $t->ColHeader = array("TANGGAL PEMERIKSAAN","","WAKTU KUNJUNGAN","KLINIK","DOKTER PEMERIKSA","DETAIL");
			   	$t->ColAlign = array("center","center","center","left","left","left","center","center");
				$t->ColFormatHtml[6] = "<A CLASS=TBL_HREF HREF='$SC?p=$PID&list=riwayat_klinik&act=detail_klinik&polinya=<#5#>&mr=".$_GET["mr"]."&rg=<#0#>'>".icon("view","View")."</A>";	
				$t->execute();
				
				echo"<br>";
         		echo"</div>";
				echo "</td></tr></table></div>";
    	
			}
			 }elseif ($_GET["list"] == "unit_rujukan"){
    	$T->show(5);
    	echo"<br>";
    	//$laporan = getFromTable("select tdesc from rs00001 where tt='LRI' and tc = '".$_SESSION[SELECT_LAP]."'");
    	$f = new Form("actions/p_dots.insert.php", "POST", "NAME=Form2");
					$f->hidden("act","new1");
					$f->hidden("f_no_reg",$d->id);
					$f->hidden("list","unit_rujukan");
				    $f->hidden("mr",$_GET["mr"]);
				    $f->hidden("f_tanggal_reg",$d2["tanggal_reg"]);
				    $f->hidden("f_id_poli",$_GET["poli"]);
				    $f->hidden("f_user_id",$_SESSION[uid]);
				    $f->hidden("status_akhir",$_GET["status_akhir"]);
				    
					echo"<br>";
					$tipe = getFromTable("select status_akhir_pasien from rs00006 where id='".$_GET["rg"]."'");
				    $f->PgConn=$con;
					$f->selectSQL("status_akhir","Status Akhir Pasien", "select '' as tc, '' as tdesc union select tc , tdesc from rs00001 where tt='SAP' and tc not in ('000')", $tipe,$ext);
				    $f->submitAndCancel("Simpan",$ext,"Batal","window.history.back()",$ext);
				    $f->execute();
				    

				        	
    }elseif ($_GET["list"] == "konsultasi"){
    	$T->show(6);
    	echo"<br>";
    	
    	//$laporan = getFromTable("select tdesc from rs00001 where tt='LRI' and tc = '".$_SESSION[SELECT_LAP]."'");
    	$f = new Form("actions/p_dots.insert.php", "POST", "NAME=Form2");
					$f->hidden("act","new2");
					$f->hidden("f_no_reg",$d->id);
					$f->hidden("list","konsultasi");
				    $f->hidden("mr",$_GET["mr"]);
				    $f->hidden("f_id_poli",$_GET["poli"]);
				    $f->hidden("f_tanggal_reg",$d2["tanggal_reg"]);
				    $f->hidden("f_user_id",$_SESSION[uid]);
				    $f->hidden("konsultasi",$_GET["konsultasi"]);
				    
					echo"<br>";
					$konsul = getFromTable("select id_konsul from c_visit where no_reg='".$_GET["rg"]."' and id_poli='".$_GET["poli"]."'");
				    $f->PgConn=$con;
					$f->selectSQL("konsultasi","Unit Yang Dituju", "select tc,tdesc from rs00001 where tt='LYN' and tc not in ('000','100','201','202','206','207','208') order by tdesc",$konsul,$ext);
				    $f->submitAndCancel("Simpan",$ext,"Batal","window.history.back()",$ext);
				    $f->execute();
		echo"<br><font color=black>&nbsp;* Catatan : Hasil Pemeriksaan Pasien harus diisi minimal Dokter Pemeriksa</font><br>";


	}elseif ($_GET["list"] == "resepobat"){ //RESEP OBAT
    	$T->show(7);
    	//echo"<br>";
    	
    	  title("Resep / Obat");
        echo "<script language='JavaScript'>\n";
       echo "document.Form3.b3.disabled = true;\n";
	   echo "document.Form3.b1.disabled = false;\n";
        echo "document.Form3.b2.disabled = false;\n";
        echo "document.Form3.b4.disabled = false;\n";
        echo "</script>\n";


        if ($_SESSION["SELECT_OBAT"]) {
           $namaObat = getFromTable("select obat from rsv0004 where id = '".$_SESSION["SELECT_OBAT"]."'");
           $hargaObat = getFromTable("select harga from rsv0004 where id = '".$_SESSION["SELECT_OBAT"]."'");
        }

        echo "<FORM ACTION='$SC' NAME=Form8>";
        echo "<INPUT TYPE=HIDDEN NAME=p VALUE='$PID'>";
        echo "<INPUT TYPE=HIDDEN NAME=rg VALUE='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=httpHeader VALUE='1'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        //echo "<a href='$SC?p=$PID&rg=".$_GET[rg]."&sub=retur'>[RETUR OBAT]</a>";
        $t = new BaseTable("100%");
        $t->printTableOpen();
        //$t->printTableHeader(Array("KODE", "Nama Obat", "Jumlah", "Satuan", "Dosis", ""));
        $t->printTableHeader(Array("KODE", "Nama Obat", "Jumlah", "Harga Satuan", "Harga Total", ""));


        if (is_array($_SESSION["obat"])) {


            foreach($_SESSION["obat"] as $k => $l) {
                $t->printRow(
                    Array($l["id"], $l["desc"], $l["jumlah"], number_format($l["harga"],2), number_format($l["total"],2),
                    "<A HREF='$SC?p=$PID&list=resepobat&rg=".$_GET["rg"]."&mr=".$_GET["mr"]."&del-obat=$k&httpHeader=1'>".icon("del-left")."</A>"),
                    Array("CENTER", "LEFT", "CENTER", "RIGHT", "RIGHT", "CENTER")
                );
            }



        }
        
	// sfdn, 27-12-2006 -> pembetulan directory icon = ../simrs/images/*.png
        $t->printRow(
            Array("<INPUT OnKeyPress='refreshSubmit2()' NAME=obat STYLE='text-align:center' TYPE=TEXT SIZE=5
            MAXLENGTH=10 VALUE='".$_SESSION["SELECT_OBAT"]."'>&nbsp;<A HREF='javascript:selectObat()'>
            <IMG BORDER=0 SRC='images/icon-view.png'></A>", $namaObat,
            "<INPUT VALUE='".(isset($_GET["jumlah_obat"]) ? $_GET["jumlah_obat"] : "1")."'NAME=jumlah_obat
            OnKeyPress='refreshSubmit2()' TYPE=TEXT SIZE=5 MAXLENGTH=10 VALUE='1' STYLE='text-align:right'>",
            number_format($hargaObat,2), "",
            "<INPUT NAME='submitButton' TYPE=SUBMIT VALUE='OK'>"),
            Array("CENTER", "LEFT", "CENTER", "RIGHT", "RIGHT", "CENTER")
        );
	// --- eof 27-12-2006 ---
        $t->printTableClose();
        echo "</FORM>";

        echo "\n<script language='JavaScript'>\n";
        echo "function selectObat() {\n";
        echo "    sWin = window.open('popup/obat.php', 'xWin', 'top=0,left=0,width=600,height=400,menubar=no,scrollbars=yes');\n";
        echo "    sWin.focus();\n";
        echo "}\n";
        echo "</script>\n";

echo "<table border=0 width='100%'><tr>";
	echo "<td align=right valign=top>";

  echo "<form name='Form9' action='actions/320RJDOTS.insert.php' method=POST>";
        echo "<input type=hidden name=rg value='".$_GET["rg"]."'>";
        echo "<INPUT TYPE=HIDDEN NAME=sub VALUE='".$_GET["sub"]."'>";
        echo "<input type=hidden name=rawatan value='".$rawatan."'>";
        echo "<INPUT TYPE=HIDDEN NAME=mr VALUE='".$_GET["mr"]."'>";
        echo "<input type=button value='Simpan' onClick='document.Form9.submit()'>&nbsp;";
        //if ($total > 0) echo "<input type=button value='Simpan &amp; Bayar' onClick='window.location=\"$SC?p=$PID&rg=".$_GET["rg"]."&sub=byr\"'>&nbsp;";
        echo "</form>";
$att = "320RJPARU" ;
 include("rincianobat.php"); 

    }else {       //pemeriksaan
    	if(!$GLOBALS['print']){
    	$T->show(0);
    	}else{}
    		$sql2 =	"SELECT A.*,B.NAMA FROM C_VISIT A 
    				LEFT JOIN RS00017 B ON A.ID_DOKTER = B.ID
    				WHERE A.ID_POLI={$_GET["mPOLI"]} AND A.NO_REG='$rg'"; 
	    	$r=pg_query($con,$sql2);
	    	$n = pg_num_rows($r);		    	
			    if($n > 0) $d2 = pg_fetch_array($r);
			    pg_free_result($r);
				//-------------------------tambah for update------hery 08072007
				echo "<div align=left><input type=button value=' Edit ' OnClick=\"window.location = './index2.php?p=$PID&rg=$rg&mr={$_GET['mr']}&poli={$_GET["poli"]}&act=edit';\">\n";   
				//echo "<input type='image' src='images/icon-edit.png' action='edit' >";
				    
				if ($_GET['act'] == "edit"){
						echo "<font color='#000000' size='2'> >>Edit Pemeriksaan Pasien</font>";
						$f = new Form("actions/p_dots.insert.php", "POST", "NAME=Form2");
						$f->hidden("act","edit");
						$f->hidden("f_no_reg",$d2["no_reg"]);
					    $f->hidden("f_tanggal_reg",$d2["tanggal_reg"]);
						$f->hidden("list","pemeriksaan");
					    $f->hidden("mr",$_GET["mr"]);
					    $f->hidden("f_id_poli",$_GET["poli"]);
					    $f->hidden("f_user_id",$_SESSION[uid]);
					   
				}else {
					if($n > 0){
						$ext= "disabled";
					}else {
						$ext = "";
					}
				//---------------------------------------------------------------------------------			
					echo "<table border=1 width='100%' cellspacing=0 cellpadding=0><tr><td valign=top width='33%'>";	
					$f = new Form("actions/p_dots.insert.php", "POST", "NAME=Form2");
					$f->hidden("act","new");
					$f->hidden("f_no_reg",$d->id);
					$f->hidden("list","pemeriksaan");
				    $f->hidden("mr",$_GET["mr"]);
				    $f->hidden("f_id_poli",$_GET["poli"]);
				    $f->hidden("f_user_id",$_SESSION[uid]);
			}
				    
				//$f->calendar("tanggal_reg","Tanggal Registrasi",15,15,$d2[1],"Form2","icon/calendar.gif","Pilih Tanggal",$ext);
					
				    echo"<div align=left class=FORM_SUBTITLE1><U>ANAMNESA PASIEN</U></div>";
				    
				    if (isset($_SESSION["SELECT_EMP"])) {
    					$_SESSION["DOKTER"]["id"] = $_SESSION["SELECT_EMP"];
    					$_SESSION["DOKTER"]["nama"] =
        				getFromTable(
			            "select nama from rs00017 where id = '".$_SESSION["DOKTER"]["id"]."'");
    					$f->textAndButton3("f_id_dokter","Dokter Pemeriksa",2,10,$_SESSION["DOKTER"]["id"],$ext,"nm2",30,70,$_SESSION["DOKTER"]["nama"],$ext,"...",$ext,"OnClick='selectPegawai();';");
			            unset($_SESSION["SELECT_EMP"]);
					}else{
						$f->textAndButton3("f_id_dokter","Dokter Pemeriksa",2,10,$d2["id_dokter"],$ext,"nm2",30,70,$d2["nama"],$ext,"...",$ext,"OnClick='selectPegawai();';");
					}
				    
					$max = count($visit_paru) ; 
					$i = 1;
					while ($i<= $max) {
				    	if 		($visit_paru["vis_".$i."F"] == "memo") {
								$f->textarea("f_vis_".$i,$visit_paru["vis_".$i] ,1, $visit_paru["vis_".$i."W"],ucfirst($d2[2+$i]),$ext);
						
				    	}elseif ($visit_paru["vis_".$i."F"] == "edit5") {
								$f->text_5("<U>PEMERIKSAAN FISIK</U>","f_vis_4",$visit_paru["vis_4"],10,10,$d2[2+$i],"","f_vis_5",$visit_paru["vis_5"],10,10,$d2[2+$i+1],"Kg","f_vis_6",$visit_paru["vis_6"],10,10,$d2[2+$i+2],"mm Hg","f_vis_7",$visit_paru["vis_7"],10,10,$d2[2+$i+3],"/ Menit","f_vis_8",$visit_paru["vis_8"],10,10,$d2[2+$i+4],"&deg;C",$ext);
						
				    	}elseif ($visit_paru["vis_".$i."F"] == "edit") {
		        				$f->text("f_vis_".$i,$visit_paru["vis_".$i],$visit_paru["vis_".$i."W"],$visit_paru["vis_".$i."W"],$d2[2+$i],$ext);
		    			
				    	}elseif ($visit_paru["vis_".$i."F"] == "edit_4") {
								$f->text_4("","f_vis_17",$visit_paru["vis_17"],10,10,$d2[2+$i],"","f_vis_18",$visit_paru["vis_18"],10,10,$d2[2+$i+1],"","f_vis_19",$visit_paru["vis_19"],10,10,$d2[2+$i+2],"","f_vis_20",$visit_paru["vis_20"],10,10,$d2[2+$i+3],"",$ext);
								
						}elseif ($visit_paru["vis_".$i."F"] == "edit3") {
								$f->text_4("","f_vis_13",$visit_paru["vis_13"],10,10,$d2["vis_13"],"","f_vis_14",$visit_paru["vis_14"],10,10,$d2["vis_14"],"","f_vis_15",$visit_paru["vis_15"],10,10,$d2["vis_15"],"","f_vis_16",$visit_paru["vis_16"],10,10,$d2["vis_16"],"",$ext);			    	
					    
						}elseif ($visit_paru["vis_".$i."F"] == "edit4") {
								$f->text_gambar4("<U>LABORATORIUM</U>","IMG BORDER=0 SRC='images/bg_tbh_06.gif'","f_vis_9",$visit_paru["vis_9"],46,50,$d2[2+$i],$ext,"f_vis_10",$visit_paru["vis_10"],46,50,$d2[2+$i+1],$ext,"f_vis_11",$visit_paru["vis_11"],46,50,$d2[2+$i+2],$ext,"f_vis_12",$visit_paru["vis_12"],46,50,$d2[2+$i+3],$ext);
						}
			    
	 		$i++ ; 	 	
			}
			$f->title1("<U>SPUNTUM</U>","left");
			if($d2["vis_21"]=="BTA.Langsung"){
			    	$f->checkbox4($visit_paru["vis_21"],"check1","BTA Langsung","BTA.Langsung","CHECKED","Biakan BTA","Biakan.BTA","","M.O","M.O","","Sitologi","Sitologi","",$ext);
			    }elseif ($d2["vis_21"]=="Biakan.BTA"){
			    	$f->checkbox4($visit_paru["vis_21"],"check1","BTA Langsung","BTA.Langsung","","Biakan BTA","Biakan.BTA","CHECKED","M.O","M.O","","Sitologi","Sitologi","",$ext);
			    }elseif($d2["vis_21"]=="M.O"){
			    	$f->checkbox4($visit_paru["vis_21"],"check1","BTA Langsung","BTA.Langsung","","Biakan BTA","Biakan.BTA","","M.O","M.O","CHECKED","Sitologi","Sitologi","",$ext);
			    }elseif ($d2["vis_21"]=="Sitologi"){
			    	$f->checkbox4($visit_paru["vis_21"],"check1","BTA Langsung","BTA.Langsung","","Biakan BTA","Biakan.BTA","","M.O","M.O","","Sitologi","Sitologi","CHECKED",$ext);
			    }else{
			    	$f->checkbox4($visit_paru["vis_21"],"check1","BTA Langsung","BTA.Langsung","","Biakan BTA","Biakan.BTA","","M.O","M.O","","Sitologi","Sitologi","",$ext);
			    }
			$f->textarea("f_vis_22",$visit_paru["vis_22"] ,1, $visit_paru["vis_22"."W"],ucfirst($d2["vis_22"]),$ext);
			$f->textarea("f_vis_23",$visit_paru["vis_23"] ,1, $visit_paru["vis_23"."W"],ucfirst($d2["vis_23"]),$ext);    
			$f->textarea("f_vis_24",$visit_paru["vis_24"] ,1, $visit_paru["vis_24"."W"],ucfirst($d2["vis_24"]),$ext);
			$f->textarea("f_vis_25",$visit_paru["vis_25"] ,1, $visit_paru["vis_25"."W"],ucfirst($d2["vis_25"]),$ext);
			$f->textarea("f_vis_26",$visit_paru["vis_26"] ,1, $visit_paru["vis_26"."W"],ucfirst($d2["vis_26"]),$ext);    
			$f->textarea1("<U>DIAGNOSA KERJA</U>","f_vis_27",$visit_paru["vis_27"] ,1, $visit_paru["vis_27"."W"],ucfirst($d2["vis_27"]),$ext);
			$f->submitAndCancel("Simpan",$ext,"Batal","window.history.back()",$ext);
			$f->execute();
			echo"</div>";
			
    	
    }
    
    //pemeriksaan
    
    echo "</div>";
    
	    echo "\n<script language='JavaScript'>\n";
	    echo "function selectLayanan() {\n";
	   	echo "    sWin = window.open('popup/layanan.php', 'xWin', 'top=0,left=0,width=600,height=400,menubar=no,scrollbars=yes');\n";
	    echo "    sWin.focus();\n";
	    echo "}\n";
        echo "function selectPegawai(tag) {\n";
        echo "    sWin = window.open('popup/pegawai.php?tag=' + tag, 'xWin',".
             " 'top=0,left=0,width=500,height=400,menubar=no,scrollbars=yes');\n";
        echo "    sWin.focus();\n";
        echo "}\n";
        
    if (empty($_GET[sub])) {
	    echo "function refreshSubmit() {\n";
	    echo "    document.Form8.submitButton.disabled = Number(document.Form8.layanan.value) == 0 || Number(document.Form8.jumlah.value == 0);\n";
	    echo "}\n";
	    echo "refreshSubmit();\n";
	    }
	    echo "</script>\n";
   		
} else {
	//update tab pasien App.25-11-07
	echo"<br>";
	$tab_disabled = array("tab1"=>true, "tab2"=>true);
	$T1 = new TabBar();
	$T1->addTab("$SC?p=$PID&list2=tab1&list=layanan", "Daftar Pasien Konsul"	, $tab_disabled["tab1"]);
	$T1->addTab("$SC?p=$PID&list2=tab2&list=layanan", "Daftar Pasien Klinik"	, $tab_disabled["tab2"]);

    if($_GET['list2']=="tab1"){
    	$T1->show(0);
    $ext = "OnChange = 'Form1.submit();'";
    $f = new Form($SC, "GET", "NAME=Form1");
    $f->PgConn = $con;
    $f->hidden("p", $PID);
    $f->hidden("poli",$_GET["mPOLI"]);
    $f->hidden("list2",tab1);
   
   		echo "<div align='right' valign='middle'>";	
		$f = new Form($SC, "GET","NAME=Form4");
	    $f->hidden("p", $PID);
	    $f->hidden("list2","tab1");
	    if (!$GLOBALS['print']){
	    	$f->search("search","Pencarian",20,20,$_GET["search"],"icon/ico_find.gif","Cari","OnChange='Form4.submit();'");
		}else { 
		   	$f->search("search","Pencarian",20,20,$_GET["search"],"icon/ico_find.gif","Cari","disabled");
		}
	    $f->execute();
    	if ($msg) errmsg("Error:", $msg);
    	echo "</div>";
		//---------------------
		echo "<br>";
		
	$SQLSTR = 	"select distinct a.mr_no,a.id,upper(a.nama)as nama,a.alm_tetap,a.kesatuan,a.tdesc,c.tdesc,case when a.tdesc like '%DINAS%' and a.statusbayar ='BELUM LUNAS' then 'BAYAR' 
				when a.tdesc ='KONTRAKTOR' and a.statusbayar ='BELUM LUNAS' then 'RESTITUSI'
				when a.statusbayar ='LUNAS' then 'BAYAR' 
				when a.statusbayar='BELUM LUNAS' then 'BELUM BAYAR' end
				from rsv_pasien4 a 
				left join c_visit b on b.no_reg = a.id
				left join rs00001 c on c.tc_poli = b.id_poli and c.tt='LYN'
				WHERE b.id_konsul='".$_GET["mPOLI"]."'";
		// 24-12-2006 --> tambahan 'where is_bayar = 'N'
		//status_akhir,rawatan di query sementara di tutup
        
		$tglhariini = date("Y-m-d", time());
    if (strlen($_GET["mPOLI"]) > 0 ) {
		$SQLWHERE =
			"AND".
			"	(UPPER(a.NAMA) LIKE '%".strtoupper($_GET["search"])."%') ";
	}
	if ($_GET["search"]) {
		$SQLWHERE =
			"and (upper(a.nama) LIKE '%".strtoupper($_GET["search"])."%' or a.id like '%".$_GET['search']."%' or a.mr_no like '%".$_GET["search"]."%' ".
					" or upper(a.pangkat_gol) like '%".strtoupper($_GET["search"])."%' or a.nrp_nip like '%".$_GET['search']."%' ".
					" or upper(a.kesatuan) like '%".strtoupper($_GET["search"])."%' ) ";
	}
	if (!isset($_GET[sort])) {

           $_GET[sort] = "a.id";
           $_GET[order] = "asc";
	}
	$rstr=pg_query($con, "$SQLSTR $SQLWHERE ");
   // $n = pg_num_rows($rstr);		    	
	$dstr = pg_fetch_array($rstr); 
	   	$t = new PgTable($con, "100%");
	    $t->SQL = "$SQLSTR $SQLWHERE ";
	    $t->setlocale("id_ID");
	    $t->ShowRowNumber = true;
	    $t->ColAlign = array("CENTER","CENTER","LEFT","LEFT","LEFT","CENTER","LEFT","LEFT","LEFT","LEFT","LEFT");	
	    $t->RowsPerPage = $ROWS_PER_PAGE;
	    $t->ColFormatHtml[2] = "<A CLASS=SUB_MENU1 HREF='$SC?p=$PID&rg=<#1#>&mr=<#0#>&poli={$_GET["mPOLI"]}&list=layanan'><#2#>";
	    //(awal)$t->ColFormatHtml[8] = "<A HREF='$SC?p=$PID&rg=<#1#>&mr=<#0#>&poli={$_GET["mPOLI"]}&act=periksa'><INPUT NAME='submitButton' TYPE=SUBMIT VALUE='Periksa' >";
	   	//$t->ColHeader = array("NO.MR", "NO<br>REGISTRASI","TANGGAL  REG","WAKTU REG","NAMA PASIEN","PANGKAT","NRP/NIP","KESATUAN","LOKET","TIPE PASIEN","STATUS");
	   	$t->ColHeader = array("NO.MR", "NO<br>REGISTRASI","NAMA PASIEN","ALAMAT","PEKERJAAN","TIPE PASIEN","UNIT ASAL","STATUS");
	    $t->ColColor[9] = "color";
	    //$t->ColRowSpan[2] = 2;
	    $t->execute();
	    echo"<br><div class=NOTE>Catatan : Daftar pasien di urut berdasarkan no antrian</div><br>";	
    }else{
    	$T1->show(1);	
    $ext = "OnChange = 'Form1.submit();'";
    $f = new Form($SC, "GET", "NAME=Form1");
    $f->PgConn = $con;
    $f->hidden("p", $PID);
    $f->hidden("poli",$_GET["mPOLI"]);
   
   		echo "<div align='right' valign='middle'>";	
		$f = new Form($SC, "GET","NAME=Form2");
	    $f->hidden("p", $PID);
	    $f->hidden("list2","tab2");
	    if (!$GLOBALS['print']){
	    	$f->search("search","Pencarian",20,20,$_GET["search"],"icon/ico_find.gif","Cari","OnChange='Form2.submit();'");
		}else { 
		   	$f->search("search","Pencarian",20,20,$_GET["search"],"icon/ico_find.gif","Cari","disabled");
		}
	    $f->execute();
    	if ($msg) errmsg("Error:", $msg);
    	echo "</div>";
		//---------------------
		echo "<br>";
		
	$SQLSTR = 	"select distinct a.mr_no,a.id,upper(a.nama)as nama,a.alm_tetap,a.kesatuan,a.tdesc,case when a.tdesc like '%DINAS%' and a.statusbayar ='BELUM LUNAS' then 'BAYAR' 
				when a.tdesc ='KONTRAKTOR' and a.statusbayar ='BELUM LUNAS' then 'RESTITUSI'
				when a.statusbayar ='LUNAS' then 'BAYAR' 
				when a.statusbayar='BELUM LUNAS' then 'BELUM BAYAR' end
				from rsv_pasien4 a 
				left join c_visit b on b.no_reg = a.id
				WHERE a.poli='".$_GET["mPOLI"]."'";
		// 24-12-2006 --> tambahan 'where is_bayar = 'N'
		//status_akhir,rawatan di query sementara di tutup
        
		$tglhariini = date("Y-m-d", time());
    if (strlen($_GET["mPOLI"]) > 0 ) {
		$SQLWHERE =
			"AND a.TANGGAL_REG = '$tglhariini' AND".
			"	(UPPER(a.NAMA) LIKE '%".strtoupper($_GET["search"])."%') ";
	}
	if ($_GET["search"]) {
		$SQLWHERE =
			"and (upper(a.nama) LIKE '%".strtoupper($_GET["search"])."%' or a.id like '%".$_GET['search']."%' or a.mr_no like '%".$_GET["search"]."%' ".
					" or upper(a.pangkat_gol) like '%".strtoupper($_GET["search"])."%' or a.nrp_nip like '%".$_GET['search']."%' ".
					" or upper(a.kesatuan) like '%".strtoupper($_GET["search"])."%' ) ";
	}
	if (!isset($_GET[sort])) {

           $_GET[sort] = "a.id";
           $_GET[order] = "asc";
	}
	$rstr=pg_query($con, "$SQLSTR $SQLWHERE ");
   // $n = pg_num_rows($rstr);		    	
	$dstr = pg_fetch_array($rstr); 
	   	$t = new PgTable($con, "100%");
	    $t->SQL = "$SQLSTR $SQLWHERE ";
	    $t->setlocale("id_ID");
	    $t->ShowRowNumber = true;
	    $t->ColAlign = array("CENTER","CENTER","LEFT","LEFT","LEFT","CENTER","LEFT","LEFT","LEFT","LEFT","LEFT");	
	    $t->RowsPerPage = $ROWS_PER_PAGE;
	    $t->ColFormatHtml[2] = "<A CLASS=SUB_MENU1 HREF='$SC?p=$PID&rg=<#1#>&mr=<#0#>&poli={$_GET["mPOLI"]}&list=layanan'><#2#>";
	    //(awal)$t->ColFormatHtml[8] = "<A HREF='$SC?p=$PID&rg=<#1#>&mr=<#0#>&poli={$_GET["mPOLI"]}&act=periksa'><INPUT NAME='submitButton' TYPE=SUBMIT VALUE='Periksa' >";
	   	//$t->ColHeader = array("NO.MR", "NO<br>REGISTRASI","TANGGAL  REG","WAKTU REG","NAMA PASIEN","PANGKAT","NRP/NIP","KESATUAN","LOKET","TIPE PASIEN","STATUS");
	   	$t->ColHeader = array("NO.MR", "NO<br>REGISTRASI","NAMA PASIEN","ALAMAT","PEKERJAAN","TIPE PASIEN","STATUS");
	    $t->ColColor[8] = "color";
	    //$t->ColRowSpan[2] = 2;
	    $t->execute();
	    echo"<br><div class=NOTE>Catatan : Daftar pasien di urut berdasarkan no antrian</div><br>";
    }
	
}
  
?>
