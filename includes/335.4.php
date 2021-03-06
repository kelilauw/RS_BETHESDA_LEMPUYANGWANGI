<?php
$PID = "335";
$SC = $_SERVER["SCRIPT_NAME"];

require_once("lib/dbconn.php");
require_once("lib/form.php");
require_once("lib/class.PgTable.php");
require_once("lib/functions.php");

$jns_kasir = array(
    "rj" => "RAWAT JALAN",
    "ri" => "RAWAT INAP",
    "igd" => "IGD",
);


$new_id=new_id();
$cek_deposit = getFromTable("select sum(jumlah) from rs00044 where no_reg = '" . $_GET[rg] . "'");
$cek_status_bayar = getFromTable("select statusbayar from rsv0012 where id = '" . $_GET[rg] . "'");

$what = $jns_kasir[$_GET["kas"]];
$sqlayanan = "NOT LIKE '%IGD%'";
if ($_GET["kas"] == "igd") {
    $sqlayanan = "LIKE '%IGD%'";
}

echo "<hr noshade size=1>";
$reg = $_GET["rg"];
$r = pg_query($con,
        "SELECT a.id, to_char(a.tanggal_reg,'DD MONTH YYYY') AS tanggal_reg, a.waktu_reg, " .
        "    a.mr_no, e.nama, to_char(e.tgl_lahir, 'DD MONTH YYYY') AS tgl_lahir, " .
        "    e.tmp_lahir, e.jenis_kelamin, f.tdesc AS agama, " .
        "    e.alm_tetap, e.kota_tetap, e.pos_tetap, e.tlp_tetap, " .
        "    a.id_penanggung, b.tdesc AS penanggung, a.id_penjamin, " .
        "    c.tdesc AS penjamin, a.no_jaminan, a.rujukan, a.rujukan_rs_id, " .
        "    d.tdesc AS rujukan_rs, a.rujukan_dokter, a.rawat_inap, " .
        "    a.status, a.tipe, g.tdesc AS tipe_desc, a.diagnosa_sementara, a.poli,a.status_akhir_pasien," .
        "    to_char(a.tanggal_reg, 'DD MONTH YYYY') AS tanggal_reg_str, " .
        "        CASE " .
        "            WHEN a.rawat_inap = 'I' THEN 'Rawat Inap'  " .
        "            WHEN a.rawat_inap = 'Y' THEN 'Rawat Jalan' " .
        "            ELSE 'IGD' " .
        "        END AS rawatan, " .
        "        age(a.tanggal_reg , e.tgl_lahir ) AS umur, " .
        "	case when a.rujukan = 'Y' then 'Rujukan' else 'Non-Rujukan' end as datang " .
        "FROM rs00006 a " .
        "   LEFT JOIN rs00001 b ON a.id_penanggung = b.tc AND b.tt = 'PEN'" .
        "   LEFT JOIN rs00001 c ON a.id_penjamin = c.tc AND c.tt = 'PJN' " .
        "   LEFT JOIN rs00002 e ON a.mr_no = e.mr_no " .
        "   LEFT JOIN rs00001 f ON e.agama_id = f.tc AND f.tt = 'AGM' " .
        "   LEFT JOIN rs00001 g ON a.tipe = g.tc AND g.tt = 'JEP' " .
        "   LEFT JOIN rs00001 d ON a.id_penjamin = d.tc AND d.tt = 'RUJ' " .
        "   LEFT JOIN rs00001 h ON a.jenis_kedatangan_id = h.tc AND h.tt = 'JDP' " .
        "WHERE a.id = '$reg'");
$n = pg_num_rows($r);
if ($n > 0)
    $d = pg_fetch_object($r);
pg_free_result($r);

$sisa = getFromTable(
        "select sum((tagihan)-pembayaran) as sisa " .
        "from rs00008  " .
        "where no_reg = '$reg' and " .
        "trans_type IN ('LTM','BYR','OB1','POS')");

$xtagih = getFromTable(
        "select sum(tagihan) as xtagih " .
        "from rs00008  " .
        "where no_reg = '$reg' and " .
        "(trans_type = 'OB2' and referensi = '')");
$sisa = $xtagih + $sisa;

//-- summary tagihan
if($_GET['kas']=='ri')
    include ("tagihan_ri");
else
	include ("tagihan");
//--

//start
//by wildan sawaludin date:20130309
//--start cek pembayaran
$flglunas11 = "N";
$amount11 = getFromTable("select sum(tagihan) from rs00008 where no_reg = $reg and is_bayar='N'");
$karcis11 = getFromTable("SELECT sum(jumlah) as jumlah FROM rs00005 WHERE reg='" . $_GET[rg] . "' AND is_karcis='Y'  ");

if ($_GET[kas] == "ri") {
    $cekBayar11 = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI')") - $karcis11;
} else {
    $cekBayar11 = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI') and is_obat='N'");
    $cekPotongan = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='POT') and is_obat='N'");
}
//--end cek pembayaran

//--start pembulatan
//--pembulatan() ada di lib/function.php
//--$total-$totalPenjamin-$obatReturn ada di include ("tagihan");
$totalpembulatanCekBayar11 = pembulatan($cekBayar11);
$PembulatanCekBayar11 = $totalpembulatanCekBayar11-$cekBayar11;
//--
$tagihan_pembulatan = ($total-$totalPenjamin-$obatReturn)-$cekBayar11-$cekPotongan;
$total_pembulatan = pembulatan($tagihan_pembulatan);
$pembulatan = $total_pembulatan - $tagihan_pembulatan;
//--end pembulatan
//--end

if ($_GET["e"] == "edit") {

    if (($_GET['mCAB'] == "")) {

        echo "STATUS PEMBAYARAN HARUS DI ISI ";
        echo "<script language=javascript>\n";
        echo "<!--\n";
        echo "window.location = \"index2.php?p=$PID&rg=" . $_GET['rg'] . "&sub=" . $_GET['sub'] . "\";\n";
        echo "-->\n";
        echo "</script>";
        exit();
    }


    /* Hitung Transaksi ASKES */
    $cekAskes = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='ASK'");

    if (!isset($cekAskes) && isset($_GET[askes])) {
        pg_query("insert into rs00005 " .
                "values(nextval('kasir_seq'), '" . $_GET[rg] . "', CURRENT_DATE, 'ASK', " .
                "'N', 'N', 0, $_GET[askes], 'Y','" . $_SESSION["uid"] . "')");
    } else {
        $askes = $cekAskes + $_GET[askes];
        pg_query("update rs00005 set jumlah = $askes where reg = '" . $_GET[rg] . "'  and kasir = 'ASK'");
    }


    /* proses data POTONGAN / KERINGANAN */
    if ($_GET[kas] == "rj" or $_GET[kas] == "igd") {
        $cekPotong = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='POT' and is_obat='N'");
    } else {
        $cekPotong = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='POT'");
    }
    if (!isset($cekPotong)) {
        pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan,jumlah, is_bayar, keterangan )" .

                " values(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE,'POT', " .

                "'N','N',0,$_GET[keringanan],'Y', '".$_GET['keterangan_potongan']."')");
    } else {
        $potong = $cekPotong + $_GET[keringanan];
        pg_query("update rs00005 set jumlah = $potong,keterangan = '".$_GET['keterangan_potongan']."' where reg = '" . $_GET[rg] . "' and kasir = 'POT'");
        #################################################################################################efrizal
        if ($_GET["mCAB"] == "012") {
            $riz = getfromtable("select no_asuransi from rs00006 where id = '" . $_GET[rg] . "' ");
            $pakai1 = getfromtable("select plafon_pakai from ciu where no_asuransi = '" . $riz . "' ");
            $sisa1 = getfromtable("select plafon_sisa from ciu where no_asuransi = '" . $riz . "' ");
            $pakai2 = $pakai1 + $potong;
            $sisa2 = $sisa1 - $potong;
            pg_query("update ciu set plafon_sisa = $sisa2, plafon_pakai = $pakai2 where no_asuransi = '" . $riz . "' ");
            ####################################################################################################efrizal
        }
    }

    /* Total POTONGAAN  */
    $total_potongan = $potong + $askes;


    if ($_GET[bayar] > 0) {

        // buat kwitansi
        $tgl = date("d",
                time());
        $bln = date("m",
                time());
        $thn = date("y",
                time());
        $thn1 = date("Y",
                time());

        $cekpendapatan = getFromTable("select sum(jumlah) from rs00005 where tgl_entry='$thn1-$bln-01' and kasir in ('BYR','BYD','BYI') ");
        $cekpendapatan2 = getFromTable("select sum(jumlah) from rs00005 where tgl_entry='$thn1-$bln-02' and kasir='$ksr' ");

        if ($tgl == 1 and $cekpendapatan == '') {

            $cek = getFromTable("select count (status) from reset_kwitansi where bulan='$bln' and tahun='$thn1' ");

            if ($cek > 0) {
                
            } else {
                $sql = ("insert into reset_kwitansi values('$bln','$thn1',1,'00000','00000','00000','00000')");
                pg_query($con,
                        $sql);
            }
        }

        if ($_GET[kas] == "igd") {
            $ksr = "BYD";
            $kasir = "IGD";
            $cekno = getFromTable("select (igd::numeric + 1) from reset_kwitansi where bulan='$bln' and tahun='$thn1' ");
            $cekno1 = str_pad(((int) $cekno),
                    5,
                    "0",
                    STR_PAD_LEFT);
        } elseif ($_GET[kas] == "rj") {
            $ksr = "BYR";
            $kasir = "RJ";
            $cekno = getFromTable("select (rj::numeric + 1) from reset_kwitansi where bulan='$bln' and tahun='$thn1' ");
            $cekno1 = str_pad(((int) $cekno),
                    5,
                    "0",
                    STR_PAD_LEFT);
        } elseif ($_GET[kas] == "ri") {
            $ksr = "BYI";
            $kasir = "RI";
            $cekno = getFromTable("select (ri::numeric + 1) from reset_kwitansi where bulan='$bln' and tahun='$thn1' ");
            $cekno1 = str_pad(((int) $cekno),
                    5,
                    "0",
                    STR_PAD_LEFT);
        }

        //Pemotongan registrasi
        $reg = $_GET["rg"];
        echo $reg;
        $no_kwitansi = $kasir . " - " . $reg;

//akhiran no kwitansi

        if ($_GET[kas] == "igd") {

            if ($_GET[bayar1] < $_GET[bayar]) {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
  is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian) " .
                        " values(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, " .
                        "'BYD','N','N',0,$_GET[bayar1],'Y','" . $_SESSION["uid"] . "','" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan1] . "','" . $_GET[total_pembulatan1] . "','$new_id','" . $_GET[f_cash_pembayaran] . "','" . $_GET[f_cash_pengembalian] . "')");
            } else {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
  is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian) " .
                        " values(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, " .
                        "'BYD','N','N',0,$_GET[bayar],'Y','" . $_SESSION["uid"] . "','" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan] . "','" . $_GET[total_pembulatan] . "','$new_id','" . $_GET[f_cash_pembayaran] . "','" . $_GET[f_cash_pengembalian] . "')");
            }
            $sql1 = ("update rs00006 set is_bayar = 'Y' where id = '" . $_GET[rg] . "'");
            $sql5 = ("update rs00005 set is_bayar = 'Y' where reg = '" . $_GET[rg] . "'");
            $sql6 = ("update rs00008 set is_bayar = 'Y' where no_reg = '" . $_GET[rg] . "'");
			
			//========= Trendy 08/01/2014 hystory user
			pg_query("insert into history_user " .
		            "(id_history, tanggal_entry,waktu_entry,trans_form,item_id,keterangan,user_id,nama_user) ".
		            "values".
		            "(nextval('rshistory_user_seq'),CURRENT_DATE,CURRENT_TIME,'Data Pembayaran Pasien', ".
		            "'Kasir -> Kasir IGD ','Data Pasien dengan No.Reg $_GET[rg] telah Dilakukan', ".
		            "'$_SESSION[uid]','$_SESSION[nama_usr]')");
			//=========
					
					
            //update no kwitansi
            $sql7 = ("update reset_kwitansi set igd = $cekno where bulan='$bln' and tahun='$thn1' ");
            pg_query($con,
                    $sql7);
            //===================
            $sql3 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'1101.02','Kas Kecil',$_GET[bayar],0,'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYD')";

            $sql2 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',0,$_GET[bayar],'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYD')";

            $sql4 = "insert into kas_masuk (id,tanggal,kode_trans,keterangan,jumlah,cara_bayar) values (nextval('kas_masuk_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',$_GET[bayar],'" . $_GET[mCAB] . "')";

            pg_query($con,
                    $sql4);
            pg_query($con,
                    $sql5);
            pg_query($con,
                    $sql6);
            pg_query($con,
                    $sql1);
            pg_query($con,
                    $sql3);
            pg_query($con,
                    $sql2);
        } elseif ($_GET[kas] == "rj") {
		
		if($obatReturn>0){
		$obatReturni=$obatReturn;
		}else{
		  $obatReturni=0;
		}
		
		
            if ($_GET[bayar1] < $_GET[bayar]) {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
		is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian,return_obat) values
		(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, 'BYR','N','N',0,$_GET[bayar1],'Y','" . $_SESSION["uid"] . "',
		'" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan1] . "','" . $_GET[total_pembulatan1] . "','$new_id','" . $_GET[f_cash_pembayaran] . "', '" . $_GET[f_cash_pengembalian] . "','$obatReturni')");
            } else {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
		is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian,return_obat) values
		(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, 'BYR','N','N',0,$_GET[bayar],'Y','" . $_SESSION["uid"] . "',
		'" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan] . "','" . $_GET[total_pembulatan] . "','$new_id','" . $_GET[f_cash_pembayaran] . "', '" . $_GET[f_cash_pengembalian] . "','$obatReturni')");
            }
            $sql1 = ("update rs00006 set is_bayar = 'Y' where id = '" . $_GET[rg] . "'");
            $sql5 = ("update rs00005 set is_bayar = 'Y' where reg = '" . $_GET[rg] . "'");
            $sql6 = ("update rs00008 set is_bayar = 'Y' where no_reg = '" . $_GET[rg] . "'");
			
			//========= Trendy 08/01/2014 hystory user
	    pg_query("insert into history_user " .
		            "(id_history, tanggal_entry,waktu_entry,trans_form,item_id,keterangan,user_id,nama_user) ".
		            "values".
		            "(nextval('rshistory_user_seq'),CURRENT_DATE,CURRENT_TIME,'Data Pembayaran Pasien', ".
		            "'Kasir -> Kasir Rawat Jalan ','Data Pasien dengan No.Reg $_GET[rg] telah Dilakukan', ".
		            "'$_SESSION[uid]','$_SESSION[nama_usr]')");
		
		//=========
		
		
            //update no kwitansi
            $sql7 = ("update reset_kwitansi set rj = $cekno where bulan='$bln' and tahun='$thn1' ");
            pg_query($con,
                    $sql7);
            //===================

            $sql3 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'1101.02','Kas Kecil',$_GET[bayar],0,'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYR')";

            $sql2 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',0,$_GET[bayar],'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYR')";

            $sql4 = "insert into kas_masuk (id,tanggal,kode_trans,keterangan,jumlah,cara_bayar) values (nextval('kas_masuk_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',$_GET[bayar],'" . $_GET[mCAB] . "')";

            pg_query($con,
                    $sql4);
            pg_query($con,
                    $sql5);
            pg_query($con,
                    $sql6);
            pg_query($con,
                    $sql1);
            pg_query($con,
                    $sql3);
            pg_query($con,
                    $sql2);
        } elseif ($_GET[kas] == "ri") {
		
		
		if($obatReturn>0){
		$obatReturni=$obatReturn;
		}else{
		  $obatReturni=0;
		}
            if ($_GET[bayar1] < $_GET[bayar]) {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
  is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian,return_obat) " .
                        " values(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, " .
                        "'BYI','N','N',0,$_GET[bayar1],'Y','" . $_SESSION["uid"] . "','" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan1] . "','" . $_GET[total_pembulatan1] . "','$new_id','" . $_GET[f_cash_pembayaran] . "', '" . $_GET[f_cash_pengembalian] . "','$obatReturni')");
            } else {
                pg_query("insert into rs00005 (id, reg, tgl_entry, kasir, is_obat, is_karcis, layanan, jumlah,
  is_bayar, user_id, cab, bayar, no_kartu, waktu_bayar, no_kwitansi, pembulatan, total_pembulatan,new_id, cash_pembayaran, cash_pengembalian,return_obat) " .
                        " values(nextval('kasir_seq'),'" . $_GET[rg] . "',CURRENT_DATE, " .
                        "'BYI','N','N',0,$_GET[bayar],'Y','" . $_SESSION["uid"] . "','" . $_GET[mCAB] . "','" . $_GET[f_bayar] . "','" . $_GET[f_no_kartu] . "',CURRENT_TIME,'$no_kwitansi','" . $_GET[pembulatan] . "','" . $_GET[total_pembulatan] . "','$new_id','" . $_GET[f_cash_pembayaran] . "', '" . $_GET[f_cash_pengembalian] . "','$obatReturni')");
            }
            $sql1 = ("update rs00006 set is_bayar = 'Y' where id = '" . $_GET[rg] . "'");
            $sql5 = ("update rs00005 set is_bayar = 'Y' where reg = '" . $_GET[rg] . "'");
            $sql6 = ("update rs00008 set is_bayar = 'Y' where no_reg = '" . $_GET[rg] . "'");
			
			
			//========= Trendy 08/01/2014 hystory user
			pg_query("insert into history_user " .
		            "(id_history, tanggal_entry,waktu_entry,trans_form,item_id,keterangan,user_id,nama_user) ".
		            "values".
		            "(nextval('rshistory_user_seq'),CURRENT_DATE,CURRENT_TIME,'Data Pembayaran Pasien', ".
		            "'Kasir -> Kasir Rawat Inap ','Data Pasien dengan No.Reg $_GET[rg] telah Dilakukan', ".
		            "'$_SESSION[uid]','$_SESSION[nama_usr]')");
		
			//=========
			
			
            //update no kwitansi
            $sql7 = ("update reset_kwitansi set ri = $cekno where bulan='$bln' and tahun='$thn1' ");
            pg_query($con,
                    $sql7);
            //===================

            $sql3 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'1101.02','Kas Kecil',$_GET[bayar],0,'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYI')";

            $sql2 = "insert into jurnal_umum (id,tanggal_akun,no_akun,keterangan,debet,kredit,user_id,nm_kasir,kasir_type) values (nextval('jurnal_umum_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',0,$_GET[bayar],'" . $_SESSION["uid"] . "','" . $_SESSION["nama_usr"] . "','kasir_BYI')";

            $sql4 = "insert into kas_masuk (id,tanggal,kode_trans,keterangan,jumlah,cara_bayar) values (nextval('kas_masuk_seq'),CURRENT_DATE,'2100.00','Pembayaran Kasir',$_GET[bayar],'" . $_GET[mCAB] . "')";

            pg_query($con,
                    $sql4);
            pg_query($con,
                    $sql5);
            pg_query($con,
                    $sql6);
            pg_query($con,
                    $sql1);
            pg_query($con,
                    $sql3);
            pg_query($con,
                    $sql2);
        }
    }

    // ambil data pasien di master data registrasi rs00006
    $r1 = pg_query($con,
            "select tipe, rujukan, id as no_reg, tanggal_reg, rawat_inap " .
            "from rs00006 " .
            "where id ='$reg' ");
    $n1 = pg_num_rows($r1);
    if ($n1 > 0)
        $d1 = pg_fetch_object($r1);
    pg_free_result($r1);

    // menghitung kunjungan pasien, sehingga dpt.digolongkan sbg.pasien L-ama/B-aru
    $reg_count = getFromTable("select count(mr_no) from rs00006 " .
            "where mr_no = (select mr_no from rs00006 " .
            "               where id = $reg)");
    $baru = "Y";
    $loket = "RJN";
    if ($reg_count > 1)
        $baru = "T";

    /* ambil data pasien rawat inap: bangsal_id, tgl_masuk dan jumlah hari dirawat */

    if ($d1->rawat_inap == "I") {
        $r2 = pg_query($con,
                "select bangsal_id, extract(day from current_timestamp - ts_check_in) as hari " .
                "from rs00010 " .
                "where no_reg = '$reg' ");
        $n2 = pg_num_rows($r2);
        if ($n2 > 0)
            $d2 = pg_fetch_object($r2);
        pg_free_result($r2);
        $loket = "RIN";
    } elseif ($d1->rawat_inap == "N") {
        $loket = "IGD";
    }

    /* pengecekan apakah pembayaran sama dengan tagihan yg. belum terbayar */
    $flglunas = "N";
    $amount = getFromTable("select sum(tagihan) from rs00008 where no_reg = $reg and is_bayar='N'");
    $karcis = getFromTable("SELECT sum(jumlah) as jumlah FROM rs00005 WHERE reg='" . $_GET[rg] . "' AND is_karcis='Y'  ");

    if ($_GET[kas] == "ri") {
        $cekBayar = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI')") - $karcis;
    } else {
        $cekBayar = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI') and is_obat='N'");
    }



    $total_pembayaran = $cekBayar + $total_potongan;

    if ($amount == $total_pembayaran) {

        $flglunas = "Y";

        // data terakhir (record terakhir) seorang pasian tercatat sbg. penghuni bangsal
        $id_max = getFromTable("select max(id) from rs00010 " .
                "where no_reg = $reg");

        pg_query("update rs00006 set is_bayar='$flglunas', " .
                "status_bayar=" . $_GET["mCAB"] . ", " .
                "status_akhir_pasien=" . $_GET["mKELUAR"] . "" .
                ", user_id = '" . $_SESSION[uid] . "' " .
                "where id = $reg ");

        pg_query("update rs00005 set is_bayar ='$flglunas' " .
                "where reg = $reg ");

        pg_query("update rs00008 set is_bayar ='$flglunas',user_id = '" . $_SESSION[uid] . "' " .
                "where no_reg = $reg AND is_bayar ='N'");

        pg_query("update rs00006 set tgl_keluar=  current_timestamp " .
                "where id = $reg ");
    }


    echo "<center><br><br><br>";
    echo "<b>Transaksi pembayaran sedang diproses...</b>";
    echo "</center>";

    echo "<script language=javascript>\n";
    echo "<!--\n";
    echo "window.location = \"$SC?p=$PID&rg=" . $_GET[rg] . "&sub=" . $_GET[sub] . "&kas=" . $_GET[kas] . "&cetak=N\";\n";
    echo "-->\n";
    echo "</script>\n";
} else {
    title("Pembayaran");
    echo "<br>";

    echo "<table border=0 width='100%' cellspacing=0 cellpadding=0>";
    echo "<tr>";
    echo "<td><img src=\"images/spacer.gif\" width=50 height=1></td>";
    echo "<td><img src=\"images/spacer.gif\" width=400 height=1></td>";
    echo "<td><img src=\"images/spacer.gif\" width=100 height=1></td>";
    echo "<td><img src=\"images/spacer.gif\" width=100 height=1></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<th class=TBL_HEAD2 width=50>NO</th>";
    echo "<th class=TBL_HEAD2 width=300>URAIAN</th>";
    echo "<th class=TBL_HEAD2 width=100>TAGIHAN</th>";
    echo "<th class=TBL_HEAD2 width=100>PENJAMIN</th>";
    echo "<th class=TBL_HEAD2 width=100>SELISIH</th>";
    echo "</tr>";


    if ($_GET["kas"] == "igd") {
        $loket = "IGD";
        $kasir = "IGD";
        $lyn = "layanan = '100'";
    } elseif ($_GET["kas"] == "rj") {
        $loket = "RJL";
        $kasir = "RJL";
        $lyn = "layanan not in ('100','99996','99997','12651','13111')";
    } else {
        $loket = "RIN";
        $kasir = "RIN";
        $lyn = "(layanan not in ('99996','99997','12651','13111'))";
        $d->poli = 0;
    }

    $poli = getFromTable("SELECT tdesc FROM rs00001 WHERE tt = 'LYN' and tc=$d->poli");

    //ngilangin karcis di ri najla 07012011
    $karcis = getFromTable("SELECT sum(jumlah) as jumlah FROM rs00005 WHERE reg='" . $_GET[rg] . "' AND is_karcis='Y'  ");
    if ($_GET[kas] == "ri") {
        $cekBayar = getFromTable("select SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI')");
        $cekBayar = $cekBayar - $karcis;
    } else {
        $cekBayar = getFromTable("SELECT SUM(jumlah) from rs00005 where reg='" . $_GET[rg] . "' and (kasir='BYR' or kasir = 'BYD' or kasir ='BYI') and is_obat='N'");
    }

    $loket = getFromTable("select " .
            "case when rawat_inap = 'I' then 'RIN' " .
            "     when rawat_inap = 'Y' then 'RJL' " .
            "     else 'IGD' " .
            "end as rawatan " .
            "from rs00006 where id = '" . $_GET[rg] . "'");

    $kodepoli = getFromTable("select poli from rs00006 where id = '" . $_GET[rg] . "'");

    $namadokter = getFromTable("SELECT B.NAMA FROM RS00017 B 
    				LEFT JOIN  C_VISIT A ON A.ID_DOKTER = B.ID
    				WHERE A.ID_POLI=$kodepoli AND A.NO_REG='" . $_GET[rg] . "'");
    if ($namadokter != "") {
        $namadokter = "(" . $namadokter . ")";
    };

    $cekAskes1 = getFromTable("select  sum(a.tagihan) from   rs00008  a,  rs00034 b 	         " .
            "where a.no_reg = '" . $_GET[rg] . "'  AND b.tipe_pasien_id = '007'  " .
            "AND  b.id = to_number(a.item_id,'999999999999') AND a.trans_form <> '-' and a.item_id <>'-'  ");

    $karcis = getFromTable("SELECT sum(jumlah) as jumlah FROM rs00005 WHERE reg='" . $_GET[rg] . "' AND is_karcis='Y'  ");

    $tipepasien = getFromTable("select  b.tipe from   rs00008  a,  rs00006 b 	         " .
            "where a.no_reg = '" . $_GET[rg] . "'  AND b.id = a.no_reg ");
    if ($loket == "IGD") {
        $lyn123 = 100;
    } elseif ($loket == "RJL") {
        $lyn123 = $kodepoli;
    }


    if ($tipepasien == '007') {
        $paket1 = 'PAKET I ASKES';
        $cekAskes = $cekAskes;
    } else {
        $paket1 = 'KARCIS + PEMERIKSAAN DOKTER';
    };


    $cekAskes = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='ASK'");

    if ($_GET[kas] == "ri") {
        $cekPotong = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='POT'");
    } else {
        $cekPotong = getFromTable("select jumlah from rs00005 where reg='" . $_GET[rg] . "' and kasir='POT' and is_obat='N'");
    }
    $karcis = $hargatiket;

    $bangsal_sudah_posting = 0.00;
    $rec = pg_query("select * from rs00008 " .
            "where trans_type = 'POS' and to_number(no_reg,'999999999999') = $reg order by id");
    $rec_num = pg_num_rows($rec);

    if ($rec_num > 0) {

        $r1 = pg_query($con,
                "select a.id, a.ts_check_in::date, e.bangsal, d.bangsal as ruangan, b.bangsal as bed, " .
                "    c.tdesc as klasifikasi_tarif, " .
                "    extract(day from a.ts_calc_stop - a.ts_calc_start) as qty, 
			(select (substring((z.ts_calc_stop::timestamp)::text,12,8))::time - (substring((z.ts_check_in::timestamp)::text,12,8))::time from rs00010 z where z.id=a.id) as jumlah_jam, " .
                "    d.harga as harga_satuan, " .
                "    extract(day from a.ts_calc_stop - a.ts_calc_start) * d.harga as harga, " .
                "    a.ts_calc_stop " .
                "from rs00010 as a " .
                "    join rs00012 as b on a.bangsal_id = b.id " .
                "    join rs00012 as d on substr(b.hierarchy,1,6) || '000000000' = d.hierarchy " .
                "    join rs00012 as e on substr(b.hierarchy,1,3) || '000000000000' = e.hierarchy " .
                "    join rs00001 as c on d.klasifikasi_tarif_id = c.tc and c.tt = 'KTR' " .
                "where to_number(a.no_reg,'9999999999') = $reg and a.ts_calc_stop is not null");


        while ($ddd = pg_fetch_object($rec)) {
            while ($d1 = pg_fetch_object($r1)) {

                if ($d1->jumlah_jam >= "02:00:00") {
                    $qty = 1;
                } else {
                    $qty = $d1->qty;
                }
                $harga = $qty * $d1->harga_satuan;

                $bangsal_sudah_posting = $bangsal_sudah_posting + $harga;
            }
        }
    }

// >>>>>>>>>>>>>>>>  <<<<<<<<<<<<<<<<<<<<<<<
    if (getFromTable("select rawat_inap from rs00006 " .
                    "where to_number(id,'999999999999') = $reg") == "I") {
// TAGIHAN SEMENTARA AKOMODASI

        $bangsal_belum_posting = 0.00;

        $r1 = pg_query($con,
                "select a.id, a.ts_check_in::date, e.bangsal, d.bangsal as ruangan, b.bangsal as bed, " .
                "    c.tdesc as klasifikasi_tarif, " .
                "    extract(day from current_timestamp - a.ts_calc_start) as qty, " .
                "    d.harga as harga_satuan, " .
                "    extract(day from current_timestamp - a.ts_calc_start) * d.harga as harga " .
                "from rs00010 as a " .
                "    join rs00012 as b on a.bangsal_id = b.id " .
                "    join rs00012 as d on substr(b.hierarchy,1,6) || '000000000' = d.hierarchy " .
                "    join rs00012 as e on substr(b.hierarchy,1,3) || '000000000000' = e.hierarchy " .
                "    join rs00001 as c on d.klasifikasi_tarif_id = c.tc and c.tt = 'KTR' " .
                "where to_number(a.no_reg,'9999999999') = $reg and ts_calc_stop is null");
        if ($d1 = pg_fetch_object($r1)) {


            $bangsal_belum_posting = $bangsal_belum_posting + $d1->harga;
            pg_free_result($r1);
        }
    }
    if ($bangsal_sudah_posting > 0) {

        $bangsal_belum_posting = 0;
    }

    $r1 = pg_query($con,
            "select sum(tagihan)as tagihan, sum(pembayaran) as pembayaran " .
            "from rs00008 " .
            "where trans_type in ('LTM', 'BYR') " .
            "and to_number(no_reg, '999999999999') = $reg");
    $d1 = pg_fetch_object($r1);
    pg_free_result($r1);


    $jml_total_Tagihan = $bangsal_sudah_posting + $bangsal_belum_posting;

/**
    $akomodasi = getFromTable("select sum(jumlah) as jumlah " .
            "from rs00005 where reg='" . $_GET[rg] . "' AND is_karcis='N' AND is_obat='N' AND kasir='$kasir' " .
            "AND layanan = '99996' ");
*/

    //include ("tagihan");

// obat nggambus
    $reg = $_GET["rg"];

    $rec = getFromTable("select count(id) from rs00008 " .
            "where trans_type = 'OB1' and to_number(no_reg,'999999999999') = $reg and referensi != 'F'");

    if ($rec > 0) {

        $SQL =
                "select a.id, to_char(tanggal_trans,'DD-MM-YYYY') as tanggal_trans, " .
                "obat, qty, c.tdesc as satuan, sum(tagihan) as tagihan, pembayaran, trans_group, d.tdesc as kategori " .
                "from rs00008 a, rs00015 b, rs00001 c, rs00001 d " .
                "where to_number(a.item_id,'999999999999') = b.id  " .
                "and b.satuan_id = c.tc and a.trans_type = 'OB1' " .
                "and c.tt = 'SAT' " .
                "and b.kategori_id = d.tc and d.tt = 'GOB' " .
                "and to_number(a.no_reg,'999999999999')= $reg  and referensi != 'F'" .
                "group by  d.tdesc, a.tanggal_trans, a.id, b.obat, a.qty, a.pembayaran, a.trans_group,   c.tdesc ";
        $r1 = pg_query($con,
                "$SQL");

        $kateg = "000";
        $ob_urut = 0;

        while ($d1 = pg_fetch_object($r1)) {
            if ($d1->kategori != $kateg) {
                $ob_urut++;
                $obatx[$ob_urut] = 0;
                $kateg = $d1->kategori;
                $cek_kateg = substr($kateg,
                        0,
                        1);
            }

            if ($cek_kateg == "A") {   // apbd
                $obatx[1] = $obatx[1] + $d1->tagihan;
            } elseif ($cek_kateg == "D") {    // dpho
                $obatx[2] = $obatx[2] + $d1->tagihan;
            } elseif ($cek_kateg == "K") {    // koperasi
                $obatx[3] = $obatx[3] + $d1->tagihan;
            }
            $tot_obat = 0;
        }
        pg_free_result($r1);
    }

    $cek_loket = getFromTable("select kasir from rs00005 where reg = '" . $_GET[rg] . "' and is_karcis = 'Y'");

// untuk list di kasir
if($_GET['kas']=='ri')
    include ("kasir_ri");
else
	include ("kasir");
//====================

    echo "\n<script language='JavaScript'>\n";
    echo "function hitung1() {\n";
    echo "       var jml,potongan,nilai,sisa,alltotal,totalpembulatan ;   \n";
    echo "       if (Math.round(document.Form1.keringanan.value) > Math.round(document.Form1.tmp_tagihan.value))   
			 {
			 alert ('Maaf, Potongan tidak boleh lebih besar dari total pembayaran!');
			 document.Form1.keringanan.value = 0;
			 }else{\n";
    echo "       potongan = Math.round(document.Form1.keringanan.value)  ;  \n";
    echo "       jml = Math.round(document.Form1.tmp_tagihan.value) - potongan ;    ; \n";
    echo "       document.Form1.bayar.value =  Math.round(jml);     \n";
    echo "       document.Form1.bayar1.value =  Math.round(jml);     \n";
	//--start pembulatan
	echo "       nilai = Math.round(document.Form1.bayar1.value);
				 sisa = Math.round(nilai)%1000;
				 if(Math.round(sisa)<=200){
			     	alltotal = Math.round(nilai) - Math.round(sisa);
			     } else if(Math.round(sisa)>200 && Math.round(sisa)<=700){
			    	alltotal = 500*(Math.round(nilai)/500)+(500-Math.round(sisa));
			     } else if(Math.round(sisa)>700){
			    	alltotal = 1000*(Math.round(nilai)/1000)+(1000-Math.round(sisa));
			     } 
			     document.Form1.total_pembulatan1.value =  Math.round(alltotal); \n";
	
	echo "      totalpembulatan = Math.round(alltotal) - nilai;
				document.Form1.pembulatan1.value =  Math.round(totalpembulatan);     \n";
	//--end pembulatan
    echo "       document.Form1.sisa1.value = Math.round(document.Form1.tmp_tagihan.value) - (Math.round(document.Form1.bayar.value) + potongan) ;     \n";
    echo "       }\n";
    echo "}\n";

    echo "function hitung2() {\n";
    echo "       var jml,potongan,nilai,sisa,alltotal,totalpembulatan ;   \n";
	 echo "  	if (Math.round(document.Form1.bayar1.value) > Math.round(document.Form1.tmp_tagihan.value))   
			 {
			 	alert ('Maaf, Pembayaran tidak boleh lebih besar dari total pembayaran!');
			 	document.Form1.bayar1.value = Math.round(document.Form1.tmp_tagihan.value);
			 }else{\n";
    echo "       potongan = Math.round(document.Form1.keringanan.value) + Math.round(document.Form1.askes.value)  ;  \n";
    echo "       jml = Math.round(document.Form1.tmp_tagihan.value) - potongan ;    ; \n";
    //--start pembulatan
	echo "       nilai = Math.round(document.Form1.bayar1.value);
				 sisa = Math.round(nilai)%1000;
				 if(Math.round(sisa)<=200){
			     	alltotal = Math.round(nilai) - Math.round(sisa);
			     } else if(Math.round(sisa)>200 && Math.round(sisa)<=700){
			    	alltotal = 500*(Math.round(nilai)/500)+(500-Math.round(sisa));
			     } else if(Math.round(sisa)>700){
			    	alltotal = 1000*(Math.round(nilai)/1000)+(1000-Math.round(sisa));
			     } 
			     document.Form1.total_pembulatan1.value =  Math.round(alltotal); \n";
	
	echo "      totalpembulatan = Math.round(alltotal) - nilai;
				document.Form1.pembulatan1.value =  Math.round(totalpembulatan);     \n";
	//--end pembulatan
    echo "       document.Form1.sisa.value = Math.round(document.Form1.tmp_tagihan.value) - (Math.round(document.Form1.bayar1.value) + potongan) ;     \n";
    echo "        \n";
    echo "   }\n";
    echo "}\n";
	
	
	//--start cash pembayaran
	echo "function hitung_cash() {\n";
    echo "       var c_bayar ;   \n";
	 echo "  	if (Math.round(document.Form1.total_pembulatan1.value) > Math.round(document.Form1.f_cash_pembayaran.value))   
			 {
			 	alert ('Maaf, Uang Cash anda kurang dari total pembayaran!');
			 	document.Form1.f_cash_pembayaran.value = '';
			 }else{\n";
    echo "       document.Form1.f_cash_pengembalian.value = Math.round(document.Form1.f_cash_pembayaran.value) - Math.round(document.Form1.total_pembulatan1.value) ;     \n";
    echo "        \n";
    echo "   }\n";
    echo "}\n";
	//--end cash pembayaran
	
	
    echo "</script>\n";

    echo "<table border=0 width='100%' cellspacing=0 cellpadding=0>";
    echo "<tr><td valign=top width='50%'>";

    $t = new Form($SC, "GET", "NAME=Form1");
    $t->PgConn = $con;
    $t->hidden("p",
            $PID);
    $t->hidden("mPERIODE",
            $_GET["mPERIODE"]);
    $t->hidden("rg",
            $_GET["rg"]);
    $t->hidden("sub",
            $_GET["sub"]);
    $t->hidden("e",
            "edit");
    //   $t->hidden("bayar",$_GET["bayar"]);
    $t->hidden("kas",
            $_GET["kas"]);

    $t->hidden("tmp_tagihan",
            number_format($tagihan,'0','',''));
######################################################################################efrizal	
    $t->selectSQL("mCAB",
            "Cara Pembayaran",
            "select '' as tc, '' as tdesc union " .
            "select a.tc , a.tdesc " .
            "from rs00001 a, rs00006 b " .
            //"where a.tt='CAB' and b.id ='" . $_GET["rg"] . "'  and case when b.tipe = '013' then (a.tc='001' or a.tc='012') else a.tc='001' end ",
            "where a.tt='CAB' and a.tc not in ('000', '002', '003', '004', '009', '010', '011') order by tc ASC",
            ($_GET["mCAB"])
                    ? $_GET[mCAB]
                    : "001",
            "OnChange=\"setciu(this.value);\"");
######################################################################################efrizal
    $t->hidden("f_no_kartu",
            "0000");
    $t->selectSQL("mKELUAR",
            "Status Akhir Pasien",
            "select '' as tc, '' as tdesc union " .
            "select tc , tdesc " .
            "from rs00001 " .
            "where tt='SAP' and tc!='000'",
            ($_GET["mKELUAR"])
                    ? $_GET[mSAP]
                    : $d->status_akhir_pasien,
            " disabled");

    $t->selectDate("tanggal",
            "Tanggal",
            getdate(),
            "disabled");


    $t->text("f_bayar",
            "Nama Pembayar",
            25,
            25,
            "");
    $t->text("askes",
            "Dibayarkan Penjamin",
            25,
            25,
            number_format($totalPenjamin, '0','',''),
            " style='text-align:right' disabled onchange='hitung1()' ");//original=>readonly=readonly style='text-align:right' onchange='hitung1()' 
    $t->text("keringanan",
            "Potongan Pembayaran",
            25,
            25,
            "0",
            "style='text-align:right' onchange='hitung1()' ");
    $t->text("keterangan_potongan",
	    "Keterangan Potongan", 
	    25,
            100, 
            getFromTable("SELECT keterangan FROM rs00005 WHERE reg='".$_GET['rg']."' AND kasir = 'POT'"),
            "");
    $t->text("bayar1",
            "Pembayaran",
            25,
            25,
            number_format($tagihan,'0','',''),
            "style='text-align:right'  onchange='hitung2()' ");
    $t->hidden("bayar",
            number_format($tagihan,'0','',''));
    $t->text("pembulatan1",
            "Pembulatan",
            25,
            25,
            number_format($pembulatan,'0','',''),
            "style='text-align:right' ");
    $t->hidden("pembulatan",
            number_format($pembulatan,'0','',''));
    $t->text("total_pembulatan1",
            "Total Pembayaran",
            25,
            25,
            number_format($total_pembulatan,'0','',''),
            "style='text-align:right' ");
	$t->hidden("total_pembulatan",
            number_format($total_pembulatan,'0','',''));
    $t->text("sisa",
            "SISA",
            25,
            25,
            0,
            "style='text-align:right' disabled");

    if ($cek_status_bayar != 'LUNAS') {
		
		//1xxx
		$t->text("f_cash_pengembalian",
				"<font color='red'>KEMBALI",
				25,
				25,
				"0",
				"style='text-align:right;' id=f_cash_pengembalian ");
		$t->text("f_cash_pembayaran",
				"<font color='red'>TUNAI",
				25,
				25,
				"",
				"style='text-align:right;' required id=f_cash_pembayaran onchange='hitung_cash()' ");
		//2xxx
	
        $t->submit(" BAYAR ",
                "HREF='index2.php" .
                "?p=$PID&e=edit&mPERIODE=" . $_GET["mPERIODE"] .
                "&rg=" . $_GET["rg"] .
                "&bayar=" . $_GET["bayar"] .
                "&cetak=Y" .
                "&sub=" . $_GET["sub"] . "'");
        // end of -- 24-12-2006
    }
    $t->execute();
####################################################################################efrizal
    $zal = getfromtable("select no_asuransi from rs00006 where id ='" . $_GET["rg"] . "' ");
    $efr = getfromtable("select case when '" . $tagihan . "' <= plafon_sisa then '" . $tagihan . "' else plafon_sisa end 
										from ciu where no_asuransi ='" . $zal . "' ");
    ?>       <SCRIPT language="JavaScript">

            function setciu( v )
            {
                if (v == "012") {
                    document.Form1.keringanan.value = <?= $efr ?>;
                    document.Form1.bayar.value = <?= $tagihan - $efr ?>;
                }else{
                    document.Form1.keringanan.value = "0";
                    document.Form1.bayar.value = <?= $tagihan ?>;
                }
            }
    </SCRIPT><?
########################################################################################efrizal
    echo "</td><td align=right valign=top>";

    echo "\n<script language='JavaScript'>\n";
    echo "function cetakaja(tag) {\n";
    echo "    sWin = window.open('includes/cetak.335.4.php?rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";

    echo "\n<script language='JavaScript'>\n";
    echo "function cetakrincianpenjamin(tag) {\n";
    $cetak_kwitansi = ($_GET['kas']=='ri') ? 'cetak.rincian_penjamin_ri.php': 'cetak.rincian_penjamin.php';
    echo "    sWin = window.open('includes/".$cetak_kwitansi."?p=cetak.rincian&rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";

    echo "\n<script language='JavaScript'>\n";
    echo "function cetakrincian(tag) {\n";
    $cetak_kwitansi = ($_GET['kas']=='ri') ? 'cetak.rincian_ri.php': 'cetak.rincian.php';
    echo "    sWin = window.open('includes/".$cetak_kwitansi."?p=cetak.rincian&rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";

    echo "\n<script language='JavaScript'>\n";
    echo "function cetakkwitansipembayaran(tag) {\n";
    echo "    sWin = window.open('includes/cetak.kwitansi_pembayaran.php?p=cetak.rincian&rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";

    echo "\n<script language='JavaScript'>\n";
    echo "function cetakrinciansementara(tag) {\n";
    echo "    sWin = window.open('includes/cetak.sementara.php?rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";
    
    echo "\n<script language='JavaScript'>\n";
    echo "function cetakrincianlayanan(tag) {\n";
    echo "    sWin = window.open('includes/cetak.rincian_layanan.php?p=cetak.rincian&rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";
    
    echo "\n<script language='JavaScript'>\n";
    echo "function cetakrincianobat(tag) {\n";
    echo "    sWin = window.open('includes/cetak.rincian_obat.php?p=cetak.rincian&rg=' + tag+'&kas=" . $_GET["kas"] . "', 'xWin'," .
    " 'top=0,left=0,width=750,height=550,menubar=no,scrollbars=yes');\n";
    echo "    sWin.focus();\n";
    echo "}\n";
    echo "</script>\n";
    ?>
    <table>
        <tr>
            <?php if ($totalPenjamin) { ?>
                <td align="center"> Cetak Kwitansi Penjamin</td>
            <?php } ?>
            <? //if ($tagihan <= 1) { ?>
                <td align="center"> Cetak Kwitansi</td>
                <td align="center"> &nbsp; </td>
            <? //} ?>
            <td align="center"> Cetak Kwitansi Sementara</td>
            <td align="center"> &nbsp; </td>
            <td align="center">Cetak Rincian</td>
        </tr>

        <tr>
            <?php if ($totalPenjamin) { ?>
                <td align="center"> <a href="javascript: cetakrincianpenjamin('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
            <?php } ?>
            <? //if ($tagihan <= 1) { ?>
                <td align="center"> <a href="javascript: cetakrincian('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
                <td align="center">  &nbsp; </td>
            <? //} ?>
            <td align="center"> <a href="javascript: cetakrinciansementara('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
            <td align="center"> &nbsp;</td>
            <td align="center"><a href="javascript: cetakaja('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"><? //echo $_GET[rg];    ?></a></td>
        </tr>
        
        <tr> 
            <? if ((int)$tagihan <= 1) { ?>
                <td align="center"> Cetak Kwitansi Penjamin Pembayaran<br/><a href="javascript: cetakkwitansipembayaran('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
                <td align="center">&nbsp; </td>
            <? } ?>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
        </tr>
        
        <tr>
            <td align="center">Cetak Rincian Layanan<br/><a href="javascript: cetakrincianlayanan('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
            <td align="center">&nbsp;</td>
            <td align="center">Cetak Rincian Obat<br/><a href="javascript: cetakrincianobat('<? echo $_GET[rg]; ?>')" ><img src="images/cetak.gif" border="0"></a></td>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
        </tr>
    </table>

    <?
    echo "</td></tr></table>";
}
?>

<script>
	addEvent(document.getElementById('f_cash_pembayaran'),'keyup',validate);
	addEvent(document.getElementById('f_cash_pembayaran'),'mouseover',validate);

	function validate(event){
		
		var str=this.value;
		
		var charsAllowed="0123456789";
		var allowed;
		
		for(var i=0;i<this.value.length;i++){
			
			allowed=false;
			
			for(var j=0;j<charsAllowed.length;j++){
				if( this.value.charAt(i)==charsAllowed.charAt(j) ){ allowed=true; }
			}
			
			if(allowed==false){ this.value = this.value.replace(this.value.charAt(i),""); i--; }
		}
		
		return true;
	}

	function addEvent(obj,type,fn) {
	 
		if (obj.addEventListener) {
			obj.addEventListener(type,fn,false);
			return true;
		} else if (obj.attachEvent) {
			obj['e'+type+fn] = fn;
			obj[type+fn] = function() { obj['e'+type+fn]( window.event );}
			var r = obj.attachEvent('on'+type, obj[type+fn]);
			return r;
		} else {
			obj['on'+type] = fn;
			return true;
		}
	}
</script>
